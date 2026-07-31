<script setup lang="ts">
import { computed } from 'vue'
import { formatCurrency, formatDate } from '@purdia/utils'
import { useAuthStore } from '@purdia/auth'
import BaseModal from '@purdia/ui/src/components/BaseModal.vue'
import BaseButton from '@purdia/ui/src/components/BaseButton.vue'
import { Printer, Check } from '@lucide/vue'
import type { Outlet } from '@/types/pos'

const props = defineProps<{
  receipt: Record<string, unknown> | null
  outlet: Outlet | null
}>()

const emit = defineEmits<{
  close: []
}>()

const show = defineModel<boolean>({ default: false })
const auth = useAuthStore()

const cashierName = computed(() => auth.user?.name ?? 'Kasir')

const outletName = computed(() => props.outlet?.name ?? '—')
const outletAddress = computed(() => props.outlet?.address ?? null)
const outletPhone = computed(() => props.outlet?.phone ?? null)
const receiptFooter = computed(() => props.outlet?.settings?.receipt_footer ?? null)

const r = computed(() => props.receipt as Record<string, unknown> | null)

const items = computed(() => (r.value?.items as Array<Record<string, unknown>>) ?? [])
const subtotal = computed(() => (r.value?.subtotal as number) ?? 0)
const discountAmount = computed(() => (r.value?.discount_amount as number) ?? 0)
const total = computed(() => (r.value?.total as number) ?? 0)
const paymentMethod = computed(() => (r.value?.payment_method as string) ?? null)
const amountTendered = computed(() => (r.value?.amount_tendered as number) ?? 0)
const changeAmount = computed(() => (r.value?.change_amount as number) ?? 0)
const transactionNumber = computed(() => (r.value?.transaction_number as string) ?? '—')
const memberName = computed(() => (r.value?.member_name as string) ?? null)
const voucherCode = computed(() => (r.value?.voucher_code as string) ?? null)
const appliedDiscounts = computed(() => (r.value?.applied_discounts as Array<{ name: string; type: string; value: number; amount: number }>) ?? [])
const status = computed(() => (r.value?.status as string) ?? 'completed')
const createdAt = computed(() => {
  if (!r.value?.created_at) return '—'
  return new Date(r.value.created_at as string).toLocaleString('id-ID', {
    day: 'numeric',
    month: 'short',
    year: 'numeric',
    hour: '2-digit',
    minute: '2-digit',
  })
})

function printReceipt() {
  window.print()
}

function close() {
  show.value = false
  emit('close')
}
</script>

