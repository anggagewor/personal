<script setup lang="ts">
import { ref, computed, watch } from 'vue'
import { useRoute, useRouter } from 'vue-router'
import { formatCurrency, formatDate } from '@purdia/utils'
import BaseButton from '@purdia/ui/src/components/BaseButton.vue'
import BaseSelect from '@purdia/ui/src/components/BaseSelect.vue'
import BaseInput from '@purdia/ui/src/components/BaseInput.vue'
import BaseBadge from '@purdia/ui/src/components/BaseBadge.vue'
import { Plus, ClipboardList, ChevronLeft, ChevronRight } from '@lucide/vue'
import type { PurchaseOrder } from '@/types/supplier'
import * as supplierApi from '@/api/supplier'

const route = useRoute()
const router = useRouter()

const outletId = computed(() => Number(route.query.outlet))

const orders = ref<PurchaseOrder[]>([])
const loading = ref(true)
const currentPage = ref(1)
const totalPages = ref(1)
const perPage = 20

// Filters
const filterStatus = ref('')
const filterPaymentStatus = ref('')
const filterDateFrom = ref('')
const filterDateTo = ref('')

const statusOptions = [
  { label: 'Semua Status', value: '' },
  { label: 'Draft', value: 'draft' },
  { label: 'Dikonfirmasi', value: 'confirmed' },
  { label: 'Sebagian', value: 'partial' },
  { label: 'Diterima', value: 'received' },
  { label: 'Dibatalkan', value: 'cancelled' },
]

const paymentStatusOptions = [
  { label: 'Semua Pembayaran', value: '' },
  { label: 'Belum Lunas', value: 'unpaid' },
  { label: 'Sebagian', value: 'partial' },
  { label: 'Lunas', value: 'paid' },
]

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

async function fetchOrders() {
  if (!outletId.value) return
  loading.value = true
  try {
    const params: Record<string, unknown> = {
      page: currentPage.value,
      per_page: perPage,
    }
    if (filterStatus.value) params.status = filterStatus.value
    if (filterPaymentStatus.value) params.payment_status = filterPaymentStatus.value
    if (filterDateFrom.value) params.date_from = filterDateFrom.value
    if (filterDateTo.value) params.date_to = filterDateTo.value

    const res = await supplierApi.fetchPurchaseOrders(outletId.value, params as Parameters<typeof supplierApi.fetchPurchaseOrders>[1])
    if (Array.isArray(res.data)) {
      orders.value = res.data
    } else {
      const paginated = res.data as unknown as { data: PurchaseOrder[]; last_page: number }
      orders.value = paginated.data ?? res.data
      totalPages.value = paginated.last_page ?? 1
    }
  } catch {
    // Error handled by @purdia/http
  } finally {
    loading.value = false
  }
}

function applyFilters() {
  currentPage.value = 1
  fetchOrders()
}

function goToDetail(po: PurchaseOrder) {
  router.push({ name: 'supplier.purchase-orders.detail', query: { outlet: outletId.value, id: po.id } })
}

function goToCreate() {
  router.push({ name: 'supplier.purchase-orders.create', query: { outlet: outletId.value } })
}

function goToPage(page: number) {
  if (page < 1 || page > totalPages.value) return
  currentPage.value = page
}

watch(currentPage, () => fetchOrders())

// Initial load
fetchOrders()
</script>

