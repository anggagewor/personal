# Requirements Document — POS (Point of Sale) Module

## Introduction

The POS module provides a lightweight, multi-format point-of-sale system designed for small businesses (retail, warung, kafe, warkop). It supports product catalog management with variants, transaction processing, discount/voucher systems, QR-based table ordering, and basic reporting. The module is broken into logical sub-modules that can be implemented incrementally.

## Sub-Module Breakdown

| Sub-Module | Scope |
|------------|-------|
| **POS Catalog** | Products, categories, variants, stock/inventory |
| **POS Transaction** | Cart, checkout, payment methods, receipts |
| **POS Discount** | Discount rules (percentage, fixed, buy-X-get-Y) |
| **POS Voucher** | Voucher codes, redemption, expiry |
| **POS QR Order** | QR code per table, customer self-ordering, order queue |
| **POS Outlet** | Multi-outlet support, outlet settings, business type |
| **POS Report** | Sales summary, product ranking, revenue reports |

## Glossary

- **POS_System**: The Point of Sale module within the personal dashboard application
- **Catalog_Service**: Sub-system responsible for managing products, categories, and variants
- **Transaction_Service**: Sub-system responsible for processing sales transactions
- **Discount_Engine**: Sub-system that evaluates and applies discount rules to transactions
- **Voucher_Service**: Sub-system that manages voucher creation, validation, and redemption
- **QR_Order_Service**: Sub-system that handles QR-code-based customer ordering from tables
- **Outlet**: A physical business location (warung, kafe, warkop, retail shop)
- **Product**: An item available for sale in the catalog
- **Variant**: A specific option of a product (e.g., size, flavor, temperature)
- **SKU**: Stock Keeping Unit — unique identifier for a product/variant combination
- **Cart**: A temporary collection of line items before checkout
- **Line_Item**: A single product/variant entry in a cart or transaction with quantity and price
- **Transaction**: A completed sale record with payment information
- **Discount_Rule**: A configured discount condition and its applied benefit
- **Voucher**: A redeemable code that grants a discount or benefit
- **Table_Session**: An active QR-order session linked to a physical table
- **Order_Queue**: A list of pending orders from QR ordering awaiting fulfillment
- **Member**: A registered customer with stored profile data (name, phone, email)
- **Walk-in**: An anonymous customer without stored data
- **Open_Bill**: A confirmed order pending payment in the pay-later flow
- **Payment_Flow**: The sequence of order and payment — either pay-first (immediate payment) or pay-later (payment after consumption)

## Requirements

---

### Requirement 1: Outlet Management

**User Story:** As a business owner, I want to configure my outlet(s) with business type and settings, so that the POS system adapts to my specific business format.

#### Acceptance Criteria

1. WHEN the user creates an outlet, THE POS_System SHALL store the outlet with a name, business type (retail, warung, kafe, warkop), address, and optional contact information
2. THE POS_System SHALL support the following business types: retail, warung, kafe, warkop
3. WHEN the user updates outlet settings, THE POS_System SHALL persist the changes and apply them to subsequent transactions
4. WHILE no outlet is configured (soft-deleted outlets count as configured), THE POS_System SHALL prompt the user to create an outlet when accessing POS features that require an outlet
5. WHEN the user deletes an outlet, THE POS_System SHALL soft-delete the outlet and retain historical transaction data associated with the outlet

---

### Requirement 2: Product Catalog — Categories

**User Story:** As a business owner, I want to organize my products into categories, so that I can manage and find items efficiently.

#### Acceptance Criteria

1. WHEN the user creates a category, THE Catalog_Service SHALL store the category with a name, optional icon, and sort order
2. THE Catalog_Service SHALL support hierarchical categories up to 2 levels deep (parent and child)
3. WHEN the user reorders categories, THE Catalog_Service SHALL persist the new sort order
4. WHEN the user deletes a category that contains products, THE Catalog_Service SHALL reassign those products to an "Uncategorized" default category
5. IF a category name already exists within the same outlet, THEN THE Catalog_Service SHALL reject the creation, prevent storage, and return a duplicate error

---

### Requirement 3: Product Catalog — Products & Variants

**User Story:** As a business owner, I want to add products with optional variants (size, flavor, temperature), so that I can represent my full menu or inventory.

#### Acceptance Criteria

