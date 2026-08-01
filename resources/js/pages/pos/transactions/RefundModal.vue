<script setup lang="ts">
import { ref, computed, watch } from 'vue'
import { useToast } from '@purdia/toast'
import { formatCurrency } from '@purdia/utils'
import BaseModal from '@purdia/ui/src/components/BaseModal.vue'
import BaseButton from '@purdia/ui/src/components/BaseButton.vue'
import BaseInput from '@purdia/ui/src/components/BaseInput.vue'
import BaseSelect from '@purdia/ui/src/components/BaseSelect.vue'
import { Minus, Plus, RotateCcw } from '@lucide/vue'
import type { Transaction, TransactionItem, RefundPayload } from '@/types/pos'
import * as posApi from '@/api/pos'

const props = defineProps<{
  transaction: Transaction | null
}>()

const emit = defineEmits<{
  refunded: []
}>()

const show = defineModel<boolean>({ default: false })
const toast = useToast()
const submitting = ref(false)

// Form state
const reason = ref('')
const refundMethod = ref<'cash' | 'original_method' | 'store_credit'>('cash')
const itemQuantities = ref<Record<number, number>>({})

const refundMethodOptions = [
  { label: 'Tunai', value: 'cash' },
  { label: 'Metode Asli', value: 'original_method' },
  { label: 'Store Credit', value: 'store_credit' },
]

// Reset on open
watch(show, (val) => {
  if (val && props.transaction) {
    reason.value = ''
    refundMethod.value = 'cash'
    itemQuantities.value = {}
    for (const item of props.transaction.items) {
      itemQuantities.value[item.id] = 0
    }
  }
})

// Computed refund amount
const refundItems = computed(() => {
  if (!props.transaction) return []
  return props.transaction.items
    .filter((item) => (itemQuantities.value[item.id] || 0) > 0)
    .map((item) => {
      const qty = itemQuantities.value[item.id] || 0
      const itemSubtotal = qty * item.unit_price
      // Pro-rata: apply same ratio as transaction total/subtotal
      const ratio = props.transaction!.subtotal > 0
        ? props.transaction!.total / props.transaction!.subtotal
        : 1
      return {
        ...item,
        refund_qty: qty,
        refund_amount: Math.floor(itemSubtotal * ratio),
      }
    })
})

const totalRefundAmount = computed(() =>
  refundItems.value.reduce((sum, item) => sum + item.refund_amount, 0),
)

const hasSelectedItems = computed(() => refundItems.value.length > 0)

const canSubmit = computed(() =>
  hasSelectedItems.value && reason.value.trim().length > 0,
)

function incrementQty(itemId: number, maxQty: number) {
  const current = itemQuantities.value[itemId] || 0
  if (current < maxQty) {
    itemQuantities.value[itemId] = current + 1
  }
}

function decrementQty(itemId: number) {
  const current = itemQuantities.value[itemId] || 0
  if (current > 0) {
    itemQuantities.value[itemId] = current - 1
  }
}

function setMaxQty(itemId: number, maxQty: number) {
  itemQuantities.value[itemId] = maxQty
}

async function submit() {
  if (!props.transaction || !canSubmit.value) return
  submitting.value = true
  try {
    const payload: RefundPayload = {
      items: refundItems.value.map((item) => ({
        transaction_item_id: item.id,
        quantity: item.refund_qty,
      })),
      reason: reason.value,
      refund_method: refundMethod.value,
    }

    await posApi.refundTransaction(props.transaction.id, payload)
    toast.success('Refund berhasil diproses.')
    show.value = false
    emit('refunded')
  } catch {
    // Error handled by @purdia/http
  } finally {
    submitting.value = false
  }
}
</script>

