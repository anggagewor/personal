<script setup lang="ts">
import { ref, computed } from 'vue'
import { useRoute, useRouter } from 'vue-router'
import { useToast } from '@purdia/toast'
import { formatCurrency, formatDate } from '@purdia/utils'
import BaseButton from '@purdia/ui/src/components/BaseButton.vue'
import BaseBadge from '@purdia/ui/src/components/BaseBadge.vue'
import { ArrowLeft, Ban, RotateCcw } from '@lucide/vue'
import type { Transaction } from '@/types/pos'
import * as posApi from '@/api/pos'
import VoidModal from './VoidModal.vue'
import RefundModal from './RefundModal.vue'

const route = useRoute()
const router = useRouter()
const toast = useToast()

const outletId = computed(() => Number(route.query.outlet))
const transactionId = computed(() => Number(route.query.id))

const transaction = ref<Transaction | null>(null)
const loading = ref(true)
const showVoidModal = ref(false)
const showRefundModal = ref(false)

async function fetchTransaction() {
  if (!transactionId.value) return
  loading.value = true
  try {
    const res = await posApi.fetchTransaction(transactionId.value)
    transaction.value = res.data
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
    case 'refunded': return 'danger'
    case 'partially_refunded': return 'warning'
  }
}

function statusLabel(status: Transaction['status']) {
  switch (status) {
    case 'completed': return 'Selesai'
    case 'pending': return 'Tertunda'
    case 'voided': return 'Dibatalkan'
    case 'refunded': return 'Refunded'
    case 'partially_refunded': return 'Partial Refund'
  }
}

function goBack() {
  router.push({ name: 'pos.transactions', query: { outlet: outletId.value } })
}

function openVoid() {
  showVoidModal.value = true
}

function openRefund() {
  showRefundModal.value = true
}

function onVoided() {
  fetchTransaction()
}

function onRefunded() {
  fetchTransaction()
}

// Initial load
fetchTransaction()
</script>

