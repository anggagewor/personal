# Implementation Plan: POS (Point of Sale) Module

## Overview

The POS module is a large DDD module (`src/Modules/Pos/`) providing multi-format point-of-sale capabilities. Implementation follows a bottom-up approach: database → domain → application → infrastructure → frontend. Tasks are organized by sub-module with proper dependency ordering.

## Tasks

- [x] 1. Database Migrations
  - [x] 1.1 Create all POS database migrations
    - Create migration files for all 15 tables (pos_outlets, pos_categories, pos_products, pos_product_variants, pos_stock_adjustments, pos_payment_methods, pos_transactions, pos_transaction_items, pos_discounts, pos_vouchers, pos_voucher_redemptions, pos_tables, pos_table_sessions, pos_order_queue, pos_members)
    - All columns use `string` for enum-like fields, `unsignedBigInteger` without FK constraints, `decimal(15,2)` for monetary values
    - Add all indexes as specified in design (unique constraints, composite indexes)
    - _Requirements: 1.1, 2.1, 3.1, 4.1, 5.6, 6.1, 7.1, 8.1, 9.1, 13.1, 15.1_

- [x] 2. Domain Layer — Enums & Exceptions
  - [x] 2.1 Create all POS domain enums
    - Create `src/Modules/Pos/Domain/Enums/` with: BusinessType, ProductStatus, TransactionStatus, DiscountType, PaymentMethodType, PaymentFlowMode, StockAdjustmentType, OrderStatus, TableSessionStatus
    - Each enum is a PHP native `enum` backed by `string`
    - _Requirements: 1.2, 3.4, 5.6, 6.1, 7.2, 15.1_

  - [x] 2.2 Create all POS domain exceptions
    - Create `src/Modules/Pos/Domain/Exceptions/` with: InsufficientStockException, InvalidVoucherException, InvalidStockAdjustmentException, DuplicateProductException, DuplicateCategoryException, VoidNotAllowedException
    - Each exception extends base `\DomainException` or `\RuntimeException`
    - _Requirements: 2.5, 3.7, 4.5, 4.7, 8.3, 12.2_

- [x] 3. Domain Layer — Entities & Contracts
  - [x] 3.1 Create domain entities
    - Create `src/Modules/Pos/Domain/Entities/` with: Outlet, Category, Product, ProductVariant, StockAdjustment, Transaction, TransactionItem, Discount, Voucher, VoucherRedemption, Table, TableSession, OrderQueue, PaymentMethod, Member
    - Pure PHP classes with business rules (no Laravel dependency)
    - Include methods: `Outlet::supportsTableOrdering()`, `Outlet::supportsPayLater()`, `Transaction::isOverdue()`, `Transaction::canVoidWithoutConfirmation()`, `Product::isActive()`
    - _Requirements: 1.1, 2.1, 3.1, 4.1, 5.1, 6.1, 7.1, 8.1, 9.1, 13.1, 15.1_

  - [x] 3.2 Create repository contracts (interfaces)
    - Create `src/Modules/Pos/Domain/Contracts/` with: OutletRepositoryInterface, CategoryRepositoryInterface, ProductRepositoryInterface, TransactionRepositoryInterface, DiscountRepositoryInterface, VoucherRepositoryInterface, TableRepositoryInterface, MemberRepositoryInterface, ReportRepositoryInterface
    - Define all method signatures as specified in design
    - _Requirements: All_

