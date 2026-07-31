# Implementation Plan: Supplier Module

## Overview

The Supplier module (`src/Modules/Supplier/`) provides procurement and supplier management capabilities for the POS system. Implementation follows a bottom-up approach: database migrations → domain layer (enums, exceptions, entities, contracts) → infrastructure layer (models, repositories, service provider) → application layer (DTOs, actions, queries) → controllers & routes → frontend → tests. This mirrors the established POS module pattern.

## Tasks

- [x] 1. Database Migrations
  - [x] 1.1 Create all Supplier database migrations
    - Create migration files for all 6 tables: `supplier_suppliers`, `supplier_purchase_orders`, `supplier_purchase_order_items`, `supplier_goods_receipts`, `supplier_goods_receipt_items`, `supplier_payments`, `supplier_products`
    - All columns use `string` for enum-like fields, `unsignedBigInteger` without FK constraints, `decimal(15,2)` for monetary values
    - `supplier_suppliers` uses `softDeletes`
    - Add all indexes as specified in design: unique(`outlet_id`, `name`) with soft-delete consideration, composite indexes on `supplier_purchase_orders` (outlet_id, supplier_id, status, payment_status, po_number unique, order_date), unique(`supplier_id`, `product_variant_id`) on `supplier_products`
    - _Requirements: 1.1, 2.1, 4.1, 6.1, 7.1_

- [x] 2. Domain Layer — Enums & Exceptions
  - [x] 2.1 Create all Supplier domain enums
    - Create `src/Modules/Supplier/Domain/Enums/` with: `PurchaseOrderStatus` (draft, confirmed, partial, received, cancelled), `PaymentStatus` (unpaid, partial, paid), `PaymentMethod` (cash, bank_transfer, e_wallet)
    - Each enum is a PHP native `enum` backed by `string`
    - _Requirements: 2.5, 2.7, 3.1, 4.4, 6.1, 6.2_

  - [x] 2.2 Create all Supplier domain exceptions
    - Create `src/Modules/Supplier/Domain/Exceptions/` with: `DuplicateSupplierException`, `InvalidPurchaseOrderStateException`, `OverDeliveryException`, `OverPaymentException`, `EmptyPurchaseOrderException`
    - Each exception extends `\DomainException`
    - _Requirements: 1.6, 2.8, 2.9, 3.3, 4.8, 4.9, 6.3_

- [x] 3. Domain Layer — Entities & Contracts
  - [x] 3.1 Create all domain entities
    - Create `src/Modules/Supplier/Domain/Entities/` with: `Supplier`, `PurchaseOrder`, `PurchaseOrderItem`, `GoodsReceipt`, `GoodsReceiptItem`, `SupplierPayment`, `SupplierProduct`
    - Pure PHP classes with typed properties and business methods
    - `PurchaseOrder`: `isDraft()`, `isEditable()`, `isCancellable()`, `canReceiveGoods()`
    - `PurchaseOrderItem`: `getRemainingQuantity()`, `isFullyReceived()`
    - _Requirements: 1.1, 2.1, 2.5, 2.6, 3.1, 4.1, 6.1, 7.1_

  - [x] 3.2 Create all repository contracts (interfaces)
    - Create `src/Modules/Supplier/Domain/Contracts/` with: `SupplierRepositoryInterface`, `PurchaseOrderRepositoryInterface`, `GoodsReceiptRepositoryInterface`, `SupplierPaymentRepositoryInterface`, `SupplierProductRepositoryInterface`
    - Define all method signatures as specified in design document
    - _Requirements: All_

