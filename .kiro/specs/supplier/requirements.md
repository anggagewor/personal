# Requirements Document — Supplier Module

## Introduction

The Supplier module manages supplier data and the procurement (pembelian) workflow for restocking products in the POS system. It covers the full purchasing cycle: managing supplier profiles, creating purchase orders, receiving goods (which updates stock), tracking purchase history, and managing supplier payment/debt obligations. The module integrates with the existing POS module's product catalog and stock management system.

## Sub-Module Breakdown

| Sub-Module | Scope |
|------------|-------|
| **Supplier Data** | Supplier profiles (nama, alamat, kontak, rekening bank) |
| **Purchase Order** | Pembelian barang ke supplier (draft → confirmed → received) |
| **Goods Receiving** | Penerimaan barang, verifikasi qty, update stock POS |
| **Purchase History** | Riwayat pembelian, filter, dan detail |
| **Payment Tracking** | Utang ke supplier, pembayaran cicilan/lunas |

## Glossary

- **Supplier_System**: The Supplier module within the Purdia Dashboard application
- **Supplier**: A vendor or distributor from whom the business purchases goods for resale
- **Purchase_Order**: A formal request to buy goods from a supplier, containing line items with product, quantity, and unit cost
- **PO_Line_Item**: A single product/variant entry in a purchase order with quantity and unit cost
- **Goods_Receipt**: A record confirming that goods from a purchase order have been physically received
- **Receipt_Line_Item**: A single product/variant entry in a goods receipt with received quantity
- **Supplier_Payment**: A payment made to a supplier to settle purchase order debt
- **Outlet**: A physical business location (from POS module) that owns the purchase orders
- **Product_Variant**: A specific product-variant combination from the POS catalog (the restockable unit)
- **Stock_Adjustment**: An existing POS mechanism for updating stock quantity with reason and type
- **PO_Status**: The lifecycle state of a purchase order (draft, confirmed, partial, received, cancelled)
- **Payment_Status**: The payment state of a purchase order (unpaid, partial, paid)

## Requirements

---

### Requirement 1: Supplier Data Management

**User Story:** As a business owner, I want to manage my supplier contacts and details, so that I can maintain an organized supplier database for procurement.

#### Acceptance Criteria

1. WHEN the user creates a supplier, THE Supplier_System SHALL store the supplier with a name, optional address, optional phone number, optional email, optional bank account details (bank name, account number, account holder name), and optional notes
2. WHEN the user updates a supplier profile, THE Supplier_System SHALL persist the changes to all editable fields
3. WHEN the user deletes a supplier, THE Supplier_System SHALL soft-delete the supplier and retain historical purchase order data associated with the supplier
4. WHEN the user searches suppliers, THE Supplier_System SHALL match against supplier name, phone number, and email
5. WHEN the user views the supplier list, THE Supplier_System SHALL display a paginated list of suppliers sorted by name ascending, with each entry showing name, phone, and total outstanding debt
6. IF a supplier name already exists within the same outlet, THEN THE Supplier_System SHALL reject the creation and return a duplicate error
7. THE Supplier_System SHALL scope all supplier data to the currently active outlet

---

### Requirement 2: Purchase Order Creation

**User Story:** As a business owner, I want to create purchase orders to suppliers, so that I can formally request goods for restocking my products.

#### Acceptance Criteria

1. WHEN the user creates a purchase order, THE Supplier_System SHALL store the order with a supplier reference, outlet reference, auto-generated PO number, order date, optional expected delivery date, and optional notes
2. THE Supplier_System SHALL generate a unique PO number with the format `PO-{YYYYMMDD}-{sequential}` (e.g., PO-20260801-001)
3. WHEN the user adds a line item to a purchase order, THE Supplier_System SHALL store the line item with a product variant reference, quantity, and unit cost (harga beli)
4. THE Supplier_System SHALL calculate the purchase order total as the sum of all line item subtotals (quantity × unit cost)
5. WHEN a purchase order is created, THE Supplier_System SHALL set the initial PO status to "draft"
6. WHILE a purchase order status is "draft", THE Supplier_System SHALL allow editing of all fields including adding, removing, and modifying line items
7. WHEN the user confirms a purchase order, THE Supplier_System SHALL change the PO status from "draft" to "confirmed" and set the payment status to "unpaid"
8. WHILE a purchase order status is "confirmed" or beyond, THE Supplier_System SHALL prevent editing of line items (quantity, unit cost, product)
9. IF a purchase order has zero line items, THEN THE Supplier_System SHALL reject confirmation and return an error indicating at least one line item is required
10. WHEN the user selects products for a PO line item, THE Supplier_System SHALL display products and variants from the POS catalog filtered by the current outlet

---

### Requirement 3: Purchase Order Cancellation

**User Story:** As a business owner, I want to cancel purchase orders that are no longer needed, so that I can keep my procurement records accurate.

#### Acceptance Criteria

1. WHILE a purchase order status is "draft" or "confirmed", THE Supplier_System SHALL allow the user to cancel the purchase order
2. WHEN the user cancels a purchase order, THE Supplier_System SHALL change the PO status to "cancelled" and record the cancellation timestamp
3. IF a purchase order has already received partial or full goods (status "partial" or "received"), THEN THE Supplier_System SHALL reject cancellation and return an error
4. WHEN a purchase order is cancelled, THE Supplier_System SHALL exclude the order from outstanding debt calculations

