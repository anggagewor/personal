<script setup lang="ts">
import { ref, computed, watch } from 'vue'
import { useToast } from '@purdia/toast'
import { formatCurrency } from '@purdia/utils'
import { usePosCartStore } from '@/stores/pos-cart'
import BaseModal from '@purdia/ui/src/components/BaseModal.vue'
import BaseButton from '@purdia/ui/src/components/BaseButton.vue'
import BaseInput from '@purdia/ui/src/components/BaseInput.vue'
import BaseSelect from '@purdia/ui/src/components/BaseSelect.vue'
import { Search, UserCheck, Ticket, CreditCard, Banknote } from '@lucide/vue'
import type { Outlet, PaymentMethod, Member, Transaction, CheckoutPayload } from '@/types/pos'
import * as posApi from '@/api/pos'

const props = defineProps<{
  outlet: Outlet | null
  paymentMethods: PaymentMethod[]
  outletId: number
}>()

const emit = defineEmits<{
  completed: [transaction: Record<string, unknown>]
}>()

const show = defineModel<boolean>({ default: false })

const toast = useToast()
const cartStore = usePosCartStore()

// Form state
const memberSearch = ref('')
const memberResults = ref<Member[]>([])
const selectedMember = ref<Member | null>(null)
const searchingMember = ref(false)

const voucherCode = ref('')
const voucherValidating = ref(false)
const voucherValid = ref<boolean | null>(null)
const voucherDiscount = ref(0)

const selectedPaymentMethod = ref<string>('')
const selectedPaymentMethodType = ref<string>('')
const amountTendered = ref<number>(0)
const submitting = ref(false)

// Payment flow
const paymentFlow = computed(() => {
  if (!props.outlet) return 'pay_first'
  return cartStore.paymentFlow
})

const isPayLater = computed(() => paymentFlow.value === 'pay_later')

// Payment method options
const paymentMethodOptions = computed(() =>
  props.paymentMethods.map((pm) => ({
    label: pm.name,
    value: pm.name,
  })),
)

// Change calculation
const changeAmount = computed(() => {
  if (selectedPaymentMethodType.value !== 'cash') return 0
  return Math.max(0, amountTendered.value - totalWithTax.value)
})

// Tax calculation (mirrors server-side logic)
const taxRate = computed(() => props.outlet?.settings?.tax_rate ?? 0)
const taxInclusive = computed(() => props.outlet?.settings?.tax_inclusive ?? false)

const taxAmount = computed(() => {
  if (taxRate.value <= 0) return 0
  const afterDiscount = Math.max(0, cartStore.subtotal - cartStore.totalDeductions)
  if (taxInclusive.value) {
    // Tax is embedded in prices
    return Math.round(afterDiscount - afterDiscount / (1 + taxRate.value / 100))
  }
  // Tax added on top
  return Math.round(afterDiscount * (taxRate.value / 100))
})

const totalWithTax = computed(() => {
  if (taxInclusive.value) {
    return cartStore.total // Tax already in price
  }
  return cartStore.total + taxAmount.value
})

const isCashPayment = computed(() => {
  const pm = props.paymentMethods.find((p) => p.name === selectedPaymentMethod.value)
  return pm?.type === 'cash'
})

const canSubmit = computed(() => {
  if (isPayLater.value) return true
  if (!selectedPaymentMethod.value) return false
  if (isCashPayment.value && amountTendered.value < totalWithTax.value) return false
  return true
})

// Reset on open
watch(show, (val) => {
  if (val) {
    memberSearch.value = ''
    memberResults.value = []
    selectedMember.value = cartStore.member
    voucherCode.value = ''
    voucherValid.value = null
    voucherDiscount.value = 0
    selectedPaymentMethod.value = ''
    selectedPaymentMethodType.value = ''
    amountTendered.value = 0
  }
})

// Watch payment method selection to determine type
watch(selectedPaymentMethod, (name) => {
  const pm = props.paymentMethods.find((p) => p.name === name)
  selectedPaymentMethodType.value = pm?.type || ''
  if (pm?.type === 'cash') {
    amountTendered.value = totalWithTax.value
  }
})

// Member search
async function searchMembers() {
  if (!memberSearch.value.trim() || memberSearch.value.length < 2) {
    memberResults.value = []
    return
  }
  searchingMember.value = true
  try {
    const res = await posApi.searchMembers(props.outletId, { q: memberSearch.value })
    memberResults.value = res.data
  } catch {
    memberResults.value = []
  } finally {
    searchingMember.value = false
  }
}

function selectMember(member: Member) {
  selectedMember.value = member
  cartStore.setMember(member)
  memberResults.value = []
  memberSearch.value = member.name
}

function clearMember() {
  selectedMember.value = null
  cartStore.setMember(null)
  memberSearch.value = ''
}