<template>
  <div>
    <!-- Header -->
    <div class="flex items-center justify-between">
      <div>
        <h1 class="text-2xl font-semibold text-gray-900 dark:text-white">Purchase Order</h1>
        <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">Kelola pembelian barang ke supplier.</p>
      </div>
      <BaseButton variant="primary" size="sm" :icon="Plus" @click="goToCreate">
        Buat PO
      </BaseButton>
    </div>

    <!-- Filters -->
    <div class="mt-6 grid grid-cols-1 gap-3 sm:grid-cols-2 lg:grid-cols-4">
      <BaseSelect
        v-model="filterStatus"
        :options="statusOptions"
        placeholder="Semua Status"
        @update:model-value="applyFilters"
      />
      <BaseSelect
        v-model="filterPaymentStatus"
        :options="paymentStatusOptions"
        placeholder="Semua Pembayaran"
        @update:model-value="applyFilters"
      />
      <BaseInput
        v-model="filterDateFrom"
        type="date"
        placeholder="Dari tanggal"
        @change="applyFilters"
      />
      <BaseInput
        v-model="filterDateTo"
        type="date"
        placeholder="Sampai tanggal"
        @change="applyFilters"
      />
    </div>

    <!-- Loading -->
    <div v-if="loading" class="mt-6 py-8 text-center text-sm text-gray-400">Memuat data...</div>

    <!-- Empty state -->
    <div v-else-if="!orders.length" class="mt-12 flex flex-col items-center text-center">
      <div class="flex h-16 w-16 items-center justify-center rounded-2xl bg-gray-100 dark:bg-gray-800">
        <ClipboardList :size="28" class="text-gray-400" />
      </div>
      <h3 class="mt-4 text-base font-medium text-gray-900 dark:text-white">Belum ada purchase order</h3>
      <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">Buat purchase order pertama untuk outlet ini.</p>
      <BaseButton variant="primary" size="sm" :icon="Plus" class="mt-4" @click="goToCreate">
        Buat PO
      </BaseButton>
    </div>

    <!-- PO Table -->
    <div v-else class="mt-6 overflow-x-auto rounded-xl border border-gray-200 bg-white dark:border-gray-700 dark:bg-gray-800">
      <table class="w-full text-left text-sm">
        <thead>
          <tr class="border-b border-gray-200 dark:border-gray-700">
            <th class="px-4 py-3 font-medium text-gray-500 dark:text-gray-400">No. PO</th>
            <th class="px-4 py-3 font-medium text-gray-500 dark:text-gray-400">Supplier</th>
            <th class="px-4 py-3 font-medium text-gray-500 dark:text-gray-400">Tanggal</th>
            <th class="px-4 py-3 font-medium text-gray-500 dark:text-gray-400">Status</th>
            <th class="px-4 py-3 font-medium text-gray-500 dark:text-gray-400">Pembayaran</th>
            <th class="px-4 py-3 text-right font-medium text-gray-500 dark:text-gray-400">Total</th>
          </tr>
        </thead>
        <tbody>
          <tr
            v-for="po in orders"
            :key="po.id"
            class="border-b border-gray-100 last:border-0 hover:bg-gray-50 dark:border-gray-700/50 dark:hover:bg-gray-700/30 cursor-pointer"
            @click="goToDetail(po)"
          >
            <td class="px-4 py-3 font-medium text-gray-900 dark:text-white">{{ po.po_number }}</td>
            <td class="px-4 py-3 text-gray-700 dark:text-gray-300">{{ po.supplier_name }}</td>
            <td class="px-4 py-3 text-gray-500 dark:text-gray-400">{{ formatDate(po.order_date, { day: 'numeric', month: 'short', year: 'numeric' }) }}</td>
            <td class="px-4 py-3">
              <BaseBadge :variant="statusVariant(po.status)" size="sm">
                {{ statusLabel(po.status) }}
              </BaseBadge>
            </td>
            <td class="px-4 py-3">
              <BaseBadge :variant="paymentStatusVariant(po.payment_status)" size="sm">
                {{ paymentStatusLabel(po.payment_status) }}
              </BaseBadge>
            </td>
            <td class="px-4 py-3 text-right font-medium text-gray-900 dark:text-white">{{ formatCurrency(po.total_amount) }}</td>
          </tr>
        </tbody>
      </table>

      <!-- Pagination -->
      <div
        v-if="totalPages > 1"
        class="flex items-center justify-between border-t border-gray-200 px-4 py-3 dark:border-gray-700"
      >
        <span class="text-xs text-gray-500 dark:text-gray-400">
          Halaman {{ currentPage }} dari {{ totalPages }}
        </span>
        <div class="flex gap-1">
          <button
            class="rounded p-1.5 text-gray-400 hover:text-gray-600 disabled:opacity-30 dark:hover:text-gray-300"
            :disabled="currentPage <= 1"
            @click="goToPage(currentPage - 1)"
          >
            <ChevronLeft :size="16" />
          </button>
          <button
            class="rounded p-1.5 text-gray-400 hover:text-gray-600 disabled:opacity-30 dark:hover:text-gray-300"
            :disabled="currentPage >= totalPages"
            @click="goToPage(currentPage + 1)"
          >
            <ChevronRight :size="16" />
          </button>
        </div>
      </div>
    </div>
  </div>
</template>
