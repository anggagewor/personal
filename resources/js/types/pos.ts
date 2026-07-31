// === Outlet ===
export interface Outlet {
  id: number
  name: string
  business_type: 'retail' | 'warung' | 'kafe' | 'warkop'
  payment_flow: 'pay_first' | 'pay_later' | 'both'
  address: string | null
  phone: string | null
  settings: OutletSettings | null
  created_at: string
}

export interface OutletSettings {
  receipt_header?: string
  receipt_footer?: string
  receipt_width?: '58mm' | '80mm'
}

// === Category ===
export interface Category {
  id: number
  outlet_id: number
  parent_id: number | null
  name: string
  icon: string | null
  sort_order: number
  children?: Category[]
}

// === Product ===
export interface Product {
  id: number
  outlet_id: number
  category_id: number
  name: string
  base_price: number
  sku: string | null
  image: string | null
  has_variants: boolean
  track_stock: boolean
  status: 'active' | 'inactive'
  variants: ProductVariant[]
}

export interface ProductVariant {
  id: number
  product_id: number
  name: string
  sku: string | null
  price: number
  stock_quantity: number
}

// === Cart (Ephemeral) ===
export interface CartItemDiscount {
  name: string
  amount: number
}

export interface CartItem {
  product_id: number
  product_variant_id: number | null
  product_name: string
  variant_name: string | null
  quantity: number
  unit_price: number
  subtotal: number
  image?: string | null
  discount_amount?: number
  discounts?: CartItemDiscount[]
  voucher_amount?: number
  voucher_label?: string | null
}

export interface CheckoutPayload {
  outlet_id: number
  items: CartItem[]
  payment_method?: string
  payment_method_type?: string
  amount_tendered?: number
  voucher_code?: string
  discount_ids?: number[]
  member_id?: number
  table_session_id?: number
  payment_flow: 'pay_first' | 'pay_later'
  notes?: string
}

// === Transaction ===
export interface Transaction {
  id: number
  outlet_id: number
  transaction_number: string
  subtotal: number
  discount_amount: number
  total: number
  payment_method: string | null
  payment_method_type: string | null
  amount_tendered: number | null
  change_amount: number | null
  status: 'completed' | 'pending' | 'voided'
  member_id: number | null
  member_name: string | null
  void_reason: string | null
  voided_at: string | null
  voucher_code: string | null
  items: TransactionItem[]
  created_at: string
}

export interface TransactionItem {
  id: number
  product_name: string
  variant_name: string | null
  quantity: number
  unit_price: number
  subtotal: number
}

// === Discount ===
export interface Discount {
  id: number
  outlet_id: number
  name: string
  type: 'percentage' | 'fixed' | 'buy_x_get_y'
  value: number
  min_purchase: number | null
  buy_quantity: number | null
  get_quantity: number | null
  product_id: number | null
  start_date: string | null
  end_date: string | null
  is_active: boolean
  member_only: boolean
  priority: number
}

// === Voucher ===
export interface Voucher {
  id: number
  outlet_id: number
  code: string
  discount_type: 'percentage' | 'fixed'
  discount_value: number
  min_purchase: number | null
  usage_limit: number | null
  usage_count: number
  expires_at: string | null
  is_active: boolean
  product_id: number | null
}

// === Table & QR Order ===
export interface PosTable {
  id: number
  outlet_id: number
  name: string
  token: string
  qr_code_path: string | null
  active_session: TableSession | null
}

export interface TableSession {
  id: number
  table_id: number
  status: 'active' | 'closed'
  opened_at: string
  closed_at: string | null
}

export interface OrderQueueItem {
  id: number
  table_session_id: number
  table_name: string
  items: QrOrderItem[]
  status: 'pending' | 'accepted' | 'completed' | 'cancelled'
  customer_name: string | null
  notes: string | null
  created_at: string
}

export interface QrOrderItem {
  product_id: number
  variant_id: number | null
  quantity: number
  name: string
  price: number
}

// === Payment Method ===
export interface PaymentMethod {
  id: number
  outlet_id: number
  type: 'cash' | 'bank_transfer' | 'e_wallet' | 'custom'
  name: string
  is_active: boolean
  settings: Record<string, string> | null
  sort_order: number
}

// === Reports ===
export interface DailySummary {
  date: string
  gross_revenue: number
  total_discount: number
  total_revenue: number
  transaction_count: number
  average_transaction: number
  top_products: { name: string; quantity: number; revenue: number }[]
}

export interface DashboardStats {
  today_revenue: number
  today_gross_revenue: number
  today_discount: number
  today_transactions: number
  weekly_trend: { date: string; revenue: number; discount: number }[]
}

// === Member ===
export interface Member {
  id: number
  outlet_id: number
  name: string
  phone: string
  email: string | null
  created_at: string
  updated_at: string
}

export interface MemberPayload {
  name: string
  phone: string
  email?: string
}

// === Open Bills ===
export interface OpenBill {
  id: number
  transaction_number: string
  subtotal: number
  discount_amount: number
  total: number
  member_id: number | null
  member_name: string | null
  table_session_id: number | null
  table_name: string | null
  items: TransactionItem[]
  is_overdue: boolean
  created_at: string
}

export interface CloseOpenBillPayload {
  payment_method: string
  payment_method_type: string
  amount_tendered?: number
}