- [x] 4. Infrastructure Layer — Models & Repositories
  - [x] 4.1 Create all Eloquent models
    - Create `src/Modules/Supplier/Infrastructure/Models/` with: `SupplierModel`, `PurchaseOrderModel`, `PurchaseOrderItemModel`, `GoodsReceiptModel`, `GoodsReceiptItemModel`, `SupplierPaymentModel`, `SupplierProductModel`
    - Define fillable, casts (enums as string, decimal), relationships, scopes
    - `SupplierModel` uses `SoftDeletes`
    - _Requirements: All_

  - [x] 4.2 Create Eloquent repositories — Supplier & SupplierProduct
    - Implement `EloquentSupplierRepository` with: paginated listing, search (name/phone/email), create, update, soft-delete, name uniqueness check, total debt calculation
    - Implement `EloquentSupplierProductRepository` with: link/unlink, find by supplier, find by product variant, get default cost
    - _Requirements: 1.1–1.7, 7.1–7.6_

  - [x] 4.3 Create Eloquent repositories — PurchaseOrder
    - Implement `EloquentPurchaseOrderRepository` with: paginated listing with filters (status, payment_status, supplier, date range), create, update, status transitions, PO number generation (PO-{YYYYMMDD}-{SEQ}), outstanding balance calculation, total paid lookup
    - _Requirements: 2.1–2.10, 3.1–3.4, 5.1–5.5_

  - [x] 4.4 Create Eloquent repositories — GoodsReceipt & Payment
    - Implement `EloquentGoodsReceiptRepository` with: find by PO, create with items, get total received by item
    - Implement `EloquentSupplierPaymentRepository` with: find by PO, find by supplier (paginated), create, get total paid for PO
    - _Requirements: 4.1–4.9, 6.1–6.7_

  - [x] 4.5 Create SupplierServiceProvider and register module
    - Create `src/Modules/Supplier/Infrastructure/Providers/SupplierServiceProvider.php`
    - Bind all 5 repository interfaces to Eloquent implementations
    - Register routes (`Routes/api.php`)
    - Register provider in `bootstrap/providers.php`
    - _Requirements: All_

- [x] 5. Application Layer — DTOs
  - [x] 5.1 Create all Data Transfer Objects
    - Create `src/Modules/Supplier/Application/DTO/` with: `SupplierData`, `PurchaseOrderData`, `PurchaseOrderItemData`, `GoodsReceiptData`, `GoodsReceiptItemData`, `SupplierPaymentData`, `SupplierProductData`
    - All DTOs are `readonly class` with typed properties
    - _Requirements: All_

- [x] 6. Application Layer — Actions (Supplier & Product)
  - [x] 6.1 Create Supplier actions
    - `CreateSupplierAction`: validate name uniqueness within outlet, create supplier
    - `UpdateSupplierAction`: validate name uniqueness (exclude self), update supplier
    - `DeleteSupplierAction`: soft-delete supplier (preserve PO history)
    - _Requirements: 1.1–1.7_

  - [x] 6.2 Create SupplierProduct actions
    - `LinkProductAction`: link product variant to supplier with optional default_unit_cost, reject duplicate links
    - `UnlinkProductAction`: remove link without affecting historical PO data
    - _Requirements: 7.1–7.6_

- [x] 7. Application Layer — Actions (Purchase Order)
  - [x] 7.1 Create PurchaseOrder CRUD actions
    - `CreatePurchaseOrderAction`: create draft PO with items, auto-generate PO number, calculate total
    - `UpdatePurchaseOrderAction`: validate draft status, update fields and items, recalculate total
    - _Requirements: 2.1–2.6, 2.10_

  - [x] 7.2 Create PurchaseOrder lifecycle actions
    - `ConfirmPurchaseOrderAction`: validate draft status, reject empty PO (throw `EmptyPurchaseOrderException`), transition to confirmed
    - `CancelPurchaseOrderAction`: validate cancellable state (draft/confirmed only), transition to cancelled with timestamp, reject if partial/received (throw `InvalidPurchaseOrderStateException`)
    - _Requirements: 2.7–2.9, 3.1–3.4_

- [x] 8. Application Layer — Actions (Goods Receipt & Payment)
  - [x] 8.1 Create GoodsReceipt action
    - `CreateGoodsReceiptAction`: validate PO status (confirmed/partial only), validate no over-delivery per item, create receipt with items, update PO item `received_quantity`, determine and update PO status (partial/received), create POS stock adjustments (type "restock", reference PO)
    - _Requirements: 4.1–4.9_

  - [x] 8.2 Create Payment action
    - `RecordPaymentAction`: validate PO not cancelled, validate no overpayment (reject if amount > outstanding balance), create payment record, determine and update PO payment_status (unpaid/partial/paid)
    - _Requirements: 6.1–6.7_

