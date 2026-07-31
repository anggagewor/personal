import { get, post, put, del } from '@purdia/http'
import type {
  Supplier,
  SupplierPayload,
  PurchaseOrder,
  PurchaseOrderPayload,
  GoodsReceipt,
  GoodsReceiptPayload,
  SupplierPayment,
  SupplierPaymentPayload,
  SupplierProduct,
  LinkProductPayload,
  PurchaseSummary,
  PurchaseBySupplier,
  PurchaseByProduct,
  SupplierDashboard,
} from '@/types/supplier'

// --- Suppliers ---

export function fetchSuppliers(outletId: number, params?: { page?: number; per_page?: number; search?: string }) {
  return get<Supplier[]>(`/supplier/outlets/${outletId}/suppliers`, { params })
}

export function createSupplier(outletId: number, payload: SupplierPayload) {
  return post<Supplier>(`/supplier/outlets/${outletId}/suppliers`, payload)
}

export function fetchSupplier(id: number) {
  return get<Supplier>(`/supplier/suppliers/${id}`)
}

export function updateSupplier(id: number, payload: SupplierPayload) {
  return put<Supplier>(`/supplier/suppliers/${id}`, payload)
}

export function deleteSupplier(id: number) {
  return del(`/supplier/suppliers/${id}`)
}

export function searchSuppliers(outletId: number, params: { q?: string; search?: string }) {
  return get<Supplier[]>(`/supplier/outlets/${outletId}/suppliers/search`, { params })
}

// --- Purchase Orders ---

export function fetchPurchaseOrders(outletId: number, params?: { page?: number; per_page?: number; status?: string; payment_status?: string; date_from?: string; date_to?: string }) {
  return get<PurchaseOrder[]>(`/supplier/outlets/${outletId}/purchase-orders`, { params })
}

export function createPurchaseOrder(outletId: number, payload: PurchaseOrderPayload) {
  return post<PurchaseOrder>(`/supplier/outlets/${outletId}/purchase-orders`, payload)
}

export function fetchPurchaseOrder(id: number) {
  return get<PurchaseOrder>(`/supplier/purchase-orders/${id}`)
}

export function updatePurchaseOrder(id: number, payload: PurchaseOrderPayload) {
  return put<PurchaseOrder>(`/supplier/purchase-orders/${id}`, payload)
}

export function confirmPurchaseOrder(id: number) {
  return post(`/supplier/purchase-orders/${id}/confirm`)
}

export function cancelPurchaseOrder(id: number) {
  return post(`/supplier/purchase-orders/${id}/cancel`)
}

// --- Goods Receipts ---

export function fetchGoodsReceipts(purchaseOrderId: number) {
  return get<GoodsReceipt[]>(`/supplier/purchase-orders/${purchaseOrderId}/receipts`)
}

export function createGoodsReceipt(purchaseOrderId: number, payload: GoodsReceiptPayload) {
  return post<GoodsReceipt>(`/supplier/purchase-orders/${purchaseOrderId}/receipts`, payload)
}

// --- Payments ---

export function fetchPaymentsByPO(purchaseOrderId: number) {
  return get<SupplierPayment[]>(`/supplier/purchase-orders/${purchaseOrderId}/payments`)
}

export function createPayment(purchaseOrderId: number, payload: SupplierPaymentPayload) {
  return post<SupplierPayment>(`/supplier/purchase-orders/${purchaseOrderId}/payments`, payload)
}

export function fetchPaymentsBySupplier(supplierId: number) {
  return get<SupplierPayment[]>(`/supplier/suppliers/${supplierId}/payments`)
}

// --- Supplier Products ---

export function fetchSupplierProducts(supplierId: number) {
  return get<SupplierProduct[]>(`/supplier/suppliers/${supplierId}/products`)
}

export function linkProduct(supplierId: number, payload: LinkProductPayload) {
  return post<SupplierProduct>(`/supplier/suppliers/${supplierId}/products`, payload)
}

export function unlinkProduct(supplierId: number, variantId: number) {
  return del(`/supplier/suppliers/${supplierId}/products/${variantId}`)
}

// --- Reports ---

export function fetchPurchaseSummary(outletId: number, params?: { start_date?: string; end_date?: string }) {
  return get<PurchaseSummary>(`/supplier/outlets/${outletId}/reports/summary`, { params })
}

export function fetchPurchaseBySupplier(outletId: number, params?: { start_date?: string; end_date?: string }) {
  return get<PurchaseBySupplier[]>(`/supplier/outlets/${outletId}/reports/by-supplier`, { params })
}

export function fetchPurchaseByProduct(outletId: number, params?: { start_date?: string; end_date?: string }) {
  return get<PurchaseByProduct[]>(`/supplier/outlets/${outletId}/reports/by-product`, { params })
}

export function exportReport(outletId: number, params?: { start_date?: string; end_date?: string; format?: string }) {
  return get(`/supplier/outlets/${outletId}/reports/export`, { params })
}

export function fetchDashboard(outletId: number) {
  return get<SupplierDashboard>(`/supplier/outlets/${outletId}/dashboard`)
}
