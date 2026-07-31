<script setup lang="ts">
import { ref, computed, onMounted } from 'vue'
import { useRoute, useRouter } from 'vue-router'
import { useToast } from '@purdia/toast'
import { formatCurrency, formatDate } from '@purdia/utils'
import BaseButton from '@purdia/ui/src/components/BaseButton.vue'
import BaseBadge from '@purdia/ui/src/components/BaseBadge.vue'
import { ArrowLeft, Ticket, Copy } from '@lucide/vue'
import type { Voucher } from '@/types/pos'
import * as posApi from '@/api/pos'

const route = useRoute()
const router = useRouter()
const toast = useToast()

const voucherId = computed(() => Number(route.query.id))
const outletId = computed(() => Number(route.query.outlet))

const voucher = ref<Voucher | null>(null)
const loading = ref(true)

async function fetchVoucher() {
  if (!voucherId.value) return
  loading.value = true
  try {
    const res = await posApi.fetchVoucher(voucherId.value)
    voucher.value = res.data
  } catch {
    // Error handled globally
  } finally {
    loading.value = false
  }
}

function goBack() {
  router.push({ name: 'pos.voucher', query: { outlet: outletId.value } })
}

function copyCode() {
  if (!voucher.value) return
  navigator.clipboard.writeText(voucher.value.code)
  toast.success('Kode disalin.')
}

function isExpired(v: Voucher) {
  if (!v.expires_at) return false
  return new Date(v.expires_at) < new Date()
}

function statusVariant(v: Voucher): 'success' | 'neutral' | 'warning' {
  if (!v.is_active) return 'neutral'
  if (isExpired(v)) return 'warning'
  return 'success'
}

function statusLabel(v: Voucher) {
  if (!v.is_active) return 'Nonaktif'
  if (isExpired(v)) return 'Kedaluwarsa'
  return 'Aktif'
}

onMounted(() => {
  fetchVoucher()
})
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
      <div>
        <h1 class="text-2xl font-semibold text-gray-900 dark:text-white">Detail Voucher</h1>
        <p class="mt-0.5 text-sm text-gray-500 dark:text-gray-400">Informasi lengkap dan riwayat penggunaan.</p>
      </div>
    </div>

    <!-- Loading -->
    <div v-if="loading" class="mt-6 py-8 text-center text-sm text-gray-400">Memuat data...</div>

    <!-- Content -->
    <div v-else-if="voucher" class="mt-6 space-y-6">
      <!-- Voucher info card -->
      <div class="rounded-xl border border-gray-200 bg-white p-6 dark:border-gray-700 dark:bg-gray-800">
        <div class="flex items-start justify-between">
          <div class="flex items-center gap-4">
            <div class="flex h-12 w-12 items-center justify-center rounded-xl bg-primary-100 text-primary-600 dark:bg-primary-900/30 dark:text-primary-400">
              <Ticket :size="24" />
            </div>
            <div>
              <div class="flex items-center gap-2">
                <h2 class="font-mono text-lg font-bold text-gray-900 dark:text-white">{{ voucher.code }}</h2>
                <button
                  class="rounded p-1 text-gray-400 hover:text-primary-600 transition-colors"
                  @click="copyCode"
                  title="Salin kode"
                >
                  <Copy :size="14" />
                </button>
              </div>
              <BaseBadge :variant="statusVariant(voucher)" size="sm" class="mt-1">
                {{ statusLabel(voucher) }}
              </BaseBadge>
            </div>
          </div>
        </div>

        <!-- Details grid -->
        <div class="mt-6 grid grid-cols-2 gap-4 sm:grid-cols-4">
          <div>
            <p class="text-xs text-gray-500 dark:text-gray-400">Tipe Diskon</p>
            <p class="mt-0.5 text-sm font-medium text-gray-900 dark:text-white">
              {{ voucher.discount_type === 'percentage' ? 'Persentase' : 'Nominal' }}
            </p>
          </div>
          <div>
            <p class="text-xs text-gray-500 dark:text-gray-400">Nilai</p>
            <p class="mt-0.5 text-sm font-medium text-gray-900 dark:text-white">
              {{ voucher.discount_type === 'percentage' ? `${voucher.discount_value}%` : formatCurrency(voucher.discount_value) }}
            </p>
          </div>
          <div>
            <p class="text-xs text-gray-500 dark:text-gray-400">Min. Pembelian</p>
            <p class="mt-0.5 text-sm font-medium text-gray-900 dark:text-white">
              {{ voucher.min_purchase ? formatCurrency(voucher.min_purchase) : '—' }}
            </p>
          </div>
          <div>
            <p class="text-xs text-gray-500 dark:text-gray-400">Kedaluwarsa</p>
            <p class="mt-0.5 text-sm font-medium text-gray-900 dark:text-white">
              {{ voucher.expires_at ? formatDate(voucher.expires_at, { day: 'numeric', month: 'short', year: 'numeric' }) : '—' }}
            </p>
          </div>
        </div>
      </div>

      <!-- Usage stats -->
      <div class="rounded-xl border border-gray-200 bg-white p-6 dark:border-gray-700 dark:bg-gray-800">
        <h3 class="text-sm font-semibold text-gray-900 dark:text-white">Statistik Penggunaan</h3>
        <div class="mt-4 grid grid-cols-2 gap-4 sm:grid-cols-3">
          <div class="rounded-lg bg-gray-50 p-4 dark:bg-gray-700/50">
            <p class="text-xs text-gray-500 dark:text-gray-400">Digunakan</p>
            <p class="mt-1 text-2xl font-bold text-gray-900 dark:text-white">{{ voucher.usage_count }}</p>
          </div>
          <div class="rounded-lg bg-gray-50 p-4 dark:bg-gray-700/50">
            <p class="text-xs text-gray-500 dark:text-gray-400">Batas</p>
            <p class="mt-1 text-2xl font-bold text-gray-900 dark:text-white">
              {{ voucher.usage_limit ?? '∞' }}
            </p>
          </div>
          <div class="rounded-lg bg-gray-50 p-4 dark:bg-gray-700/50">
            <p class="text-xs text-gray-500 dark:text-gray-400">Sisa</p>
            <p class="mt-1 text-2xl font-bold text-gray-900 dark:text-white">
              {{ voucher.usage_limit ? voucher.usage_limit - voucher.usage_count : '∞' }}
            </p>
          </div>
        </div>
      </div>

      <!-- Redemption history placeholder -->
      <div class="rounded-xl border border-gray-200 bg-white p-6 dark:border-gray-700 dark:bg-gray-800">
        <h3 class="text-sm font-semibold text-gray-900 dark:text-white">Riwayat Penukaran</h3>
        <div class="mt-4 flex flex-col items-center py-8 text-center">
          <p class="text-sm text-gray-500 dark:text-gray-400">
            Riwayat penukaran akan tampil di sini berdasarkan transaksi yang menggunakan voucher ini.
          </p>
        </div>
      </div>
    </div>

    <!-- Not found -->
    <div v-else class="mt-12 flex flex-col items-center text-center">
      <p class="text-sm text-gray-500 dark:text-gray-400">Voucher tidak ditemukan.</p>
      <BaseButton variant="secondary" size="sm" class="mt-4" @click="goBack">Kembali</BaseButton>
    </div>
  </div>
</template>
