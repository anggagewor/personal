<script setup lang="ts">
import { computed } from 'vue'
import { formatCurrency } from '@purdia/utils'
import BaseModal from '@purdia/ui/src/components/BaseModal.vue'
import BaseButton from '@purdia/ui/src/components/BaseButton.vue'
import { Printer, Check } from '@lucide/vue'
import type { Transaction } from '@/types/pos'

defineProps<{
  receipt: Record<string, unknown> | null
}>()

const emit = defineEmits<{
  close: []
}>()

const show = defineModel<boolean>({ default: false })

// Cast receipt to typed transaction for easier access
const transaction = computed(() => {
  return show.value ? (show as unknown as { value: Transaction }).value : null
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
        <!-- Header -->
        <div class="text-center">
          <p class="font-bold text-gray-900 dark:text-white">{{ (receipt as Record<string, unknown>).transaction_number || '—' }}</p>
          <p class="mt-1 text-gray-500">{{ new Date((receipt as Record<string, unknown>).created_at as string).toLocaleString('id-ID') }}</p>
        </div>

        <div class="my-3 border-t border-dashed border-gray-300 dark:border-gray-600" />

        <!-- Items -->
        <div v-if="(receipt as Record<string, unknown>).items" class="space-y-1">
          <div
            v-for="(item, idx) in ((receipt as Record<string, unknown>).items as Array<Record<string, unknown>>)"
            :key="idx"
            class="flex justify-between"
          >
            <div class="flex-1">
              <span class="text-gray-900 dark:text-white">{{ item.product_name }}</span>
              <span v-if="item.variant_name" class="text-gray-400"> ({{ item.variant_name }})</span>
              <span class="text-gray-500"> x{{ item.quantity }}</span>
            </div>
            <span class="text-gray-900 dark:text-white">{{ formatCurrency(item.subtotal as number) }}</span>
          </div>
        </div>

        <div class="my-3 border-t border-dashed border-gray-300 dark:border-gray-600" />

        <!-- Totals -->
        <div class="space-y-1">
          <div class="flex justify-between">
            <span class="text-gray-500">Subtotal</span>
            <span class="text-gray-900 dark:text-white">{{ formatCurrency((receipt as Record<string, unknown>).subtotal as number) }}</span>
          </div>
          <div v-if="((receipt as Record<string, unknown>).discount_amount as number) > 0" class="flex justify-between">
            <span class="text-gray-500">Diskon</span>
            <span class="text-red-500">-{{ formatCurrency((receipt as Record<string, unknown>).discount_amount as number) }}</span>
          </div>
          <div class="flex justify-between font-bold">
            <span class="text-gray-900 dark:text-white">Total</span>
            <span class="text-gray-900 dark:text-white">{{ formatCurrency((receipt as Record<string, unknown>).total as number) }}</span>
          </div>
        </div>

        <!-- Payment info -->
        <div v-if="(receipt as Record<string, unknown>).payment_method" class="mt-2 space-y-1">
          <div class="flex justify-between">
            <span class="text-gray-500">Pembayaran</span>
            <span class="text-gray-900 dark:text-white">{{ (receipt as Record<string, unknown>).payment_method }}</span>
          </div>
          <div v-if="(receipt as Record<string, unknown>).amount_tendered" class="flex justify-between">
            <span class="text-gray-500">Dibayar</span>
            <span class="text-gray-900 dark:text-white">{{ formatCurrency((receipt as Record<string, unknown>).amount_tendered as number) }}</span>
          </div>
          <div v-if="((receipt as Record<string, unknown>).change_amount as number) > 0" class="flex justify-between">
            <span class="text-gray-500">Kembalian</span>
            <span class="text-green-600 dark:text-green-400">{{ formatCurrency((receipt as Record<string, unknown>).change_amount as number) }}</span>
          </div>
        </div>

        <!-- Status for open bills -->
        <div v-if="(receipt as Record<string, unknown>).status === 'pending'" class="mt-3 text-center">
          <span class="rounded-full bg-yellow-100 px-2 py-0.5 text-xs font-medium text-yellow-700 dark:bg-yellow-900/30 dark:text-yellow-300">
            Open Bill — Belum Dibayar
          </span>
        </div>

        <!-- Member -->
        <div v-if="(receipt as Record<string, unknown>).member_name" class="mt-2 text-center text-gray-500">
          Member: {{ (receipt as Record<string, unknown>).member_name }}
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