- [x] 9. Application Layer — Queries
  - [x] 9.1 Create report queries
    - `PurchaseSummaryQuery`: total purchase value, total paid, total outstanding debt for date range (exclude cancelled POs)
    - `PurchaseBySupplierQuery`: grouped by supplier with totals and debt
    - `PurchaseByProductQuery`: grouped by product variant with total quantity and cost
    - `SupplierDashboardQuery`: total outstanding debt, pending PO count, recent POs
    - _Requirements: 5.5, 8.1–8.5_

- [x] 10. Checkpoint — Backend domain and application layers
  - Ensure all tests pass, ask the user if questions arise.

- [x] 11. Infrastructure Layer — Form Requests
  - [x] 11.1 Create all FormRequest validation classes
    - Create `src/Modules/Supplier/Infrastructure/Requests/` with: `StoreSupplierRequest`, `UpdateSupplierRequest`, `StorePurchaseOrderRequest`, `UpdatePurchaseOrderRequest`, `StoreGoodsReceiptRequest`, `StoreSupplierPaymentRequest`, `LinkProductRequest`, `ExportReportRequest`
    - Validate required fields, types, string lengths, enum values using `Rule::in()`
    - PO requests validate items array structure (product_variant_id, quantity > 0, unit_cost > 0)
    - Goods receipt validates items array (purchase_order_item_id, quantity > 0)
    - _Requirements: All_

- [x] 12. Infrastructure Layer — API Resources
  - [x] 12.1 Create all API resource classes
    - Create `src/Modules/Supplier/Infrastructure/Resources/` with: `SupplierResource`, `SupplierListResource`, `PurchaseOrderResource`, `PurchaseOrderListResource`, `PurchaseOrderItemResource`, `GoodsReceiptResource`, `SupplierPaymentResource`, `SupplierProductResource`, `PurchaseReportResource`
    - Follow API response format: `{ data, message, meta }`
    - `SupplierListResource` includes `total_debt` summary
    - `PurchaseOrderResource` includes items, total_paid, outstanding_balance
    - _Requirements: All_

- [x] 13. Infrastructure Layer — Controllers & Routes
  - [x] 13.1 Create controllers — Supplier & SupplierProduct
    - `SupplierController`: index (paginated + search), store, show (with debt summary), update, destroy (soft-delete), search (quick search endpoint)
    - `SupplierProductController`: index (list linked products), link, unlink
    - Thin controllers calling Actions, catch domain exceptions and return appropriate HTTP responses
    - _Requirements: 1.1–1.7, 7.1–7.6_

  - [x] 13.2 Create controllers — PurchaseOrder & GoodsReceipt
    - `PurchaseOrderController`: index (paginated + filters), store, show (with items/receipts/payments), update, confirm, cancel
    - `GoodsReceiptController`: index (list by PO), store (create receipt + stock update)
    - _Requirements: 2.1–2.10, 3.1–3.4, 4.1–4.9, 5.1–5.4_

  - [x] 13.3 Create controllers — Payment & Reports
    - `SupplierPaymentController`: indexByPO, indexBySupplier, store
    - `SupplierReportController`: summary, bySupplier, byProduct, export (CSV), dashboard
    - _Requirements: 5.5, 6.1–6.7, 8.1–8.5_

  - [x] 13.4 Create route file and register all endpoints
    - Create `src/Modules/Supplier/Infrastructure/Routes/api.php`
    - All 22 endpoints under `supplier/` prefix with `auth:sanctum` middleware
    - Route grouping: suppliers, purchase-orders, receipts (nested under PO), payments (nested under PO and supplier), products (nested under supplier), reports, dashboard
    - _Requirements: All_

- [x] 14. Checkpoint — Backend complete
  - Ensure all tests pass, ask the user if questions arise.