1. WHEN the user creates a product, THE Catalog_Service SHALL store the product with a name, base price, category, optional image, and optional SKU
2. WHERE a product has variants enabled, THE Catalog_Service SHALL allow defining variant groups (e.g., "Ukuran", "Suhu") with variant options (e.g., "Kecil", "Besar", "Panas", "Dingin")
3. WHERE a product has variants, THE Catalog_Service SHALL require a price for each variant combination (which may differ from the base price)
4. WHEN the user deactivates a product, THE Catalog_Service SHALL exclude the product from the POS transaction interface while retaining historical transaction data
5. THE Catalog_Service SHALL generate a unique SKU for each product-variant combination if the user does not provide one
6. WHEN the user searches products, THE Catalog_Service SHALL match against product name, SKU, and category name
7. IF a product name already exists within the same outlet and category, THEN THE Catalog_Service SHALL reject the creation and return a duplicate error
8. WHEN a product deactivation is attempted, THE Catalog_Service SHALL force exclusion from the POS transaction interface regardless of whether the deactivation operation encounters errors

---

### Requirement 4: Stock Management

**User Story:** As a business owner, I want to track stock levels for my products, so that I know when to restock and prevent selling unavailable items.

#### Acceptance Criteria

1. WHERE stock tracking is enabled for a product, THE Catalog_Service SHALL maintain a current stock quantity for each product-variant combination
2. WHEN a transaction is completed, THE Catalog_Service SHALL decrement the stock quantity by the sold quantity
3. WHEN stock quantity reaches zero, THE Catalog_Service SHALL mark the product-variant as "Habis" (out of stock) in the transaction interface
4. WHEN the user performs a stock adjustment (restock or correction), THE Catalog_Service SHALL update the quantity and log the adjustment with reason and timestamp
5. IF a line item quantity exceeds available stock, THEN THE Transaction_Service SHALL reject the line item and return an insufficient stock error
6. WHERE stock tracking is disabled for a product, THE Catalog_Service SHALL allow unlimited sales without stock validation, but THE Transaction_Service SHALL still reject line items for non-stock reasons (payment validation, business rules)
7. IF a stock adjustment has a quantity of zero, THEN THE Catalog_Service SHALL reject the adjustment before logging

---

### Requirement 5: Transaction Processing — Cart & Checkout

**User Story:** As a cashier, I want to add items to a cart and process checkout, so that I can complete sales efficiently.

#### Acceptance Criteria

1. WHEN the user adds a product to the cart, THE Transaction_Service SHALL create a line item with product, variant (if applicable), quantity, and unit price
2. WHEN the user changes line item quantity, THE Transaction_Service SHALL recalculate the line item subtotal as quantity multiplied by unit price, and recalculate the cart total as the sum of all line item subtotals
3. WHEN the user removes a line item, THE Transaction_Service SHALL remove the item and recalculate the cart total
4. THE Transaction_Service SHALL display the cart total as the sum of all line item subtotals minus applied discounts and vouchers
5. WHEN the user initiates checkout, THE Transaction_Service SHALL validate stock availability for all line items (where stock tracking is enabled)
6. WHEN checkout is confirmed with payment, THE Transaction_Service SHALL create a transaction record with a unique transaction number, timestamp, line items, payment method, and total amount
7. WHEN a transaction is completed, THE Transaction_Service SHALL generate a receipt containing outlet name, transaction number, date/time, line items, discounts, total, and payment method

---

### Requirement 6: Payment Methods

**User Story:** As a business owner, I want to accept multiple payment methods, so that I can accommodate customer preferences.

#### Acceptance Criteria

1. THE Transaction_Service SHALL support the following payment methods: cash, bank transfer, e-wallet (QRIS), and custom methods defined by the user
2. WHEN payment method is cash, THE Transaction_Service SHALL calculate and display change amount based on the amount tendered
3. WHEN the user configures payment methods for an outlet, THE POS_System SHALL persist the enabled payment methods and display only enabled methods during checkout
4. WHEN a transaction is completed, THE Transaction_Service SHALL record the payment method used in the transaction record

---

### Requirement 7: Discount Rules

**User Story:** As a business owner, I want to create discount rules that automatically apply or can be manually triggered, so that I can run promotions.

#### Acceptance Criteria

1. WHEN the user creates a discount rule, THE Discount_Engine SHALL store the rule with a name, type (percentage, fixed amount, buy-X-get-Y), value, and optional conditions
2. THE Discount_Engine SHALL support the following discount types: percentage off total, fixed amount off total, percentage off specific product, buy-X-get-Y-free
3. WHERE a discount has date-range conditions, THE Discount_Engine SHALL apply the discount only within the valid date range
4. WHERE a discount has minimum purchase conditions, THE Discount_Engine SHALL apply the discount only when the cart subtotal meets or exceeds the minimum amount
5. WHEN multiple discounts apply to a transaction, THE Discount_Engine SHALL apply discounts in priority order and recalculate after each application
6. IF a discount rule is inactive, THEN THE Discount_Engine SHALL exclude the rule from evaluation during checkout
7. WHERE a discount has a "member_only" condition, THE Discount_Engine SHALL apply the discount only when the transaction is linked to a Member customer
8. IF a discount has a "member_only" condition and the transaction is for a Walk-in customer, THEN THE Discount_Engine SHALL exclude the discount from evaluation
9. WHERE a discount does NOT have a "member_only" condition, THE Discount_Engine SHALL apply the discount to all customer types (Walk-in and Member) when other conditions are met
10. IF the priority ordering mechanism fails during multiple discount evaluation, THEN THE Discount_Engine SHALL apply each individually qualifying discount without priority ordering (graceful degradation)

