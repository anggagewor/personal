<script setup lang="ts">
import { ref, computed, watch } from 'vue'
import { useRoute, useRouter } from 'vue-router'
import { useToast } from '@purdia/toast'
import { formatCurrency, formatDate } from '@purdia/utils'
import BaseButton from '@purdia/ui/src/components/BaseButton.vue'
import BaseInput from '@purdia/ui/src/components/BaseInput.vue'
import BaseSelect from '@purdia/ui/src/components/BaseSelect.vue'
import BaseBadge from '@purdia/ui/src/components/BaseBadge.vue'
import { Receipt, ChevronLeft, ChevronRight } from '@lucide/vue'
import type { Transaction } from '@/types/pos'
import * as posApi from '@/api/pos'

const route = useRoute()
const router = useRouter()
const toast = useToast()

const outletId = computed(() => Number(route.query.outlet))

const transactions = ref<Transaction[]>([])
const loading = ref(true)
const currentPage = ref(1)
const totalPages = ref(1)
const perPage = 20

// Filters
const filterStatus = ref('')
const filterDateFrom = ref('')
const filterDateTo = ref('')
const filterPaymentMethod = ref('')

const statusOptions = [
  { label: 'Semua Status', value: '' },
  { label: 'Selesai', value: 'completed' },
  { label: 'Tertunda', value: 'pending' },
  { label: 'Dibatalkan', value: 'voided' },
]

const paymentMethodOptions = [
  { label: 'Semua Metode', value: '' },
  { label: 'Cash', value: 'cash' },
  { label: 'Bank Transfer', value: 'bank_transfer' },
  { label: 'E-Wallet', value: 'e_wallet' },
]

async function fetchTransactions() {
  if (!outletId.value) return
  loading.value = true
  try {
    const params: Record<string, string | number> = {
      page: currentPage.value,
      per_page: perPage,
    }
    if (filterStatus.value) params.status = filterStatus.value
    if (filterDateFrom.value) params.date_from = filterDateFrom.value
    if (filterDateTo.value) params.date_to = filterDateTo.value
    if (filterPaymentMethod.value) params.payment_method_type = filterPaymentMethod.value

    const res = await posApi.fetchTransactions(outletId.value, params)
    if (Array.isArray(res.data)) {
      transactions.value = res.data
    } else {
      // Paginated response
      const paginated = res.data as unknown as { data: Transaction[]; last_page: number }
      transactions.value = paginated.data ?? res.data
      totalPages.value = paginated.last_page ?? 1
    }
  } catch {
    // Error handled globally
  } finally {
    loading.value = false
  }
}

function statusVariant(status: Transaction['status']): 'success' | 'warning' | 'danger' {
  switch (status) {
    case 'completed': return 'success'
    case 'pending': return 'warning'
    case 'voided': return 'danger'
  }
}

function statusLabel(status: Transaction['status']) {
  switch (status) {
    case 'completed': return 'Selesai'
    case 'pending': return 'Tertunda'
    case 'voided': return 'Dibatalkan'
  }
}

function viewDetail(transaction: Transaction) {
  router.push({ name: 'pos.transactions.detail', query: { outlet: outletId.value, id: transaction.id } })
}

function goToPage(page: number) {
  if (page < 1 || page > totalPages.value) return
  currentPage.value = page
}

function applyFilters() {
  currentPage.value = 1
  fetchTransactions()
}

watch(currentPage, () => fetchTransactions())

// Initial load
fetchTransactions()
</script>

