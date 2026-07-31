<script setup lang="ts">
import { ref, computed, watch } from 'vue'
import { useToast } from '@purdia/toast'
import BaseModal from '@purdia/ui/src/components/BaseModal.vue'
import BaseButton from '@purdia/ui/src/components/BaseButton.vue'
import BaseInput from '@purdia/ui/src/components/BaseInput.vue'
import type { PurchaseOrder, GoodsReceiptPayload } from '@/types/supplier'
import * as supplierApi from '@/api/supplier'

const props = defineProps<{
  modelValue: boolean
  purchaseOrder: PurchaseOrder
}>()

const emit = defineEmits<{
  'update:modelValue': [value: boolean]
  saved: []
}>()

const toast = useToast()
const submitting = ref(false)
const errors = ref<Record<string, string[]>>({})

const form = ref({
  receipt_date: new Date().toISOString().slice(0, 10),
  notes: '',
  items: [] as { purchase_order_item_id: number; product_variant_id: number; quantity: number }[],
})

const isOpen = computed({
  get: () => props.modelValue,
  set: (v) => emit('update:modelValue', v),
})

// Compute remaining qty for each PO item
const itemsWithRemaining = computed(() => {
  return props.purchaseOrder.items.map((item) => ({
    ...item,
    remaining: item.quantity - item.received_quantity,
  }))
})

// Only items with remaining > 0
const receivableItems = computed(() => {
  return itemsWithRemaining.value.filter((item) => item.remaining > 0)
})

// Validation errors per item
const itemErrors = computed(() => {
  return form.value.items.map((formItem, index) => {
    const poItem = receivableItems.value[index]
    if (!poItem) return ''
    if (formItem.quantity < 0) return 'Qty tidak boleh negatif'
    if (formItem.quantity > poItem.remaining) return `Maks. ${poItem.remaining}`
    return ''
  })
})

const isValid = computed(() => {
  // At least one item must have qty > 0
  const hasAnyQty = form.value.items.some((item) => item.quantity > 0)
  if (!hasAnyQty) return false

  // No over-delivery
  const hasErrors = itemErrors.value.some((err) => err !== '')
  if (hasErrors) return false

  // Receipt date is required
  if (!form.value.receipt_date) return false

  return true
})

watch(
  () => props.modelValue,
  (open) => {
    if (open) {
      errors.value = {}
      form.value = {
        receipt_date: new Date().toISOString().slice(0, 10),
        notes: '',
        items: receivableItems.value.map((item) => ({
          purchase_order_item_id: item.id,
          product_variant_id: item.product_variant_id,
          quantity: 0,
        })),
      }
    }
  },
)

async function save() {
  if (!isValid.value) return
  errors.value = {}

  // Filter only items with qty > 0
  const payload: GoodsReceiptPayload = {
    receipt_date: form.value.receipt_date,
    notes: form.value.notes.trim() || null,
    items: form.value.items
      .filter((item) => item.quantity > 0)
      .map((item) => ({
        purchase_order_item_id: item.purchase_order_item_id,
        product_variant_id: item.product_variant_id,
        quantity: Number(item.quantity),
      })),
  }

  submitting.value = true
  try {
    await supplierApi.createGoodsReceipt(props.purchaseOrder.id, payload)
    toast.success('Penerimaan barang berhasil dicatat.')
    isOpen.value = false
    emit('saved')
  } catch (err: any) {
    if (err?.response?.status === 422 && err.response.data?.errors) {
      errors.value = err.response.data.errors
    }
  } finally {
    submitting.value = false
  }
}
</script>

<template>
  <BaseModal v-model="isOpen" size="lg" persistent>
    <template #default>
      <h2 class="text-lg font-semibold text-gray-900 dark:text-white">
        Terima Barang
      </h2>
      <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">
        PO: {{ purchaseOrder.po_number }} — {{ purchaseOrder.supplier_name }}
      </p>

      <form class="mt-4 space-y-4" @submit.prevent="save">
        <!-- Receipt date -->
        <BaseInput
          v-model="form.receipt_date"
          type="date"
          label="Tanggal Penerimaan"
          required
          :error="errors.receipt_date?.[0]"
        />

        <!-- Items table -->
        <div>
          <label class="text-sm font-medium text-gray-700 dark:text-gray-300">Item</label>
          <div class="mt-2 overflow-x-auto rounded-lg border border-gray-200 dark:border-gray-700">
            <table class="w-full text-left text-sm">
              <thead>
                <tr class="border-b border-gray-200 bg-gray-50 dark:border-gray-700 dark:bg-gray-800/50">
                  <th class="px-3 py-2 font-medium text-gray-500 dark:text-gray-400">Produk</th>
                  <th class="px-3 py-2 font-medium text-gray-500 dark:text-gray-400">Varian</th>
                  <th class="px-3 py-2 text-right font-medium text-gray-500 dark:text-gray-400">Dipesan</th>
                  <th class="px-3 py-2 text-right font-medium text-gray-500 dark:text-gray-400">Diterima</th>
                  <th class="px-3 py-2 text-right font-medium text-gray-500 dark:text-gray-400">Sisa</th>
                  <th class="px-3 py-2 text-right font-medium text-gray-500 dark:text-gray-400">Qty Terima</th>
                </tr>
              </thead>
              <tbody>
                <tr
                  v-for="(item, index) in receivableItems"
                  :key="item.id"
                  class="border-b border-gray-100 last:border-0 dark:border-gray-700/50"
                >
                  <td class="px-3 py-2 font-medium text-gray-900 dark:text-white">{{ item.product_name }}</td>
                  <td class="px-3 py-2 text-gray-600 dark:text-gray-300">{{ item.variant_name }}</td>
                  <td class="px-3 py-2 text-right text-gray-900 dark:text-white">{{ item.quantity }}</td>
                  <td class="px-3 py-2 text-right text-gray-600 dark:text-gray-300">{{ item.received_quantity }}</td>
                  <td class="px-3 py-2 text-right font-medium text-gray-900 dark:text-white">{{ item.remaining }}</td>
                  <td class="px-3 py-2 text-right">
                    <div class="flex flex-col items-end">
                      <input
                        v-model.number="form.items[index].quantity"
                        type="number"
                        min="0"
                        :max="item.remaining"
                        class="w-20 rounded-md border border-gray-300 bg-white px-2 py-1 text-right text-sm text-gray-900 focus:border-primary-500 focus:outline-none focus:ring-2 focus:ring-primary-100 dark:border-gray-600 dark:bg-gray-800 dark:text-white dark:focus:ring-primary-900/30"
                      />
                      <span v-if="itemErrors[index]" class="mt-1 text-xs text-red-500">
                        {{ itemErrors[index] }}
                      </span>
                    </div>
                  </td>
                </tr>
              </tbody>
            </table>
          </div>
          <span v-if="errors['items']?.[0]" class="mt-1 text-xs text-red-500">{{ errors['items'][0] }}</span>
        </div>

        <!-- Notes -->
        <BaseInput
          v-model="form.notes"
          label="Catatan"
          placeholder="Catatan penerimaan (opsional)"
          :error="errors.notes?.[0]"
        />

        <!-- Actions -->
        <div class="flex justify-end gap-2 pt-2">
          <BaseButton variant="secondary" size="sm" type="button" @click="isOpen = false">
            Batal
          </BaseButton>
          <BaseButton variant="primary" size="sm" type="submit" :disabled="submitting || !isValid">
            {{ submitting ? 'Menyimpan...' : 'Simpan Penerimaan' }}
          </BaseButton>
        </div>
      </form>
    </template>
  </BaseModal>
</template>
