<script setup lang="ts">
import { ref, computed, onMounted, watch } from 'vue'
import { useRoute } from 'vue-router'
import { formatCurrency, formatDate } from '@purdia/utils'
import BaseButton from '@purdia/ui/src/components/BaseButton.vue'
import BaseInput from '@purdia/ui/src/components/BaseInput.vue'
import { Download, CalendarDays } from '@lucide/vue'
import type { DailySummary } from '@/types/pos'
import * as posApi from '@/api/pos'
import { usePosOutlet } from '@/composables/usePosOutlet'

const route = useRoute()
const { outletId } = usePosOutlet()

const selectedDate = ref(new Date().toISOString().slice(0, 10))
const summary = ref<DailySummary | null>(null)
const loading = ref(false)

async function fetchReport() {
  if (!outletId.value) return
  loading.value = true
  try {
    const res = await posApi.fetchDailyReport(outletId.value, { date: selectedDate.value })
    summary.value = res.data
  } catch {
    // Error handled by @purdia/http onError
  } finally {
    loading.value = false
  }
}

function exportCsv() {
  if (!summary.value) return

  const rows: string[][] = [
    ['Tanggal', 'Penjualan Kotor', 'Total Diskon', 'Pendapatan Bersih', 'Jumlah Transaksi', 'Rata-rata Transaksi'],
    [
      summary.value.date,
      String(summary.value.gross_revenue),
      String(summary.value.total_discount),
      String(summary.value.total_revenue),
      String(summary.value.transaction_count),
      String(summary.value.average_transaction),
    ],
    [],
    ['Produk Terlaris', 'Jumlah', 'Pendapatan'],
  ]

  for (const p of summary.value.top_products) {
    rows.push([p.name, String(p.quantity), String(p.revenue)])
  }

  const csv = rows.map(r => r.join(',')).join('\n')
  downloadCsv(csv, `laporan-harian-${summary.value.date}.csv`)
}

function downloadCsv(content: string, filename: string) {
  const blob = new Blob([content], { type: 'text/csv;charset=utf-8;' })
  const url = URL.createObjectURL(blob)
  const a = document.createElement('a')
  a.href = url
  a.download = filename
  a.click()
  URL.revokeObjectURL(url)
}

onMounted(() => {
  if (outletId.value) fetchReport()
})

watch(outletId, (val) => { if (val) fetchReport() })
</script>

<template>
  <div>
    <!-- Header -->
    <div class="flex items-center justify-between">
      <div>
        <h1 class="text-2xl font-semibold text-gray-900 dark:text-white">Laporan Harian</h1>
        <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">Ringkasan penjualan harian outlet.</p>
      </div>
      <BaseButton variant="secondary" size="sm" :icon="Download" :disabled="!summary" @click="exportCsv">
        Export CSV
      </BaseButton>
    </div>

    <!-- Date picker -->
    <div class="mt-4 flex items-center gap-3">
      <CalendarDays :size="16" class="text-gray-400" />
      <BaseInput
        v-model="selectedDate"
        type="date"
        class="w-48"
        @change="fetchReport"
      />
    </div>

    <!-- Loading -->
    <div v-if="loading" class="mt-6 py-8 text-center text-sm text-gray-400">Memuat data...</div>

    <template v-else-if="summary">
      <!-- Stats Cards -->
      <div class="mt-6 grid grid-cols-1 gap-4 sm:grid-cols-2 lg:grid-cols-4">
        <div class="rounded-xl border border-gray-200 bg-white p-5 dark:border-gray-700 dark:bg-gray-800">
          <p class="text-xs text-gray-500 dark:text-gray-400">Penjualan Kotor</p>
          <p class="mt-1 text-xl font-bold text-gray-900 dark:text-white">{{ formatCurrency(summary.gross_revenue) }}</p>
        </div>
        <div class="rounded-xl border border-gray-200 bg-white p-5 dark:border-gray-700 dark:bg-gray-800">
          <p class="text-xs text-gray-500 dark:text-gray-400">Total Diskon</p>
          <p class="mt-1 text-xl font-bold text-red-600 dark:text-red-400">-{{ formatCurrency(summary.total_discount) }}</p>
        </div>
        <div class="rounded-xl border border-gray-200 bg-white p-5 dark:border-gray-700 dark:bg-gray-800">
          <p class="text-xs text-gray-500 dark:text-gray-400">Pendapatan Bersih</p>
          <p class="mt-1 text-xl font-bold text-emerald-600 dark:text-emerald-400">{{ formatCurrency(summary.total_revenue) }}</p>
        </div>
        <div class="rounded-xl border border-gray-200 bg-white p-5 dark:border-gray-700 dark:bg-gray-800">
          <p class="text-xs text-gray-500 dark:text-gray-400">Transaksi</p>
          <p class="mt-1 text-xl font-bold text-gray-900 dark:text-white">{{ summary.transaction_count }}</p>
          <p class="text-xs text-gray-400 mt-0.5">Rata-rata {{ formatCurrency(summary.average_transaction) }}</p>
        </div>
      </div>

      <!-- Top Products -->
      <div class="mt-6 rounded-xl border border-gray-200 bg-white dark:border-gray-700 dark:bg-gray-800">
        <div class="border-b border-gray-200 px-5 py-4 dark:border-gray-700">
          <h3 class="text-sm font-semibold text-gray-900 dark:text-white">Produk Terlaris</h3>
        </div>
        <div v-if="summary.top_products.length" class="divide-y divide-gray-100 dark:divide-gray-700">
          <div
            v-for="(product, idx) in summary.top_products"
            :key="idx"
            class="flex items-center justify-between px-5 py-3"
          >
            <div class="flex items-center gap-3">
              <span class="flex h-6 w-6 items-center justify-center rounded-full bg-gray-100 text-xs font-medium text-gray-600 dark:bg-gray-700 dark:text-gray-300">
                {{ idx + 1 }}
              </span>
              <span class="text-sm text-gray-900 dark:text-white">{{ product.name }}</span>
            </div>
            <div class="text-right">
              <span class="text-sm font-medium text-gray-900 dark:text-white">{{ formatCurrency(product.revenue) }}</span>
              <span class="ml-2 text-xs text-gray-500 dark:text-gray-400">({{ product.quantity }}x)</span>
            </div>
          </div>
        </div>
        <div v-else class="px-5 py-8 text-center text-sm text-gray-400">
          Belum ada data produk untuk tanggal ini.
        </div>
      </div>
    </template>

    <!-- Empty state -->
    <div v-else class="mt-6 py-8 text-center text-sm text-gray-400">
      Pilih tanggal untuk melihat laporan.
    </div>
  </div>
</template>
