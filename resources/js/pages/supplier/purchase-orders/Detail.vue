<script setup lang="ts">
import { ref, computed, onMounted } from 'vue'
import { useRoute, useRouter } from 'vue-router'
import { useToast } from '@purdia/toast'
import { formatCurrency, formatDate } from '@purdia/utils'
import BaseButton from '@purdia/ui/src/components/BaseButton.vue'
import BaseBadge from '@purdia/ui/src/components/BaseBadge.vue'
import { ArrowLeft, CheckCircle, XCircle, Package, Wallet } from '@lucide/vue'
import type { PurchaseOrder, GoodsReceipt, SupplierPayment } from '@/types/supplier'
import * as supplierApi from '@/api/supplier'

const route = useRoute()
const router = useRouter()
const toast = useToast()

const outletId = computed(() => Number(route.query.outlet))
const poId = computed(() => Number(route.query.id))

const order = ref<PurchaseOrder | null>(null)
const receipts = ref<GoodsReceipt[]>([])
const payments = ref<SupplierPayment[]>([])
const loading = ref(true)
const actionLoading = ref(false)

// Modals (components will be created in a later task)
const showGoodsReceiptForm = ref(false)
const showPaymentForm = ref(false)

function statusVariant(status: string) {
  switch (status) {
    case 'draft': return 'neutral'
    case 'confirmed': return 'info'
    case 'partial': return 'warning'
    case 'received': return 'success'
    case 'cancelled': return 'danger'
    default: return 'neutral'
  }
}

function statusLabel(status: string) {
  switch (status) {
    case 'draft': return 'Draft'
    case 'confirmed': return 'Dikonfirmasi'
    case 'partial': return 'Sebagian'
    case 'received': return 'Diterima'
    case 'cancelled': return 'Dibatalkan'
    default: return status
  }
}

function paymentStatusVariant(status: string) {
  switch (status) {
    case 'unpaid': return 'danger'
    case 'partial': return 'warning'
    case 'paid': return 'success'
    default: return 'neutral'
  }
}

function paymentStatusLabel(status: string) {
  switch (status) {
    case 'unpaid': return 'Belum Lunas'
    case 'partial': return 'Sebagian'
    case 'paid': return 'Lunas'
    default: return status
  }
}

function formatPaymentMethod(method: string): string {
  switch (method) {
    case 'cash': return 'Tunai'
    case 'bank_transfer': return 'Transfer Bank'
    case 'e_wallet': return 'E-Wallet'
    default: return method
  }
}

const outstanding = computed(() => {
  if (!order.value) return 0
  return order.value.outstanding_balance
})

async function fetchOrder() {
  if (!poId.value) return
  loading.value = true
  try {
    const res = await supplierApi.fetchPurchaseOrder(poId.value)
    order.value = res.data
  } catch {
    // Error handled globally
  } finally {
    loading.value = false
  }
}

async function fetchReceipts() {
  if (!poId.value) return
  try {
    const res = await supplierApi.fetchGoodsReceipts(poId.value)
    receipts.value = res.data
  } catch {
    // Error handled globally
  }
}

async function fetchPayments() {
  if (!poId.value) return
  try {
    const res = await supplierApi.fetchPaymentsByPO(poId.value)
    payments.value = res.data
  } catch {
    // Error handled globally
  }
}

async function confirmOrder() {
  if (!order.value) return
  if (!confirm('Konfirmasi purchase order ini? Setelah dikonfirmasi, item tidak dapat diubah.')) return
  actionLoading.value = true
  try {
    await supplierApi.confirmPurchaseOrder(order.value.id)
    toast.success('Purchase order berhasil dikonfirmasi.')
    await fetchOrder()
  } catch {
    // Error handled globally
  } finally {
    actionLoading.value = false
  }
}

async function cancelOrder() {
  if (!order.value) return
  if (!confirm('Batalkan purchase order ini?')) return
  actionLoading.value = true
  try {
    await supplierApi.cancelPurchaseOrder(order.value.id)
    toast.success('Purchase order berhasil dibatalkan.')
    await fetchOrder()
  } catch {
    // Error handled globally
  } finally {
    actionLoading.value = false
  }
}

