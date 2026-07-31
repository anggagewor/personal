<script setup lang="ts">
import { ref, computed, watch, onMounted } from 'vue'
import { useRoute } from 'vue-router'
import { formatCurrency } from '@purdia/utils'
import BaseButton from '@purdia/ui/src/components/BaseButton.vue'
import BaseInput from '@purdia/ui/src/components/BaseInput.vue'
import { Download, ShoppingCart, Wallet, CreditCard, ClipboardList } from '@lucide/vue'
import type { PurchaseSummary, PurchaseBySupplier, PurchaseByProduct } from '@/types/supplier'
import * as supplierApi from '@/api/supplier'

const route = useRoute()

const outletId = computed(() => Number(route.query.outlet))

// Date range filters — default to current month
const now = new Date()
const firstOfMonth = new Date(now.getFullYear(), now.getMonth(), 1).toISOString().slice(0, 10)
const today = now.toISOString().slice(0, 10)

const dateFrom = ref(firstOfMonth)
const dateTo = ref(today)

const summary = ref<PurchaseSummary | null>(null)
const bySupplier = ref<PurchaseBySupplier[]>([])
const byProduct = ref<PurchaseByProduct[]>([])
const loading = ref(true)

async function fetchReports() {
  if (!outletId.value) return
  loading.value = true
  try {
    const params = { start_date: dateFrom.value, end_date: dateTo.value }
    const [summaryRes, supplierRes, productRes] = await Promise.all([
      supplierApi.fetchPurchaseSummary(outletId.value, params),
      supplierApi.fetchPurchaseBySupplier(outletId.value, params),
      supplierApi.fetchPurchaseByProduct(outletId.value, params),
    ])
    summary.value = summaryRes.data
    bySupplier.value = supplierRes.data
    byProduct.value = productRes.data
  } catch {
    // Error handled by @purdia/http
  } finally {
    loading.value = false
  }
}

function exportCsv() {
  if (!outletId.value) return
  const params = new URLSearchParams({
    start_date: dateFrom.value,
    end_date: dateTo.value,
    format: 'csv',
  })
  window.open(`/api/supplier/outlets/${outletId.value}/reports/export?${params.toString()}`, '_blank')
}

watch([dateFrom, dateTo], () => {
  fetchReports()
})

onMounted(() => {
  fetchReports()
})
</script>