---

### Requirement 4: Goods Receiving

**User Story:** As a business owner, I want to record the receipt of goods from purchase orders, so that my stock is accurately updated and I can track delivery fulfillment.

#### Acceptance Criteria

1. WHEN the user creates a goods receipt for a purchase order, THE Supplier_System SHALL allow specifying the received quantity for each PO line item
2. WHEN a goods receipt is saved, THE Supplier_System SHALL update the POS stock quantity for each received product variant by creating a stock adjustment of type "restock" with reference to the purchase order
3. THE Supplier_System SHALL allow partial receiving — the received quantity for a line item may be less than the ordered quantity
4. WHEN all line items in a purchase order are fully received (total received equals total ordered for every line item), THE Supplier_System SHALL change the PO status to "received"
5. WHEN some but not all line items are fully received, THE Supplier_System SHALL change the PO status to "partial"
6. THE Supplier_System SHALL allow multiple goods receipts against a single purchase order (for split deliveries)
7. WHEN a goods receipt is saved, THE Supplier_System SHALL store the receipt date, received quantities per line item, and optional notes
8. IF the received quantity for a line item exceeds the remaining undelivered quantity (ordered minus previously received), THEN THE Supplier_System SHALL reject the receipt line and return an over-delivery error
9. WHILE a purchase order status is "draft" or "cancelled", THE Supplier_System SHALL prevent creating goods receipts against the order

---

### Requirement 5: Purchase Order History & Tracking

**User Story:** As a business owner, I want to view my purchase history and track order status, so that I can monitor procurement activities and supplier performance.

#### Acceptance Criteria

1. WHEN the user views purchase order history, THE Supplier_System SHALL display a paginated list of purchase orders sorted by date descending, with filters for supplier, PO status, payment status, and date range
2. WHEN the user views a purchase order detail, THE Supplier_System SHALL display PO number, supplier name, order date, status, payment status, line items with ordered/received quantities, total amount, total paid, and outstanding balance
3. WHEN the user views a purchase order detail, THE Supplier_System SHALL display a list of associated goods receipts with receipt date, received quantities, and notes
4. WHEN the user views a purchase order detail, THE Supplier_System SHALL display a list of associated payments with payment date, amount, and method
5. THE Supplier_System SHALL provide a summary view showing total purchase value, total outstanding debt, and purchase count for a given date range

---

### Requirement 6: Supplier Payment Tracking

**User Story:** As a business owner, I want to track payments to suppliers, so that I can manage my payables and know how much I owe each supplier.

#### Acceptance Criteria

1. WHEN the user records a payment to a supplier, THE Supplier_System SHALL store the payment with a purchase order reference, amount, payment date, payment method (cash, bank transfer, e-wallet), and optional notes
2. WHEN a payment is recorded, THE Supplier_System SHALL update the purchase order payment status: "paid" if total payments equal or exceed PO total, "partial" if total payments are less than PO total
3. IF the payment amount exceeds the outstanding balance of the purchase order, THEN THE Supplier_System SHALL reject the payment and return an overpayment error
4. WHEN the user views supplier detail, THE Supplier_System SHALL display total outstanding debt (sum of unpaid PO balances) for that supplier
5. WHEN the user views a payment history for a supplier, THE Supplier_System SHALL display a paginated list of payments sorted by date descending, grouped by purchase order
6. THE Supplier_System SHALL support recording multiple payments against a single purchase order (for installment payments)
7. WHEN the user views the supplier list, THE Supplier_System SHALL display the total outstanding debt per supplier as a summary column

---

### Requirement 7: Supplier-Product Linkage

**User Story:** As a business owner, I want to associate suppliers with the products they supply, so that I can quickly select products when creating purchase orders and track which supplier provides which items.

#### Acceptance Criteria

1. WHEN the user links a product variant to a supplier, THE Supplier_System SHALL store the association with an optional default unit cost (harga beli default)
2. WHEN the user creates a PO line item and selects a supplier-linked product, THE Supplier_System SHALL pre-fill the unit cost with the supplier's default unit cost for that product variant
3. WHEN the user views a supplier detail, THE Supplier_System SHALL display a list of linked products with their default unit costs
4. THE Supplier_System SHALL allow a product variant to be linked to multiple suppliers (different sources for the same product)
5. THE Supplier_System SHALL allow a supplier to be linked to multiple product variants
6. WHEN the user removes a supplier-product link, THE Supplier_System SHALL delete the association without affecting historical purchase order data

---

### Requirement 8: Purchase Order Reporting

**User Story:** As a business owner, I want to see purchase reports, so that I can analyze spending patterns and manage cash flow.

#### Acceptance Criteria

1. WHEN the user views the purchase summary report, THE Supplier_System SHALL display total purchase value, total paid, and total outstanding debt for a selected date range
2. WHEN the user views the report grouped by supplier, THE Supplier_System SHALL display purchase total and outstanding debt per supplier for the selected period
3. WHEN the user views the report grouped by product, THE Supplier_System SHALL display total quantity purchased and total cost per product for the selected period
4. THE Supplier_System SHALL provide an exportable summary (CSV format) for purchase reports
5. WHEN the user views the supplier dashboard widget, THE Supplier_System SHALL display total outstanding debt, number of pending POs (confirmed but not fully received), and recent purchase orders

