<script setup lang="ts">
import { ref, onMounted } from 'vue'
import { get } from '@purdia/http'
import { TrendingUp, TrendingDown } from '@lucide/vue'

interface MarketDashboardItem {
  id: number
  symbol: string
  type: string
  label: string | null
  price: number | null
  change: number | null
  change_percent: number | null
  previous_close: number | null
  sparkline: number[]
}

const items = ref<MarketDashboardItem[]>([])
const loading = ref(true)

function formatPrice(price: number, symbol: string): string {
  if (symbol.includes('/IDR')) return 'Rp ' + Math.round(price).toLocaleString('id-ID')
  if (price > 1000) return price.toLocaleString('en-US', { minimumFractionDigits: 2, maximumFractionDigits: 2 })
  if (price > 1) return price.toLocaleString('en-US', { minimumFractionDigits: 2, maximumFractionDigits: 4 })
  return price.toLocaleString('en-US', { minimumFractionDigits: 4, maximumFractionDigits: 6 })
}

function formatChange(change: number, percent: number): string {
  const sign = change >= 0 ? '+' : ''
  // For large values (IDR pairs) show without decimals
  const changeStr = Math.abs(change) > 100
    ? sign + Math.round(change).toLocaleString('id-ID')
    : sign + change.toFixed(2)
  return `${changeStr} (${sign}${percent.toFixed(2)}%)`
}

/**
 * Generate SVG path for sparkline from price array.
 */
function sparklinePath(prices: number[]): string {
  if (prices.length < 2) return ''

  const width = 80
  const height = 28
  const padding = 2

  const min = Math.min(...prices)
  const max = Math.max(...prices)
  const range = max - min || 1

  const points = prices.map((price, i) => {
    const x = padding + (i / (prices.length - 1)) * (width - padding * 2)
    const y = padding + (1 - (price - min) / range) * (height - padding * 2)
    return `${x},${y}`
  })

  return 'M' + points.join(' L')
}

function sparklineColor(item: MarketDashboardItem): string {
  if (item.change_percent === null) return '#9ca3af'
  return item.change_percent >= 0 ? '#10b981' : '#ef4444'
}

async function fetchDashboard() {
  loading.value = true
  try {
    const res = await get<MarketDashboardItem[]>('/market/dashboard')
    items.value = res.data
  } catch {
    // handled globally
  } finally {
    loading.value = false
  }
}

onMounted(fetchDashboard)
</script>

<template>
  <div class="rounded-xl border border-gray-200 bg-white p-5 dark:border-gray-700 dark:bg-gray-800">
    <div class="flex items-center justify-between">
      <div class="flex items-center gap-2">
        <TrendingUp :size="16" class="text-gray-500 dark:text-gray-400" />
        <h2 class="text-sm font-semibold text-gray-900 dark:text-white">Market</h2>
      </div>
      <router-link
        to="/market"
        class="text-xs text-primary-600 hover:text-primary-700 dark:text-primary-400"
      >
        Lihat detail
      </router-link>
    </div>

    <!-- Loading -->
    <div v-if="loading" class="mt-4 space-y-3">
      <div v-for="i in 4" :key="i" class="flex items-center justify-between">
        <div class="h-4 w-20 animate-pulse rounded bg-gray-200 dark:bg-gray-700" />
        <div class="h-4 w-16 animate-pulse rounded bg-gray-200 dark:bg-gray-700" />
      </div>
    </div>

    <!-- Content -->
    <div v-else-if="items.length" class="mt-3 divide-y divide-gray-100 dark:divide-gray-700">
      <div
        v-for="item in items.slice(0, 8)"
        :key="item.id"
        class="flex items-center gap-3 py-2.5 first:pt-0 last:pb-0"
      >
        <!-- Symbol + Label -->
        <div class="min-w-0 flex-1">
          <p class="text-sm font-bold text-gray-900 dark:text-white truncate">{{ item.symbol }}</p>
          <p v-if="item.label" class="text-[11px] text-gray-500 dark:text-gray-400 truncate">{{ item.label }}</p>
        </div>

        <!-- Sparkline -->
        <div class="shrink-0 w-20 h-7">
          <svg
            v-if="item.sparkline.length >= 2"
            :viewBox="`0 0 80 28`"
            class="w-full h-full"
            preserveAspectRatio="none"
          >
            <path
              :d="sparklinePath(item.sparkline)"
              fill="none"
              :stroke="sparklineColor(item)"
              stroke-width="1.5"
              stroke-linecap="round"
              stroke-linejoin="round"
            />
          </svg>
          <div v-else class="flex items-center justify-center h-full">
            <span class="text-[10px] text-gray-300 dark:text-gray-600">—</span>
          </div>
        </div>

        <!-- Price + Change -->
        <div class="text-right shrink-0">
          <p v-if="item.price !== null" class="text-sm font-semibold tabular-nums text-gray-900 dark:text-white">
            {{ formatPrice(item.price, item.symbol) }}
          </p>
          <p v-else class="text-xs text-gray-400">—</p>
          <p
            v-if="item.change !== null && item.change_percent !== null"
            class="text-[11px] font-medium tabular-nums"
            :class="item.change_percent >= 0 ? 'text-emerald-600 dark:text-emerald-400' : 'text-red-600 dark:text-red-400'"
          >
            {{ formatChange(item.change, item.change_percent) }}
          </p>
        </div>
      </div>
    </div>

    <!-- Empty state -->
    <div v-else class="mt-4 flex flex-col items-center py-4 text-center">
      <TrendingUp :size="28" class="text-gray-300 dark:text-gray-600" />
      <p class="mt-2 text-sm text-gray-400 dark:text-gray-500">Belum ada watchlist.</p>
      <router-link
        to="/settings/market"
        class="mt-1 text-xs text-primary-600 hover:text-primary-700 dark:text-primary-400"
      >
        Tambah simbol
      </router-link>
    </div>
  </div>
</template>