- [x] 4. Infrastructure Layer — Models & Repositories
  - [x] 4.1 Create Eloquent models
    - Create `src/Modules/Pos/Infrastructure/Models/` with all 15 models: OutletModel, CategoryModel, ProductModel, ProductVariantModel, TransactionModel, TransactionItemModel, DiscountModel, VoucherModel, VoucherRedemptionModel, PaymentMethodModel, StockAdjustmentModel, MemberModel, TableModel, TableSessionModel, OrderQueueModel
    - Define fillable, casts (enums as string, json, decimal), relationships, scopes
    - OutletModel uses SoftDeletes
    - _Requirements: All_

  - [x] 4.2 Create Eloquent repositories — Outlet, Category, Product
    - Implement EloquentOutletRepository, EloquentCategoryRepository, EloquentProductRepository
    - Map between Entity ↔ Model
    - Include stock management methods (adjustStock, decrementStock, getStockLevel)
    - Include duplicate name checks (existsByName)
    - _Requirements: 1.1-1.5, 2.1-2.5, 3.1-3.8, 4.1-4.7_

  - [x] 4.3 Create Eloquent repositories — Transaction, Discount, Voucher
    - Implement EloquentTransactionRepository, EloquentDiscountRepository, EloquentVoucherRepository
    - Transaction: generateTransactionNumber (TRX-{YYMMDD}-{SEQ}), findOpenBills, closeOpenBill
    - Discount: findApplicable with member_only filtering
    - Voucher: incrementUsage, recordRedemption, batchCreate
    - _Requirements: 5.1-5.7, 7.1-7.10, 8.1-8.6, 15.1-15.8_

  - [x] 4.4 Create Eloquent repositories — Table, Member, Report
    - Implement EloquentTableRepository, EloquentMemberRepository, EloquentReportRepository
    - Table: createSession, closeSession, addToOrderQueue, findPendingOrders
    - Member: search by name/phone, paginated listing
    - Report: getDailySummary, getDateRangeSummary, getProductRanking, getRevenueByPaymentMethod, getDashboardStats
    - _Requirements: 9.1-9.10, 10.1-10.7, 13.1-13.8_

  - [x] 4.5 Create PosServiceProvider and register module
    - Create `src/Modules/Pos/Infrastructure/Providers/PosServiceProvider.php`
    - Bind all repository interfaces to Eloquent implementations
    - Register authenticated routes (`Routes/api.php`) and public routes (`Routes/public.php`)
    - Register provider in `bootstrap/providers.php`
    - _Requirements: All_

- [x] 5. Application Layer — DTOs
  - [x] 5.1 Create all Data Transfer Objects
    - Create `src/Modules/Pos/Application/DTO/` with: OutletData, CategoryData, ProductData, ProductVariantData, CheckoutData, LineItemData, DiscountData, VoucherData, StockAdjustmentData, MemberData, QrOrderData
    - All DTOs are `readonly class` with typed properties
    - _Requirements: All_

- [x] 6. Application Layer — Actions (Outlet & Catalog)
  - [x] 6.1 Create Outlet actions
    - CreateOutletAction, UpdateOutletAction, DeleteOutletAction
    - DeleteOutlet performs soft-delete
    - _Requirements: 1.1-1.5_

  - [x] 6.2 Create Catalog actions
    - CreateCategoryAction (with duplicate name check), UpdateCategoryAction, DeleteCategoryAction (reassign products to Uncategorized), ReorderCategoryAction
    - CreateProductAction (with duplicate check, SKU auto-generation, default variant creation), UpdateProductAction, DeactivateProductAction, AdjustStockAction (reject zero quantity)
    - _Requirements: 2.1-2.5, 3.1-3.8, 4.1-4.7_

- [x] 7. Application Layer — Actions (Transaction & Payment)
  - [x] 7.1 Create Transaction actions
    - CreateTransactionAction: validate stock, determine flow (pay-first/pay-later), decrement stock, link member (with fallback to walk-in), apply discounts/voucher, generate transaction number
    - VoidTransactionAction: restore stock, validate void rules (24h threshold), mark as voided
    - CloseOpenBillAction: validate pending status, record payment, transition to completed
    - GenerateReceiptAction: format receipt with all required fields (thermal/PDF)
    - _Requirements: 5.1-5.7, 6.1-6.4, 11.1-11.4, 12.1-12.4, 13.5-13.8, 15.2-15.5_

  - [x] 7.2 Create Discount and Voucher actions
    - CreateDiscountAction, UpdateDiscountAction, DeleteDiscountAction
    - EvaluateDiscountsAction: check active, date range, min purchase, member_only condition (in order), apply by priority, graceful degradation on priority failure
    - CreateVoucherAction, BatchCreateVoucherAction (unique codes with prefix), RedeemVoucherAction (validate expiry, usage, min purchase), ValidateVoucherAction
    - _Requirements: 7.1-7.10, 8.1-8.6, 14.1-14.4_

