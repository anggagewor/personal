<script setup lang="ts">
import { ref, onMounted } from 'vue'
import { get } from '@purdia/http'
import { Coins, TrendingUp, TrendingDown } from '@lucide/vue'

interface GoldDashboard {
  latest: { date: string; price: number; change: number; change_percent: number } | null
  sparkline: number[]
  stats: { high_30d: number; low_30d: number; change_30d: number; change_percent_30d: number } | null
}

const data = ref<GoldDashboard | null>(null)
const loading = ref(true)

function sparklinePath(prices: number[]): string {
  if (prices.length < 2) return ''
  const width = 100
  const height = 32
  const pad = 2
  const min = Math.min(...prices)
  const max = Math.max(...prices)
  const range = max - min || 1

  const points = prices.map((price, i) => {
    const x = pad + (i / (prices.length - 1)) * (width - pad * 2)
    const y = pad + (1 - (price - min) / range) * (height - pad * 2)
    return `${x},${y}`
  })

  return 'M' + points.join(' L')
}

onMounted(async () => {
  try {
    const res = await get<GoldDashboard>('/gold/dashboard')
    data.value = res.data
  } catch {
    // handled globally
  } finally {
    loading.value = false
  }
})
</script>

<template>
  <div class="rounded-xl border border-gray-200 bg-white p-5 dark:border-gray-700 dark:bg-gray-800">
    <div class="flex items-center justify-between">
      <div class="flex items-center gap-2">
        <Coins :size="16" class="text-amber-500" />
        <h2 class="text-sm font-semibold text-gray-900 dark:text-white">Emas Antam</h2>
      </div>
      <router-link to="/gold" class="text-xs text-primary-600 hover:text-primary-700 dark:text-primary-400">
        Lihat detail
      </router-link>
    </div>

    <!-- Loading -->
    <div v-if="loading" class="mt-4 space-y-2">
      <div class="h-5 w-32 animate-pulse rounded bg-gray-200 dark:bg-gray-700" />
      <div class="h-8 w-full animate-pulse rounded bg-gray-200 dark:bg-gray-700" />
    </div>

    <!-- Content -->
    <template v-else-if="data?.latest">
      <div class="mt-3 flex items-end justify-between">
        <div>
          <p class="text-xl font-bold text-gray-900 dark:text-white">
            Rp {{ data.latest.price.toLocaleString('id-ID') }}
          </p>
          <div class="mt-0.5 flex items-center gap-1">
            <component :is="data.latest.change >= 0 ? TrendingUp : TrendingDown" :size="12"
              :class="data.latest.change >= 0 ? 'text-emerald-500' : 'text-red-500'" />
            <span class="text-xs font-medium"
              :class="data.latest.change >= 0 ? 'text-emerald-600 dark:text-emerald-400' : 'text-red-600 dark:text-red-400'">
              {{ data.latest.change >= 0 ? '+' : '' }}{{ data.latest.change.toLocaleString('id-ID') }}
              ({{ data.latest.change_percent >= 0 ? '+' : '' }}{{ data.latest.change_percent.toFixed(2) }}%)
            </span>
          </div>
          <p class="mt-0.5 text-[11px] text-gray-400">{{ data.latest.date }}</p>
        </div>

        <!-- Sparkline -->
        <div class="w-24 h-8 shrink-0">
          <svg v-if="data.sparkline.length >= 2" viewBox="0 0 100 32" class="w-full h-full" preserveAspectRatio="none">
            <path
              :d="sparklinePath(data.sparkline)"
              fill="none"
              :stroke="data.latest.change >= 0 ? '#10b981' : '#ef4444'"
              stroke-width="1.5"
              stroke-linecap="round"
              stroke-linejoin="round"
            />
          </svg>
        </div>
      </div>

      <!-- 30d stats compact -->
      <div v-if="data.stats" class="mt-3 flex items-center justify-between rounded-lg bg-gray-50 px-3 py-2 dark:bg-gray-700/40">
        <div class="text-center">
          <p class="text-[10px] text-gray-500 dark:text-gray-400">30d Low</p>
          <p class="text-xs font-semibold text-gray-900 dark:text-white">{{ (data.stats.low_30d / 1000).toFixed(0) }}rb</p>
        </div>
        <div class="text-center">
          <p class="text-[10px] text-gray-500 dark:text-gray-400">30d High</p>
          <p class="text-xs font-semibold text-gray-900 dark:text-white">{{ (data.stats.high_30d / 1000).toFixed(0) }}rb</p>
        </div>
        <div class="text-center">
          <p class="text-[10px] text-gray-500 dark:text-gray-400">30d Δ</p>
          <p class="text-xs font-semibold" :class="data.stats.change_percent_30d >= 0 ? 'text-emerald-600 dark:text-emerald-400' : 'text-red-600 dark:text-red-400'">
            {{ data.stats.change_percent_30d >= 0 ? '+' : '' }}{{ data.stats.change_percent_30d.toFixed(1) }}%
          </p>
        </div>
      </div>
    </template>

    <!-- Empty -->
    <div v-else class="mt-4 flex flex-col items-center py-3 text-center">
      <Coins :size="24" class="text-gray-300 dark:text-gray-600" />
      <p class="mt-1 text-xs text-gray-400 dark:text-gray-500">Belum ada data emas.</p>
    </div>
  </div>
</template>
