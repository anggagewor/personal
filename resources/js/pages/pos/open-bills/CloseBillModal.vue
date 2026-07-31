<script setup lang="ts">
import { ref, computed, watch } from 'vue'
import { useToast } from '@purdia/toast'
import { formatCurrency } from '@purdia/utils'
import BaseModal from '@purdia/ui/src/components/BaseModal.vue'
import BaseButton from '@purdia/ui/src/components/BaseButton.vue'
import BaseInput from '@purdia/ui/src/components/BaseInput.vue'
import BaseSelect from '@purdia/ui/src/components/BaseSelect.vue'
import type { OpenBill, PaymentMethod, CloseOpenBillPayload } from '@/types/pos'
import * as posApi from '@/api/pos'

const props = defineProps<{
  modelValue: boolean
  bill: OpenBill | null
  paymentMethods: PaymentMethod[]
}>()

const emit = defineEmits<{
  'update:modelValue': [value: boolean]
  closed: []
}>()

const toast = useToast()
const submitting = ref(false)

const selectedPaymentMethod = ref('')
const selectedPaymentMethodType = ref('')
const amountTendered = ref<number>(0)

const isOpen = computed({
  get: () => props.modelValue,
  set: (v) => emit('update:modelValue', v),
})

const paymentMethodOptions = computed(() =>
  props.paymentMethods.map((pm) => ({
    label: pm.name,
    value: pm.name,
  })),
)

const isCash = computed(() => selectedPaymentMethodType.value === 'cash')

const changeAmount = computed(() => {
  if (!isCash.value || !props.bill) return 0
  const change = amountTendered.value - props.bill.total
  return change > 0 ? change : 0
})

const canSubmit = computed(() => {
  if (!selectedPaymentMethod.value) return false
  if (isCash.value && amountTendered.value < (props.bill?.total ?? 0)) return false
  return true
})

watch(
  () => props.modelValue,
  (open) => {
    if (open) {
      selectedPaymentMethod.value = ''
      selectedPaymentMethodType.value = ''
      amountTendered.value = 0
    }
  },
)

watch(selectedPaymentMethod, (name) => {
  const pm = props.paymentMethods.find((p) => p.name === name)
  selectedPaymentMethodType.value = pm?.type ?? ''
  if (pm && props.bill) {
    amountTendered.value = props.bill.total
  }
})

async function submit() {
  if (!props.bill || !canSubmit.value) return

  const payload: CloseOpenBillPayload = {
    payment_method: selectedPaymentMethod.value,
    payment_method_type: selectedPaymentMethodType.value,
    amount_tendered: isCash.value ? amountTendered.value : undefined,
  }

  submitting.value = true
  try {
    await posApi.closeOpenBill(props.bill.id, payload)
    toast.success('Tagihan berhasil ditutup.')
    isOpen.value = false
    emit('closed')
  } catch {
    // Error handled globally
  } finally {
    submitting.value = false
  }
}
</script>

<template>
  <BaseModal v-model="isOpen" size="sm" persistent>
    <template #default>
      <h2 class="text-lg font-semibold text-gray-900 dark:text-white">Tutup Tagihan</h2>

      <div v-if="bill" class="mt-3 rounded-lg bg-gray-50 p-3 dark:bg-gray-700/50">
        <div class="flex justify-between text-sm">
          <span class="text-gray-500 dark:text-gray-400">No. Transaksi</span>
          <span class="font-mono text-xs font-semibold text-gray-900 dark:text-white">{{ bill.transaction_number }}</span>
        </div>
        <div class="mt-1 flex justify-between text-sm">
          <span class="text-gray-500 dark:text-gray-400">Total</span>
          <span class="font-semibold text-gray-900 dark:text-white">{{ formatCurrency(bill.total) }}</span>
        </div>
      </div>

      <form class="mt-4 space-y-4" @submit.prevent="submit">
        <BaseSelect
          v-model="selectedPaymentMethod"
          label="Metode Pembayaran"
          :options="paymentMethodOptions"
          placeholder="Pilih metode"
          required
        />

        <div v-if="isCash">
          <BaseInput
            v-model.number="amountTendered"
            label="Jumlah Dibayar"
            type="number"
            :min="bill?.total ?? 0"
            required
          />
          <div v-if="changeAmount > 0" class="mt-2 flex justify-between rounded-lg bg-green-50 p-2 text-sm dark:bg-green-900/20">
            <span class="text-green-700 dark:text-green-400">Kembalian</span>
            <span class="font-semibold text-green-700 dark:text-green-400">{{ formatCurrency(changeAmount) }}</span>
          </div>
        </div>

        <div class="flex justify-end gap-2 pt-2">
          <BaseButton variant="secondary" size="sm" type="button" @click="isOpen = false">
            Batal
          </BaseButton>
          <BaseButton variant="primary" size="sm" type="submit" :disabled="submitting || !canSubmit">
            {{ submitting ? 'Memproses...' : 'Tutup Tagihan' }}
          </BaseButton>
        </div>
      </form>
    </template>
  </BaseModal>
</template>