- [x] 8. Application Layer — Actions (Member & QR Order)
  - [x] 8.1 Create Member actions
    - CreateMemberAction, UpdateMemberAction, DeleteMemberAction (preserve transaction history)
    - _Requirements: 13.1-13.4_

  - [x] 8.2 Create QR Order actions
    - CreateTableAction (generate unique token), GenerateQrCodeAction
    - SubmitOrderAction (always pay-later, add to order queue)
    - AcceptOrderAction (create transaction from order), CloseTableSessionAction
    - Business type validation (only kafe/warkop)
    - _Requirements: 9.1-9.10, 15.8_

  - [x] 8.3 Create Report queries
    - DailySalesReportQuery, DateRangeSalesReportQuery, ProductRankingQuery, RevenueByPaymentMethodQuery, OpenBillsQuery
    - Exclude voided transactions from all aggregations
    - Return zero values for empty periods (not null)
    - _Requirements: 10.1-10.7, 15.7_

- [x] 9. Checkpoint — Backend domain and application layers
  - Ensure all tests pass, ask the user if questions arise.

- [ ] 10. Infrastructure Layer — Form Requests
  - [x] 10.1 Create all FormRequest validation classes
    - Create `src/Modules/Pos/Infrastructure/Requests/` with all 19 request classes
    - StoreOutletRequest, UpdateOutletRequest, StoreCategoryRequest, UpdateCategoryRequest, StoreProductRequest, UpdateProductRequest, StoreStockAdjustmentRequest, StoreTransactionRequest, VoidTransactionRequest, CloseOpenBillRequest, StoreDiscountRequest, UpdateDiscountRequest, StoreVoucherRequest, BatchStoreVoucherRequest, RedeemVoucherRequest, StoreMemberRequest, UpdateMemberRequest, StoreTableRequest, SubmitQrOrderRequest
    - Use `Rule::enum()` for enum fields stored as string
    - _Requirements: All_

- [ ] 11. Infrastructure Layer — API Resources
  - [~] 11.1 Create all API resource classes
    - Create `src/Modules/Pos/Infrastructure/Resources/` with: OutletResource, CategoryResource, ProductResource, ProductVariantResource, TransactionResource, TransactionItemResource, DiscountResource, VoucherResource, MemberResource, TableResource, OrderQueueResource, ReportResource, ReceiptResource
    - Follow API response format: `{ data, message, meta }`
    - _Requirements: All_

- [ ] 12. Infrastructure Layer — Controllers & Routes
  - [~] 12.1 Create controllers — Outlet, Category, Product, Stock
    - OutletController (index, store, update, destroy)
    - CategoryController (index, store, update, destroy, reorder)
    - ProductController (index, store, show, update, deactivate)
    - StockController (store, index)
    - Use AuthorizesOwnership trait, thin controllers calling Actions
    - _Requirements: 1.1-1.5, 2.1-2.5, 3.1-3.8, 4.1-4.7_

  - [~] 12.2 Create controllers — Transaction, OpenBill, PaymentMethod
    - TransactionController (index, store, show, void)
    - OpenBillController (index, close)
    - PaymentMethodController (index, store, update, destroy)
    - _Requirements: 5.1-5.7, 6.1-6.4, 12.1-12.4, 15.1-15.8_

  - [~] 12.3 Create controllers — Discount, Voucher, Member
    - DiscountController (index, store, update, destroy, evaluate)
    - VoucherController (index, store, batchStore, show, validate)
    - MemberController (index, store, show, update, destroy, search)
    - _Requirements: 7.1-7.10, 8.1-8.6, 13.1-13.8, 14.1-14.4_

  - [~] 12.4 Create controllers — Table, OrderQueue, Report, Receipt, QrOrderPublic
    - TableController (index, store, destroy, closeSession)
    - OrderQueueController (index, accept)
    - ReportController (daily, range, products, payments, export, dashboard)
    - ReceiptController (show, updateTemplate)
    - QrOrderPublicController (menu, submitOrder, orderStatus) — no auth middleware
    - _Requirements: 9.1-9.10, 10.1-10.7, 11.1-11.4_

  - [~] 12.5 Create route files
    - `src/Modules/Pos/Infrastructure/Routes/api.php` — all authenticated routes under `pos/` prefix with `auth:sanctum` middleware
    - `src/Modules/Pos/Infrastructure/Routes/public.php` — QR order public routes under `pos/qr/` prefix (no auth)
    - _Requirements: All_