<template>
  <div>
    <!-- Header -->
    <div class="flex items-center justify-between">
      <div>
        <h1 class="text-2xl font-semibold text-gray-900 dark:text-white">Riwayat Transaksi</h1>
        <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">Daftar semua transaksi outlet.</p>
      </div>
    </div>

    <!-- Filters -->
    <div class="mt-6 flex flex-wrap items-end gap-3">
      <div class="w-40">
        <BaseSelect
          v-model="filterStatus"
          label="Status"
          :options="statusOptions"
          :clearable="false"
          @update:model-value="applyFilters"
        />
      </div>
      <div class="w-40">
        <BaseSelect
          v-model="filterPaymentMethod"
          label="Metode Bayar"
          :options="paymentMethodOptions"
          :clearable="false"
          @update:model-value="applyFilters"
        />
      </div>
      <div class="w-40">
        <BaseInput
          v-model="filterDateFrom"
          label="Dari Tanggal"
          type="date"
          @change="applyFilters"
        />
      </div>
      <div class="w-40">
        <BaseInput
          v-model="filterDateTo"
          label="Sampai Tanggal"
          type="date"
          @change="applyFilters"
        />
      </div>
    </div>

    <!-- Loading -->
    <div v-if="loading" class="mt-6 py-8 text-center text-sm text-gray-400">Memuat data...</div>

    <!-- Empty state -->
    <div v-else-if="!transactions.length" class="mt-12 flex flex-col items-center text-center">
      <div class="flex h-16 w-16 items-center justify-center rounded-2xl bg-gray-100 dark:bg-gray-800">
        <Receipt :size="28" class="text-gray-400" />
      </div>
      <h3 class="mt-4 text-base font-medium text-gray-900 dark:text-white">Belum ada transaksi</h3>
      <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">Transaksi akan muncul setelah checkout dari kasir.</p>
    </div>

    <!-- Transaction table -->
    <div v-else class="mt-6 overflow-x-auto rounded-xl border border-gray-200 bg-white dark:border-gray-700 dark:bg-gray-800">
      <table class="w-full text-left text-sm">
        <thead>
          <tr class="border-b border-gray-200 dark:border-gray-700">
            <th class="px-4 py-3 font-medium text-gray-500 dark:text-gray-400">No. Transaksi</th>
            <th class="px-4 py-3 font-medium text-gray-500 dark:text-gray-400">Tanggal</th>
            <th class="px-4 py-3 text-right font-medium text-gray-500 dark:text-gray-400">Total</th>
            <th class="px-4 py-3 font-medium text-gray-500 dark:text-gray-400">Metode Bayar</th>
            <th class="px-4 py-3 font-medium text-gray-500 dark:text-gray-400">Status</th>
          </tr>
        </thead>
        <tbody>
          <tr
            v-for="trx in transactions"
            :key="trx.id"
            class="cursor-pointer border-b border-gray-100 last:border-0 hover:bg-gray-50 dark:border-gray-700/50 dark:hover:bg-gray-700/30"
            @click="viewDetail(trx)"
          >
            <td class="px-4 py-3 font-mono text-xs font-semibold text-gray-900 dark:text-white">
              {{ trx.transaction_number }}
            </td>
            <td class="px-4 py-3 text-gray-600 dark:text-gray-300">
              {{ formatDate(trx.created_at, { day: 'numeric', month: 'short', year: 'numeric', hour: '2-digit', minute: '2-digit' }) }}
            </td>
            <td class="px-4 py-3 text-right font-medium text-gray-900 dark:text-white">
              {{ formatCurrency(trx.total) }}
            </td>
            <td class="px-4 py-3 text-gray-600 dark:text-gray-300">
              {{ trx.payment_method ?? '—' }}
            </td>
            <td class="px-4 py-3">
              <BaseBadge :variant="statusVariant(trx.status)" size="sm">
                {{ statusLabel(trx.status) }}
              </BaseBadge>
            </td>
          </tr>
        </tbody>
      </table>

      <!-- Pagination -->
      <div
        v-if="totalPages > 1"
        class="flex items-center justify-between border-t border-gray-200 px-4 py-3 dark:border-gray-700"
      >
        <span class="text-xs text-gray-500 dark:text-gray-400">
          Halaman {{ currentPage }} dari {{ totalPages }}
        </span>
        <div class="flex gap-1">
          <button
            class="rounded p-1.5 text-gray-400 hover:text-gray-600 disabled:opacity-30 dark:hover:text-gray-300"
            :disabled="currentPage <= 1"
            @click="goToPage(currentPage - 1)"
          >
            <ChevronLeft :size="16" />
          </button>
          <button
            class="rounded p-1.5 text-gray-400 hover:text-gray-600 disabled:opacity-30 dark:hover:text-gray-300"
            :disabled="currentPage >= totalPages"
            @click="goToPage(currentPage + 1)"
          >
            <ChevronRight :size="16" />
          </button>
        </div>
      </div>
    </div>
  </div>
</template>