---

### Requirement 8: Voucher System

**User Story:** As a business owner, I want to create and distribute voucher codes, so that customers can redeem discounts.

#### Acceptance Criteria

1. WHEN the user creates a voucher, THE Voucher_Service SHALL store the voucher with a unique code, discount type (percentage or fixed), value, expiry date, and usage limit
2. WHEN a customer redeems a voucher code during checkout, THE Voucher_Service SHALL validate the code against expiry date, usage limit, and minimum purchase amount
3. IF a voucher code is invalid, expired, or fully redeemed, THEN THE Voucher_Service SHALL reject the redemption and return a descriptive error message
4. WHEN a voucher is successfully redeemed, THE Voucher_Service SHALL increment the usage counter and apply the discount to the transaction
5. THE Voucher_Service SHALL support batch voucher generation with a shared prefix and sequential or random suffixes
6. WHEN the user views voucher details, THE Voucher_Service SHALL display the total redemption count, remaining uses (computed as usage_limit minus usage_count), and revenue impact

---

### Requirement 9: QR Table Ordering

**User Story:** As a cafe/warkop owner, I want customers to scan a QR code at their table and place orders from their phone, so that I can reduce wait staff and speed up service.

#### Acceptance Criteria

1. WHEN the user configures tables for an outlet, THE QR_Order_Service SHALL generate a unique QR code for each table
2. WHEN a customer scans a table QR code, THE QR_Order_Service SHALL open a public ordering page showing the outlet menu (no authentication required)
3. WHILE a table session is active, THE QR_Order_Service SHALL allow the customer to add items to their order and submit
4. WHEN a customer submits an order, THE QR_Order_Service SHALL add the order to the Order_Queue with table number, items, and timestamp
5. WHEN a new order arrives in the queue, THE QR_Order_Service SHALL notify the cashier/kitchen via real-time update on the POS dashboard
6. WHEN the cashier accepts an order from the queue, THE Transaction_Service SHALL create a transaction linked to the table session
7. WHEN the cashier explicitly marks a table session as complete, THE QR_Order_Service SHALL close the session and free the table for new customers (no automatic table cleanup)
8. IF the outlet business type does not support table ordering (e.g., retail), THEN THE QR_Order_Service SHALL hide the table ordering feature for that outlet
9. WHEN a customer submits an order via QR, THE QR_Order_Service SHALL default to pay-later flow, creating an Open_Bill that remains pending until the cashier closes the bill with payment
10. WHEN the outlet business type changes from a table-ordering-capable type (kafe, warkop) to retail, THE QR_Order_Service SHALL immediately disable table ordering for the outlet and invalidate all existing QR codes

---

### Requirement 10: Sales Reporting

**User Story:** As a business owner, I want to see sales reports and analytics, so that I can make informed business decisions.

#### Acceptance Criteria

1. WHEN the user views the daily report, THE POS_System SHALL display total revenue, transaction count, average transaction value, and top-selling products for the selected date
2. WHEN the user views a date-range report, THE POS_System SHALL aggregate sales data across the selected period with daily breakdown
3. THE POS_System SHALL provide a product ranking report showing quantity sold and revenue per product for a given period
4. WHEN the user filters reports by payment method, THE POS_System SHALL display totals grouped by each payment method
5. THE POS_System SHALL provide an exportable summary (CSV format) for any report view
6. WHEN the user views the dashboard, THE POS_System SHALL display today's revenue, today's transaction count, and a 7-day revenue trend chart
7. WHEN no transaction data exists for the selected period, THE POS_System SHALL display reports with zero values and empty lists instead of a "no data" message

---

### Requirement 11: Receipt Management

**User Story:** As a business owner, I want to customize receipt content and reprint past receipts, so that I maintain professional documentation.

#### Acceptance Criteria