<template>
  <BaseModal v-model="show" size="lg" persistent>
    <template #default>
      <h2 class="text-lg font-semibold text-gray-900 dark:text-white">
        <RotateCcw :size="18" class="inline mr-2" />
        Refund Transaksi
      </h2>
      <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">
        {{ transaction?.transaction_number }} — Pilih item dan jumlah yang akan di-refund.
      </p>

      <div v-if="transaction" class="mt-4 space-y-4">
        <!-- Item selection -->
        <div class="rounded-lg border border-gray-200 dark:border-gray-700 overflow-hidden">
          <table class="w-full text-left text-sm">
            <thead>
              <tr class="border-b border-gray-200 bg-gray-50 dark:border-gray-700 dark:bg-gray-900/50">
                <th class="px-4 py-2 font-medium text-gray-500 dark:text-gray-400">Produk</th>
                <th class="px-4 py-2 text-right font-medium text-gray-500 dark:text-gray-400">Harga</th>
                <th class="px-4 py-2 text-center font-medium text-gray-500 dark:text-gray-400">Tersedia</th>
                <th class="px-4 py-2 text-center font-medium text-gray-500 dark:text-gray-400">Refund</th>
              </tr>
            </thead>
            <tbody>
              <tr
                v-for="item in transaction.items"
                :key="item.id"
                class="border-b border-gray-100 last:border-0 dark:border-gray-700/50"
              >
                <td class="px-4 py-3 text-gray-900 dark:text-white">
                  {{ item.product_name }}
                  <span v-if="item.variant_name" class="text-xs text-gray-500"> ({{ item.variant_name }})</span>
                </td>
                <td class="px-4 py-3 text-right text-gray-600 dark:text-gray-300">
                  {{ formatCurrency(item.unit_price) }}
                </td>
                <td class="px-4 py-3 text-center text-gray-600 dark:text-gray-300">
                  {{ item.quantity }}
                </td>
                <td class="px-4 py-3">
                  <div class="flex items-center justify-center gap-1">
                    <button
                      class="flex h-6 w-6 items-center justify-center rounded border border-gray-300 text-gray-600 hover:bg-gray-200 dark:border-gray-600 dark:text-gray-300 dark:hover:bg-gray-700"
                      @click="decrementQty(item.id)"
                    >
                      <Minus :size="12" />
                    </button>
                    <span class="w-8 text-center text-sm font-medium text-gray-900 dark:text-white">
                      {{ itemQuantities[item.id] || 0 }}
                    </span>
                    <button
                      class="flex h-6 w-6 items-center justify-center rounded border border-gray-300 text-gray-600 hover:bg-gray-200 dark:border-gray-600 dark:text-gray-300 dark:hover:bg-gray-700"
                      @click="incrementQty(item.id, item.quantity)"
                    >
                      <Plus :size="12" />
                    </button>
                    <button
                      class="ml-1 rounded px-1.5 py-0.5 text-[10px] font-medium text-primary-600 hover:bg-primary-50 dark:text-primary-400 dark:hover:bg-primary-900/20"
                      @click="setMaxQty(item.id, item.quantity)"
                    >
                      Semua
                    </button>
                  </div>
                </td>
              </tr>
            </tbody>
          </table>
        </div>

        <!-- Reason + method -->
        <div class="grid grid-cols-1 gap-4 sm:grid-cols-2">
          <BaseInput
            v-model="reason"
            label="Alasan Refund"
            placeholder="Contoh: Produk rusak"
            required
          />
          <BaseSelect
            v-model="refundMethod"
            label="Metode Pengembalian"
            :options="refundMethodOptions"
          />
        </div>

        <!-- Summary -->
        <div v-if="hasSelectedItems" class="rounded-lg border border-gray-200 bg-gray-50 p-3 dark:border-gray-700 dark:bg-gray-900/50">
          <h4 class="text-xs font-semibold text-gray-700 dark:text-gray-300 uppercase">Ringkasan Refund</h4>
          <div class="mt-2 space-y-1">
            <div v-for="item in refundItems" :key="item.id" class="flex justify-between text-sm">
              <span class="text-gray-600 dark:text-gray-400">
                {{ item.product_name }} x{{ item.refund_qty }}
              </span>
              <span class="text-gray-900 dark:text-white">{{ formatCurrency(item.refund_amount) }}</span>
            </div>
          </div>
          <div class="mt-2 flex justify-between border-t border-gray-200 pt-2 dark:border-gray-700">
            <span class="font-semibold text-gray-900 dark:text-white">Total Refund</span>
            <span class="font-bold text-red-600 dark:text-red-400">{{ formatCurrency(totalRefundAmount) }}</span>
          </div>
        </div>
      </div>

      <!-- Actions -->
      <div class="mt-6 flex justify-end gap-2">
        <BaseButton variant="secondary" size="sm" @click="show = false">Batal</BaseButton>
        <BaseButton
          variant="danger"
          size="sm"
          :icon="RotateCcw"
          :loading="submitting"
          :disabled="!canSubmit"
          @click="submit"
        >
          Proses Refund
        </BaseButton>
      </div>
    </template>
  </BaseModal>
</template>
