import { get, post, put, del, upload } from '@purdia/http'
import type {
  Outlet,
  Category,
  Product,
  Transaction,
  Discount,
  Voucher,
  PosTable,
  OrderQueueItem,
  PaymentMethod,
  Member,
  MemberPayload,
  DailySummary,
  DashboardStats,
  OpenBill,
  CloseOpenBillPayload,
  CheckoutPayload,
} from '@/types/pos'

// --- Outlets ---

export function fetchOutlets() {
  return get<Outlet[]>('/pos/outlets')
}

export function createOutlet(payload: Partial<Outlet>) {
  return post<Outlet>('/pos/outlets', payload)
}

export function updateOutlet(id: number, payload: Partial<Outlet>) {
  return put<Outlet>(`/pos/outlets/${id}`, payload)
}

export function deleteOutlet(id: number) {
  return del(`/pos/outlets/${id}`)
}

// --- Categories ---

export function fetchCategories(outletId: number) {
  return get<Category[]>(`/pos/outlets/${outletId}/categories`)
}

export function createCategory(outletId: number, payload: Partial<Category>) {
  return post<Category>(`/pos/outlets/${outletId}/categories`, payload)
}

export function updateCategory(id: number, payload: Partial<Category>) {
  return put<Category>(`/pos/categories/${id}`, payload)
}

export function deleteCategory(id: number) {
  return del(`/pos/categories/${id}`)
}

export function reorderCategories(payload: { ids: number[] }) {
  return post('/pos/categories/reorder', payload)
}

// --- Products ---

export function fetchProducts(outletId: number, params?: { category_id?: number; status?: string }) {
  return get<Product[]>(`/pos/outlets/${outletId}/products`, { params })
}

export function createProduct(outletId: number, payload: Partial<Product>, imageFile?: File | null) {
  if (imageFile) {
    const formData = buildProductFormData(payload, imageFile)
    return upload<Product>(`/pos/outlets/${outletId}/products`, formData)
  }
  return post<Product>(`/pos/outlets/${outletId}/products`, payload)
}

export function fetchProduct(id: number) {
  return get<Product>(`/pos/products/${id}`)
}

export function updateProduct(id: number, payload: Partial<Product>, imageFile?: File | null, removeImage?: boolean) {
  if (imageFile || removeImage) {
    const formData = buildProductFormData(payload, imageFile, removeImage)
    formData.append('_method', 'PUT')
    return upload<Product>(`/pos/products/${id}`, formData)
  }
  return put<Product>(`/pos/products/${id}`, payload)
}

function buildProductFormData(payload: Partial<Product>, imageFile?: File | null, removeImage?: boolean): FormData {
  const formData = new FormData()

  if (payload.name) formData.append('name', payload.name)
  if (payload.category_id) formData.append('category_id', String(payload.category_id))
  if (payload.base_price !== undefined) formData.append('base_price', String(payload.base_price))
  if (payload.sku !== undefined) formData.append('sku', payload.sku || '')
  if (payload.has_variants !== undefined) formData.append('has_variants', payload.has_variants ? '1' : '0')
  if (payload.track_stock !== undefined) formData.append('track_stock', payload.track_stock ? '1' : '0')

  if (imageFile) {
    formData.append('image', imageFile)
  }
  if (removeImage) {
    formData.append('remove_image', '1')
  }

  if (payload.variants && payload.variants.length > 0) {
    payload.variants.forEach((v, i) => {
      formData.append(`variants[${i}][name]`, v.name)
      formData.append(`variants[${i}][price]`, String(v.price))
      if (v.sku) formData.append(`variants[${i}][sku]`, v.sku)
      formData.append(`variants[${i}][stock_quantity]`, String(v.stock_quantity))
    })
  }

  return formData
}

export function deactivateProduct(id: number) {
  return post(`/pos/products/${id}/deactivate`)
}

// --- Stock ---

export function adjustStock(productId: number, payload: { variant_id?: number; quantity: number; type: 'set' | 'adjust'; reason?: string }) {
  return post(`/pos/products/${productId}/stock`, payload)
}

export function fetchStock(outletId: number) {
  return get(`/pos/outlets/${outletId}/stock`)
}

// --- Transactions ---

export function fetchTransactions(outletId: number, params?: { page?: number; per_page?: number; status?: string }) {
  return get<Transaction[]>(`/pos/outlets/${outletId}/transactions`, { params })
}

export function createTransaction(outletId: number, payload: CheckoutPayload) {
  return post<Transaction>(`/pos/outlets/${outletId}/transactions`, payload)
}

export function fetchTransaction(id: number) {
  return get<Transaction>(`/pos/transactions/${id}`)
}

export function voidTransaction(id: number, payload: { reason: string }) {
  return post(`/pos/transactions/${id}/void`, payload)
}

// --- Open Bills ---

export function fetchOpenBills(outletId: number) {
  return get<OpenBill[]>(`/pos/outlets/${outletId}/open-bills`)
}

export function closeOpenBill(id: number, payload: CloseOpenBillPayload) {
  return post(`/pos/open-bills/${id}/close`, payload)
}

// --- Payment Methods ---

export function fetchPaymentMethods(outletId: number) {
  return get<PaymentMethod[]>(`/pos/outlets/${outletId}/payment-methods`)
}

export function createPaymentMethod(outletId: number, payload: Partial<PaymentMethod>) {
  return post<PaymentMethod>(`/pos/outlets/${outletId}/payment-methods`, payload)
}