- [~] 13. Checkpoint — Backend complete
  - Ensure all tests pass, ask the user if questions arise.

- [ ] 14. Frontend — Types & API Layer
  - [~] 14.1 Create POS TypeScript types
    - Create `resources/js/types/pos.ts` with all interfaces: Outlet, OutletSettings, Category, Product, ProductVariant, CartItem, CheckoutPayload, Transaction, TransactionItem, Discount, Voucher, PosTable, TableSession, OrderQueueItem, QrOrderItem, PaymentMethod, DailySummary, DashboardStats, Member, MemberPayload, OpenBill, CloseOpenBillPayload
    - _Requirements: All_

  - [~] 14.2 Create POS API layer
    - Create `resources/js/api/pos.ts` with all API call functions using `@purdia/http`
    - Organize by sub-module: outlets, categories, products, stock, transactions, openBills, discounts, vouchers, members, tables, orders, reports, receipts, qrOrder (public)
    - _Requirements: All_

- [ ] 15. Frontend — Pinia Cart Store
  - [~] 15.1 Create POS cart Pinia store
    - Create `resources/js/stores/pos-cart.ts`
    - Ephemeral cart state: items, member linking, voucher, discount selections, payment flow mode
    - Actions: addItem, removeItem, updateQuantity, setMember, applyVoucher, clearCart
    - Getters: subtotal, discountTotal, total, itemCount
    - _Requirements: 5.1-5.4_

- [ ] 16. Frontend — Outlet & Catalog Pages
  - [~] 16.1 Create POS index and outlet pages
    - `pages/pos/Index.vue` — module entry, outlet selection/creation prompt
    - `pages/pos/outlet/Setup.vue` — outlet create/edit form (name, business type, payment flow, address, phone)
    - `pages/pos/outlet/Settings.vue` — payment methods config, receipt template settings
    - _Requirements: 1.1-1.5, 6.3, 11.1_

  - [~] 16.2 Create catalog management pages
    - `pages/pos/catalog/Index.vue` — product catalog management with category sidebar
    - `pages/pos/catalog/CategoryList.vue` — draggable category list with reordering
    - `pages/pos/catalog/ProductForm.vue` — create/edit product + variants modal
    - `pages/pos/catalog/StockAdjustment.vue` — stock adjustment modal
    - _Requirements: 2.1-2.5, 3.1-3.8, 4.1-4.7_

- [ ] 17. Frontend — Cashier Interface
  - [~] 17.1 Create main POS cashier page
    - `pages/pos/cashier/Index.vue` — main POS cashier layout (product grid + cart panel)
    - `pages/pos/cashier/ProductGrid.vue` — product selection grid with category tabs and search
    - `pages/pos/cashier/CartPanel.vue` — cart sidebar with line items, quantity controls, subtotal
    - `pages/pos/cashier/CheckoutModal.vue` — payment flow selection, member linking, voucher input, payment method, amount tendered/change
    - `pages/pos/cashier/ReceiptPreview.vue` — receipt display with print action
    - _Requirements: 5.1-5.7, 6.1-6.4, 13.5-13.6, 15.2-15.5_

