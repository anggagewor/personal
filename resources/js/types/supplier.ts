// === Supplier ===
export interface Supplier {
  id: number
  outlet_id: number
  name: string
  address: string | null
  phone: string | null
  email: string | null
  bank_name: string | null
  bank_account_number: string | null
  bank_account_holder: string | null
  notes: string | null
  total_debt: number
  created_at: string
  updated_at: string
}

export interface SupplierPayload {
  name: string
  address?: string | null
  phone?: string | null
  email?: string | null
  bank_name?: string | null
  bank_account_number?: string | null
  bank_account_holder?: string | null
  notes?: string | null
}

// === Purchase Order ===
export interface PurchaseOrder {
  id: number
  outlet_id: number
  supplier_id: number
  supplier_name: string
  po_number: string
  order_date: string
  expected_delivery_date: string | null
  status: 'draft' | 'confirmed' | 'partial' | 'received' | 'cancelled'
  payment_status: 'unpaid' | 'partial' | 'paid'
  total_amount: number
  total_paid: number
  outstanding_balance: number
  notes: string | null
  cancelled_at: string | null
  items: PurchaseOrderItem[]
  created_at: string
  updated_at: string
}

export interface PurchaseOrderItem {
  id: number
  purchase_order_id: number
  product_variant_id: number
  product_name: string
  variant_name: string
  quantity: number
  unit_cost: number
  subtotal: number
  received_quantity: number
}

export interface PurchaseOrderPayload {
  supplier_id: number
  order_date: string
  expected_delivery_date?: string | null
  notes?: string | null
  items: PurchaseOrderItemPayload[]
}

export interface PurchaseOrderItemPayload {
  product_variant_id: number
  product_name: string
  variant_name: string
  quantity: number
  unit_cost: number
}

// === Goods Receipt ===
export interface GoodsReceipt {
  id: number
  purchase_order_id: number
  receipt_date: string
  notes: string | null
  items: GoodsReceiptItem[]
  created_at: string
}

export interface GoodsReceiptItem {
  id: number
  goods_receipt_id: number
  purchase_order_item_id: number
  product_variant_id: number
  quantity: number
}

export interface GoodsReceiptPayload {
  receipt_date: string
  notes?: string | null
  items: GoodsReceiptItemPayload[]
}

export interface GoodsReceiptItemPayload {
  purchase_order_item_id: number
  product_variant_id: number
  quantity: number
}

// === Payment ===
export interface SupplierPayment {
  id: number
  purchase_order_id: number
  amount: number
  payment_date: string
  payment_method: 'cash' | 'bank_transfer' | 'e_wallet'
  notes: string | null
  created_at: string
}

export interface SupplierPaymentPayload {
  amount: number
  payment_date: string
  payment_method: 'cash' | 'bank_transfer' | 'e_wallet'
  notes?: string | null
}

// === Supplier Product ===
export interface SupplierProduct {
  id: number
  supplier_id: number
  product_variant_id: number
  product_name: string
  variant_name: string
  default_unit_cost: number | null
}

export interface LinkProductPayload {
  product_variant_id: number
  default_unit_cost?: number | null
}

// === Reports & Dashboard ===
export interface PurchaseSummary {
  total_purchase_value: number
  total_paid: number
  total_outstanding_debt: number
  purchase_count: number
}

export interface PurchaseBySupplier {
  supplier_id: number
  supplier_name: string
  total_purchase: number
  outstanding_debt: number
}

export interface PurchaseByProduct {
  product_variant_id: number
  product_name: string
  variant_name: string
  total_quantity: number
  total_cost: number
}

export interface SupplierDashboard {
  total_outstanding_debt: number
  pending_po_count: number
  recent_purchase_orders: PurchaseOrder[]
}
