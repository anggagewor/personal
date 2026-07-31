<script setup lang="ts">
import { ref, computed } from 'vue'
import { useRoute, useRouter } from 'vue-router'
import { useToast } from '@purdia/toast'
import { formatCurrency, formatDate } from '@purdia/utils'
import BaseButton from '@purdia/ui/src/components/BaseButton.vue'
import BaseBadge from '@purdia/ui/src/components/BaseBadge.vue'
import { Plus, Ticket, Eye } from '@lucide/vue'
import type { Voucher } from '@/types/pos'
import * as posApi from '@/api/pos'
import VoucherForm from './VoucherForm.vue'

const route = useRoute()
const router = useRouter()
const toast = useToast()

const outletId = computed(() => Number(route.query.outlet))

const vouchers = ref<Voucher[]>([])
const loading = ref(true)

// Form modal
const showForm = ref(false)

async function fetchVouchers() {
  if (!outletId.value) return
  loading.value = true
  try {
    const res = await posApi.fetchVouchers(outletId.value)
    vouchers.value = res.data
  } catch {
    // Error handled globally
  } finally {
    loading.value = false
  }
}

function openCreate() {
  showForm.value = true
}

function viewDetail(voucher: Voucher) {
  router.push({ name: 'pos.voucher.detail', query: { outlet: outletId.value, id: voucher.id } })
}

function usageText(voucher: Voucher) {
  if (voucher.usage_limit) {
    return `${voucher.usage_count}/${voucher.usage_limit}`
  }
  return `${voucher.usage_count}/∞`
}

function remainingCount(voucher: Voucher) {
  if (!voucher.usage_limit) return null
  return voucher.usage_limit - voucher.usage_count
}

function isExpired(voucher: Voucher) {
  if (!voucher.expires_at) return false
  return new Date(voucher.expires_at) < new Date()
}

function statusVariant(voucher: Voucher): 'success' | 'neutral' | 'warning' {
  if (!voucher.is_active) return 'neutral'
  if (isExpired(voucher)) return 'warning'
  return 'success'
}

function statusLabel(voucher: Voucher) {
  if (!voucher.is_active) return 'Nonaktif'
  if (isExpired(voucher)) return 'Kedaluwarsa'
  return 'Aktif'
}

function onSaved() {
  fetchVouchers()
}

// Initial load
fetchVouchers()
</script>

<template>
  <div>
    <!-- Header -->
    <div class="flex items-center justify-between">
      <div>
        <h1 class="text-2xl font-semibold text-gray-900 dark:text-white">Voucher</h1>
        <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">Kelola voucher diskon outlet.</p>
      </div>
      <BaseButton variant="primary" size="sm" :icon="Plus" @click="openCreate">
        Voucher Baru
      </BaseButton>
    </div>

    <!-- Loading -->
    <div v-if="loading" class="mt-6 py-8 text-center text-sm text-gray-400">Memuat data...</div>

    <!-- Empty state -->
    <div v-else-if="!vouchers.length" class="mt-12 flex flex-col items-center text-center">
      <div class="flex h-16 w-16 items-center justify-center rounded-2xl bg-gray-100 dark:bg-gray-800">
        <Ticket :size="28" class="text-gray-400" />
      </div>
      <h3 class="mt-4 text-base font-medium text-gray-900 dark:text-white">Belum ada voucher</h3>
      <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">Buat voucher pertama untuk outlet ini.</p>
      <BaseButton variant="primary" size="sm" :icon="Plus" class="mt-4" @click="openCreate">
        Buat Voucher
      </BaseButton>
    </div>

    <!-- Voucher table -->
    <div v-else class="mt-6 overflow-x-auto rounded-xl border border-gray-200 bg-white dark:border-gray-700 dark:bg-gray-800">
      <table class="w-full text-left text-sm">
        <thead>
          <tr class="border-b border-gray-200 dark:border-gray-700">
            <th class="px-4 py-3 font-medium text-gray-500 dark:text-gray-400">Kode</th>
            <th class="px-4 py-3 font-medium text-gray-500 dark:text-gray-400">Tipe</th>
            <th class="px-4 py-3 font-medium text-gray-500 dark:text-gray-400">Nilai</th>
            <th class="px-4 py-3 font-medium text-gray-500 dark:text-gray-400">Penggunaan</th>
            <th class="px-4 py-3 font-medium text-gray-500 dark:text-gray-400">Sisa</th>
            <th class="px-4 py-3 font-medium text-gray-500 dark:text-gray-400">Kedaluwarsa</th>
            <th class="px-4 py-3 font-medium text-gray-500 dark:text-gray-400">Status</th>
            <th class="px-4 py-3 font-medium text-gray-500 dark:text-gray-400"></th>
          </tr>
        </thead>
        <tbody>
          <tr
            v-for="voucher in vouchers"
            :key="voucher.id"
            class="border-b border-gray-100 last:border-0 hover:bg-gray-50 dark:border-gray-700/50 dark:hover:bg-gray-700/30"
          >
            <td class="px-4 py-3 font-mono text-xs font-semibold text-gray-900 dark:text-white">
              {{ voucher.code }}
            </td>
            <td class="px-4 py-3 text-gray-600 dark:text-gray-300">
              {{ voucher.discount_type === 'percentage' ? 'Persentase' : 'Nominal' }}
            </td>
            <td class="px-4 py-3 font-medium text-gray-900 dark:text-white">
              {{ voucher.discount_type === 'percentage' ? `${voucher.discount_value}%` : formatCurrency(voucher.discount_value) }}
            </td>
            <td class="px-4 py-3 text-gray-600 dark:text-gray-300">
              {{ usageText(voucher) }}
            </td>
            <td class="px-4 py-3 text-gray-600 dark:text-gray-300">
              <span v-if="remainingCount(voucher) !== null">{{ remainingCount(voucher) }}</span>
              <span v-else class="text-gray-400">∞</span>
            </td>
            <td class="px-4 py-3 text-gray-600 dark:text-gray-300">
              <span v-if="voucher.expires_at">{{ formatDate(voucher.expires_at, { day: 'numeric', month: 'short', year: 'numeric' }) }}</span>
              <span v-else class="text-gray-400">—</span>
            </td>
            <td class="px-4 py-3">
              <BaseBadge :variant="statusVariant(voucher)" size="sm">
                {{ statusLabel(voucher) }}
              </BaseBadge>
            </td>
            <td class="px-4 py-3">
              <button
                class="rounded p-1.5 text-gray-400 hover:text-primary-600 transition-colors"
                @click="viewDetail(voucher)"
                title="Detail"
              >
                <Eye :size="16" />
              </button>
            </td>
          </tr>
        </tbody>
      </table>
    </div>

    <!-- Voucher Form Modal -->
    <VoucherForm
      v-model="showForm"
      :outlet-id="outletId"
      @saved="onSaved"
    />
  </div>
</template>
