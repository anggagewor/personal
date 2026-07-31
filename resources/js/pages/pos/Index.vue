<script setup lang="ts">
import { ref, onMounted } from 'vue'
import { useRouter } from 'vue-router'
import { useToast } from '@purdia/toast'
import BaseButton from '@purdia/ui/src/components/BaseButton.vue'
import BaseInput from '@purdia/ui/src/components/BaseInput.vue'
import BaseSelect from '@purdia/ui/src/components/BaseSelect.vue'
import BaseModal from '@purdia/ui/src/components/BaseModal.vue'
import { Plus, Store, Settings, ShoppingCart, Trash2 } from '@lucide/vue'
import type { Outlet } from '@/types/pos'
import * as posApi from '@/api/pos'
import { usePosOutletStore } from '@/stores/pos-outlet'

const router = useRouter()
const toast = useToast()
const outletStore = usePosOutletStore()

const outlets = ref<Outlet[]>([])
const loading = ref(true)
const showCreateModal = ref(false)
const submitting = ref(false)

const form = ref({
  name: '',
  business_type: 'retail' as Outlet['business_type'],
  payment_flow: 'pay_first' as Outlet['payment_flow'],
  address: '',
  phone: '',
})

const businessTypeOptions = [
  { label: 'Retail', value: 'retail' },
  { label: 'Warung', value: 'warung' },
  { label: 'Kafe', value: 'kafe' },
  { label: 'Warkop', value: 'warkop' },
]

const paymentFlowOptions = [
  { label: 'Bayar Dulu', value: 'pay_first' },
  { label: 'Bayar Nanti', value: 'pay_later' },
  { label: 'Keduanya', value: 'both' },
]

async function fetchOutlets() {
  loading.value = true
  try {
    const res = await posApi.fetchOutlets()
    outlets.value = res.data
  } catch {
    // Error handled by @purdia/http onError
  } finally {
    loading.value = false
  }
}

function openCreateModal() {
  form.value = { name: '', business_type: 'retail', payment_flow: 'pay_first', address: '', phone: '' }
  showCreateModal.value = true
}

async function createOutlet() {
  if (!form.value.name) return
  submitting.value = true
  try {
    await posApi.createOutlet({
      name: form.value.name,
      business_type: form.value.business_type,
      payment_flow: form.value.payment_flow,
      address: form.value.address || null,
      phone: form.value.phone || null,
    })
    toast.success('Outlet berhasil dibuat.')
    showCreateModal.value = false
    fetchOutlets()
    outletStore.refresh()
  } catch {
    // Error handled by @purdia/http onError
  } finally {
    submitting.value = false
  }
}

async function deleteOutlet(outlet: Outlet) {
  if (!confirm(`Hapus outlet "${outlet.name}"?`)) return
  try {
    await posApi.deleteOutlet(outlet.id)
    toast.success('Outlet berhasil dihapus.')
    fetchOutlets()
    outletStore.refresh()
  } catch {
    // Error handled by @purdia/http onError
  }
}

function goToCashier(outlet: Outlet) {
  router.push({ name: 'pos.cashier', query: { outlet: outlet.id } })
}

function goToSettings(outlet: Outlet) {
  router.push({ name: 'pos.outlet.settings', query: { outlet: outlet.id } })
}

onMounted(() => {
  fetchOutlets()
})
</script>