export function updatePaymentMethod(id: number, payload: Partial<PaymentMethod>) {
  return put<PaymentMethod>(`/pos/payment-methods/${id}`, payload)
}

export function deletePaymentMethod(id: number) {
  return del(`/pos/payment-methods/${id}`)
}

// --- Discounts ---

export function fetchDiscounts(outletId: number) {
  return get<Discount[]>(`/pos/outlets/${outletId}/discounts`)
}

export function createDiscount(outletId: number, payload: Partial<Discount>) {
  return post<Discount>(`/pos/outlets/${outletId}/discounts`, payload)
}

export function updateDiscount(id: number, payload: Partial<Discount>) {
  return put<Discount>(`/pos/discounts/${id}`, payload)
}

export function deleteDiscount(id: number) {
  return del(`/pos/discounts/${id}`)
}

export function evaluateDiscounts(payload: { outlet_id: number; items: { product_id: number; quantity: number; subtotal: number }[]; member_id?: number }) {
  const subtotal = payload.items.reduce((sum, i) => sum + i.subtotal, 0)
  return post<{ applicable: Discount[]; total_discount: number }>('/pos/discounts/evaluate', { ...payload, subtotal })
}

// --- Vouchers ---

export function fetchVouchers(outletId: number) {
  return get<Voucher[]>(`/pos/outlets/${outletId}/vouchers`)
}

export function createVoucher(outletId: number, payload: Partial<Voucher>) {
  return post<Voucher>(`/pos/outlets/${outletId}/vouchers`, payload)
}

export function createVoucherBatch(outletId: number, payload: { prefix: string; count: number; discount_type: string; discount_value: number; expires_at?: string }) {
  return post<Voucher[]>(`/pos/outlets/${outletId}/vouchers/batch`, payload)
}

export function fetchVoucher(id: number) {
  return get<Voucher>(`/pos/vouchers/${id}`)
}

export function validateVoucher(payload: { code: string; outlet_id: number; subtotal: number; items?: { product_id: number; subtotal: number }[] }) {
  return post<{ valid: boolean; voucher?: Voucher; discount_amount?: number }>('/pos/vouchers/validate', payload)
}

// --- Members ---

export function fetchMembers(outletId: number, params?: { page?: number; per_page?: number }) {
  return get<Member[]>(`/pos/outlets/${outletId}/members`, { params })
}

export function createMember(outletId: number, payload: MemberPayload) {
  return post<Member>(`/pos/outlets/${outletId}/members`, payload)
}

export function fetchMember(id: number) {
  return get<Member>(`/pos/members/${id}`)
}

export function updateMember(id: number, payload: MemberPayload) {
  return put<Member>(`/pos/members/${id}`, payload)
}

export function deleteMember(id: number) {
  return del(`/pos/members/${id}`)
}

export function searchMembers(outletId: number, params: { q: string }) {
  return get<Member[]>(`/pos/outlets/${outletId}/members/search`, { params })
}

// --- Tables ---

export function fetchTables(outletId: number) {
  return get<PosTable[]>(`/pos/outlets/${outletId}/tables`)
}

export function createTable(outletId: number, payload: { name: string }) {
  return post<PosTable>(`/pos/outlets/${outletId}/tables`, payload)
}

export function deleteTable(id: number) {
  return del(`/pos/tables/${id}`)
}

export function closeTableSession(id: number) {
  return post(`/pos/tables/${id}/close-session`)
}

// --- Order Queue ---

export function fetchOrderQueue(outletId: number) {
  return get<OrderQueueItem[]>(`/pos/outlets/${outletId}/order-queue`)
}

export function acceptOrder(id: number) {
  return post(`/pos/order-queue/${id}/accept`)
}

// --- Reports ---

export function fetchDailyReport(outletId: number, params: { date: string }) {
  return get<DailySummary>(`/pos/outlets/${outletId}/reports/daily`, { params })
}

export function fetchRangeReport(outletId: number, params: { start_date: string; end_date: string }) {
  return get(`/pos/outlets/${outletId}/reports/range`, { params })
}

export function fetchProductsReport(outletId: number, params: { start_date: string; end_date: string }) {
  return get(`/pos/outlets/${outletId}/reports/products`, { params })
}

export function fetchPaymentsReport(outletId: number, params: { start_date: string; end_date: string }) {
  return get(`/pos/outlets/${outletId}/reports/payments`, { params })
}

export function fetchDashboard(outletId: number) {
  return get<DashboardStats>(`/pos/outlets/${outletId}/reports/dashboard`)
}

export function exportReport(outletId: number, params: { start_date: string; end_date: string; format?: string }) {
  return get(`/pos/outlets/${outletId}/reports/export`, { params })
}

// --- Receipts ---

export function fetchReceipt(transactionId: number) {
  return get(`/pos/transactions/${transactionId}/receipt`)
}

export function updateReceiptTemplate(outletId: number, payload: { header?: string; footer?: string; width?: '58mm' | '80mm' }) {
  return put(`/pos/outlets/${outletId}/receipt-template`, payload)
}

// --- QR Order (Public - no auth) ---

export function fetchQrMenu(token: string) {
  return get<{ outlet: Outlet; categories: Category[]; products: Product[] }>(`/pos/qr/${token}/menu`)
}

export function createQrOrder(token: string, payload: { items: { product_id: number; variant_id?: number; quantity: number }[]; customer_name?: string; notes?: string }) {
  return post(`/pos/qr/${token}/order`, payload)
}

export function fetchQrOrder(token: string, orderId: number) {
  return get(`/pos/qr/${token}/order/${orderId}`)
}