- [x] 15. Frontend — Types & API Layer
  - [x] 15.1 Create Supplier TypeScript types
    - Create `resources/js/types/supplier.ts` with all interfaces as specified in design: `Supplier`, `SupplierPayload`, `PurchaseOrder`, `PurchaseOrderItem`, `PurchaseOrderPayload`, `PurchaseOrderItemPayload`, `GoodsReceipt`, `GoodsReceiptItem`, `GoodsReceiptPayload`, `GoodsReceiptItemPayload`, `SupplierPayment`, `SupplierPaymentPayload`, `SupplierProduct`, `LinkProductPayload`, `PurchaseSummary`, `PurchaseBySupplier`, `PurchaseByProduct`, `SupplierDashboard`
    - _Requirements: All_

  - [x] 15.2 Create Supplier API layer
    - Create `resources/js/api/supplier.ts` with all API call functions using `@purdia/http`
    - Organize by sub-module: suppliers (CRUD + search), purchaseOrders (CRUD + confirm + cancel), goodsReceipts (list + create), payments (listByPO + listBySupplier + create), supplierProducts (list + link + unlink), reports (summary + bySupplier + byProduct + export + dashboard)
    - _Requirements: All_

- [x] 16. Frontend — Supplier & Dashboard Pages
  - [x] 16.1 Create Supplier module index / dashboard page
    - `pages/supplier/Index.vue` — Supplier module dashboard showing total outstanding debt, pending PO count, recent purchase orders
    - Uses `SupplierDashboardQuery` API
    - _Requirements: 8.5_

  - [x] 16.2 Create supplier list and detail pages
    - `pages/supplier/suppliers/Index.vue` — Paginated supplier list with search, showing name, phone, total debt
    - `pages/supplier/suppliers/Detail.vue` — Supplier detail view with info, linked products, outstanding debt, payment history
    - `pages/supplier/suppliers/SupplierForm.vue` — Create/edit supplier modal (name, address, phone, email, bank details, notes). Use `persistent` prop on BaseModal.
    - _Requirements: 1.1–1.7, 6.4, 6.5, 6.7, 7.3_

- [x] 17. Frontend — Purchase Order Pages
  - [x] 17.1 Create purchase order list and detail pages
    - `pages/supplier/purchase-orders/Index.vue` — PO list (paginated) with filters: supplier, status, payment status, date range. Sorted by date descending.
    - `pages/supplier/purchase-orders/Detail.vue` — PO detail showing PO number, supplier, dates, status, items with ordered/received qty, goods receipts list, payments list, total/paid/outstanding
    - _Requirements: 2.1–2.10, 5.1–5.4_

  - [x] 17.2 Create purchase order form page
    - `pages/supplier/purchase-orders/Create.vue` — Full page form for create/edit PO. Supplier selector, date fields, dynamic line items (product variant picker from POS catalog, quantity, unit cost with pre-fill from supplier-product link). Auto-calculates subtotals and total.
    - _Requirements: 2.1–2.6, 2.10, 7.2_

  - [x] 17.3 Create goods receipt form modal
    - `pages/supplier/purchase-orders/GoodsReceiptForm.vue` — Modal for recording goods receipt. Shows PO items with ordered/received/remaining quantities. Input received qty per item. Validates no over-delivery client-side. Uses `persistent` prop.
    - _Requirements: 4.1–4.9_

- [x] 18. Frontend — Payment & Reports Pages
  - [x] 18.1 Create payment form modal
    - `pages/supplier/payments/PaymentForm.vue` — Modal for recording payment against PO. Shows outstanding balance. Input: amount, payment date, payment method (cash/bank transfer/e-wallet), notes. Validates no overpayment client-side. Uses `persistent` prop.
    - _Requirements: 6.1–6.6_

  - [x] 18.2 Create purchase reports pages
    - `pages/supplier/reports/Index.vue` — Reports dashboard with date range filter
    - `pages/supplier/reports/PurchaseSummary.vue` — Summary cards: total purchase, total paid, total debt, purchase count. Grouped-by-supplier and grouped-by-product tables.
    - CSV export button
    - _Requirements: 8.1–8.4_

- [x] 19. Frontend — Router Integration
  - [x] 19.1 Register all Supplier routes in the frontend router
    - Add all Supplier page routes to `resources/js/router/index.ts` (lazy-loaded)
    - Protected routes with DashboardLayout
    - Add Supplier entry to sidebar navigation config
    - _Requirements: All_

