<script setup lang="ts">
import { ref, onMounted, computed } from 'vue'
import { useRoute, useRouter } from 'vue-router'
import { useToast } from '@purdia/toast'
import BaseButton from '@purdia/ui/src/components/BaseButton.vue'
import BaseInput from '@purdia/ui/src/components/BaseInput.vue'
import BaseSelect from '@purdia/ui/src/components/BaseSelect.vue'
import BaseModal from '@purdia/ui/src/components/BaseModal.vue'
import BaseCheckbox from '@purdia/ui/src/components/BaseCheckbox.vue'
import { ArrowLeft, Plus, Pencil, Trash2, Receipt, CreditCard, Save } from '@lucide/vue'
import type { Outlet, PaymentMethod } from '@/types/pos'
import * as posApi from '@/api/pos'

const route = useRoute()
const router = useRouter()
const toast = useToast()

const outletId = computed(() => Number(route.params.outletId))
const outlet = ref<Outlet | null>(null)
const paymentMethods = ref<PaymentMethod[]>([])
const loading = ref(true)
const submitting = ref(false)

// --- Payment Method Form ---
const showPaymentForm = ref(false)
const editingPaymentMethod = ref<PaymentMethod | null>(null)
const paymentForm = ref({
  type: 'cash' as PaymentMethod['type'],
  name: '',
  is_active: true,
})

const paymentTypeOptions = [
  { label: 'Cash', value: 'cash' },
  { label: 'Transfer Bank', value: 'bank_transfer' },
  { label: 'E-Wallet', value: 'e_wallet' },
  { label: 'Custom', value: 'custom' },
]

// --- Receipt Template Form ---
const receiptForm = ref({
  receipt_header: '',
  receipt_footer: '',
  receipt_width: '58mm' as '58mm' | '80mm',
})

const receiptWidthOptions = [
  { label: '58mm (Kecil)', value: '58mm' },
  { label: '80mm (Standar)', value: '80mm' },
]

async function fetchData() {
  loading.value = true
  try {
    const [outletsRes, methodsRes] = await Promise.all([
      posApi.fetchOutlets(),
      posApi.fetchPaymentMethods(outletId.value),
    ])
    outlet.value = outletsRes.data.find((o: Outlet) => o.id === outletId.value) ?? null
    paymentMethods.value = methodsRes.data

    // Load receipt template from outlet settings
    if (outlet.value?.settings) {
      receiptForm.value = {
        receipt_header: outlet.value.settings.receipt_header ?? '',
        receipt_footer: outlet.value.settings.receipt_footer ?? '',
        receipt_width: outlet.value.settings.receipt_width ?? '58mm',
      }
    }
  } catch {
    // Error handled by @purdia/http onError
  } finally {
    loading.value = false
  }
}

// --- Payment Methods ---
function openCreatePayment() {
  editingPaymentMethod.value = null
  paymentForm.value = { type: 'cash', name: '', is_active: true }
  showPaymentForm.value = true
}

function openEditPayment(method: PaymentMethod) {
  editingPaymentMethod.value = method
  paymentForm.value = {
    type: method.type,
    name: method.name,
    is_active: method.is_active,
  }
  showPaymentForm.value = true
}

async function savePaymentMethod() {
  if (!paymentForm.value.name) return
  submitting.value = true
  try {
    const payload = {
      type: paymentForm.value.type,
      name: paymentForm.value.name,
      is_active: paymentForm.value.is_active,
    }

    if (editingPaymentMethod.value) {
      await posApi.updatePaymentMethod(editingPaymentMethod.value.id, payload)
      toast.success('Metode pembayaran berhasil diperbarui.')
    } else {
      await posApi.createPaymentMethod(outletId.value, payload)
      toast.success('Metode pembayaran berhasil ditambahkan.')
    }
    showPaymentForm.value = false
    const res = await posApi.fetchPaymentMethods(outletId.value)
    paymentMethods.value = res.data
  } catch {
    // Error handled by @purdia/http onError
  } finally {
    submitting.value = false
  }
}

async function deletePaymentMethod(method: PaymentMethod) {
  if (!confirm(`Hapus metode "${method.name}"?`)) return
  try {
    await posApi.deletePaymentMethod(method.id)
    toast.success('Metode pembayaran berhasil dihapus.')
    const res = await posApi.fetchPaymentMethods(outletId.value)
    paymentMethods.value = res.data
  } catch {
    // Error handled by @purdia/http onError
  }
}

// --- Receipt Template ---
async function saveReceiptTemplate() {
  submitting.value = true
  try {
    await posApi.updateReceiptTemplate(outletId.value, {
      header: receiptForm.value.receipt_header || undefined,
      footer: receiptForm.value.receipt_footer || undefined,
      width: receiptForm.value.receipt_width,
    })
    toast.success('Template struk berhasil disimpan.')
  } catch {
    // Error handled by @purdia/http onError
  } finally {
    submitting.value = false
  }
}

onMounted(() => {
  fetchData()
})
</script>

