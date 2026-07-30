<script setup lang="ts">
import { ref, computed, watch } from 'vue'
import { get } from '@purdia/http'
import { useToast } from '@purdia/toast'
import BaseSelect from '@purdia/ui/src/components/BaseSelect.vue'
import BaseInput from '@purdia/ui/src/components/BaseInput.vue'
import { formatCurrency } from '@purdia/utils'
import { useFiscalPeriod } from '@/composables/useFiscalPeriod'
import { BookOpen } from '@lucide/vue'

interface Account {
  id: number
  code: string
  name: string
  type: string
  normal_balance: string
}

interface LedgerLine {
  date: string
  entry_number: number
  description: string
  debit: number
  credit: number
  balance: number
}

interface LedgerResponse {
  account: Account
  opening_balance: number
  lines: LedgerLine[]
}

const toast = useToast()
const { startDate, endDate } = useFiscalPeriod()

const accounts = ref<Account[]>([])
const selectedAccountId = ref<number | string>('')
const ledgerData = ref<LedgerResponse | null>(null)
const loading = ref(false)
const loadingAccounts = ref(true)

// Flatten grouped accounts into a flat list for the selector
const accountOptions = computed(() =>
  accounts.value.map((acc) => ({
    label: `${acc.code} - ${acc.name}`,
    value: acc.id,
  }))
)

// Current total balance (last running balance or opening balance if no lines)
const currentBalance = computed(() => {
  if (!ledgerData.value) return 0
  if (ledgerData.value.lines.length > 0) {
    return ledgerData.value.lines[ledgerData.value.lines.length - 1].balance
  }
  return ledgerData.value.opening_balance
})

// Whether we have a date filter applied (to show opening balance row)
const hasDateFilter = computed(() => {
  return !!(startDate.value || endDate.value)
})

async function fetchAccounts() {
  loadingAccounts.value = true
  try {
    const res = await get<Record<string, Account[]>>('/accounting/accounts')
    // Flatten grouped accounts into a single list
    const grouped = res.data
    const flat: Account[] = []
    for (const type of Object.keys(grouped)) {
      if (Array.isArray(grouped[type])) {
        flat.push(...grouped[type])
      }
    }
    accounts.value = flat
  } catch {
    // Error toast handled globally by @purdia/http onError
  } finally {
    loadingAccounts.value = false
  }
}

async function fetchLedger() {
  if (!selectedAccountId.value) {
    ledgerData.value = null
    return
  }

  loading.value = true
  try {
    const params: Record<string, string> = {}
    if (startDate.value) params.start_date = startDate.value
    if (endDate.value) params.end_date = endDate.value

    const res = await get<LedgerResponse>(`/accounting/ledger/${selectedAccountId.value}`, { params })
    ledgerData.value = res.data
  } catch {
    // Error toast handled globally by @purdia/http onError
  } finally {
    loading.value = false
  }
}

// Watch for account selection or date range changes
watch(selectedAccountId, () => {
  if (selectedAccountId.value) {
    fetchLedger()
  } else {
    ledgerData.value = null
  }
})

watch([startDate, endDate], () => {
  if (selectedAccountId.value) {
    fetchLedger()
  }
})

fetchAccounts()
</script>