<template>
  <div>
    <!-- Header -->
    <div class="flex items-center justify-between">
      <div>
        <h1 class="text-2xl font-semibold text-gray-900 dark:text-white">Point of Sale</h1>
        <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">Kelola outlet dan mulai transaksi kasir.</p>
      </div>
      <BaseButton variant="primary" size="sm" :icon="Plus" @click="openCreateModal">
        Outlet Baru
      </BaseButton>
    </div>

    <!-- Loading -->
    <div v-if="loading" class="mt-6 py-8 text-center text-sm text-gray-400">Memuat data...</div>

    <!-- Empty state -->
    <div v-else-if="!outlets.length" class="mt-12 flex flex-col items-center text-center">
      <div class="flex h-16 w-16 items-center justify-center rounded-2xl bg-gray-100 dark:bg-gray-800">
        <Store :size="28" class="text-gray-400" />
      </div>
      <h3 class="mt-4 text-base font-medium text-gray-900 dark:text-white">Belum ada outlet</h3>
      <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">Buat outlet pertama kamu untuk mulai menggunakan POS.</p>
      <BaseButton variant="primary" size="sm" :icon="Plus" class="mt-4" @click="openCreateModal">
        Buat Outlet
      </BaseButton>
    </div>

    <!-- Outlets grid -->
    <div v-else class="mt-6 grid grid-cols-1 gap-4 sm:grid-cols-2 lg:grid-cols-3">
      <div
        v-for="outlet in outlets"
        :key="outlet.id"
        class="group relative rounded-xl border border-gray-200 bg-white p-5 transition-shadow hover:shadow-md dark:border-gray-700 dark:bg-gray-800"
      >
        <div class="flex items-start justify-between">
          <div class="flex items-center gap-3">
            <div class="flex h-10 w-10 items-center justify-center rounded-lg bg-primary-100 text-primary-600 dark:bg-primary-900/30 dark:text-primary-400">
              <Store :size="20" />
            </div>
            <div>
              <h3 class="text-sm font-semibold text-gray-900 dark:text-white">{{ outlet.name }}</h3>
              <p class="text-xs text-gray-500 dark:text-gray-400 capitalize">{{ outlet.business_type }}</p>
            </div>
          </div>
          <button
            class="rounded p-1 text-gray-400 opacity-0 group-hover:opacity-100 hover:text-red-500 transition-all"
            @click.stop="deleteOutlet(outlet)"
            title="Hapus outlet"
          >
            <Trash2 :size="14" />
          </button>
        </div>

        <div class="mt-4 space-y-1 text-xs text-gray-500 dark:text-gray-400">
          <p v-if="outlet.address">{{ outlet.address }}</p>
          <p v-if="outlet.phone">{{ outlet.phone }}</p>
          <p>
            Alur bayar:
            <span class="font-medium text-gray-700 dark:text-gray-300">
              {{ outlet.payment_flow === 'pay_first' ? 'Bayar Dulu' : outlet.payment_flow === 'pay_later' ? 'Bayar Nanti' : 'Keduanya' }}
            </span>
          </p>
        </div>

        <div class="mt-4 flex gap-2">
          <BaseButton variant="primary" size="xs" :icon="ShoppingCart" @click="goToCashier(outlet)">
            Kasir
          </BaseButton>
          <BaseButton variant="secondary" size="xs" :icon="Settings" @click="goToSettings(outlet)">
            Pengaturan
          </BaseButton>
        </div>
      </div>
    </div>

    <!-- Create Outlet Modal -->
    <BaseModal v-model="showCreateModal" size="md" persistent>
      <template #default>
        <h2 class="text-lg font-semibold text-gray-900 dark:text-white">Buat Outlet Baru</h2>
        <form class="mt-4 space-y-4" @submit.prevent="createOutlet">
          <BaseInput
            v-model="form.name"
            label="Nama Outlet"
            placeholder="contoh: Toko Sejahtera"
            :maxlength="100"
            required
          />
          <BaseSelect
            v-model="form.business_type"
            label="Tipe Bisnis"
            :options="businessTypeOptions"
            :clearable="false"
            required
          />
          <BaseSelect
            v-model="form.payment_flow"
            label="Alur Pembayaran"
            :options="paymentFlowOptions"
            :clearable="false"
            required
          />
          <BaseInput
            v-model="form.address"
            label="Alamat (opsional)"
            placeholder="Jl. Contoh No. 1"
          />
          <BaseInput
            v-model="form.phone"
            label="Telepon (opsional)"
            placeholder="08123456789"
          />
          <div class="flex justify-end gap-2 pt-2">
            <BaseButton variant="secondary" size="sm" type="button" @click="showCreateModal = false">Batal</BaseButton>
            <BaseButton variant="primary" size="sm" type="submit" :loading="submitting">Simpan</BaseButton>
          </div>
        </form>
      </template>
    </BaseModal>
  </div>
</template>