1. WHERE the user has configured a receipt template, THE Transaction_Service SHALL use the custom template when generating receipts
2. THE Transaction_Service SHALL include the following on every receipt: outlet name, outlet address, transaction number, date/time, line items with quantity and price, discounts applied, total amount, and payment method
3. WHEN the user requests a receipt reprint, THE Transaction_Service SHALL regenerate the receipt from the stored transaction data
4. THE Transaction_Service SHALL support receipt output as thermal-print-formatted text (58mm/80mm width) and PDF format

---

### Requirement 12: Transaction History & Void

**User Story:** As a business owner, I want to view past transactions and void incorrect ones, so that I can maintain accurate records.

#### Acceptance Criteria

1. WHEN the user views transaction history, THE Transaction_Service SHALL display a paginated list of transactions sorted by date descending, with filters for date range, payment method, and status
2. WHEN the user voids a transaction, THE Transaction_Service SHALL mark the transaction as voided, record the void reason and timestamp, and restore stock quantities (where stock tracking is enabled)
3. IF a transaction is older than 24 hours, THEN THE Transaction_Service SHALL require a confirmation with reason before allowing void; for transactions within 24 hours, THE Transaction_Service SHALL allow optional confirmation and reason input but not require them
4. WHEN a transaction is voided, THE Transaction_Service SHALL exclude the voided amount from sales reports

---

### Requirement 13: Customer Management — Walk-in & Member

**User Story:** As a business owner, I want to distinguish between walk-in (anonymous) and member (registered) customers, so that I can build customer relationships and offer targeted promotions.

#### Acceptance Criteria

1. THE POS_System SHALL support two customer types: Walk-in (anonymous, no data stored) and Member (registered with name, phone, and optional email)
2. WHEN the user creates a member, THE POS_System SHALL store the member profile with name, phone number, and optional email, scoped to the outlet
3. WHEN the user updates a member profile, THE POS_System SHALL persist the changes to name, phone, and email fields
4. WHEN the user deletes a member, THE POS_System SHALL remove the member profile while retaining historical transaction data linked to the member
5. WHEN the cashier processes a checkout, THE Transaction_Service SHALL allow optionally linking the transaction to an existing Member
6. WHEN a transaction is not linked to any member, THE Transaction_Service SHALL treat the transaction as a Walk-in sale without requiring any customer data
7. WHEN the user searches members, THE POS_System SHALL match against member name and phone number
8. IF linking a transaction to a Member fails, THEN THE Transaction_Service SHALL default to processing the transaction as a Walk-in sale

---

### Requirement 14: Member-Specific Discounts

**User Story:** As a business owner, I want to create discounts exclusive to registered members, so that I can reward loyal customers and incentivize membership.

#### Acceptance Criteria

1. WHEN the user creates or updates a discount rule, THE Discount_Engine SHALL allow setting a "member_only" condition on the rule
2. WHILE a discount rule has the "member_only" condition enabled, THE Discount_Engine SHALL apply the discount only to transactions linked to a Member customer
3. IF a discount rule has the "member_only" condition and the current transaction is a Walk-in sale, THEN THE Discount_Engine SHALL exclude the discount from the applicable rules
4. WHEN the Discount_Engine evaluates applicable discounts, THE Discount_Engine SHALL check the member_only condition after verifying active status, date range, and minimum purchase conditions

---

### Requirement 15: Payment Flow Modes — Pay First & Pay Later

**User Story:** As a business owner, I want to configure whether my outlet uses pay-first (immediate payment) or pay-later (open bill) flow, so that the POS matches my business operations (e.g., retail pays first, kafe/warkop pays after consuming).

#### Acceptance Criteria

1. WHEN the user configures outlet settings, THE POS_System SHALL allow selecting the payment flow mode: pay-first, pay-later, or both
2. WHILE the outlet payment flow is set to pay-first, THE Transaction_Service SHALL require payment to be completed before confirming the order
3. WHILE the outlet payment flow is set to pay-later, THE Transaction_Service SHALL create an Open_Bill with status "pending" only when the cashier explicitly confirms an order (not automatically from configuration)
4. WHEN the cashier closes an Open_Bill, THE Transaction_Service SHALL record the payment method and amount, mark the bill as completed, and generate a receipt
5. WHILE the outlet payment flow is set to both, THE Transaction_Service SHALL allow the cashier to choose between pay-first and pay-later at checkout time
6. IF an Open_Bill has been pending for more than 24 hours, THEN THE POS_System SHALL highlight the bill in the open bills list as overdue
7. WHEN the user views open bills, THE Transaction_Service SHALL display a list of all Open_Bills sorted by creation time, with table number (if applicable), customer name (if member), and order total
8. WHEN QR table ordering is used, THE QR_Order_Service SHALL default to pay-later flow regardless of the outlet setting, creating an Open_Bill for the table session
