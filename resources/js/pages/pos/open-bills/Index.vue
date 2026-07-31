<script setup lang="ts">
import { ref, computed } from 'vue'
import { useRoute } from 'vue-router'
import { useToast } from '@purdia/toast'
import { formatCurrency, formatDate } from '@purdia/utils'
import BaseButton from '@purdia/ui/src/components/BaseButton.vue'
import BaseBadge from '@purdia/ui/src/components/BaseBadge.vue'
import { ClipboardList, AlertTriangle } from '@lucide/vue'
import type { OpenBill, PaymentMethod } from '@/types/pos'
import * as posApi from '@/api/pos'
import CloseBillModal from './CloseBillModal.vue'

const route = useRoute()
const toast = useToast()

const outletId = computed(() => Number(route.query.outlet))

const bills = ref<OpenBill[]>([])
const paymentMethods = ref<PaymentMethod[]>([])
const loading = ref(true)

// Close modal
const showCloseModal = ref(false)
const selectedBill = ref<OpenBill | null>(null)

async function fetchData() {
  if (!outletId.value) return
  loading.value = true
  try {
    const [billsRes, pmRes] = await Promise.all([
      posApi.fetchOpenBills(outletId.value),
      posApi.fetchPaymentMethods(outletId.value),
    ])
    bills.value = billsRes.data
    paymentMethods.value = pmRes.data
  } catch {
    // Error handled globally
  } finally {
    loading.value = false
  }
}

function openClose(bill: OpenBill) {
  selectedBill.value = bill
  showCloseModal.value = true
}

function onClosed() {
  fetchData()
}

function itemsCount(bill: OpenBill) {
  return bill.items?.length ?? 0
}

// Initial load
fetchData()
</script>

<template>
  <div>
    <!-- Header -->
    <div class="flex items-center justify-between">
      <div>
        <h1 class="text-2xl font-semibold text-gray-900 dark:text-white">Tagihan Terbuka</h1>
        <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">Transaksi yang belum dibayar (pay-later).</p>
      </div>
    </div>

    <!-- Loading -->
    <div v-if="loading" class="mt-6 py-8 text-center text-sm text-gray-400">Memuat data...</div>

    <!-- Empty state -->
    <div v-else-if="!bills.length" class="mt-12 flex flex-col items-center text-center">
      <div class="flex h-16 w-16 items-center justify-center rounded-2xl bg-gray-100 dark:bg-gray-800">
        <ClipboardList :size="28" class="text-gray-400" />
      </div>
      <h3 class="mt-4 text-base font-medium text-gray-900 dark:text-white">Tidak ada tagihan terbuka</h3>
      <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">Semua tagihan sudah dibayar.</p>
    </div>

    <!-- Bills list -->
    <div v-else class="mt-6 space-y-3">
      <div
        v-for="bill in bills"
        :key="bill.id"
        class="flex items-center justify-between rounded-xl border bg-white p-4 transition-shadow hover:shadow-md dark:bg-gray-800"
        :class="bill.is_overdue
          ? 'border-amber-300 dark:border-amber-700'
          : 'border-gray-200 dark:border-gray-700'"
      >
        <div class="flex items-center gap-4">
          <!-- Overdue indicator -->
          <div
            class="flex h-10 w-10 shrink-0 items-center justify-center rounded-lg"
            :class="bill.is_overdue
              ? 'bg-amber-100 text-amber-600 dark:bg-amber-900/30 dark:text-amber-400'
              : 'bg-gray-100 text-gray-500 dark:bg-gray-700 dark:text-gray-400'"
          >
            <AlertTriangle v-if="bill.is_overdue" :size="20" />
            <ClipboardList v-else :size="20" />
          </div>

          <!-- Info -->
          <div>
            <div class="flex items-center gap-2">
              <h3 class="font-mono text-xs font-semibold text-gray-900 dark:text-white">
                {{ bill.transaction_number }}
              </h3>
              <BaseBadge v-if="bill.is_overdue" variant="warning" size="sm">
                Lewat 24 jam
              </BaseBadge>
            </div>
            <div class="mt-1 flex flex-wrap items-center gap-3 text-xs text-gray-500 dark:text-gray-400">
              <span>{{ formatDate(bill.created_at, { day: 'numeric', month: 'short', year: 'numeric', hour: '2-digit', minute: '2-digit' }) }}</span>
              <span>{{ itemsCount(bill) }} item</span>
              <span v-if="bill.member_name">{{ bill.member_name }}</span>
              <span v-if="bill.table_name">Meja: {{ bill.table_name }}</span>
            </div>
          </div>
        </div>

        <!-- Total + close button -->
        <div class="flex items-center gap-4">
          <span class="text-sm font-semibold text-gray-900 dark:text-white">
            {{ formatCurrency(bill.total) }}
          </span>
          <BaseButton variant="primary" size="sm" @click="openClose(bill)">
            Tutup
          </BaseButton>
        </div>
      </div>
    </div>

    <!-- Close Bill Modal -->
    <CloseBillModal
      v-model="showCloseModal"
      :bill="selectedBill"
      :payment-methods="paymentMethods"
      @closed="onClosed"
    />
  </div>
</template>