// Voucher validation
async function validateVoucher() {
  if (!voucherCode.value.trim()) return
  voucherValidating.value = true
  try {
    const res = await posApi.validateVoucher({
      code: voucherCode.value,
      outlet_id: props.outletId,
      subtotal: cartStore.subtotal,
    })
    if (res.data.valid && res.data.voucher) {
      voucherValid.value = true
      voucherDiscount.value = res.data.discount_amount || 0
      cartStore.applyVoucher(res.data.voucher)
      toast.success('Voucher berhasil diterapkan.')
    } else {
      voucherValid.value = false
      cartStore.applyVoucher(null)
      toast.error('Voucher tidak valid.')
    }
  } catch {
    voucherValid.value = false
    cartStore.applyVoucher(null)
  } finally {
    voucherValidating.value = false
  }
}

// Submit checkout
async function submit() {
  submitting.value = true
  try {
    const payload: CheckoutPayload = {
      outlet_id: props.outletId,
      items: cartStore.items,
      member_id: selectedMember.value?.id,
      voucher_code: voucherValid.value ? voucherCode.value : undefined,
      discount_ids: cartStore.applicableDiscounts.map((d) => d.id),
    }

    if (!isPayLater.value) {
      payload.payment_method = selectedPaymentMethod.value
      payload.payment_method_type = selectedPaymentMethodType.value
      if (isCashPayment.value) {
        payload.amount_tendered = amountTendered.value
      }
    }

    const res = await posApi.createTransaction(props.outletId, payload)
    toast.success('Transaksi berhasil!')
    emit('completed', res.data as unknown as Record<string, unknown>)
  } catch {
    // Error handled by @purdia/http
  } finally {
    submitting.value = false
  }
}

// Payment flow toggle (for outlets that support 'both')
function setPaymentFlow(flow: 'pay_first' | 'pay_later') {
  cartStore.setPaymentFlow(flow)
}
</script>