<template>
  <div>
    <!-- Header -->
    <div class="flex items-center gap-3">
      <button
        class="rounded-lg p-2 text-gray-400 hover:bg-gray-100 hover:text-gray-600 dark:hover:bg-gray-700 dark:hover:text-gray-300"
        @click="goBack"
      >
        <ArrowLeft :size="20" />
      </button>
      <div class="flex-1">
        <h1 class="text-2xl font-semibold text-gray-900 dark:text-white">Detail Transaksi</h1>
        <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">
          {{ transaction?.transaction_number ?? '...' }}
        </p>
      </div>
      <BaseButton
        v-if="transaction && (transaction.status === 'completed' || transaction.status === 'partially_refunded')"
        variant="secondary"
        size="sm"
        :icon="RotateCcw"
        @click="openRefund"
      >
        Refund
      </BaseButton>
      <BaseButton
        v-if="transaction && transaction.status === 'completed'"
        variant="danger"
        size="sm"
        :icon="Ban"
        @click="openVoid"
      >
        Void
      </BaseButton>
    </div>

    <!-- Loading -->
    <div v-if="loading" class="mt-6 py-8 text-center text-sm text-gray-400">Memuat data...</div>

    <!-- Detail content -->
    <div v-else-if="transaction" class="mt-6 space-y-6">
      <!-- Info card -->
      <div class="rounded-xl border border-gray-200 bg-white p-5 dark:border-gray-700 dark:bg-gray-800">
        <div class="grid grid-cols-1 gap-4 sm:grid-cols-2 lg:grid-cols-3">
          <div>
            <span class="text-xs font-medium text-gray-500 dark:text-gray-400">No. Transaksi</span>
            <p class="mt-0.5 font-mono text-sm font-semibold text-gray-900 dark:text-white">
              {{ transaction.transaction_number }}
            </p>
          </div>
          <div>
            <span class="text-xs font-medium text-gray-500 dark:text-gray-400">Tanggal</span>
            <p class="mt-0.5 text-sm text-gray-900 dark:text-white">
              {{ formatDate(transaction.created_at, { day: 'numeric', month: 'long', year: 'numeric', hour: '2-digit', minute: '2-digit' }) }}
            </p>
          </div>
          <div>
            <span class="text-xs font-medium text-gray-500 dark:text-gray-400">Status</span>
            <div class="mt-0.5">
              <BaseBadge :variant="statusVariant(transaction.status)" size="sm">
                {{ statusLabel(transaction.status) }}
              </BaseBadge>
            </div>
          </div>
          <div>
            <span class="text-xs font-medium text-gray-500 dark:text-gray-400">Metode Bayar</span>
            <p class="mt-0.5 text-sm text-gray-900 dark:text-white">
              {{ transaction.payment_method ?? '—' }}
            </p>
          </div>
          <div>
            <span class="text-xs font-medium text-gray-500 dark:text-gray-400">Member</span>
            <p class="mt-0.5 text-sm text-gray-900 dark:text-white">
              {{ transaction.member_name ?? 'Walk-in' }}
            </p>
          </div>
          <div>
            <span class="text-xs font-medium text-gray-500 dark:text-gray-400">Voucher</span>
            <p class="mt-0.5 text-sm text-gray-900 dark:text-white">
              {{ transaction.voucher_code ?? '—' }}
            </p>
          </div>
        </div>
      </div>

      <!-- Void info -->
      <div
        v-if="transaction.status === 'voided'"
        class="rounded-xl border border-red-200 bg-red-50 p-4 dark:border-red-800 dark:bg-red-900/20"
      >
        <h3 class="text-sm font-semibold text-red-800 dark:text-red-300">Informasi Void</h3>
        <div class="mt-2 space-y-1 text-sm text-red-700 dark:text-red-400">
          <p><strong>Alasan:</strong> {{ transaction.void_reason }}</p>
          <p v-if="transaction.voided_at">
            <strong>Waktu:</strong> {{ formatDate(transaction.voided_at, { day: 'numeric', month: 'long', year: 'numeric', hour: '2-digit', minute: '2-digit' }) }}
          </p>
        </div>
      </div>

      <!-- Items table -->
      <div class="rounded-xl border border-gray-200 bg-white dark:border-gray-700 dark:bg-gray-800">
        <div class="border-b border-gray-200 px-5 py-3 dark:border-gray-700">
          <h3 class="text-sm font-semibold text-gray-900 dark:text-white">Item</h3>
        </div>
        <table class="w-full text-left text-sm">
          <thead>
            <tr class="border-b border-gray-200 dark:border-gray-700">
              <th class="px-5 py-3 font-medium text-gray-500 dark:text-gray-400">Produk</th>
              <th class="px-5 py-3 text-right font-medium text-gray-500 dark:text-gray-400">Harga</th>
              <th class="px-5 py-3 text-right font-medium text-gray-500 dark:text-gray-400">Qty</th>
              <th class="px-5 py-3 text-right font-medium text-gray-500 dark:text-gray-400">Subtotal</th>
            </tr>
          </thead>
          <tbody>
            <tr
              v-for="item in transaction.items"
              :key="item.id"
              class="border-b border-gray-100 last:border-0 dark:border-gray-700/50"
            >
              <td class="px-5 py-3 text-gray-900 dark:text-white">
                {{ item.product_name }}
                <span v-if="item.variant_name" class="text-gray-500"> ({{ item.variant_name }})</span>
              </td>
              <td class="px-5 py-3 text-right text-gray-600 dark:text-gray-300">
                {{ formatCurrency(item.unit_price) }}
              </td>
              <td class="px-5 py-3 text-right text-gray-600 dark:text-gray-300">
                {{ item.quantity }}
              </td>
              <td class="px-5 py-3 text-right font-medium text-gray-900 dark:text-white">
                {{ formatCurrency(item.subtotal) }}
              </td>
            </tr>
          </tbody>
        </table>
      </div>

      <!-- Totals -->
      <div class="rounded-xl border border-gray-200 bg-white p-5 dark:border-gray-700 dark:bg-gray-800">
        <div class="space-y-2 text-sm">
          <div class="flex justify-between">
            <span class="text-gray-500 dark:text-gray-400">Subtotal</span>
            <span class="text-gray-900 dark:text-white">{{ formatCurrency(transaction.subtotal) }}</span>
          </div>
          <div v-if="transaction.discount_amount > 0" class="flex justify-between">
            <span class="text-gray-500 dark:text-gray-400">Diskon</span>
            <span class="text-green-600 dark:text-green-400">-{{ formatCurrency(transaction.discount_amount) }}</span>
          </div>
          <div v-if="transaction.tax_amount > 0" class="flex justify-between">
            <span class="text-gray-500 dark:text-gray-400">
              Pajak ({{ transaction.tax_rate }}%{{ transaction.tax_inclusive ? ', inklusif' : '' }})
            </span>
            <span class="text-gray-900 dark:text-white">
              {{ transaction.tax_inclusive ? '(termasuk) ' : '+' }}{{ formatCurrency(transaction.tax_amount) }}
            </span>
          </div>
          <div class="flex justify-between border-t border-gray-200 pt-2 dark:border-gray-700">
            <span class="font-semibold text-gray-900 dark:text-white">Total</span>
            <span class="font-semibold text-gray-900 dark:text-white">{{ formatCurrency(transaction.total) }}</span>
          </div>
          <div v-if="transaction.refunded_amount > 0" class="flex justify-between">
            <span class="text-gray-500 dark:text-gray-400">Refunded</span>
            <span class="text-red-500">-{{ formatCurrency(transaction.refunded_amount) }}</span>
          </div>
          <div v-if="transaction.amount_tendered" class="flex justify-between">
            <span class="text-gray-500 dark:text-gray-400">Dibayar</span>
            <span class="text-gray-900 dark:text-white">{{ formatCurrency(transaction.amount_tendered) }}</span>
          </div>
          <div v-if="transaction.change_amount" class="flex justify-between">
            <span class="text-gray-500 dark:text-gray-400">Kembalian</span>
            <span class="text-gray-900 dark:text-white">{{ formatCurrency(transaction.change_amount) }}</span>
          </div>
        </div>
      </div>
    </div>

    <!-- Void Modal -->
    <VoidModal
      v-model="showVoidModal"
      :transaction="transaction"
      @voided="onVoided"
    />

    <!-- Refund Modal -->
    <RefundModal
      v-model="showRefundModal"
      :transaction="transaction"
      @refunded="onRefunded"
    />
  </div>
</template>