- [ ] 18. Frontend — Discount & Voucher Pages
  - [~] 18.1 Create discount management pages
    - `pages/pos/discount/Index.vue` — discount rules list with active/inactive toggle
    - `pages/pos/discount/DiscountForm.vue` — create/edit discount rule (type, value, conditions, member_only, priority, date range)
    - _Requirements: 7.1-7.10, 14.1-14.4_

  - [~] 18.2 Create voucher management pages
    - `pages/pos/voucher/Index.vue` — voucher list with stats (usage, remaining)
    - `pages/pos/voucher/VoucherForm.vue` — create single/batch voucher (code, type, value, limits, expiry)
    - `pages/pos/voucher/VoucherDetail.vue` — voucher detail with redemption history
    - _Requirements: 8.1-8.6_

- [ ] 19. Frontend — Tables & QR Order Pages
  - [~] 19.1 Create table management and order queue pages
    - `pages/pos/tables/Index.vue` — table list with QR code generation, session status
    - `pages/pos/tables/OrderQueue.vue` — incoming orders panel with accept action
    - `pages/pos/tables/QrCodeDisplay.vue` — QR code print view
    - _Requirements: 9.1-9.7, 9.8_

  - [~] 19.2 Create public QR order pages
    - `pages/pos/qr-order/Menu.vue` — public menu page (no DashboardLayout, no auth)
    - `pages/pos/qr-order/Cart.vue` — customer cart on phone
    - `pages/pos/qr-order/OrderStatus.vue` — order status after submission
    - Register public routes without auth guard
    - _Requirements: 9.2-9.5, 9.9, 15.8_

- [ ] 20. Frontend — Transaction History, Members & Open Bills
  - [~] 20.1 Create transaction history pages
    - `pages/pos/transactions/Index.vue` — transaction history (paginated, filterable by date/status/payment)
    - `pages/pos/transactions/TransactionDetail.vue` — detail view with void action
    - `pages/pos/transactions/VoidModal.vue` — void confirmation with reason input (required for >24h)
    - _Requirements: 12.1-12.4_

  - [~] 20.2 Create member management pages
    - `pages/pos/members/Index.vue` — member list (paginated, searchable by name/phone)
    - `pages/pos/members/MemberForm.vue` — create/edit member modal (name, phone, email)
    - _Requirements: 13.1-13.4, 13.7_

  - [~] 20.3 Create open bills pages
    - `pages/pos/open-bills/Index.vue` — open bills list with overdue highlighting (>24h)
    - `pages/pos/open-bills/CloseBillModal.vue` — close bill with payment method selection and amount tendered
    - _Requirements: 15.3-15.7_

- [ ] 21. Frontend — Reports & Dashboard
  - [~] 21.1 Create reports pages
    - `pages/pos/reports/Index.vue` — reports dashboard with navigation
    - `pages/pos/reports/DailyReport.vue` — daily sales summary (revenue, count, avg, top products)
    - `pages/pos/reports/ProductRanking.vue` — product performance table
    - `pages/pos/reports/RevenueTrend.vue` — 7-day revenue chart using `@purdia/charts` LineChart
    - CSV export button on each report view
    - Display zero values for empty periods
    - _Requirements: 10.1-10.7_

- [ ] 22. Frontend — Router Integration
  - [~] 22.1 Register all POS routes in the frontend router
    - Add all POS page routes to `resources/js/router/index.ts` (lazy-loaded)
    - Protected routes with DashboardLayout
    - Public QR order routes with `meta: { guest: true }` (no layout)
    - Add POS entry to sidebar navigation config
    - _Requirements: All_

- [~] 23. Checkpoint — Frontend complete
  - Ensure all tests pass, ask the user if questions arise.

