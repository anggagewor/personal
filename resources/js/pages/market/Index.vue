<script setup lang="ts">
import { ref, onMounted, onUnmounted } from 'vue'
import { useToast } from '@purdia/toast'
import { formatRelativeTime } from '@purdia/utils'
import BaseButton from '@purdia/ui/src/components/BaseButton.vue'
import BaseSkeleton from '@purdia/ui/src/components/BaseSkeleton.vue'
import { LineChart } from '@purdia/charts'
import type { ChartData, ChartOptions } from 'chart.js'
import { TrendingUp, TrendingDown, RefreshCw, Settings, Download, Upload, FileDown, Clock } from '@lucide/vue'
import type { WatchlistItem, PriceData, HistoryPoint } from '@/types/market'
import * as marketApi from '@/api/market'

const { success, error: showError } = useToast()

const items = ref<WatchlistItem[]>([])
const prices = ref<Record<string, PriceData>>({})
const refreshInterval = ref(15)
const loading = ref(true)
const refreshing = ref(false)
const lastSyncedAt = ref<string | null>(null)

const allHistory = ref<Record<string, HistoryPoint[]>>({})
const chartLoading = ref(false)

type RangeKey = '1d' | '7d' | '30d'
const activeRange = ref<RangeKey>('7d')

const rangeOptions: { key: RangeKey; label: string }[] = [
  { key: '1d', label: '1H' },
  { key: '7d', label: '7H' },
  { key: '30d', label: '30H' },
]

function getRangeFromTo(range: RangeKey): { from: string; to: string } {
  const now = new Date()
  const to = now.toISOString().slice(0, 19).replace('T', ' ')
  const from = new Date(now)
  switch (range) {
    case '1d': from.setDate(from.getDate() - 1); break
    case '7d': from.setDate(from.getDate() - 7); break
    case '30d': from.setDate(from.getDate() - 30); break
  }
  return { from: from.toISOString().slice(0, 19).replace('T', ' '), to }
}

let autoRefreshTimer: ReturnType<typeof setInterval> | null = null
const importing = ref(false)
const fileInput = ref<HTMLInputElement | null>(null)

function getPrice(symbol: string): PriceData | null {
  return prices.value[symbol] ?? null
}

function formatPrice(price: number, symbol: string): string {
  if (symbol.includes('/IDR')) {
    return 'Rp ' + Math.round(price).toLocaleString('id-ID')
  }
  if (price > 1000) return price.toLocaleString('en-US', { minimumFractionDigits: 2, maximumFractionDigits: 2 })
  if (price > 1) return price.toLocaleString('en-US', { minimumFractionDigits: 4, maximumFractionDigits: 4 })
  return price.toLocaleString('en-US', { minimumFractionDigits: 6, maximumFractionDigits: 6 })
}

function buildLineChartData(symbol: string): ChartData<'line'> | null {
  const history = allHistory.value[symbol]
  if (!history || history.length < 2) return null

  const priceData = getPrice(symbol)
  const isPositive = priceData ? priceData.change_percent >= 0 : true
  const color = isPositive ? '#10b981' : '#ef4444'

  return {
    labels: history.map(p => {
      const d = new Date(p.fetched_at)
      return activeRange.value === '1d'
        ? d.toLocaleTimeString('id-ID', { hour: '2-digit', minute: '2-digit' })
        : d.toLocaleDateString('id-ID', { day: '2-digit', month: 'short' })
    }),
    datasets: [{
      label: symbol,
      data: history.map(p => p.price),
      borderColor: color,
      backgroundColor: color + '20',
      borderWidth: 2,
      pointRadius: 0,
      pointHoverRadius: 4,
      tension: 0.3,
      fill: true,
    }],
  }
}

function getLineChartOptions(): ChartOptions<'line'> {
  return {
    responsive: true,
    maintainAspectRatio: false,
    plugins: {
      legend: { display: false },
      tooltip: { mode: 'index', intersect: false },
    },
    scales: {
      x: {
        grid: { display: false },
        ticks: { font: { size: 10 }, maxTicksLimit: 6 },
      },
      y: {
        grid: { color: '#f1f5f9' },
        ticks: { font: { size: 10 } },
      },
    },
    interaction: { mode: 'nearest', axis: 'x', intersect: false },
  }
}