<template>
  <BaseModal v-model="show" size="lg" persistent>
    <template #default>
      <h2 class="text-lg font-semibold text-gray-900 dark:text-white">Checkout</h2>

      <div class="mt-4 space-y-5">
        <!-- Payment flow selection (only for 'both' outlets) -->
        <div v-if="outlet?.payment_flow === 'both'" class="flex gap-2">
          <BaseButton
            :variant="paymentFlow === 'pay_first' ? 'primary' : 'secondary'"
            size="sm"
            @click="setPaymentFlow('pay_first')"
          >
            Bayar Sekarang
          </BaseButton>
          <BaseButton
            :variant="paymentFlow === 'pay_later' ? 'primary' : 'secondary'"
            size="sm"
            @click="setPaymentFlow('pay_later')"
          >
            Bayar Nanti (Open Bill)
          </BaseButton>
        </div>

        <!-- Member linking -->
        <div>
          <label class="mb-1 block text-sm font-medium text-gray-700 dark:text-gray-300">
            <UserCheck :size="14" class="inline mr-1" />
            Member (opsional)
          </label>
          <div v-if="selectedMember" class="flex items-center gap-2 rounded-lg border border-primary-200 bg-primary-50 px-3 py-2 dark:border-primary-800 dark:bg-primary-900/20">
            <span class="text-sm text-primary-700 dark:text-primary-300">{{ selectedMember.name }} — {{ selectedMember.phone }}</span>
            <BaseButton variant="ghost" size="xs" class="ml-auto text-red-500 hover:text-red-600" @click="clearMember">Hapus</BaseButton>
          </div>
          <div v-else class="relative">
            <BaseInput
              v-model="memberSearch"
              placeholder="Cari member (nama/telepon)..."
              :icon="Search"
              size="md"
              @input="searchMembers"
            />
            <!-- Search results dropdown -->
            <div
              v-if="memberResults.length"
              class="absolute z-20 mt-1 w-full rounded-lg border border-gray-200 bg-white py-1 shadow-lg dark:border-gray-600 dark:bg-gray-800"
            >
              <button
                v-for="m in memberResults"
                :key="m.id"
                class="flex w-full items-center gap-2 px-3 py-2 text-left text-sm hover:bg-gray-100 dark:hover:bg-gray-700"
                @click="selectMember(m)"
              >
                <span class="font-medium text-gray-900 dark:text-white">{{ m.name }}</span>
                <span class="text-gray-400">{{ m.phone }}</span>
              </button>
            </div>
          </div>
        </div>

        <!-- Voucher input -->
        <div>
          <label class="mb-1 block text-sm font-medium text-gray-700 dark:text-gray-300">
            <Ticket :size="14" class="inline mr-1" />
            Kode Voucher (opsional)
          </label>
          <div class="flex gap-2">
            <BaseInput
              v-model="voucherCode"
              placeholder="Masukkan kode voucher"
              size="md"
              class="flex-1"
            />
            <BaseButton
              variant="secondary"
              size="sm"
              :loading="voucherValidating"
              @click="validateVoucher"
            >
              Validasi
            </BaseButton>
          </div>
          <p v-if="voucherValid === true" class="mt-1 text-xs text-green-600 dark:text-green-400">
            Diskon voucher: {{ formatCurrency(voucherDiscount) }}
          </p>
          <p v-if="voucherValid === false" class="mt-1 text-xs text-red-500 dark:text-red-400">
            Voucher tidak valid atau sudah tidak berlaku.
          </p>
        </div>

        <!-- Payment method (only for pay-first) -->
        <div v-if="!isPayLater">
          <label class="mb-1 block text-sm font-medium text-gray-700 dark:text-gray-300">
            <CreditCard :size="14" class="inline mr-1" />
            Metode Pembayaran
          </label>
          <BaseSelect
            v-model="selectedPaymentMethod"
            :options="paymentMethodOptions"
            placeholder="Pilih metode pembayaran"
          />
        </div>

        <!-- Amount tendered (cash only) -->
        <div v-if="!isPayLater && isCashPayment">
          <label class="mb-1 block text-sm font-medium text-gray-700 dark:text-gray-300">
            <Banknote :size="14" class="inline mr-1" />
            Jumlah Dibayar
          </label>
          <BaseInput
            v-model.number="amountTendered"
            type="number"
            :min="0"
            placeholder="0"
          />

          <!-- Quick amount buttons -->
          <div class="mt-2 flex flex-wrap gap-2">
            <button
              v-for="amount in [totalWithTax, 50000, 100000, 200000, 500000]"
              :key="amount"
              class="rounded-md border border-gray-200 px-3 py-1 text-xs font-medium text-gray-700 hover:bg-gray-100 dark:border-gray-600 dark:text-gray-300 dark:hover:bg-gray-700"
              @click="amountTendered = amount"
            >
              {{ formatCurrency(amount) }}
            </button>
          </div>

          <!-- Change display -->
          <div v-if="amountTendered >= totalWithTax" class="mt-3 rounded-lg bg-green-50 p-3 dark:bg-green-900/20">
            <p class="text-sm text-green-700 dark:text-green-300">
              Kembalian: <span class="font-bold">{{ formatCurrency(changeAmount) }}</span>
            </p>
          </div>
          <div v-else-if="amountTendered > 0" class="mt-3 rounded-lg bg-red-50 p-3 dark:bg-red-900/20">
            <p class="text-sm text-red-600 dark:text-red-400">
              Kurang: {{ formatCurrency(totalWithTax - amountTendered) }}
            </p>
          </div>
        </div>

        <!-- Order summary -->
        <div class="rounded-lg border border-gray-200 bg-gray-50 p-3 dark:border-gray-700 dark:bg-gray-900/50">
          <div class="flex justify-between text-sm">
            <span class="text-gray-500 dark:text-gray-400">Subtotal ({{ cartStore.itemCount }} item)</span>
            <span class="text-gray-900 dark:text-white">{{ formatCurrency(cartStore.subtotal) }}</span>
          </div>
          <div v-if="cartStore.discountTotal > 0" class="mt-1 flex justify-between text-sm">
            <span class="text-gray-500 dark:text-gray-400">Diskon</span>
            <span class="text-red-500">-{{ formatCurrency(cartStore.discountTotal) }}</span>
          </div>
          <div v-if="cartStore.voucherTotal > 0" class="mt-1 flex justify-between text-sm">
            <span class="text-gray-500 dark:text-gray-400">Voucher</span>
            <span class="text-red-500">-{{ formatCurrency(cartStore.voucherTotal) }}</span>
          </div>
          <div v-if="taxAmount > 0" class="mt-1 flex justify-between text-sm">
            <span class="text-gray-500 dark:text-gray-400">
              Pajak ({{ outlet?.settings?.tax_rate ?? 0 }}%{{ outlet?.settings?.tax_inclusive ? ', inklusif' : '' }})
            </span>
            <span class="text-gray-700 dark:text-gray-300">
              {{ outlet?.settings?.tax_inclusive ? '(termasuk)' : '+' + formatCurrency(taxAmount) }}
            </span>
          </div>
          <div class="mt-2 flex justify-between border-t border-gray-200 pt-2 dark:border-gray-700">
            <span class="font-semibold text-gray-900 dark:text-white">Total</span>
            <span class="text-lg font-bold text-primary-600 dark:text-primary-400">{{ formatCurrency(totalWithTax) }}</span>
          </div>
        </div>
      </div>

      <!-- Actions -->
      <div class="mt-6 flex justify-end gap-2">
        <BaseButton variant="secondary" size="sm" @click="show = false">Batal</BaseButton>
        <BaseButton
          variant="primary"
          size="sm"
          :loading="submitting"
          :disabled="!canSubmit"
          @click="submit"
        >
          {{ isPayLater ? 'Buat Open Bill' : 'Proses Pembayaran' }}
        </BaseButton>
      </div>
    </template>
  </BaseModal>
</template>
