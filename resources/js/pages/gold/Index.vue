<script setup lang="ts">
import { ref, computed, onMounted } from 'vue'
import { formatCurrency } from '@purdia/utils'
import { useToast } from '@purdia/toast'
import BaseButton from '@purdia/ui/src/components/BaseButton.vue'
import BaseSkeleton from '@purdia/ui/src/components/BaseSkeleton.vue'
import { LineChart } from '@purdia/charts'
import { TrendingUp, TrendingDown, Coins, Download, Upload, FileDown } from '@lucide/vue'
import type { GoldHistory, GoldDashboard } from '@/types/gold'
import * as goldApi from '@/api/gold'

const { success, error: showError } = useToast()

// --- State ---
const dashboard = ref<GoldDashboard | null>(null)
const history = ref<GoldHistory[]>([])
const loading = ref(true)
const period = ref<string>('1y')
const chartLoading = ref(false)
const importing = ref(false)
const fileInput = ref<HTMLInputElement | null>(null)

const periods = [
  { value: '1m', label: '1 Bulan' },
  { value: '3m', label: '3 Bulan' },
  { value: '6m', label: '6 Bulan' },
  { value: '1y', label: '1 Tahun' },
  { value: '5y', label: '5 Tahun' },
  { value: 'all', label: 'Semua' },
]

// --- Chart ---
const chartData = computed(() => {
  if (!history.value.length) return null

  // Subsample if too many points (> 500) for performance
  let data = history.value
  if (data.length > 500) {
    const step = Math.ceil(data.length / 500)
    data = data.filter((_, i) => i % step === 0 || i === data.length - 1)
  }

  return {
    labels: data.map(h => {
      const d = new Date(h.date)
      return d.toLocaleDateString('id-ID', { day: 'numeric', month: 'short', year: period.value === '1m' || period.value === '3m' ? undefined : '2-digit' })
    }),
    datasets: [{
      label: 'Harga Emas Antam (Rp/gram)',
      data: data.map(h => h.price),
      borderColor: '#f59e0b',
      backgroundColor: 'rgba(245, 158, 11, 0.05)',
      fill: true,
      tension: 0.2,
      pointRadius: 0,
      pointHoverRadius: 4,
      borderWidth: 2,
    }],
  }
})

const chartOptions = {
  responsive: true,
  maintainAspectRatio: false,
  interaction: {
    mode: 'index' as const,
    intersect: false,
  },
  plugins: {
    legend: { display: false },
    tooltip: {
      callbacks: {
        label: (ctx: { parsed: { y: number } }) => `Rp ${ctx.parsed.y.toLocaleString('id-ID')}`,
      },
    },
  },
  scales: {
    x: {
      display: true,
      grid: { display: false },
      ticks: { maxTicksLimit: 12, font: { size: 10 } },
    },
    y: {
      display: true,
      grid: { color: 'rgba(156, 163, 175, 0.1)' },
      ticks: {
        font: { size: 10 },
        callback: (value: number) => `${(value / 1000).toFixed(0)}rb`,
      },
    },
  },
}

// --- Actions ---
async function fetchAll() {
  loading.value = true
  try {
    const [dashRes, histRes] = await Promise.allSettled([
      goldApi.fetchDashboard(),
      goldApi.fetchHistory({ period: period.value }),
    ])
    if (dashRes.status === 'fulfilled') dashboard.value = dashRes.value.data
    if (histRes.status === 'fulfilled') history.value = histRes.value.data
  } catch {
    // handled globally
  } finally {
    loading.value = false
  }
}

async function changePeriod(p: string) {
  period.value = p
  chartLoading.value = true
  try {
    const res = await goldApi.fetchHistory({ period: p })
    history.value = res.data
  } catch {
    // handled globally
  } finally {
    chartLoading.value = false
  }
}

// --- Export / Import ---
async function handleExport(format: 'csv' | 'json') {
  try {
    const res = await goldApi.exportData({ format })

    const blob = new Blob([res as any], { type: format === 'csv' ? 'text/csv' : 'application/json' })
    const url = URL.createObjectURL(blob)
    const a = document.createElement('a')
    a.href = url
    a.download = `gold-prices.${format}`
    a.click()
    URL.revokeObjectURL(url)
  } catch {
    showError('Gagal mengekspor data.')
  }
}