- [ ] 24. Feature Tests — Backend
  - [~] 24.1 Write feature tests for Outlet and Catalog CRUD
    - `tests/Feature/Pos/OutletTest.php` — CRUD, soft-delete, ownership
    - `tests/Feature/Pos/CategoryTest.php` — CRUD, reorder, duplicate name rejection, deletion with product reassignment
    - `tests/Feature/Pos/ProductTest.php` — CRUD, variants, deactivation, duplicate name, SKU generation, search
    - `tests/Feature/Pos/StockTest.php` — adjustments, zero-quantity rejection, stock levels
    - _Requirements: 1.1-1.5, 2.1-2.5, 3.1-3.8, 4.1-4.7_

  - [~] 24.2 Write feature tests for Transaction, Void, and Payment Flow
    - `tests/Feature/Pos/TransactionTest.php` — checkout flow, stock decrement, transaction number uniqueness
    - `tests/Feature/Pos/VoidTransactionTest.php` — void with stock restore, 24h threshold, void reason
    - `tests/Feature/Pos/PaymentFlowTest.php` — pay-first validation, pay-later open bill creation, both mode
    - `tests/Feature/Pos/OpenBillTest.php` — list open bills, close with payment, overdue detection
    - _Requirements: 5.1-5.7, 6.1-6.4, 12.1-12.4, 15.1-15.8_

  - [~] 24.3 Write feature tests for Discount, Voucher, and Member
    - `tests/Feature/Pos/DiscountTest.php` — CRUD, evaluate applicable discounts
    - `tests/Feature/Pos/MemberDiscountTest.php` — member_only filtering, walk-in exclusion, evaluation order
    - `tests/Feature/Pos/VoucherTest.php` — CRUD, validate, redeem, batch create, expiry/usage checks
    - `tests/Feature/Pos/MemberTest.php` — CRUD, search, deletion preserves history, linking fallback
    - _Requirements: 7.1-7.10, 8.1-8.6, 13.1-13.8, 14.1-14.4_

  - [~] 24.4 Write feature tests for QR Order and Reports
    - `tests/Feature/Pos/TableOrderTest.php` — table CRUD, session management, business type restriction
    - `tests/Feature/Pos/QrOrderPublicTest.php` — public menu, submit order, order status (no auth)
    - `tests/Feature/Pos/ReportTest.php` — daily summary, date range, product ranking, payment breakdown, zero values
    - `tests/Feature/Pos/ReceiptTest.php` — receipt generation, required fields, reprint consistency
    - _Requirements: 9.1-9.10, 10.1-10.7, 11.1-11.4_

- [ ] 25. Property-Based Tests
  - [~] 25.1 Write property test for cart calculation invariant
    - **Property 1: Cart total invariant**
    - **Validates: Requirements 5.2, 5.3, 5.4**

  - [~] 25.2 Write property tests for stock management
    - **Property 2: Stock decrement on transaction**
    - **Property 3: Stock validation rejects overselling**
    - **Property 4: Stock-tracking-disabled allows unlimited sales**
    - **Property 5: Stock adjustment correctness**
    - **Property 37: Zero-quantity stock adjustment rejected**
    - **Validates: Requirements 4.2, 4.4, 4.5, 4.6, 4.7**

  - [~] 25.3 Write property tests for discount evaluation
    - **Property 7: Discount eligibility evaluation**
    - **Property 8: Discount priority ordering**
    - **Property 26: Member-only discount exclusion**
    - **Property 27: Non-member-only discounts apply universally**
    - **Property 28: Member-only condition evaluation order**
    - **Property 39: Discount graceful degradation on priority failure**
    - **Validates: Requirements 7.3-7.10, 14.2-14.4**

  - [~] 25.4 Write property tests for voucher validation
    - **Property 9: Voucher validation completeness**
    - **Property 10: Voucher usage counter increment**
    - **Property 11: Batch voucher uniqueness**
    - **Validates: Requirements 8.2-8.5**

  - [~] 25.5 Write property tests for uniqueness constraints
    - **Property 12: Category uniqueness constraint**
    - **Property 13: Product uniqueness constraint**
    - **Property 15: SKU auto-generation uniqueness**
    - **Property 19: Transaction number uniqueness**
    - **Property 23: QR table token uniqueness**
    - **Validates: Requirements 2.5, 3.5, 3.7, 5.6, 9.1**

  - [~] 25.6 Write property tests for payment flow and open bills
    - **Property 6: Cash change calculation**
    - **Property 29: Pay-first requires payment before confirmation**
    - **Property 30: Pay-later creates pending Open Bill**
    - **Property 31: Open Bill closure transitions to completed**
    - **Property 32: Open Bill overdue detection**
    - **Property 33: QR orders always use pay-later flow**
    - **Validates: Requirements 6.2, 15.2-15.4, 15.6, 15.8**

  - [~] 25.7 Write property tests for member management
    - **Property 34: Member linking failure fallback**
    - **Property 35: Member data persistence round-trip**
    - **Property 36: Member deletion preserves transaction history**
    - **Validates: Requirements 13.2-13.4, 13.8**

  - [~] 25.8 Write property tests for transaction void and reports
    - **Property 17: Transaction void restores stock**
    - **Property 18: Voided transactions excluded from reports**
    - **Property 38: Reports return zero values for empty periods**
    - **Validates: Requirements 10.1, 10.3, 10.4, 10.7, 12.2, 12.4**

  - [~] 25.9 Write property tests for receipts and product exclusion
    - **Property 14: Deactivated products excluded from active queries**
    - **Property 16: Product search completeness**
    - **Property 20: Receipt contains all required fields**
    - **Property 21: Receipt reprint consistency**
    - **Property 22: Table ordering restricted by business type**
    - **Property 24: Transaction history ordering**
    - **Property 25: Category deletion reassigns products**
    - **Property 40: Business type change invalidates QR codes**
    - **Validates: Requirements 2.4, 3.4, 3.6, 9.8, 9.10, 10.1, 11.2, 11.3, 12.1**