<template>
  <div>
    <!-- Header -->
    <div>
      <h1 class="text-2xl font-semibold text-gray-900 dark:text-white">Buku Besar</h1>
      <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">
        Lihat transaksi dan saldo berjalan per akun.
      </p>
    </div>

    <!-- Controls -->
    <div class="mt-6 grid grid-cols-1 gap-4 sm:grid-cols-3">
      <BaseSelect
        v-model="selectedAccountId"
        :options="accountOptions"
        label="Pilih Akun"
        placeholder="Pilih akun..."
        :searchable="true"
      />
      <BaseInput
        v-model="startDate"
        label="Tanggal Mulai"
        type="date"
      />
      <BaseInput
        v-model="endDate"
        label="Tanggal Akhir"
        type="date"
      />
    </div>

    <!-- Balance Card -->
    <div
      v-if="ledgerData"
      class="mt-6 rounded-xl border border-gray-200 bg-white p-5 dark:border-gray-700 dark:bg-gray-800"
    >
      <div class="flex items-center gap-3">
        <div class="flex h-10 w-10 items-center justify-center rounded-lg bg-blue-100 text-blue-600 dark:bg-blue-900/30 dark:text-blue-400">
          <BookOpen :size="20" />
        </div>
        <div>
          <p class="text-xs text-gray-500 dark:text-gray-400">Saldo {{ ledgerData.account.name }}</p>
          <p class="text-lg font-bold text-gray-900 dark:text-white">
            {{ formatCurrency(currentBalance) }}
          </p>
        </div>
      </div>
    </div>

    <!-- Ledger Table -->
    <div
      v-if="ledgerData"
      class="mt-6 overflow-x-auto rounded-xl border border-gray-200 bg-white dark:border-gray-700 dark:bg-gray-800"
    >
      <table class="w-full text-sm">
        <thead>
          <tr class="border-b border-gray-100 text-left text-xs font-medium uppercase tracking-wider text-gray-500 dark:border-gray-700 dark:text-gray-400">
            <th class="px-5 py-3">Tanggal</th>
            <th class="px-5 py-3">No. Jurnal</th>
            <th class="px-5 py-3">Keterangan</th>
            <th class="px-5 py-3 text-right">Debit</th>
            <th class="px-5 py-3 text-right">Kredit</th>
            <th class="px-5 py-3 text-right">Saldo</th>
          </tr>
        </thead>
        <tbody class="divide-y divide-gray-100 dark:divide-gray-700">
          <!-- Opening balance row when date filter applied -->
          <tr v-if="hasDateFilter" class="bg-gray-50 dark:bg-gray-700/50">
            <td class="px-5 py-3 text-gray-500 dark:text-gray-400">—</td>
            <td class="px-5 py-3 text-gray-500 dark:text-gray-400">—</td>
            <td class="px-5 py-3 font-medium text-gray-700 dark:text-gray-300">Saldo Awal</td>
            <td class="px-5 py-3 text-right text-gray-500 dark:text-gray-400">—</td>
            <td class="px-5 py-3 text-right text-gray-500 dark:text-gray-400">—</td>
            <td class="px-5 py-3 text-right font-medium text-gray-900 dark:text-white">
              {{ formatCurrency(ledgerData.opening_balance) }}
            </td>
          </tr>

          <!-- Ledger lines -->
          <tr v-for="(line, index) in ledgerData.lines" :key="index">
            <td class="px-5 py-3 text-gray-700 dark:text-gray-300">{{ line.date }}</td>
            <td class="px-5 py-3 text-gray-700 dark:text-gray-300">{{ line.entry_number }}</td>
            <td class="px-5 py-3 text-gray-700 dark:text-gray-300">{{ line.description }}</td>
            <td class="px-5 py-3 text-right text-gray-700 dark:text-gray-300">
              {{ line.debit > 0 ? formatCurrency(line.debit) : '—' }}
            </td>
            <td class="px-5 py-3 text-right text-gray-700 dark:text-gray-300">
              {{ line.credit > 0 ? formatCurrency(line.credit) : '—' }}
            </td>
            <td class="px-5 py-3 text-right font-medium text-gray-900 dark:text-white">
              {{ formatCurrency(line.balance) }}
            </td>
          </tr>

          <!-- Empty state -->
          <tr v-if="ledgerData.lines.length === 0 && !hasDateFilter">
            <td colspan="6" class="px-5 py-8 text-center text-gray-400 dark:text-gray-500">
              Belum ada transaksi untuk akun ini.
            </td>
          </tr>
          <tr v-if="ledgerData.lines.length === 0 && hasDateFilter">
            <td colspan="6" class="px-5 py-8 text-center text-gray-400 dark:text-gray-500">
              Tidak ada transaksi dalam periode ini.
            </td>
          </tr>
        </tbody>
      </table>
    </div>

    <!-- Empty state before account selection -->
    <div
      v-if="!ledgerData && !loading"
      class="mt-6 rounded-xl border border-gray-200 bg-white px-5 py-12 text-center dark:border-gray-700 dark:bg-gray-800"
    >
      <BookOpen class="mx-auto h-12 w-12 text-gray-300 dark:text-gray-600" />
      <p class="mt-3 text-sm text-gray-500 dark:text-gray-400">
        Pilih akun untuk melihat buku besar.
      </p>
    </div>

    <!-- Loading state -->
    <div
      v-if="loading"
      class="mt-6 rounded-xl border border-gray-200 bg-white px-5 py-12 text-center dark:border-gray-700 dark:bg-gray-800"
    >
      <div class="flex items-center justify-center gap-2 text-sm text-gray-500 dark:text-gray-400">
        <svg class="h-5 w-5 animate-spin" viewBox="0 0 24 24" fill="none">
          <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4" />
          <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z" />
        </svg>
        Memuat data...
      </div>
    </div>
  </div>
</template>
