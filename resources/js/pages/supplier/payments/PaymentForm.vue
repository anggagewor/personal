<script setup lang="ts">
import { ref, computed, watch } from 'vue'
import { useToast } from '@purdia/toast'
import { formatCurrency } from '@purdia/utils'
import BaseModal from '@purdia/ui/src/components/BaseModal.vue'
import BaseButton from '@purdia/ui/src/components/BaseButton.vue'
import BaseInput from '@purdia/ui/src/components/BaseInput.vue'
import BaseSelect from '@purdia/ui/src/components/BaseSelect.vue'
import type { PurchaseOrder, SupplierPaymentPayload } from '@/types/supplier'
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
  amount: 0,
  payment_date: new Date().toISOString().slice(0, 10),
  payment_method: 'cash' as 'cash' | 'bank_transfer' | 'e_wallet',
  notes: '',
})

const methodOptions = [
  { label: 'Tunai', value: 'cash' },
  { label: 'Transfer Bank', value: 'bank_transfer' },
  { label: 'E-Wallet', value: 'e_wallet' },
]

const isOpen = computed({
  get: () => props.modelValue,
  set: (v) => emit('update:modelValue', v),
})

const outstandingBalance = computed(() => props.purchaseOrder.outstanding_balance)

const amountError = computed(() => {
  if (errors.value.amount?.[0]) return errors.value.amount[0]
  if (form.value.amount <= 0) return 'Jumlah harus lebih dari 0'
  if (form.value.amount > outstandingBalance.value) return 'Jumlah melebihi sisa tagihan'
  return undefined
})

const isFormValid = computed(() => {
  return (
    form.value.amount > 0 &&
    form.value.amount <= outstandingBalance.value &&
    form.value.payment_date.trim() !== ''
  )
})

watch(
  () => props.modelValue,
  (open) => {
    if (open) {
      errors.value = {}
      form.value = {
        amount: 0,
        payment_date: new Date().toISOString().slice(0, 10),
        payment_method: 'cash',
        notes: '',
      }
    }
  },
)

async function save() {
  if (!isFormValid.value) return
  errors.value = {}

  const payload: SupplierPaymentPayload = {
    amount: form.value.amount,
    payment_date: form.value.payment_date,
    payment_method: form.value.payment_method,
    notes: form.value.notes.trim() || undefined,
  }

  submitting.value = true
  try {
    await supplierApi.createPayment(props.purchaseOrder.id, payload)
    toast.success('Pembayaran berhasil dicatat.')
    emit('saved')
    isOpen.value = false
  } catch (err: any) {
    if (err?.response?.status === 422 && err.response.data?.errors) {
      errors.value = err.response.data.errors
    } else {
      toast.error('Gagal mencatat pembayaran.')
    }
  } finally {
    submitting.value = false
  }
}

function getError(field: string): string | undefined {
  return errors.value[field]?.[0]
}
</script>

<template>
  <BaseModal v-model="isOpen" size="md" persistent>
    <template #default>
      <h2 class="text-lg font-semibold text-gray-900 dark:text-white">
        Catat Pembayaran
      </h2>

      <!-- PO info -->
      <div class="mt-3 rounded-lg bg-gray-50 p-3 dark:bg-gray-700/50">
        <div class="flex items-center justify-between">
          <span class="text-sm text-gray-600 dark:text-gray-400">No. PO</span>
          <span class="text-sm font-medium text-gray-900 dark:text-white">
            {{ purchaseOrder.po_number }}
          </span>
        </div>
        <div class="mt-1 flex items-center justify-between">
          <span class="text-sm text-gray-600 dark:text-gray-400">Sisa Tagihan</span>
          <span class="text-sm font-semibold text-red-600 dark:text-red-400">
            {{ formatCurrency(outstandingBalance) }}
          </span>
        </div>
      </div>

      <form class="mt-4 space-y-4" @submit.prevent="save">
        <!-- Amount -->
        <BaseInput
          v-model="form.amount"
          type="number"
          label="Jumlah Bayar"
          placeholder="0"
          required
          :min="1"
          :max="outstandingBalance"
          :error="amountError"
          @update:model-value="(v: any) => (form.amount = Number(v))"
        />

        <!-- Payment date -->
        <BaseInput
          v-model="form.payment_date"
          type="date"
          label="Tanggal Bayar"
          required
          :error="getError('payment_date')"
        />

        <!-- Payment method -->
        <BaseSelect
          v-model="form.payment_method"
          :options="methodOptions"
          label="Metode Pembayaran"
          placeholder="Pilih metode"
          :searchable="false"
          :clearable="false"
          :error="getError('payment_method')"
        />

        <!-- Notes -->
        <BaseInput
          v-model="form.notes"
          label="Catatan"
          placeholder="Catatan tambahan (opsional)"
          :error="getError('notes')"
        />

        <div class="flex justify-end gap-2 pt-2">
          <BaseButton variant="secondary" size="sm" type="button" @click="isOpen = false">
            Batal
          </BaseButton>
          <BaseButton
            variant="primary"
            size="sm"
            type="submit"
            :disabled="submitting || !isFormValid"
          >
            {{ submitting ? 'Menyimpan...' : 'Simpan Pembayaran' }}
          </BaseButton>
        </div>
      </form>
    </template>
  </BaseModal>
</template>