- [x] 20. Checkpoint — Frontend complete
  - Ensure all tests pass, ask the user if questions arise.

- [x] 21. Feature Tests — Backend
  - [x] 21.1 Write feature tests for Supplier CRUD and search
    - `tests/Feature/Supplier/SupplierTest.php` — Create, read, update, soft-delete, duplicate name rejection, search by name/phone/email, pagination, outlet scoping, debt summary display
    - _Requirements: 1.1–1.7_

  - [x] 21.2 Write feature tests for Purchase Order lifecycle
    - `tests/Feature/Supplier/PurchaseOrderTest.php` — Create draft, edit draft, add/remove items, total calculation, PO number generation
    - `tests/Feature/Supplier/PurchaseOrderConfirmTest.php` — Confirm with items (success), confirm empty PO (fail), edit confirmed PO (fail)
    - `tests/Feature/Supplier/PurchaseOrderCancelTest.php` — Cancel draft (success), cancel confirmed (success), cancel partial (fail), cancel received (fail), cancelled PO excluded from debt
    - _Requirements: 2.1–2.10, 3.1–3.4_

  - [x] 21.3 Write feature tests for Goods Receiving
    - `tests/Feature/Supplier/GoodsReceiptTest.php` — Full receipt (all items), partial receipt, multiple receipts (split delivery), over-delivery rejection, receipt on draft PO (fail), receipt on cancelled PO (fail), PO status transitions (partial/received), stock adjustment creation verification
    - _Requirements: 4.1–4.9_

  - [x] 21.4 Write feature tests for Payment and Supplier-Product
    - `tests/Feature/Supplier/SupplierPaymentTest.php` — Single payment, installment payments, full payment, overpayment rejection, payment on cancelled PO (fail), payment status transitions (partial/paid)
    - `tests/Feature/Supplier/SupplierProductTest.php` — Link product, unlink product, duplicate link rejection, default cost lookup, unlink preserves PO history
    - _Requirements: 6.1–6.7, 7.1–7.6_

  - [x] 21.5 Write feature tests for Reports
    - `tests/Feature/Supplier/SupplierReportTest.php` — Purchase summary with date range, by-supplier grouping, by-product grouping, CSV export format, cancelled POs excluded, dashboard widget data
    - _Requirements: 5.5, 8.1–8.5_