<template>
  <div>
    <!-- Header -->
    <div class="flex items-center gap-3">
      <button
        class="rounded-lg p-1.5 text-gray-500 hover:bg-gray-100 hover:text-gray-700 dark:text-gray-400 dark:hover:bg-gray-700 dark:hover:text-gray-200"
        @click="router.push({ name: 'pos' })"
      >
        <ArrowLeft :size="20" />
      </button>
      <div>
        <h1 class="text-2xl font-semibold text-gray-900 dark:text-white">
          Pengaturan Outlet
        </h1>
        <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">
          {{ outlet?.name ?? 'Memuat...' }} — Konfigurasi pembayaran dan struk.
        </p>
      </div>
    </div>

    <!-- Loading -->
    <div v-if="loading" class="mt-6 py-8 text-center text-sm text-gray-400">Memuat data...</div>

    <div v-else class="mt-8 max-w-2xl space-y-8">
      <!-- Payment Methods Section -->
      <section class="rounded-xl border border-gray-200 bg-white p-6 dark:border-gray-700 dark:bg-gray-800">
        <div class="flex items-center justify-between">
          <div class="flex items-center gap-2">
            <CreditCard :size="18" class="text-gray-500 dark:text-gray-400" />
            <h2 class="text-base font-semibold text-gray-900 dark:text-white">Metode Pembayaran</h2>
          </div>
          <BaseButton variant="primary" size="xs" :icon="Plus" @click="openCreatePayment">
            Tambah
          </BaseButton>
        </div>
        <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">Kelola metode pembayaran yang tersedia di outlet ini.</p>

        <!-- Payment Methods List -->
        <div v-if="paymentMethods.length" class="mt-5 space-y-2">
          <div
            v-for="method in paymentMethods"
            :key="method.id"
            class="group flex items-center justify-between rounded-lg border border-gray-100 bg-gray-50 px-4 py-3 dark:border-gray-700 dark:bg-gray-700/50"
          >
            <div class="flex items-center gap-3">
              <div
                class="h-2.5 w-2.5 rounded-full"
                :class="method.is_active ? 'bg-emerald-500' : 'bg-gray-300 dark:bg-gray-600'"
              />
              <div>
                <p class="text-sm font-medium text-gray-900 dark:text-white">{{ method.name }}</p>
                <p class="text-xs text-gray-500 dark:text-gray-400 capitalize">{{ method.type.replace('_', ' ') }}</p>
              </div>
            </div>
            <div class="flex items-center gap-1 opacity-0 group-hover:opacity-100 transition-opacity">
              <button
                class="rounded p-1.5 text-gray-400 hover:text-primary-600 hover:bg-primary-50 dark:hover:bg-primary-900/20 transition-colors"
                @click="openEditPayment(method)"
                title="Edit"
              >
                <Pencil :size="14" />
              </button>
              <button
                class="rounded p-1.5 text-gray-400 hover:text-red-600 hover:bg-red-50 dark:hover:bg-red-900/20 transition-colors"
                @click="deletePaymentMethod(method)"
                title="Hapus"
              >
                <Trash2 :size="14" />
              </button>
            </div>
          </div>
        </div>
        <div v-else class="mt-5 py-4 text-center text-sm text-gray-400">
          Belum ada metode pembayaran. Tambahkan minimal satu.
        </div>
      </section>

      <!-- Receipt Template Section -->
      <section class="rounded-xl border border-gray-200 bg-white p-6 dark:border-gray-700 dark:bg-gray-800">
        <div class="flex items-center gap-2">
          <Receipt :size="18" class="text-gray-500 dark:text-gray-400" />
          <h2 class="text-base font-semibold text-gray-900 dark:text-white">Template Struk</h2>
        </div>
        <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">Kustomisasi tampilan struk cetak.</p>

        <form class="mt-5 space-y-4" @submit.prevent="saveReceiptTemplate">
          <BaseInput
            v-model="receiptForm.receipt_header"
            label="Header Struk"
            placeholder="contoh: Terima kasih telah berbelanja!"
          />
          <BaseInput
            v-model="receiptForm.receipt_footer"
            label="Footer Struk"
            placeholder="contoh: Barang yang sudah dibeli tidak dapat dikembalikan."
          />
          <BaseSelect
            v-model="receiptForm.receipt_width"
            label="Lebar Kertas"
            :options="receiptWidthOptions"
            :clearable="false"
          />
          <div class="flex justify-end pt-2">
            <BaseButton variant="primary" size="sm" :icon="Save" type="submit" :loading="submitting">
              Simpan Template
            </BaseButton>
          </div>
        </form>
      </section>

      <!-- Edit Outlet Link -->
      <section class="rounded-xl border border-gray-200 bg-white p-6 dark:border-gray-700 dark:bg-gray-800">
        <h2 class="text-base font-semibold text-gray-900 dark:text-white">Data Outlet</h2>
        <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">Ubah nama, tipe bisnis, atau alur pembayaran outlet.</p>
        <div class="mt-4">
          <BaseButton
            variant="secondary"
            size="sm"
            @click="router.push({ name: 'pos.outlet.setup', params: { outletId: outletId } })"
          >
            Edit Data Outlet
          </BaseButton>
        </div>
      </section>
    </div>

    <!-- Payment Method Modal -->
    <BaseModal v-model="showPaymentForm" size="md" persistent>
      <template #default>
        <h2 class="text-lg font-semibold text-gray-900 dark:text-white">
          {{ editingPaymentMethod ? 'Edit Metode Pembayaran' : 'Tambah Metode Pembayaran' }}
        </h2>
        <form class="mt-4 space-y-4" @submit.prevent="savePaymentMethod">
          <BaseSelect
            v-model="paymentForm.type"
            label="Tipe"
            :options="paymentTypeOptions"
            :clearable="false"
            required
          />
          <BaseInput
            v-model="paymentForm.name"
            label="Nama Tampilan"
            placeholder="contoh: BCA Transfer"
            :maxlength="50"
            required
          />
          <BaseCheckbox v-model="paymentForm.is_active" label="Aktif" />
          <div class="flex justify-end gap-2 pt-2">
            <BaseButton variant="secondary" size="sm" type="button" @click="showPaymentForm = false">Batal</BaseButton>
            <BaseButton variant="primary" size="sm" type="submit" :loading="submitting">Simpan</BaseButton>
          </div>
        </form>
      </template>
    </BaseModal>
  </div>
</template>