<template>
  <BaseModal v-model="show" size="sm">
    <template #default>
      <div class="text-center">
        <!-- Success indicator -->
        <div class="mx-auto flex h-12 w-12 items-center justify-center rounded-full bg-green-100 dark:bg-green-900/30">
          <Check :size="24" class="text-green-600 dark:text-green-400" />
        </div>
        <h2 class="mt-3 text-lg font-semibold text-gray-900 dark:text-white">Transaksi Berhasil!</h2>
      </div>

      <!-- Receipt content -->
      <div v-if="receipt" class="mt-4 rounded-lg border border-gray-200 bg-gray-50 p-4 font-mono text-xs dark:border-gray-700 dark:bg-gray-900/50">
        <!-- Outlet header -->
        <div class="text-center">
          <p class="text-sm font-bold text-gray-900 dark:text-white">{{ outletName }}</p>
          <p v-if="outletAddress" class="mt-0.5 text-gray-500 leading-tight">{{ outletAddress }}</p>
          <p v-if="outletPhone" class="text-gray-500">{{ outletPhone }}</p>
        </div>

        <div class="my-3 border-t border-dashed border-gray-300 dark:border-gray-600" />

        <!-- Transaction info -->
        <div class="text-center">
          <p class="font-bold text-gray-900 dark:text-white">{{ transactionNumber }}</p>
          <p class="mt-0.5 text-gray-500">{{ createdAt }}</p>
          <p class="text-gray-500">Kasir: {{ cashierName }}</p>
          <p v-if="memberName" class="text-gray-500">Member: {{ memberName }}</p>
        </div>

        <div class="my-3 border-t border-dashed border-gray-300 dark:border-gray-600" />

        <!-- Items -->
        <div class="space-y-1.5">
          <div
            v-for="(item, idx) in items"
            :key="idx"
            class="flex justify-between"
          >
            <div class="flex-1 min-w-0">
              <span class="text-gray-900 dark:text-white">{{ item.product_name }}</span>
              <span v-if="item.variant_name" class="text-gray-400"> ({{ item.variant_name }})</span>
              <div class="text-gray-500">
                {{ item.quantity }} x {{ formatCurrency(item.unit_price as number) }}
              </div>
            </div>
            <span class="shrink-0 text-gray-900 dark:text-white">{{ formatCurrency(item.subtotal as number) }}</span>
          </div>
        </div>

        <div class="my-3 border-t border-dashed border-gray-300 dark:border-gray-600" />

        <!-- Totals -->
        <div class="space-y-1">
          <div class="flex justify-between">
            <span class="text-gray-500">Subtotal</span>
            <span class="text-gray-900 dark:text-white">{{ formatCurrency(subtotal) }}</span>
          </div>

          <!-- Discount breakdown -->
          <template v-if="appliedDiscounts.length">
            <div v-for="(disc, idx) in appliedDiscounts" :key="idx" class="flex justify-between">
              <span class="text-gray-500 truncate mr-2">{{ disc.name }}</span>
              <span class="shrink-0 text-red-500">-{{ formatCurrency(disc.amount) }}</span>
            </div>
          </template>
          <div v-else-if="discountAmount > 0" class="flex justify-between">
            <span class="text-gray-500">Diskon</span>
            <span class="text-red-500">-{{ formatCurrency(discountAmount) }}</span>
          </div>

          <div class="flex justify-between font-bold border-t border-gray-300 pt-1 dark:border-gray-600">
            <span class="text-gray-900 dark:text-white">Total</span>
            <span class="text-gray-900 dark:text-white">{{ formatCurrency(total) }}</span>
          </div>
        </div>

        <!-- Payment info -->
        <div v-if="paymentMethod" class="mt-2 space-y-1 border-t border-dashed border-gray-300 pt-2 dark:border-gray-600">
          <div class="flex justify-between">
            <span class="text-gray-500">Pembayaran</span>
            <span class="text-gray-900 dark:text-white">{{ paymentMethod }}</span>
          </div>
          <div v-if="amountTendered > 0" class="flex justify-between">
            <span class="text-gray-500">Dibayar</span>
            <span class="text-gray-900 dark:text-white">{{ formatCurrency(amountTendered) }}</span>
          </div>
          <div v-if="changeAmount > 0" class="flex justify-between">
            <span class="text-gray-500">Kembalian</span>
            <span class="font-bold text-green-600 dark:text-green-400">{{ formatCurrency(changeAmount) }}</span>
          </div>
        </div>

        <!-- Status for open bills -->
        <div v-if="status === 'pending'" class="mt-3 text-center">
          <span class="rounded-full bg-yellow-100 px-2 py-0.5 text-xs font-medium text-yellow-700 dark:bg-yellow-900/30 dark:text-yellow-300">
            Open Bill — Belum Dibayar
          </span>
        </div>

        <!-- Footer -->
        <div v-if="receiptFooter" class="mt-3 border-t border-dashed border-gray-300 pt-2 text-center text-gray-500 dark:border-gray-600">
          {{ receiptFooter }}
        </div>
      </div>

      <!-- Actions -->
      <div class="mt-4 flex gap-2">
        <BaseButton variant="secondary" size="sm" class="flex-1" :icon="Printer" @click="printReceipt">
          Cetak
        </BaseButton>
        <BaseButton variant="primary" size="sm" class="flex-1" @click="close">
          Selesai
        </BaseButton>
      </div>
    </template>
  </BaseModal>
</template>