<template>
  <div>
    <!-- Header -->
    <div class="flex items-center justify-between">
      <div>
        <h1 class="text-2xl font-semibold text-gray-900 dark:text-white">Laporan Pembelian</h1>
        <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">Ringkasan dan detail pembelian ke supplier.</p>
      </div>
      <BaseButton variant="secondary" size="sm" :icon="Download" :disabled="!summary" @click="exportCsv">
        Export CSV
      </BaseButton>
    </div>

    <!-- Date Range Filter -->
    <div class="mt-4 flex flex-wrap items-center gap-3">
      <div class="flex items-center gap-2">
        <label class="text-sm text-gray-500 dark:text-gray-400">Dari</label>
        <BaseInput v-model="dateFrom" type="date" class="w-44" />
      </div>
      <div class="flex items-center gap-2">
        <label class="text-sm text-gray-500 dark:text-gray-400">Sampai</label>
        <BaseInput v-model="dateTo" type="date" class="w-44" />
      </div>
    </div>

    <!-- Loading -->
    <div v-if="loading" class="mt-6 py-8 text-center text-sm text-gray-400">Memuat data...</div>

    <template v-else-if="summary">
      <!-- Summary Cards -->
      <div class="mt-6 grid grid-cols-1 gap-4 sm:grid-cols-2 lg:grid-cols-4">
        <div class="rounded-xl border border-gray-200 bg-white p-5 dark:border-gray-700 dark:bg-gray-800">
          <div class="flex items-center gap-3">
            <div class="flex h-10 w-10 items-center justify-center rounded-lg bg-blue-100 text-blue-600 dark:bg-blue-900/30 dark:text-blue-400">
              <ShoppingCart :size="20" />
            </div>
            <div>
              <p class="text-xs text-gray-500 dark:text-gray-400">Total Pembelian</p>
              <p class="text-lg font-bold text-gray-900 dark:text-white">{{ formatCurrency(summary.total_purchase_value) }}</p>
            </div>
          </div>
        </div>

        <div class="rounded-xl border border-gray-200 bg-white p-5 dark:border-gray-700 dark:bg-gray-800">
          <div class="flex items-center gap-3">
            <div class="flex h-10 w-10 items-center justify-center rounded-lg bg-emerald-100 text-emerald-600 dark:bg-emerald-900/30 dark:text-emerald-400">
              <CreditCard :size="20" />
            </div>
            <div>
              <p class="text-xs text-gray-500 dark:text-gray-400">Total Dibayar</p>
              <p class="text-lg font-bold text-gray-900 dark:text-white">{{ formatCurrency(summary.total_paid) }}</p>
            </div>
          </div>
        </div>

        <div class="rounded-xl border border-gray-200 bg-white p-5 dark:border-gray-700 dark:bg-gray-800">
          <div class="flex items-center gap-3">
            <div class="flex h-10 w-10 items-center justify-center rounded-lg bg-red-100 text-red-600 dark:bg-red-900/30 dark:text-red-400">
              <Wallet :size="20" />
            </div>
            <div>
              <p class="text-xs text-gray-500 dark:text-gray-400">Total Utang</p>
              <p class="text-lg font-bold text-gray-900 dark:text-white">{{ formatCurrency(summary.total_outstanding_debt) }}</p>
            </div>
          </div>
        </div>

        <div class="rounded-xl border border-gray-200 bg-white p-5 dark:border-gray-700 dark:bg-gray-800">
          <div class="flex items-center gap-3">
            <div class="flex h-10 w-10 items-center justify-center rounded-lg bg-amber-100 text-amber-600 dark:bg-amber-900/30 dark:text-amber-400">
              <ClipboardList :size="20" />
            </div>
            <div>
              <p class="text-xs text-gray-500 dark:text-gray-400">Jumlah PO</p>
              <p class="text-lg font-bold text-gray-900 dark:text-white">{{ summary.purchase_count }}</p>
            </div>
          </div>
        </div>
      </div>

      <!-- Per Supplier Table -->
      <div class="mt-8">
        <h2 class="text-base font-semibold text-gray-900 dark:text-white">Per Supplier</h2>
        <div v-if="!bySupplier.length" class="mt-4 rounded-xl border border-gray-200 bg-white p-8 text-center dark:border-gray-700 dark:bg-gray-800">
          <p class="text-sm text-gray-500 dark:text-gray-400">Tidak ada data pembelian per supplier untuk periode ini.</p>
        </div>
        <div v-else class="mt-4 overflow-hidden rounded-xl border border-gray-200 dark:border-gray-700">
          <table class="w-full text-left text-sm">
            <thead class="border-b border-gray-200 bg-gray-50 dark:border-gray-700 dark:bg-gray-800/50">
              <tr>
                <th class="px-4 py-3 font-medium text-gray-500 dark:text-gray-400">Supplier</th>
                <th class="px-4 py-3 text-right font-medium text-gray-500 dark:text-gray-400">Total Pembelian</th>
                <th class="px-4 py-3 text-right font-medium text-gray-500 dark:text-gray-400">Sisa Utang</th>
              </tr>
            </thead>
            <tbody class="divide-y divide-gray-200 bg-white dark:divide-gray-700 dark:bg-gray-800">
              <tr v-for="item in bySupplier" :key="item.supplier_id">
                <td class="px-4 py-3 font-medium text-gray-900 dark:text-white">{{ item.supplier_name }}</td>
                <td class="px-4 py-3 text-right text-gray-700 dark:text-gray-300">{{ formatCurrency(item.total_purchase) }}</td>
                <td class="px-4 py-3 text-right text-gray-700 dark:text-gray-300">{{ formatCurrency(item.outstanding_debt) }}</td>
              </tr>
            </tbody>
          </table>
        </div>
      </div>

      <!-- Per Product Table -->
      <div class="mt-8">
        <h2 class="text-base font-semibold text-gray-900 dark:text-white">Per Produk</h2>
        <div v-if="!byProduct.length" class="mt-4 rounded-xl border border-gray-200 bg-white p-8 text-center dark:border-gray-700 dark:bg-gray-800">
          <p class="text-sm text-gray-500 dark:text-gray-400">Tidak ada data pembelian per produk untuk periode ini.</p>
        </div>
        <div v-else class="mt-4 overflow-hidden rounded-xl border border-gray-200 dark:border-gray-700">
          <table class="w-full text-left text-sm">
            <thead class="border-b border-gray-200 bg-gray-50 dark:border-gray-700 dark:bg-gray-800/50">
              <tr>
                <th class="px-4 py-3 font-medium text-gray-500 dark:text-gray-400">Produk</th>
                <th class="px-4 py-3 font-medium text-gray-500 dark:text-gray-400">Varian</th>
                <th class="px-4 py-3 text-right font-medium text-gray-500 dark:text-gray-400">Total Qty</th>
                <th class="px-4 py-3 text-right font-medium text-gray-500 dark:text-gray-400">Total Biaya</th>
              </tr>
            </thead>
            <tbody class="divide-y divide-gray-200 bg-white dark:divide-gray-700 dark:bg-gray-800">
              <tr v-for="item in byProduct" :key="item.product_variant_id">
                <td class="px-4 py-3 font-medium text-gray-900 dark:text-white">{{ item.product_name }}</td>
                <td class="px-4 py-3 text-gray-700 dark:text-gray-300">{{ item.variant_name }}</td>
                <td class="px-4 py-3 text-right text-gray-700 dark:text-gray-300">{{ item.total_quantity }}</td>
                <td class="px-4 py-3 text-right text-gray-700 dark:text-gray-300">{{ formatCurrency(item.total_cost) }}</td>
              </tr>
            </tbody>
          </table>
        </div>
      </div>
    </template>

    <!-- No outlet state -->
    <div v-else-if="!outletId" class="mt-6 py-8 text-center text-sm text-gray-400">
      Pilih outlet terlebih dahulu.
    </div>
  </div>
</template>