- [x] 22. Property-Based Tests
  - [x] 22.1 Write property test for supplier data persistence
    - **Property 1: Supplier data persistence round-trip**
    - **Validates: Requirements 1.1, 1.2**

  - [x] 22.2 Write property test for supplier soft-delete
    - **Property 2: Supplier soft-delete preserves PO history**
    - **Validates: Requirements 1.3**

  - [x] 22.3 Write property test for supplier search completeness
    - **Property 3: Supplier search completeness**
    - **Validates: Requirements 1.4**

  - [x] 22.4 Write property test for supplier name uniqueness
    - **Property 4: Supplier name uniqueness constraint**
    - **Validates: Requirements 1.6**

  - [x] 22.5 Write property test for outlet scoping
    - **Property 5: Outlet scoping isolation**
    - **Validates: Requirements 1.7**

  - [x] 22.6 Write property test for PO number format
    - **Property 6: PO number format and uniqueness**
    - **Validates: Requirements 2.2**

  - [x] 22.7 Write property test for PO total calculation
    - **Property 7: PO total calculation invariant**
    - **Validates: Requirements 2.4**

  - [x] 22.8 Write property test for PO initial state
    - **Property 8: PO initial state invariant**
    - **Validates: Requirements 2.5, 2.7**

  - [x] 22.9 Write property test for PO editability by state
    - **Property 9: PO editability by state**
    - **Validates: Requirements 2.6, 2.8**

  - [x] 22.10 Write property test for PO confirmation requires items
    - **Property 10: PO confirmation requires line items**
    - **Validates: Requirements 2.7, 2.9**

  - [x] 22.11 Write property test for PO cancellation state guard
    - **Property 11: PO cancellation state guard**
    - **Validates: Requirements 3.1, 3.2, 3.3**

  - [x] 22.12 Write property test for cancelled PO excluded from debt
    - **Property 12: Cancelled PO excluded from debt**
    - **Validates: Requirements 3.4**

  - [x] 22.13 Write property test for goods receipt stock update
    - **Property 13: Goods receipt updates POS stock**
    - **Validates: Requirements 4.2**

  - [x] 22.14 Write property test for over-delivery prevention
    - **Property 14: Over-delivery prevention**
    - **Validates: Requirements 4.8**

  - [x] 22.15 Write property test for PO receiving status
    - **Property 15: PO receiving status determination**
    - **Validates: Requirements 4.4, 4.5**

  - [x] 22.16 Write property test for goods receipt state guard
    - **Property 16: Goods receipt state guard**
    - **Validates: Requirements 4.9**

  - [x] 22.17 Write property test for payment status determination
    - **Property 17: Payment status determination**
    - **Validates: Requirements 6.2**

  - [x] 22.18 Write property test for overpayment prevention
    - **Property 18: Overpayment prevention**
    - **Validates: Requirements 6.3**

  - [x] 22.19 Write property test for supplier outstanding debt
    - **Property 19: Supplier outstanding debt calculation**
    - **Validates: Requirements 6.4, 6.7**

  - [x] 22.20 Write property test for supplier-product many-to-many
    - **Property 20: Supplier-product link many-to-many**
    - **Validates: Requirements 7.1, 7.4, 7.5**

  - [x] 22.21 Write property test for default unit cost pre-fill
    - **Property 21: Default unit cost pre-fill**
    - **Validates: Requirements 7.2**

  - [x] 22.22 Write property test for unlink preserves PO history
    - **Property 22: Supplier-product unlink preserves PO history**
    - **Validates: Requirements 7.6**

  - [x] 22.23 Write property test for report aggregation consistency
    - **Property 23: Report aggregation consistency**
    - **Validates: Requirements 8.1, 8.2, 8.3**

  - [x] 22.24 Write property test for PO list ordering
    - **Property 24: PO list ordering invariant**
    - **Validates: Requirements 5.1**

- [x] 23. Final Checkpoint
  - Ensure all tests pass, ask the user if questions arise.

## Notes

- Tasks marked with `*` are optional and can be skipped for faster MVP
- Each task references specific requirements for traceability
- Checkpoints ensure incremental validation at key milestones
- Property tests validate universal correctness properties from the design document (24 properties)
- Feature tests validate specific HTTP flows, validation rules, and edge cases
- The module integrates with POS via `pos_product_variants` (product catalog) and `pos_stock_adjustments` (stock updates on goods receipt)
- All monetary values use `decimal(15,2)` consistent with POS module
- UI language is Bahasa Indonesia as per project convention
- Table prefix `supplier_` avoids naming collisions with other modules
- Goods receiving creates stock adjustments with type "restock" and reference to the PO
- Supplier soft-delete preserves historical PO data integrity
- PO number format: `PO-{YYYYMMDD}-{SEQ}` (e.g., PO-20260801-001)

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
    { "id": 8, "tasks": ["8.1", "8.2"] },
    { "id": 9, "tasks": ["9.1"] },
    { "id": 10, "tasks": ["11.1"] },
    { "id": 11, "tasks": ["12.1"] },
    { "id": 12, "tasks": ["13.1", "13.2", "13.3"] },
    { "id": 13, "tasks": ["13.4"] },
    { "id": 14, "tasks": ["15.1"] },
    { "id": 15, "tasks": ["15.2"] },
    { "id": 16, "tasks": ["16.1", "16.2"] },
    { "id": 17, "tasks": ["17.1", "17.2"] },
    { "id": 18, "tasks": ["17.3", "18.1", "18.2"] },
    { "id": 19, "tasks": ["19.1"] },
    { "id": 20, "tasks": ["21.1", "21.2", "21.3", "21.4", "21.5"] },
    { "id": 21, "tasks": ["22.1", "22.2", "22.3", "22.4", "22.5", "22.6", "22.7", "22.8", "22.9", "22.10", "22.11", "22.12", "22.13", "22.14", "22.15", "22.16", "22.17", "22.18", "22.19", "22.20", "22.21", "22.22", "22.23", "22.24"] }
  ]
}
```
