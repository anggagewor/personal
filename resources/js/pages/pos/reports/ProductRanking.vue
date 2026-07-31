<script setup lang="ts">
import { ref, computed, onMounted } from 'vue'
import { useRoute } from 'vue-router'
import { formatCurrency } from '@purdia/utils'
import BaseButton from '@purdia/ui/src/components/BaseButton.vue'
import BaseInput from '@purdia/ui/src/components/BaseInput.vue'
import { Download, Package } from '@lucide/vue'
import * as posApi from '@/api/pos'

interface ProductRankItem {
  product_name: string
  quantity_sold: number
  revenue: number
}

const route = useRoute()

const outletId = computed(() => Number(route.query.outlet))

const startDate = ref(getDefaultStartDate())
const endDate = ref(new Date().toISOString().slice(0, 10))
const products = ref<ProductRankItem[]>([])
const loading = ref(false)

function getDefaultStartDate(): string {
  const d = new Date()
  d.setDate(d.getDate() - 30)
  return d.toISOString().slice(0, 10)
}

async function fetchReport() {
  if (!outletId.value) return
  loading.value = true
  try {
    const res = await posApi.fetchProductsReport(outletId.value, {
      start_date: startDate.value,
      end_date: endDate.value,
    })
    products.value = (res.data as ProductRankItem[]) ?? []
  } catch {
    // Error handled by @purdia/http onError
  } finally {
    loading.value = false
  }
}

function exportCsv() {
  if (!products.value.length) return

  const rows: string[][] = [
    ['Produk', 'Jumlah Terjual', 'Pendapatan'],
    ...products.value.map(p => [p.product_name, String(p.quantity_sold), String(p.revenue)]),
  ]

  const csv = rows.map(r => r.join(',')).join('\n')
  downloadCsv(csv, `ranking-produk-${startDate.value}-${endDate.value}.csv`)
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
  fetchReport()
})
</script>

<template>
  <div>
    <!-- Header -->
    <div class="flex items-center justify-between">
      <div>
        <h1 class="text-2xl font-semibold text-gray-900 dark:text-white">Ranking Produk</h1>
        <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">Performa produk berdasarkan penjualan.</p>
      </div>
      <BaseButton variant="secondary" size="sm" :icon="Download" :disabled="!products.length" @click="exportCsv">
        Export CSV
      </BaseButton>
    </div>

    <!-- Date Range -->
    <div class="mt-4 flex flex-wrap items-center gap-3">
      <BaseInput
        v-model="startDate"
        type="date"
        class="w-44"
        @change="fetchReport"
      />
      <span class="text-sm text-gray-500 dark:text-gray-400">s/d</span>
      <BaseInput
        v-model="endDate"
        type="date"
        class="w-44"
        @change="fetchReport"
      />
    </div>

    <!-- Loading -->
    <div v-if="loading" class="mt-6 py-8 text-center text-sm text-gray-400">Memuat data...</div>

    <!-- Table -->
    <div v-else-if="products.length" class="mt-6 overflow-hidden rounded-xl border border-gray-200 bg-white dark:border-gray-700 dark:bg-gray-800">
      <table class="w-full text-sm">
        <thead>
          <tr class="border-b border-gray-200 bg-gray-50 dark:border-gray-700 dark:bg-gray-800/50">
            <th class="px-5 py-3 text-left font-medium text-gray-600 dark:text-gray-400">#</th>
            <th class="px-5 py-3 text-left font-medium text-gray-600 dark:text-gray-400">Produk</th>
            <th class="px-5 py-3 text-right font-medium text-gray-600 dark:text-gray-400">Terjual</th>
            <th class="px-5 py-3 text-right font-medium text-gray-600 dark:text-gray-400">Pendapatan</th>
          </tr>
        </thead>
        <tbody class="divide-y divide-gray-100 dark:divide-gray-700">
          <tr v-for="(product, idx) in products" :key="idx" class="hover:bg-gray-50 dark:hover:bg-gray-700/30">
            <td class="px-5 py-3 text-gray-500 dark:text-gray-400">{{ idx + 1 }}</td>
            <td class="px-5 py-3 text-gray-900 dark:text-white">
              <div class="flex items-center gap-2">
                <Package :size="14" class="text-gray-400" />
                {{ product.product_name }}
              </div>
            </td>
            <td class="px-5 py-3 text-right text-gray-700 dark:text-gray-300">{{ product.quantity_sold }}</td>
            <td class="px-5 py-3 text-right font-medium text-gray-900 dark:text-white">{{ formatCurrency(product.revenue) }}</td>
          </tr>
        </tbody>
      </table>
    </div>

    <!-- Empty -->
    <div v-else class="mt-6 flex flex-col items-center py-12 text-center">
      <div class="flex h-14 w-14 items-center justify-center rounded-2xl bg-gray-100 dark:bg-gray-800">
        <Package :size="24" class="text-gray-400" />
      </div>
      <p class="mt-3 text-sm text-gray-500 dark:text-gray-400">Belum ada data penjualan untuk periode ini.</p>
    </div>
  </div>
</template>