- [~] 26. Final Checkpoint
  - Ensure all tests pass, ask the user if questions arise.

## Notes

- Tasks marked with `*` are optional and can be skipped for faster MVP
- Each task references specific requirements for traceability
- Checkpoints ensure incremental validation at key milestones
- Property tests validate universal correctness properties from the design document
- Feature tests validate specific HTTP flows and edge cases
- The cart is ephemeral (Pinia store) — no server-side cart persistence
- QR order public pages do NOT use DashboardLayout and have no auth requirement
- All monetary values use `decimal(15,2)` for consistency with Finance module
- UI language is Bahasa Indonesia as per project convention
- Frontend imports use `@purdia/*` packages — no custom implementations for covered functionality

## Task Dependency Graph

```json
{
  "waves": [
    { "id": 0, "tasks": ["1.1"] },
    { "id": 1, "tasks": ["2.1", "2.2"] },
    { "id": 2, "tasks": ["3.1", "3.2"] },
    { "id": 3, "tasks": ["4.1"] },
    { "id": 4, "tasks": ["4.2", "4.3", "4.4"] },
    { "id": 5, "tasks": ["4.5", "5.1"] },
    { "id": 6, "tasks": ["6.1", "6.2"] },
    { "id": 7, "tasks": ["7.1", "7.2"] },
    { "id": 8, "tasks": ["8.1", "8.2", "8.3"] },
    { "id": 9, "tasks": ["10.1"] },
    { "id": 10, "tasks": ["11.1"] },
    { "id": 11, "tasks": ["12.1", "12.2", "12.3", "12.4"] },
    { "id": 12, "tasks": ["12.5"] },
    { "id": 13, "tasks": ["14.1"] },
    { "id": 14, "tasks": ["14.2", "15.1"] },
    { "id": 15, "tasks": ["16.1", "16.2"] },
    { "id": 16, "tasks": ["17.1"] },
    { "id": 17, "tasks": ["18.1", "18.2", "19.1", "19.2"] },
    { "id": 18, "tasks": ["20.1", "20.2", "20.3"] },
    { "id": 19, "tasks": ["21.1"] },
    { "id": 20, "tasks": ["22.1"] },
    { "id": 21, "tasks": ["24.1", "24.2", "24.3", "24.4"] },
    { "id": 22, "tasks": ["25.1", "25.2", "25.3", "25.4", "25.5", "25.6", "25.7", "25.8", "25.9"] }
  ]
}
```
