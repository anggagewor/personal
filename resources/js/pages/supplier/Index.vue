<script setup lang="ts">
import { ref, computed, onMounted } from 'vue'
import { useRoute } from 'vue-router'
import { formatCurrency, formatDate } from '@purdia/utils'
import BaseBadge from '@purdia/ui/src/components/BaseBadge.vue'
import { Package, ClipboardList, Wallet, Truck } from '@lucide/vue'
import type { SupplierDashboard } from '@/types/supplier'
import * as supplierApi from '@/api/supplier'

const route = useRoute()

const outletId = computed(() => Number(route.params.outletId))

const dashboard = ref<SupplierDashboard | null>(null)
const loading = ref(true)

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

async function fetchDashboard() {
  if (!outletId.value) return
  loading.value = true
  try {
    const res = await supplierApi.fetchDashboard(outletId.value)
    dashboard.value = res.data
  } catch {
    // Error handled by @purdia/http
  } finally {
    loading.value = false
  }
}

onMounted(fetchDashboard)
</script>

<template>
  <div>
    <!-- No outlet selected -->
    <div v-if="!outletId" class="mt-12 flex flex-col items-center text-center">
      <div class="flex h-16 w-16 items-center justify-center rounded-2xl bg-gray-100 dark:bg-gray-800">
        <Package :size="28" class="text-gray-400" />
      </div>
      <h3 class="mt-4 text-base font-medium text-gray-900 dark:text-white">Pilih Outlet</h3>
      <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">Pilih outlet terlebih dahulu untuk mengakses modul Supplier.</p>
    </div>

    <template v-else>
      <!-- Header -->
      <div class="flex items-center justify-between">
        <div>
          <h1 class="text-2xl font-semibold text-gray-900 dark:text-white">Supplier</h1>
          <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">Kelola supplier dan pembelian barang.</p>
        </div>
      </div>

      <!-- Loading -->
      <div v-if="loading" class="mt-6 py-8 text-center text-sm text-gray-400">Memuat data...</div>

      <template v-else-if="dashboard">
        <!-- Summary Cards -->
        <div class="mt-6 grid grid-cols-1 gap-4 sm:grid-cols-2 lg:grid-cols-3">
          <!-- Total Utang -->
          <div class="rounded-xl border border-gray-200 bg-white p-5 dark:border-gray-700 dark:bg-gray-800">
            <div class="flex items-center gap-3">
              <div class="flex h-10 w-10 items-center justify-center rounded-lg bg-red-100 text-red-600 dark:bg-red-900/30 dark:text-red-400">
                <Wallet :size="20" />
              </div>
              <div>
                <p class="text-xs text-gray-500 dark:text-gray-400">Total Utang</p>
                <p class="text-lg font-bold text-gray-900 dark:text-white">{{ formatCurrency(dashboard.total_outstanding_debt) }}</p>
              </div>
            </div>
          </div>

          <!-- PO Pending -->
          <div class="rounded-xl border border-gray-200 bg-white p-5 dark:border-gray-700 dark:bg-gray-800">
            <div class="flex items-center gap-3">
              <div class="flex h-10 w-10 items-center justify-center rounded-lg bg-amber-100 text-amber-600 dark:bg-amber-900/30 dark:text-amber-400">
                <ClipboardList :size="20" />
              </div>
              <div>
                <p class="text-xs text-gray-500 dark:text-gray-400">PO Pending</p>
                <p class="text-lg font-bold text-gray-900 dark:text-white">{{ dashboard.pending_po_count }}</p>
              </div>
            </div>
          </div>

          <!-- Quick Navigation -->
          <div class="rounded-xl border border-gray-200 bg-white p-5 dark:border-gray-700 dark:bg-gray-800">
            <div class="flex items-center gap-3">
              <div class="flex h-10 w-10 items-center justify-center rounded-lg bg-blue-100 text-blue-600 dark:bg-blue-900/30 dark:text-blue-400">
                <Truck :size="20" />
              </div>
              <div>
                <p class="text-xs text-gray-500 dark:text-gray-400">Pembelian Terakhir</p>
                <p class="text-lg font-bold text-gray-900 dark:text-white">{{ dashboard.recent_purchase_orders.length }}</p>
              </div>
            </div>
          </div>
        </div>

        <!-- Recent Purchase Orders -->
        <div class="mt-8">
          <h2 class="text-base font-semibold text-gray-900 dark:text-white">Pembelian Terakhir</h2>
          <div v-if="!dashboard.recent_purchase_orders.length" class="mt-4 rounded-xl border border-gray-200 bg-white p-8 text-center dark:border-gray-700 dark:bg-gray-800">
            <p class="text-sm text-gray-500 dark:text-gray-400">Belum ada pembelian.</p>
          </div>
          <div v-else class="mt-4 overflow-hidden rounded-xl border border-gray-200 dark:border-gray-700">
            <table class="w-full text-left text-sm">
              <thead class="border-b border-gray-200 bg-gray-50 dark:border-gray-700 dark:bg-gray-800/50">
                <tr>
                  <th class="px-4 py-3 font-medium text-gray-500 dark:text-gray-400">No. PO</th>
                  <th class="px-4 py-3 font-medium text-gray-500 dark:text-gray-400">Supplier</th>
                  <th class="px-4 py-3 font-medium text-gray-500 dark:text-gray-400">Tanggal</th>
                  <th class="px-4 py-3 font-medium text-gray-500 dark:text-gray-400">Status</th>
                  <th class="px-4 py-3 font-medium text-gray-500 dark:text-gray-400">Pembayaran</th>
                  <th class="px-4 py-3 text-right font-medium text-gray-500 dark:text-gray-400">Total</th>
                </tr>
              </thead>
              <tbody class="divide-y divide-gray-200 bg-white dark:divide-gray-700 dark:bg-gray-800">
                <tr v-for="po in dashboard.recent_purchase_orders" :key="po.id">
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
          </div>
        </div>
      </template>
    </template>
  </div>
</template>