async function fetchAll() {
  loading.value = true
  try {
    const [itemsRes, pricesRes] = await Promise.allSettled([
      marketApi.fetchWatchlist(),
      marketApi.fetchPrices(),
    ])
    if (itemsRes.status === 'fulfilled') items.value = itemsRes.value.data
    if (pricesRes.status === 'fulfilled') {
      prices.value = pricesRes.value.data
      refreshInterval.value = pricesRes.value.meta?.refresh_interval ?? 15
      lastSyncedAt.value = pricesRes.value.meta?.last_synced_at ?? null
    }
  } catch {
    // handled globally
  } finally {
    loading.value = false
  }
  await loadAllHistory()
}

async function loadAllHistory() {
  if (!items.value.length) return
  chartLoading.value = true
  const { from, to } = getRangeFromTo(activeRange.value)

  const results = await Promise.allSettled(
    items.value.map(item => marketApi.fetchHistory(item.symbol, { from, to }))
  )

  const historyMap: Record<string, HistoryPoint[]> = {}
  results.forEach((res, idx) => {
    if (res.status === 'fulfilled' && res.value.data.length > 0) {
      historyMap[items.value[idx].symbol] = res.value.data
    }
  })
  allHistory.value = historyMap
  chartLoading.value = false
}

async function changeRange(range: RangeKey) {
  activeRange.value = range
  await loadAllHistory()
}

async function refreshPrices() {
  refreshing.value = true
  try {
    const res = await marketApi.fetchPrices()
    prices.value = res.data
    lastSyncedAt.value = res.meta?.last_synced_at ?? null
  } catch {
    // handled globally
  } finally {
    refreshing.value = false
  }
}

function startAutoRefresh() {
  if (autoRefreshTimer) clearInterval(autoRefreshTimer)
  autoRefreshTimer = setInterval(refreshPrices, refreshInterval.value * 60 * 1000)
}

async function handleExport(format: 'csv' | 'json') {
  try {
    const res = await marketApi.exportData({ format })
    const blob = new Blob([res as any], { type: format === 'csv' ? 'text/csv' : 'application/json' })
    const url = URL.createObjectURL(blob)
    const a = document.createElement('a')
    a.href = url
    a.download = `market-history.${format}`
    a.click()
    URL.revokeObjectURL(url)
  } catch {
    showError('Gagal mengekspor data.')
  }
}

async function downloadTemplate() {
  try {
    const res = await marketApi.downloadTemplate()
    const blob = new Blob([res as any], { type: 'text/csv' })
    const url = URL.createObjectURL(blob)
    const a = document.createElement('a')
    a.href = url
    a.download = 'market-import-template.csv'
    a.click()
    URL.revokeObjectURL(url)
  } catch {
    showError('Gagal mengunduh template.')
  }
}

function triggerImport() {
  fileInput.value?.click()
}

async function handleImport(event: Event) {
  const target = event.target as HTMLInputElement
  const file = target.files?.[0]
  if (!file) return
  importing.value = true
  try {
    const formData = new FormData()
    formData.append('file', file)
    const res = await marketApi.importData(formData)
    success(res.data.message ?? `Berhasil mengimpor ${res.data.data?.imported ?? 0} data.`)
    await loadAllHistory()
  } catch {
    showError('Gagal mengimpor data. Pastikan format CSV sesuai template.')
  } finally {
    importing.value = false
    target.value = ''
  }
}

onMounted(async () => {
  await fetchAll()
  startAutoRefresh()
})

onUnmounted(() => {
  if (autoRefreshTimer) clearInterval(autoRefreshTimer)
})
</script>