async function downloadTemplate() {
  try {
    const res = await goldApi.downloadTemplate()
    const blob = new Blob([res as any], { type: 'text/csv' })
    const url = URL.createObjectURL(blob)
    const a = document.createElement('a')
    a.href = url
    a.download = 'gold-import-template.csv'
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
    const res = await goldApi.importData(formData)
    success(res.data.message ?? `Berhasil mengimpor ${res.data.data?.imported ?? 0} data.`)
    await fetchAll()
  } catch {
    showError('Gagal mengimpor data. Pastikan format CSV sesuai template.')
  } finally {
    importing.value = false
    target.value = ''
  }
}

onMounted(fetchAll)
</script>

<template>
  <div>
    <div class="flex items-center justify-between">
      <div>
        <h1 class="text-2xl font-semibold text-gray-900 dark:text-white">Emas Antam</h1>
        <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">Pantau harga emas Antam per gram.</p>
      </div>
      <div class="flex items-center gap-2">
        <BaseButton variant="secondary" size="sm" :icon="FileDown" @click="downloadTemplate">Template</BaseButton>
        <BaseButton variant="secondary" size="sm" :icon="Upload" :disabled="importing" @click="triggerImport">
          {{ importing ? 'Mengimpor...' : 'Import' }}
        </BaseButton>
        <BaseButton variant="secondary" size="sm" :icon="Download" @click="handleExport('csv')">Export</BaseButton>
      </div>
    </div>
    <input ref="fileInput" type="file" accept=".csv" class="hidden" @change="handleImport" />

    <!-- Loading -->
    <div v-if="loading" class="mt-6 space-y-4">
      <div class="grid grid-cols-1 gap-4 sm:grid-cols-3">
        <div v-for="i in 3" :key="i" class="rounded-xl border border-gray-200 bg-white p-5 dark:border-gray-700 dark:bg-gray-800">
          <BaseSkeleton variant="text" width="40%" />
          <BaseSkeleton variant="text" width="70%" height="28px" class="mt-2" />
        </div>
      </div>
      <div class="rounded-xl border border-gray-200 bg-white p-5 dark:border-gray-700 dark:bg-gray-800">
        <BaseSkeleton variant="rect" height="300px" />
      </div>
    </div>

    <!-- Content -->
    <template v-else-if="dashboard?.latest">
      <!-- Stats cards -->
      <div class="mt-6 grid grid-cols-1 gap-4 sm:grid-cols-3">
        <!-- Current price -->
        <div class="rounded-xl border border-gray-200 bg-white p-5 dark:border-gray-700 dark:bg-gray-800">
          <div class="flex items-center gap-3">
            <div class="flex h-10 w-10 items-center justify-center rounded-lg bg-amber-100 text-amber-600 dark:bg-amber-900/30 dark:text-amber-400">
              <Coins :size="20" />
            </div>
            <div>
              <p class="text-xs text-gray-500 dark:text-gray-400">Harga Hari Ini</p>
              <p class="text-xl font-bold text-gray-900 dark:text-white">Rp {{ dashboard.latest.price.toLocaleString('id-ID') }}</p>
            </div>
          </div>
          <div class="mt-3 flex items-center gap-1.5">
            <component :is="dashboard.latest.change >= 0 ? TrendingUp : TrendingDown" :size="14"
              :class="dashboard.latest.change >= 0 ? 'text-emerald-500' : 'text-red-500'" />
            <span class="text-sm font-medium"
              :class="dashboard.latest.change >= 0 ? 'text-emerald-600 dark:text-emerald-400' : 'text-red-600 dark:text-red-400'">
              {{ dashboard.latest.change >= 0 ? '+' : '' }}Rp {{ dashboard.latest.change.toLocaleString('id-ID') }}
              ({{ dashboard.latest.change_percent >= 0 ? '+' : '' }}{{ dashboard.latest.change_percent.toFixed(2) }}%)
            </span>
          </div>
          <p class="mt-1 text-xs text-gray-400">{{ dashboard.latest.date }}</p>
        </div>

        <!-- 30d high/low -->
        <div v-if="dashboard.stats" class="rounded-xl border border-gray-200 bg-white p-5 dark:border-gray-700 dark:bg-gray-800">
          <p class="text-xs text-gray-500 dark:text-gray-400">30 Hari Terakhir</p>
          <div class="mt-3 space-y-2">
            <div class="flex items-center justify-between">
              <span class="text-sm text-gray-600 dark:text-gray-300">Tertinggi</span>
              <span class="text-sm font-semibold text-gray-900 dark:text-white">Rp {{ dashboard.stats.high_30d.toLocaleString('id-ID') }}</span>
            </div>
            <div class="flex items-center justify-between">
              <span class="text-sm text-gray-600 dark:text-gray-300">Terendah</span>
              <span class="text-sm font-semibold text-gray-900 dark:text-white">Rp {{ dashboard.stats.low_30d.toLocaleString('id-ID') }}</span>
            </div>
          </div>
        </div>

        <!-- 30d change -->
        <div v-if="dashboard.stats" class="rounded-xl border border-gray-200 bg-white p-5 dark:border-gray-700 dark:bg-gray-800">
          <p class="text-xs text-gray-500 dark:text-gray-400">Perubahan 30 Hari</p>
          <p class="mt-3 text-xl font-bold"
            :class="dashboard.stats.change_30d >= 0 ? 'text-emerald-600 dark:text-emerald-400' : 'text-red-600 dark:text-red-400'">
            {{ dashboard.stats.change_30d >= 0 ? '+' : '' }}Rp {{ dashboard.stats.change_30d.toLocaleString('id-ID') }}
          </p>
          <p class="mt-1 text-sm"
            :class="dashboard.stats.change_percent_30d >= 0 ? 'text-emerald-600 dark:text-emerald-400' : 'text-red-600 dark:text-red-400'">
            {{ dashboard.stats.change_percent_30d >= 0 ? '+' : '' }}{{ dashboard.stats.change_percent_30d.toFixed(2) }}%
          </p>
        </div>
      </div>

      <!-- Chart -->
      <div class="mt-6 rounded-xl border border-gray-200 bg-white p-5 dark:border-gray-700 dark:bg-gray-800">
        <div class="flex items-center justify-between">
          <h2 class="text-sm font-semibold text-gray-900 dark:text-white">Grafik Harga</h2>
          <div class="flex gap-1">
            <button
              v-for="p in periods"
              :key="p.value"
              class="rounded-md px-2.5 py-1 text-xs font-medium transition-colors"
              :class="period === p.value
                ? 'bg-amber-100 text-amber-700 dark:bg-amber-900/30 dark:text-amber-300'
                : 'text-gray-500 hover:bg-gray-100 dark:text-gray-400 dark:hover:bg-gray-700'"
              @click="changePeriod(p.value)"
            >
              {{ p.label }}
            </button>
          </div>
        </div>

        <div v-if="chartLoading" class="mt-4 flex items-center justify-center py-20">
          <div class="h-6 w-6 animate-spin rounded-full border-2 border-amber-500 border-t-transparent"></div>
        </div>
        <div v-else-if="chartData" class="mt-4 h-80">
          <LineChart :data="chartData" :options="chartOptions" />
        </div>
        <div v-else class="mt-4 flex flex-col items-center py-20 text-center">
          <Coins :size="32" class="text-gray-300 dark:text-gray-600" />
          <p class="mt-2 text-sm text-gray-400">Belum ada data.</p>
          <p class="text-xs text-gray-400">Jalankan <code>php artisan gold:import-history</code></p>
        </div>
      </div>

      <!-- Info -->
      <p class="mt-3 text-xs text-gray-400 dark:text-gray-500 text-center">
        Data harga emas Antam per gram. Update otomatis setiap hari pukul 10:00 WIB.
      </p>
    </template>

    <!-- Empty state -->
    <div v-else class="mt-12 flex flex-col items-center text-center">
      <Coins :size="48" class="text-gray-300 dark:text-gray-600" />
      <p class="mt-3 text-sm text-gray-500 dark:text-gray-400">Belum ada data harga emas.</p>
      <p class="mt-1 text-xs text-gray-400">Jalankan <code class="rounded bg-gray-100 px-1.5 py-0.5 dark:bg-gray-700">php artisan gold:import-history</code> untuk import data historis.</p>
    </div>
  </div>
</template>
