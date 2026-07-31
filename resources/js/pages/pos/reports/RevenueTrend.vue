<script setup lang="ts">
import { ref, computed, onMounted, watch } from 'vue'
import { useRoute } from 'vue-router'
import { formatCurrency } from '@purdia/utils'
import BaseButton from '@purdia/ui/src/components/BaseButton.vue'
import { Download, TrendingUp } from '@lucide/vue'
import * as posApi from '@/api/pos'
import { usePosOutlet } from '@/composables/usePosOutlet'

interface TrendDay {
  date: string
  revenue: number
}

const route = useRoute()
const { outletId } = usePosOutlet()

const trend = ref<TrendDay[]>([])
const loading = ref(false)

const maxRevenue = computed(() => Math.max(...trend.value.map(d => d.revenue), 1))
const totalRevenue = computed(() => trend.value.reduce((sum, d) => sum + d.revenue, 0))
const avgRevenue = computed(() => (trend.value.length ? totalRevenue.value / trend.value.length : 0))

async function fetchTrend() {
  if (!outletId.value) return
  loading.value = true
  try {
    const end = new Date().toISOString().slice(0, 10)
    const start = new Date(Date.now() - 6 * 24 * 60 * 60 * 1000).toISOString().slice(0, 10)
    const res = await posApi.fetchRangeReport(outletId.value, {
      start_date: start,
      end_date: end,
    })
    // Normalize: ensure 7 days with zero values for missing days
    const data = (res.data as TrendDay[]) ?? []
    trend.value = fillMissingDays(data, start, end)
  } catch {
    // Error handled by @purdia/http onError
  } finally {
    loading.value = false
  }
}

function fillMissingDays(data: TrendDay[], start: string, end: string): TrendDay[] {
  const map = new Map(data.map(d => [d.date, d.revenue]))
  const result: TrendDay[] = []
  const current = new Date(start)
  const endDate = new Date(end)

  while (current <= endDate) {
    const dateStr = current.toISOString().slice(0, 10)
    result.push({ date: dateStr, revenue: map.get(dateStr) ?? 0 })
    current.setDate(current.getDate() + 1)
  }
  return result
}

function barHeight(revenue: number): string {
  if (maxRevenue.value === 0) return '4px'
  return `${Math.max(4, (revenue / maxRevenue.value) * 160)}px`
}

function exportCsv() {
  if (!trend.value.length) return

  const rows: string[][] = [
    ['Tanggal', 'Pendapatan'],
    ...trend.value.map(d => [d.date, String(d.revenue)]),
    [],
    ['Total', String(totalRevenue.value)],
    ['Rata-rata', String(Math.round(avgRevenue.value))],
  ]

  const csv = rows.map(r => r.join(',')).join('\n')
  downloadCsv(csv, `tren-pendapatan-7-hari.csv`)
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

function formatShortDate(date: string): string {
  const d = new Date(date)
  const days = ['Min', 'Sen', 'Sel', 'Rab', 'Kam', 'Jum', 'Sab']
  return days[d.getDay()] ?? ''
}

onMounted(() => {
  if (outletId.value) fetchTrend()
})

watch(outletId, (val) => { if (val) fetchTrend() })
</script>

<template>
  <div>
    <!-- Header -->
    <div class="flex items-center justify-between">
      <div>
        <h1 class="text-2xl font-semibold text-gray-900 dark:text-white">Tren Pendapatan</h1>
        <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">Grafik pendapatan 7 hari terakhir.</p>
      </div>
      <BaseButton variant="secondary" size="sm" :icon="Download" :disabled="!trend.length" @click="exportCsv">
        Export CSV
      </BaseButton>
    </div>

    <!-- Loading -->
    <div v-if="loading" class="mt-6 py-8 text-center text-sm text-gray-400">Memuat data...</div>

    <template v-else>
      <!-- Summary -->
      <div class="mt-6 grid grid-cols-1 gap-4 sm:grid-cols-2">
        <div class="rounded-xl border border-gray-200 bg-white p-5 dark:border-gray-700 dark:bg-gray-800">
          <p class="text-xs text-gray-500 dark:text-gray-400">Total 7 Hari</p>
          <p class="mt-1 text-xl font-bold text-gray-900 dark:text-white">{{ formatCurrency(totalRevenue) }}</p>
        </div>
        <div class="rounded-xl border border-gray-200 bg-white p-5 dark:border-gray-700 dark:bg-gray-800">
          <p class="text-xs text-gray-500 dark:text-gray-400">Rata-rata Harian</p>
          <p class="mt-1 text-xl font-bold text-gray-900 dark:text-white">{{ formatCurrency(avgRevenue) }}</p>
        </div>
      </div>

      <!-- Bar Chart -->
      <div class="mt-6 rounded-xl border border-gray-200 bg-white p-5 dark:border-gray-700 dark:bg-gray-800">
        <h3 class="text-sm font-semibold text-gray-900 dark:text-white">Pendapatan Per Hari</h3>

        <div v-if="trend.length" class="mt-4 flex items-end justify-between gap-2" style="height: 200px">
          <div
            v-for="day in trend"
            :key="day.date"
            class="flex flex-1 flex-col items-center gap-1"
          >
            <!-- Revenue label -->
            <span class="text-[10px] font-medium text-gray-600 dark:text-gray-300">
              {{ day.revenue > 0 ? formatCurrency(day.revenue) : '0' }}
            </span>
            <!-- Bar -->
            <div
              class="w-full rounded-t bg-primary-500 dark:bg-primary-400 transition-all"
              :style="{ height: barHeight(day.revenue) }"
            ></div>
            <!-- Day label -->
            <span class="text-[10px] text-gray-400">{{ formatShortDate(day.date) }}</span>
            <span class="text-[9px] text-gray-400">{{ day.date.slice(5) }}</span>
          </div>
        </div>

        <div v-else class="mt-4 py-8 text-center text-sm text-gray-400">
          <TrendingUp :size="24" class="mx-auto mb-2 text-gray-300" />
          Belum ada data pendapatan.
        </div>
      </div>
    </template>
  </div>
</template>
