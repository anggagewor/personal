<script setup lang="ts">
import { ref, computed, onMounted, watch } from 'vue'
import { useRoute, useRouter } from 'vue-router'
import { formatCurrency } from '@purdia/utils'
import BaseButton from '@purdia/ui/src/components/BaseButton.vue'
import { BarChart3, CalendarDays, Package, TrendingUp } from '@lucide/vue'
import type { DashboardStats } from '@/types/pos'
import * as posApi from '@/api/pos'
import { usePosOutlet } from '@/composables/usePosOutlet'

const route = useRoute()
const router = useRouter()
const { outletId } = usePosOutlet()

const stats = ref<DashboardStats | null>(null)
const loading = ref(true)

async function fetchDashboard() {
  if (!outletId.value) return
  loading.value = true
  try {
    const res = await posApi.fetchDashboard(outletId.value)
    stats.value = res.data
  } catch {
    // Error handled by @purdia/http onError
  } finally {
    loading.value = false
  }
}

function goTo(name: string) {
  router.push({ name, query: { outlet: outletId.value } })
}

onMounted(() => {
  if (outletId.value) fetchDashboard()
})

watch(outletId, (val) => { if (val) fetchDashboard() })
</script>

<template>
  <div>
    <!-- Header -->
    <div class="flex items-center justify-between">
      <div>
        <h1 class="text-2xl font-semibold text-gray-900 dark:text-white">Laporan POS</h1>
        <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">Ringkasan dan laporan penjualan outlet.</p>
      </div>
    </div>

    <!-- Loading -->
    <div v-if="loading" class="mt-6 py-8 text-center text-sm text-gray-400">Memuat data...</div>

    <template v-else>
      <!-- Summary Cards -->
      <div class="mt-6 grid grid-cols-1 gap-4 sm:grid-cols-2">
        <div class="rounded-xl border border-gray-200 bg-white p-5 dark:border-gray-700 dark:bg-gray-800">
          <div class="flex items-center gap-3">
            <div class="flex h-10 w-10 items-center justify-center rounded-lg bg-emerald-100 text-emerald-600 dark:bg-emerald-900/30 dark:text-emerald-400">
              <TrendingUp :size="20" />
            </div>
            <div>
              <p class="text-lg font-bold text-gray-900 dark:text-white">{{ formatCurrency(stats?.today_revenue ?? 0) }}</p>
              <p class="text-xs text-gray-500 dark:text-gray-400">Pendapatan Hari Ini</p>
            </div>
          </div>
        </div>
        <div class="rounded-xl border border-gray-200 bg-white p-5 dark:border-gray-700 dark:bg-gray-800">
          <div class="flex items-center gap-3">
            <div class="flex h-10 w-10 items-center justify-center rounded-lg bg-blue-100 text-blue-600 dark:bg-blue-900/30 dark:text-blue-400">
              <BarChart3 :size="20" />
            </div>
            <div>
              <p class="text-lg font-bold text-gray-900 dark:text-white">{{ stats?.today_transactions ?? 0 }}</p>
              <p class="text-xs text-gray-500 dark:text-gray-400">Transaksi Hari Ini</p>
            </div>
          </div>
        </div>
      </div>

      <!-- Navigation -->
      <div class="mt-6 grid grid-cols-1 gap-4 sm:grid-cols-3">
        <button
          class="flex items-center gap-3 rounded-xl border border-gray-200 bg-white p-5 text-left transition-shadow hover:shadow-md dark:border-gray-700 dark:bg-gray-800"
          @click="goTo('pos.reports.daily')"
        >
          <div class="flex h-10 w-10 items-center justify-center rounded-lg bg-amber-100 text-amber-600 dark:bg-amber-900/30 dark:text-amber-400">
            <CalendarDays :size="20" />
          </div>
          <div>
            <p class="text-sm font-semibold text-gray-900 dark:text-white">Laporan Harian</p>
            <p class="text-xs text-gray-500 dark:text-gray-400">Ringkasan penjualan per hari</p>
          </div>
        </button>

        <button
          class="flex items-center gap-3 rounded-xl border border-gray-200 bg-white p-5 text-left transition-shadow hover:shadow-md dark:border-gray-700 dark:bg-gray-800"
          @click="goTo('pos.reports.products')"
        >
          <div class="flex h-10 w-10 items-center justify-center rounded-lg bg-purple-100 text-purple-600 dark:bg-purple-900/30 dark:text-purple-400">
            <Package :size="20" />
          </div>
          <div>
            <p class="text-sm font-semibold text-gray-900 dark:text-white">Ranking Produk</p>
            <p class="text-xs text-gray-500 dark:text-gray-400">Performa produk terlaris</p>
          </div>
        </button>

        <button
          class="flex items-center gap-3 rounded-xl border border-gray-200 bg-white p-5 text-left transition-shadow hover:shadow-md dark:border-gray-700 dark:bg-gray-800"
          @click="goTo('pos.reports.revenue')"
        >
          <div class="flex h-10 w-10 items-center justify-center rounded-lg bg-indigo-100 text-indigo-600 dark:bg-indigo-900/30 dark:text-indigo-400">
            <BarChart3 :size="20" />
          </div>
          <div>
            <p class="text-sm font-semibold text-gray-900 dark:text-white">Tren Pendapatan</p>
            <p class="text-xs text-gray-500 dark:text-gray-400">Grafik pendapatan 7 hari terakhir</p>
          </div>
        </button>
      </div>

      <!-- Weekly Trend Mini Chart -->
      <div v-if="stats?.weekly_trend?.length" class="mt-6 rounded-xl border border-gray-200 bg-white p-5 dark:border-gray-700 dark:bg-gray-800">
        <h3 class="text-sm font-semibold text-gray-900 dark:text-white">Tren Pendapatan 7 Hari</h3>
        <div class="mt-4 flex items-end gap-2" style="height: 120px">
          <div
            v-for="day in stats.weekly_trend"
            :key="day.date"
            class="flex flex-1 flex-col items-center gap-1"
          >
            <div
              class="w-full rounded-t bg-primary-500 dark:bg-primary-400 transition-all"
              :style="{
                height: `${Math.max(4, (day.revenue / Math.max(...stats.weekly_trend.map(d => d.revenue), 1)) * 100)}px`,
              }"
            ></div>
            <span class="text-[10px] text-gray-400">{{ day.date.slice(5) }}</span>
          </div>
        </div>
      </div>
    </template>
  </div>
</template>
