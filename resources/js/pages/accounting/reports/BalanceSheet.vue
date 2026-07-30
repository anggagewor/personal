<script setup lang="ts">
import { ref, watch } from 'vue'
import { formatCurrency } from '@purdia/utils'
import BaseBadge from '@purdia/ui/src/components/BaseBadge.vue'
import BaseSpinner from '@purdia/ui/src/components/BaseSpinner.vue'
import type { BalanceSheetData } from '@/types/accounting'
import * as accountingApi from '@/api/accounting'

function getToday(): string {
  const now = new Date()
  return `${now.getFullYear()}-${String(now.getMonth() + 1).padStart(2, '0')}-${String(now.getDate()).padStart(2, '0')}`
}

const selectedDate = ref(getToday())
const data = ref<BalanceSheetData | null>(null)
const loading = ref(true)

async function fetchReport() {
  loading.value = true
  try {
    const res = await accountingApi.fetchBalanceSheet({ date: selectedDate.value })
    data.value = res.data
  } catch {
    // Error handled by @purdia/http onError
  } finally {
    loading.value = false
  }
}

watch(selectedDate, () => fetchReport())
fetchReport()
</script>

<template>
  <div>
    <div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
      <div>
        <h1 class="text-2xl font-semibold text-gray-900 dark:text-white">Neraca</h1>
        <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">Balance Sheet per tanggal yang dipilih.</p>
      </div>
      <div class="flex items-center gap-3">
        <input
          type="date"
          v-model="selectedDate"
          class="rounded-lg border border-gray-300 bg-white px-3 py-1.5 text-sm dark:border-gray-600 dark:bg-gray-800 dark:text-white"
        />
        <BaseBadge v-if="data" :variant="data.is_balanced ? 'success' : 'danger'">
          {{ data.is_balanced ? 'Seimbang' : 'Tidak Seimbang' }}
        </BaseBadge>
      </div>
    </div>

    <!-- Loading -->
    <div v-if="loading" class="mt-8 flex justify-center">
      <BaseSpinner />
    </div>

    <!-- Report Content -->
    <div v-else-if="data" class="mt-6 space-y-6">
      <!-- Aset Section -->
      <div class="rounded-xl border border-gray-200 bg-white dark:border-gray-700 dark:bg-gray-800">
        <div class="border-b border-gray-200 px-5 py-3 dark:border-gray-700">
          <h2 class="text-base font-semibold text-gray-900 dark:text-white">Aset</h2>
        </div>
        <div class="divide-y divide-gray-100 dark:divide-gray-700">
          <div
            v-for="account in data.assets"
            :key="account.account_id"
            class="flex items-center justify-between px-5 py-2.5"
          >
            <div class="flex items-center gap-3">
              <span class="font-mono text-xs text-gray-500 dark:text-gray-400">{{ account.code }}</span>
              <span class="text-sm text-gray-900 dark:text-white">{{ account.name }}</span>
            </div>
            <span class="text-sm font-medium text-gray-700 dark:text-gray-300">{{ formatCurrency(account.balance) }}</span>
          </div>
          <div v-if="data.assets.length === 0" class="px-5 py-4 text-center text-sm text-gray-400">
            Tidak ada data aset.
          </div>
        </div>
        <div class="flex items-center justify-between border-t border-gray-300 bg-gray-50 px-5 py-3 dark:border-gray-600 dark:bg-gray-800/50">
          <span class="text-sm font-semibold text-gray-700 dark:text-gray-300">Total Aset</span>
          <span class="text-sm font-bold text-gray-900 dark:text-white">{{ formatCurrency(data.total_assets) }}</span>
        </div>
      </div>

      <!-- Kewajiban Section -->
      <div class="rounded-xl border border-gray-200 bg-white dark:border-gray-700 dark:bg-gray-800">
        <div class="border-b border-gray-200 px-5 py-3 dark:border-gray-700">
          <h2 class="text-base font-semibold text-gray-900 dark:text-white">Kewajiban</h2>
        </div>
        <div class="divide-y divide-gray-100 dark:divide-gray-700">
          <div
            v-for="account in data.liabilities"
            :key="account.account_id"
            class="flex items-center justify-between px-5 py-2.5"
          >
            <div class="flex items-center gap-3">
              <span class="font-mono text-xs text-gray-500 dark:text-gray-400">{{ account.code }}</span>
              <span class="text-sm text-gray-900 dark:text-white">{{ account.name }}</span>
            </div>
            <span class="text-sm font-medium text-gray-700 dark:text-gray-300">{{ formatCurrency(account.balance) }}</span>
          </div>
          <div v-if="data.liabilities.length === 0" class="px-5 py-4 text-center text-sm text-gray-400">
            Tidak ada data kewajiban.
          </div>
        </div>
        <div class="flex items-center justify-between border-t border-gray-300 bg-gray-50 px-5 py-3 dark:border-gray-600 dark:bg-gray-800/50">
          <span class="text-sm font-semibold text-gray-700 dark:text-gray-300">Total Kewajiban</span>
          <span class="text-sm font-bold text-gray-900 dark:text-white">{{ formatCurrency(data.total_liabilities) }}</span>
        </div>
      </div>

      <!-- Ekuitas Section -->
      <div class="rounded-xl border border-gray-200 bg-white dark:border-gray-700 dark:bg-gray-800">
        <div class="border-b border-gray-200 px-5 py-3 dark:border-gray-700">
          <h2 class="text-base font-semibold text-gray-900 dark:text-white">Ekuitas</h2>
        </div>
        <div class="divide-y divide-gray-100 dark:divide-gray-700">
          <div
            v-for="account in data.equity"
            :key="account.account_id"
            class="flex items-center justify-between px-5 py-2.5"
          >
            <div class="flex items-center gap-3">
              <span class="font-mono text-xs text-gray-500 dark:text-gray-400">{{ account.code }}</span>
              <span class="text-sm text-gray-900 dark:text-white">{{ account.name }}</span>
            </div>
            <span class="text-sm font-medium text-gray-700 dark:text-gray-300">{{ formatCurrency(account.balance) }}</span>
          </div>
          <!-- Laba Periode Berjalan row -->
          <div class="flex items-center justify-between px-5 py-2.5">
            <div class="flex items-center gap-3">
              <span class="font-mono text-xs text-gray-500 dark:text-gray-400">&nbsp;</span>
              <span class="text-sm italic text-gray-700 dark:text-gray-300">Laba Periode Berjalan</span>
            </div>
            <span
              class="text-sm font-medium"
              :class="data.net_income >= 0 ? 'text-emerald-600 dark:text-emerald-400' : 'text-red-600 dark:text-red-400'"
            >
              {{ formatCurrency(data.net_income) }}
            </span>
          </div>
        </div>
        <div class="flex items-center justify-between border-t border-gray-300 bg-gray-50 px-5 py-3 dark:border-gray-600 dark:bg-gray-800/50">
          <span class="text-sm font-semibold text-gray-700 dark:text-gray-300">Total Ekuitas</span>
          <span class="text-sm font-bold text-gray-900 dark:text-white">{{ formatCurrency(data.total_equity) }}</span>
        </div>
      </div>

      <!-- Accounting Equation Display -->
      <div class="rounded-xl border border-gray-200 bg-white p-5 dark:border-gray-700 dark:bg-gray-800">
        <h3 class="mb-3 text-sm font-medium text-gray-500 dark:text-gray-400">Persamaan Akuntansi</h3>
        <div class="flex flex-wrap items-center justify-center gap-2 text-center">
          <div>
            <p class="text-xs text-gray-500 dark:text-gray-400">Total Aset</p>
            <p class="text-lg font-bold text-gray-900 dark:text-white">{{ formatCurrency(data.total_assets) }}</p>
          </div>
          <span class="text-lg font-bold text-gray-400">=</span>
          <div>
            <p class="text-xs text-gray-500 dark:text-gray-400">Total Kewajiban</p>
            <p class="text-lg font-bold text-gray-900 dark:text-white">{{ formatCurrency(data.total_liabilities) }}</p>
          </div>
          <span class="text-lg font-bold text-gray-400">+</span>
          <div>
            <p class="text-xs text-gray-500 dark:text-gray-400">Total Ekuitas</p>
            <p class="text-lg font-bold text-gray-900 dark:text-white">{{ formatCurrency(data.total_equity) }}</p>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>