function openGoodsReceiptForm() {
  showGoodsReceiptForm.value = true
}

function openPaymentForm() {
  showPaymentForm.value = true
}

function onGoodsReceiptSaved() {
  fetchOrder()
  fetchReceipts()
}

function onPaymentSaved() {
  fetchOrder()
  fetchPayments()
}

function goBack() {
  router.push({ name: 'supplier.purchase-orders', query: { outlet: outletId.value } })
}

onMounted(() => {
  fetchOrder()
  fetchReceipts()
  fetchPayments()
})
</script>

<template>
  <div>
    <!-- Back button -->
    <button
      class="mb-4 flex items-center gap-1 text-sm text-gray-500 hover:text-gray-700 dark:text-gray-400 dark:hover:text-gray-200"
      @click="goBack"
    >
      <ArrowLeft :size="16" />
      Kembali ke daftar PO
    </button>

    <!-- Loading -->
    <div v-if="loading" class="py-8 text-center text-sm text-gray-400">Memuat data...</div>

    <template v-else-if="order">
      <!-- Header -->
      <div class="flex items-start justify-between">
        <div>
          <h1 class="text-2xl font-semibold text-gray-900 dark:text-white">{{ order.po_number }}</h1>
          <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">{{ order.supplier_name }}</p>
        </div>
        <div class="flex gap-2">
          <BaseButton
            v-if="order.status === 'draft'"
            variant="primary"
            size="sm"
            :icon="CheckCircle"
            :disabled="actionLoading"
            @click="confirmOrder"
          >
            Konfirmasi
          </BaseButton>
          <BaseButton
            v-if="order.status === 'draft' || order.status === 'confirmed'"
            variant="danger"
            size="sm"
            :icon="XCircle"
            :disabled="actionLoading"
            @click="cancelOrder"
          >
            Batalkan
          </BaseButton>
          <BaseButton
            v-if="order.status === 'confirmed' || order.status === 'partial'"
            variant="secondary"
            size="sm"
            :icon="Package"
            @click="openGoodsReceiptForm"
          >
            Terima Barang
          </BaseButton>
          <BaseButton
            v-if="order.status !== 'cancelled' && outstanding > 0"
            variant="secondary"
            size="sm"
            :icon="Wallet"
            @click="openPaymentForm"
          >
            Bayar
          </BaseButton>
        </div>
      </div>

      <!-- Info cards -->
      <div class="mt-6 grid grid-cols-1 gap-4 sm:grid-cols-2 lg:grid-cols-4">
        <div class="rounded-xl border border-gray-200 bg-white p-4 dark:border-gray-700 dark:bg-gray-800">
          <p class="text-xs text-gray-500 dark:text-gray-400">Tanggal Order</p>
          <p class="mt-1 text-sm font-medium text-gray-900 dark:text-white">
            {{ formatDate(order.order_date, { day: 'numeric', month: 'long', year: 'numeric' }) }}
          </p>
        </div>
        <div class="rounded-xl border border-gray-200 bg-white p-4 dark:border-gray-700 dark:bg-gray-800">
          <p class="text-xs text-gray-500 dark:text-gray-400">Tanggal Pengiriman</p>
          <p class="mt-1 text-sm font-medium text-gray-900 dark:text-white">
            {{ order.expected_delivery_date ? formatDate(order.expected_delivery_date, { day: 'numeric', month: 'long', year: 'numeric' }) : '—' }}
          </p>
        </div>
        <div class="rounded-xl border border-gray-200 bg-white p-4 dark:border-gray-700 dark:bg-gray-800">
          <p class="text-xs text-gray-500 dark:text-gray-400">Status</p>
          <div class="mt-1">
            <BaseBadge :variant="statusVariant(order.status)" size="sm">
              {{ statusLabel(order.status) }}
            </BaseBadge>
          </div>
        </div>
        <div class="rounded-xl border border-gray-200 bg-white p-4 dark:border-gray-700 dark:bg-gray-800">
          <p class="text-xs text-gray-500 dark:text-gray-400">Pembayaran</p>
          <div class="mt-1">
            <BaseBadge :variant="paymentStatusVariant(order.payment_status)" size="sm">
              {{ paymentStatusLabel(order.payment_status) }}
            </BaseBadge>
          </div>
        </div>
      </div>

      <!-- Notes -->
      <div v-if="order.notes" class="mt-4 rounded-xl border border-gray-200 bg-white p-4 dark:border-gray-700 dark:bg-gray-800">
        <p class="text-xs text-gray-500 dark:text-gray-400">Catatan</p>
        <p class="mt-1 text-sm text-gray-700 dark:text-gray-300">{{ order.notes }}</p>
      </div>

      <!-- Items table -->
      <div class="mt-8">
        <h2 class="text-base font-semibold text-gray-900 dark:text-white">Item Pembelian</h2>
        <div class="mt-4 overflow-x-auto rounded-xl border border-gray-200 bg-white dark:border-gray-700 dark:bg-gray-800">
          <table class="w-full text-left text-sm">
            <thead>
              <tr class="border-b border-gray-200 dark:border-gray-700">
                <th class="px-4 py-3 font-medium text-gray-500 dark:text-gray-400">Produk</th>
                <th class="px-4 py-3 font-medium text-gray-500 dark:text-gray-400">Varian</th>
                <th class="px-4 py-3 text-right font-medium text-gray-500 dark:text-gray-400">Qty Dipesan</th>
                <th class="px-4 py-3 text-right font-medium text-gray-500 dark:text-gray-400">Qty Diterima</th>
                <th class="px-4 py-3 text-right font-medium text-gray-500 dark:text-gray-400">Harga</th>
                <th class="px-4 py-3 text-right font-medium text-gray-500 dark:text-gray-400">Subtotal</th>
              </tr>
            </thead>
            <tbody>
              <tr
                v-for="item in order.items"
                :key="item.id"
                class="border-b border-gray-100 last:border-0 dark:border-gray-700/50"
              >
                <td class="px-4 py-3 font-medium text-gray-900 dark:text-white">{{ item.product_name }}</td>
                <td class="px-4 py-3 text-gray-600 dark:text-gray-300">{{ item.variant_name }}</td>
                <td class="px-4 py-3 text-right text-gray-900 dark:text-white">{{ item.quantity }}</td>
                <td class="px-4 py-3 text-right" :class="item.received_quantity >= item.quantity ? 'text-green-600 dark:text-green-400' : 'text-gray-900 dark:text-white'">
                  {{ item.received_quantity }}
                </td>
                <td class="px-4 py-3 text-right text-gray-600 dark:text-gray-300">{{ formatCurrency(item.unit_cost) }}</td>
                <td class="px-4 py-3 text-right font-medium text-gray-900 dark:text-white">{{ formatCurrency(item.subtotal) }}</td>
              </tr>
            </tbody>
          </table>
        </div>
      </div>

      <!-- Totals -->
      <div class="mt-4 flex justify-end">
        <div class="w-full max-w-xs space-y-2 rounded-xl border border-gray-200 bg-white p-4 dark:border-gray-700 dark:bg-gray-800">
          <div class="flex justify-between text-sm">
            <span class="text-gray-500 dark:text-gray-400">Total</span>
            <span class="font-medium text-gray-900 dark:text-white">{{ formatCurrency(order.total_amount) }}</span>
          </div>
          <div class="flex justify-between text-sm">
            <span class="text-gray-500 dark:text-gray-400">Total Dibayar</span>
            <span class="font-medium text-green-600 dark:text-green-400">{{ formatCurrency(order.total_paid) }}</span>
          </div>
          <div class="flex justify-between border-t border-gray-200 pt-2 text-sm dark:border-gray-700">
            <span class="font-medium text-gray-700 dark:text-gray-300">Sisa Tagihan</span>
            <span class="font-semibold" :class="outstanding > 0 ? 'text-red-600 dark:text-red-400' : 'text-gray-900 dark:text-white'">
              {{ formatCurrency(outstanding) }}
            </span>
          </div>
        </div>
      </div>

      <!-- Goods Receipts -->
      <div class="mt-8">
        <h2 class="text-base font-semibold text-gray-900 dark:text-white">Penerimaan Barang</h2>
        <div v-if="!receipts.length" class="mt-4 rounded-xl border border-gray-200 bg-white p-6 text-center dark:border-gray-700 dark:bg-gray-800">
          <p class="text-sm text-gray-500 dark:text-gray-400">Belum ada penerimaan barang.</p>
        </div>
        <div v-else class="mt-4 space-y-3">
          <div
            v-for="receipt in receipts"
            :key="receipt.id"
            class="rounded-xl border border-gray-200 bg-white p-4 dark:border-gray-700 dark:bg-gray-800"
          >
            <div class="flex items-center justify-between">
              <div>
                <p class="text-sm font-medium text-gray-900 dark:text-white">
                  {{ formatDate(receipt.receipt_date, { day: 'numeric', month: 'long', year: 'numeric' }) }}
                </p>
                <p v-if="receipt.notes" class="mt-0.5 text-xs text-gray-500 dark:text-gray-400">{{ receipt.notes }}</p>
              </div>
              <span class="text-xs text-gray-400 dark:text-gray-500">
                {{ receipt.items.length }} item
              </span>
            </div>
            <div class="mt-2 flex flex-wrap gap-2">
              <span
                v-for="item in receipt.items"
                :key="item.id"
                class="rounded-md bg-gray-100 px-2 py-1 text-xs text-gray-700 dark:bg-gray-700 dark:text-gray-300"
              >
                {{ item.quantity }} unit
              </span>
            </div>
          </div>
        </div>
      </div>

      <!-- Payments -->
      <div class="mt-8">
        <h2 class="text-base font-semibold text-gray-900 dark:text-white">Riwayat Pembayaran</h2>
        <div v-if="!payments.length" class="mt-4 rounded-xl border border-gray-200 bg-white p-6 text-center dark:border-gray-700 dark:bg-gray-800">
          <p class="text-sm text-gray-500 dark:text-gray-400">Belum ada pembayaran.</p>
        </div>
        <div v-else class="mt-4 overflow-x-auto rounded-xl border border-gray-200 bg-white dark:border-gray-700 dark:bg-gray-800">
          <table class="w-full text-left text-sm">
            <thead>
              <tr class="border-b border-gray-200 dark:border-gray-700">
                <th class="px-4 py-3 font-medium text-gray-500 dark:text-gray-400">Tanggal</th>
                <th class="px-4 py-3 font-medium text-gray-500 dark:text-gray-400">Metode</th>
                <th class="px-4 py-3 text-right font-medium text-gray-500 dark:text-gray-400">Jumlah</th>
                <th class="px-4 py-3 font-medium text-gray-500 dark:text-gray-400">Catatan</th>
              </tr>
            </thead>
            <tbody>
              <tr
                v-for="payment in payments"
                :key="payment.id"
                class="border-b border-gray-100 last:border-0 dark:border-gray-700/50"
              >
                <td class="px-4 py-3 text-gray-900 dark:text-white">
                  {{ formatDate(payment.payment_date, { day: 'numeric', month: 'short', year: 'numeric' }) }}
                </td>
                <td class="px-4 py-3 text-gray-600 dark:text-gray-300">{{ formatPaymentMethod(payment.payment_method) }}</td>
                <td class="px-4 py-3 text-right font-medium text-gray-900 dark:text-white">{{ formatCurrency(payment.amount) }}</td>
                <td class="px-4 py-3 text-gray-500 dark:text-gray-400">{{ payment.notes ?? '—' }}</td>
              </tr>
            </tbody>
          </table>
        </div>
      </div>
    </template>
  </div>
</template>