<template>
  <div>
    <div class="flex items-center justify-between">
      <div>
        <h1 class="text-2xl font-semibold text-gray-900 dark:text-white">Market</h1>
        <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">Pantau harga aset secara real-time.</p>
      </div>
      <div class="flex items-center gap-2">
        <BaseButton variant="secondary" size="sm" :icon="FileDown" @click="downloadTemplate">Template</BaseButton>
        <BaseButton variant="secondary" size="sm" :icon="Upload" :disabled="importing" @click="triggerImport">
          {{ importing ? 'Mengimpor...' : 'Import' }}
        </BaseButton>
        <BaseButton variant="secondary" size="sm" :icon="Download" @click="handleExport('csv')">Export</BaseButton>
        <BaseButton variant="secondary" size="sm" :icon="RefreshCw" :disabled="refreshing" @click="refreshPrices">
          {{ refreshing ? 'Memperbarui...' : 'Refresh' }}
        </BaseButton>
        <router-link to="/settings/market">
          <BaseButton variant="secondary" size="sm" :icon="Settings">Atur</BaseButton>
        </router-link>
      </div>
    </div>
    <input ref="fileInput" type="file" accept=".csv" class="hidden" @change="handleImport" />

    <!-- Last sync info -->
    <div v-if="lastSyncedAt" class="mt-3 flex items-center gap-1.5 text-xs text-gray-500 dark:text-gray-400">
      <Clock :size="12" />
      <span>Terakhir sinkron: {{ formatRelativeTime(lastSyncedAt) }}</span>
    </div>

    <!-- Loading skeleton -->
    <div v-if="loading" class="mt-6 space-y-4">
      <div class="grid grid-cols-1 gap-3 sm:grid-cols-2 lg:grid-cols-3">
        <div v-for="i in 6" :key="i" class="rounded-xl border border-gray-200 bg-white p-4 dark:border-gray-700 dark:bg-gray-800">
          <BaseSkeleton variant="text" width="40%" />
          <BaseSkeleton variant="text" width="60%" height="24px" class="mt-2" />
          <BaseSkeleton variant="text" width="30%" class="mt-1" />
        </div>
      </div>
    </div>

    <!-- Content -->
    <template v-else>
      <!-- Empty state -->
      <div v-if="!items.length" class="mt-12 flex flex-col items-center text-center">
        <TrendingUp :size="48" class="text-gray-300 dark:text-gray-600" />
        <p class="mt-3 text-sm text-gray-500 dark:text-gray-400">Belum ada simbol di watchlist.</p>
        <router-link to="/settings/market" class="mt-2 text-sm text-primary-600 hover:text-primary-700 dark:text-primary-400">
          Tambah di pengaturan
        </router-link>
      </div>

      <template v-else>
        <!-- Price cards grid -->
        <div class="mt-6 grid grid-cols-1 gap-3 sm:grid-cols-2 lg:grid-cols-3">
          <div
            v-for="item in items"
            :key="item.id"
            class="rounded-xl border border-gray-200 bg-white p-4 dark:border-gray-700 dark:bg-gray-800"
          >
            <div class="flex items-center justify-between">
              <div>
                <p class="text-sm font-bold text-gray-900 dark:text-white">{{ item.symbol }}</p>
                <p v-if="item.label" class="text-xs text-gray-500 dark:text-gray-400">{{ item.label }}</p>
              </div>
              <span class="inline-flex items-center rounded px-1.5 py-0.5 text-[10px] font-bold uppercase" :class="{
                'bg-blue-100 text-blue-700 dark:bg-blue-900/30 dark:text-blue-300': item.type === 'forex',
                'bg-amber-100 text-amber-700 dark:bg-amber-900/30 dark:text-amber-300': item.type === 'crypto',
                'bg-violet-100 text-violet-700 dark:bg-violet-900/30 dark:text-violet-300': item.type === 'stock',
                'bg-emerald-100 text-emerald-700 dark:bg-emerald-900/30 dark:text-emerald-300': item.type === 'commodity',
              }">{{ item.type }}</span>
            </div>

            <template v-if="getPrice(item.symbol)">
              <p class="mt-2 text-lg font-bold text-gray-900 dark:text-white">
                {{ formatPrice(getPrice(item.symbol)!.price, item.symbol) }}
              </p>
              <div class="mt-0.5 flex items-center gap-1">
                <component
                  :is="getPrice(item.symbol)!.change_percent >= 0 ? TrendingUp : TrendingDown"
                  :size="12"
                  :class="getPrice(item.symbol)!.change_percent >= 0 ? 'text-emerald-500' : 'text-red-500'"
                />
                <span
                  class="text-xs font-medium"
                  :class="getPrice(item.symbol)!.change_percent >= 0 ? 'text-emerald-600 dark:text-emerald-400' : 'text-red-600 dark:text-red-400'"
                >
                  {{ getPrice(item.symbol)!.change_percent >= 0 ? '+' : '' }}{{ getPrice(item.symbol)!.change_percent.toFixed(2) }}%
                </span>
              </div>
            </template>
            <template v-else>
              <p class="mt-2 text-sm text-gray-400">Belum ada data</p>
            </template>
          </div>
        </div>

        <!-- Range selector -->
        <div class="mt-6 flex items-center justify-between">
          <h2 class="text-sm font-semibold text-gray-900 dark:text-white">Grafik Harga</h2>
          <div class="flex items-center gap-1">
            <button
              v-for="opt in rangeOptions"
              :key="opt.key"
              class="rounded px-2 py-1 text-xs font-medium transition-colors"
              :class="activeRange === opt.key
                ? 'bg-primary-100 text-primary-700 dark:bg-primary-900/30 dark:text-primary-300'
                : 'text-gray-500 hover:bg-gray-100 dark:text-gray-400 dark:hover:bg-gray-700'"
              @click="changeRange(opt.key)"
            >
              {{ opt.label }}
            </button>
          </div>
        </div>

        <!-- Per-symbol line charts -->
        <div v-if="chartLoading" class="mt-4 flex items-center justify-center py-16">
          <RefreshCw :size="20" class="animate-spin text-gray-400" />
        </div>

        <div v-else-if="Object.keys(allHistory).length > 0" class="mt-4 grid grid-cols-1 gap-4 lg:grid-cols-2">
          <div
            v-for="item in items.filter(i => allHistory[i.symbol]?.length >= 2)"
            :key="'chart-' + item.id"
            class="rounded-xl border border-gray-200 bg-white p-4 dark:border-gray-700 dark:bg-gray-800"
          >
            <div class="mb-3 flex items-center justify-between">
              <div class="flex items-center gap-2">
                <p class="text-sm font-semibold text-gray-900 dark:text-white">{{ item.symbol }}</p>
                <span v-if="item.label" class="text-xs text-gray-500 dark:text-gray-400">{{ item.label }}</span>
              </div>
              <span
                v-if="getPrice(item.symbol)"
                class="text-xs font-medium"
                :class="getPrice(item.symbol)!.change_percent >= 0 ? 'text-emerald-600 dark:text-emerald-400' : 'text-red-600 dark:text-red-400'"
              >
                {{ getPrice(item.symbol)!.change_percent >= 0 ? '+' : '' }}{{ getPrice(item.symbol)!.change_percent.toFixed(2) }}%
              </span>
            </div>
            <LineChart
              v-if="buildLineChartData(item.symbol)"
              :data="buildLineChartData(item.symbol)!"
              :options="getLineChartOptions()"
              :height="200"
            />
          </div>
        </div>

        <div v-else class="mt-4 flex flex-col items-center rounded-xl border border-gray-200 bg-white py-16 text-center dark:border-gray-700 dark:bg-gray-800">
          <TrendingUp :size="32" class="text-gray-300 dark:text-gray-600" />
          <p class="mt-2 text-sm text-gray-400 dark:text-gray-500">Belum ada riwayat harga.</p>
          <p class="mt-1 text-xs text-gray-400 dark:text-gray-600">Data akan terkumpul seiring waktu setelah cron aktif.</p>
        </div>

        <!-- Auto refresh info -->
        <p class="mt-3 text-xs text-gray-400 dark:text-gray-500 text-center">
          Harga diperbarui otomatis setiap {{ refreshInterval }} menit. Data dari Twelve Data.
        </p>
      </template>
    </template>
  </div>
</template>
