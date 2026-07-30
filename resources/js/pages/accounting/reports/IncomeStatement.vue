<script setup lang="ts">
import { ref, watch } from 'vue'
import { get } from '@purdia/http'
import { formatCurrency } from '@purdia/utils'
import { useFiscalPeriod } from '@/composables/useFiscalPeriod'
import BaseSpinner from '@purdia/ui/src/components/BaseSpinner.vue'

interface ReportAccount {
  account_id: number
  code: string
  name: string
  balance: number
}

interface IncomeStatementData {
  revenue: {
    accounts: ReportAccount[]
    total: number
  }
  expense: {
    accounts: ReportAccount[]
    total: number
  }
  net_income: number
  label: string
}

const { startDate, endDate } = useFiscalPeriod()

const data = ref<IncomeStatementData | null>(null)
const loading = ref(true)

function netIncomeColor(value: number): string {
  if (value > 0) return 'text-emerald-600 dark:text-emerald-400'
  if (value < 0) return 'text-red-600 dark:text-red-400'
  return 'text-gray-500 dark:text-gray-400'
}

async function fetchReport() {
  loading.value = true
  try {
    const res = await get<IncomeStatementData>('/accounting/reports/income-statement', {
      params: { start_date: startDate.value, end_date: endDate.value },
    })
    data.value = res.data
  } catch {
    // Error handled by @purdia/http onError
  } finally {
    loading.value = false
  }
}

watch([startDate, endDate], () => fetchReport())
fetchReport()
</script>

<template>
  <div>
    <div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
      <div>
        <h1 class="text-2xl font-semibold text-gray-900 dark:text-white">Laporan Laba Rugi</h1>
        <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">Income Statement untuk periode yang dipilih.</p>
      </div>
      <div class="flex items-center gap-2">
        <input
          type="date"
          v-model="startDate"
          class="rounded-lg border border-gray-300 bg-white px-3 py-1.5 text-sm dark:border-gray-600 dark:bg-gray-800 dark:text-white"
        />
        <span class="text-sm text-gray-500 dark:text-gray-400">s/d</span>
        <input
          type="date"
          v-model="endDate"
          class="rounded-lg border border-gray-300 bg-white px-3 py-1.5 text-sm dark:border-gray-600 dark:bg-gray-800 dark:text-white"
        />
      </div>
    </div>

    <!-- Loading -->
    <div v-if="loading" class="mt-8 flex justify-center">
      <BaseSpinner />
    </div>

    <!-- Report Content -->
    <div v-else-if="data" class="mt-6 space-y-6">
      <!-- Pendapatan Section -->
      <div class="rounded-xl border border-gray-200 bg-white dark:border-gray-700 dark:bg-gray-800">
        <div class="border-b border-gray-200 px-5 py-3 dark:border-gray-700">
          <h2 class="text-base font-semibold text-gray-900 dark:text-white">Pendapatan</h2>
        </div>
        <div class="divide-y divide-gray-100 dark:divide-gray-700">
          <div
            v-for="account in data.revenue.accounts"
            :key="account.account_id"
            class="flex items-center justify-between px-5 py-2.5"
          >
            <div class="flex items-center gap-3">
              <span class="font-mono text-xs text-gray-500 dark:text-gray-400">{{ account.code }}</span>
              <span class="text-sm text-gray-900 dark:text-white">{{ account.name }}</span>
            </div>
            <span class="text-sm font-medium text-gray-700 dark:text-gray-300">{{ formatCurrency(account.balance) }}</span>
          </div>
          <div v-if="data.revenue.accounts.length === 0" class="px-5 py-4 text-center text-sm text-gray-400">
            Tidak ada pendapatan pada periode ini.
          </div>
        </div>
        <div class="flex items-center justify-between border-t border-gray-300 bg-gray-50 px-5 py-3 dark:border-gray-600 dark:bg-gray-800/50">
          <span class="text-sm font-semibold text-gray-700 dark:text-gray-300">Total Pendapatan</span>
          <span class="text-sm font-bold text-gray-900 dark:text-white">{{ formatCurrency(data.revenue.total) }}</span>
        </div>
      </div>

      <!-- Beban Section -->
      <div class="rounded-xl border border-gray-200 bg-white dark:border-gray-700 dark:bg-gray-800">
        <div class="border-b border-gray-200 px-5 py-3 dark:border-gray-700">
          <h2 class="text-base font-semibold text-gray-900 dark:text-white">Beban</h2>
        </div>
        <div class="divide-y divide-gray-100 dark:divide-gray-700">
          <div
            v-for="account in data.expense.accounts"
            :key="account.account_id"
            class="flex items-center justify-between px-5 py-2.5"
          >
            <div class="flex items-center gap-3">
              <span class="font-mono text-xs text-gray-500 dark:text-gray-400">{{ account.code }}</span>
              <span class="text-sm text-gray-900 dark:text-white">{{ account.name }}</span>
            </div>
            <span class="text-sm font-medium text-gray-700 dark:text-gray-300">{{ formatCurrency(account.balance) }}</span>
          </div>
          <div v-if="data.expense.accounts.length === 0" class="px-5 py-4 text-center text-sm text-gray-400">
            Tidak ada beban pada periode ini.
          </div>
        </div>
        <div class="flex items-center justify-between border-t border-gray-300 bg-gray-50 px-5 py-3 dark:border-gray-600 dark:bg-gray-800/50">
          <span class="text-sm font-semibold text-gray-700 dark:text-gray-300">Total Beban</span>
          <span class="text-sm font-bold text-gray-900 dark:text-white">{{ formatCurrency(data.expense.total) }}</span>
        </div>
      </div>

      <!-- Net Income Section -->
      <div class="rounded-xl border border-gray-200 bg-white p-5 dark:border-gray-700 dark:bg-gray-800">
        <div class="flex items-center justify-between">
          <span class="text-base font-semibold text-gray-900 dark:text-white">{{ data.label }}</span>
          <span class="text-xl font-bold" :class="netIncomeColor(data.net_income)">
            {{ formatCurrency(Math.abs(data.net_income)) }}
          </span>
        </div>
      </div>
    </div>
  </div>
</template>
