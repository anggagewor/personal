<script setup lang="ts">
import { ref, watch } from 'vue'
import { get } from '@purdia/http'
import { formatCurrency } from '@purdia/utils'
import { useFiscalPeriod } from '@/composables/useFiscalPeriod'
import BaseBadge from '@purdia/ui/src/components/BaseBadge.vue'
import BaseSpinner from '@purdia/ui/src/components/BaseSpinner.vue'

interface TrialBalanceAccount {
  account_id: number
  code: string
  name: string
  debit: number
  credit: number
}

interface TrialBalanceData {
  accounts: TrialBalanceAccount[]
  total_debit: number
  total_credit: number
  is_balanced: boolean
}

const { startDate, endDate } = useFiscalPeriod()

const data = ref<TrialBalanceData | null>(null)
const loading = ref(true)

async function fetchReport() {
  loading.value = true
  try {
    const res = await get<TrialBalanceData>('/accounting/reports/trial-balance', {
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
        <h1 class="text-2xl font-semibold text-gray-900 dark:text-white">Neraca Saldo</h1>
        <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">Trial Balance untuk periode yang dipilih.</p>
      </div>
      <div class="flex items-center gap-3">
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
        <BaseBadge v-if="data" :variant="data.is_balanced ? 'success' : 'danger'">
          {{ data.is_balanced ? 'Seimbang' : 'Tidak Seimbang' }}
        </BaseBadge>
      </div>
    </div>

    <!-- Loading -->
    <div v-if="loading" class="mt-8 flex justify-center">
      <BaseSpinner />
    </div>

    <!-- Report Table -->
    <div v-else-if="data" class="mt-6 overflow-x-auto rounded-xl border border-gray-200 bg-white dark:border-gray-700 dark:bg-gray-800">
      <table class="w-full text-sm">
        <thead>
          <tr class="border-b border-gray-200 bg-gray-50 dark:border-gray-700 dark:bg-gray-800/50">
            <th class="px-4 py-3 text-left font-medium text-gray-600 dark:text-gray-300">Kode</th>
            <th class="px-4 py-3 text-left font-medium text-gray-600 dark:text-gray-300">Nama Akun</th>
            <th class="px-4 py-3 text-right font-medium text-gray-600 dark:text-gray-300">Debit</th>
            <th class="px-4 py-3 text-right font-medium text-gray-600 dark:text-gray-300">Kredit</th>
          </tr>
        </thead>
        <tbody class="divide-y divide-gray-100 dark:divide-gray-700">
          <tr v-for="account in data.accounts" :key="account.account_id" class="hover:bg-gray-50 dark:hover:bg-gray-700/30">
            <td class="px-4 py-2.5 font-mono text-gray-700 dark:text-gray-300">{{ account.code }}</td>
            <td class="px-4 py-2.5 text-gray-900 dark:text-white">{{ account.name }}</td>
            <td class="px-4 py-2.5 text-right text-gray-700 dark:text-gray-300">
              {{ account.debit > 0 ? formatCurrency(account.debit) : '-' }}
            </td>
            <td class="px-4 py-2.5 text-right text-gray-700 dark:text-gray-300">
              {{ account.credit > 0 ? formatCurrency(account.credit) : '-' }}
            </td>
          </tr>
        </tbody>
        <tfoot>
          <tr class="border-t-2 border-gray-300 bg-gray-50 font-semibold dark:border-gray-600 dark:bg-gray-800/50">
            <td class="px-4 py-3" colspan="2">Total</td>
            <td class="px-4 py-3 text-right text-gray-900 dark:text-white">{{ formatCurrency(data.total_debit) }}</td>
            <td class="px-4 py-3 text-right text-gray-900 dark:text-white">{{ formatCurrency(data.total_credit) }}</td>
          </tr>
        </tfoot>
      </table>

      <!-- Empty state -->
      <div v-if="data.accounts.length === 0" class="py-8 text-center text-sm text-gray-400">
        Tidak ada data untuk periode ini.
      </div>
    </div>
  </div>
</template>
