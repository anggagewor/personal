<script setup lang="ts">
import { ref, onMounted, computed } from 'vue'
import { useRoute, useRouter } from 'vue-router'
import { useToast } from '@purdia/toast'
import BaseButton from '@purdia/ui/src/components/BaseButton.vue'
import BaseInput from '@purdia/ui/src/components/BaseInput.vue'
import BaseSelect from '@purdia/ui/src/components/BaseSelect.vue'
import { ArrowLeft, Save } from '@lucide/vue'
import type { Outlet } from '@/types/pos'
import * as posApi from '@/api/pos'

const route = useRoute()
const router = useRouter()
const toast = useToast()

const outletId = computed(() => Number(route.params.outletId))
const isEdit = computed(() => !!outletId.value)
const loading = ref(false)
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

async function fetchOutlet() {
  if (!isEdit.value) return
  loading.value = true
  try {
    const res = await posApi.fetchOutlets()
    const outlet = res.data.find((o: Outlet) => o.id === outletId.value)
    if (outlet) {
      form.value = {
        name: outlet.name,
        business_type: outlet.business_type,
        payment_flow: outlet.payment_flow,
        address: outlet.address ?? '',
        phone: outlet.phone ?? '',
      }
    }
  } catch {
    // Error handled by @purdia/http onError
  } finally {
    loading.value = false
  }
}

async function submitForm() {
  if (!form.value.name) return
  submitting.value = true
  try {
    const payload = {
      name: form.value.name,
      business_type: form.value.business_type,
      payment_flow: form.value.payment_flow,
      address: form.value.address || null,
      phone: form.value.phone || null,
    }

    if (isEdit.value) {
      await posApi.updateOutlet(outletId.value, payload)
      toast.success('Outlet berhasil diperbarui.')
    } else {
      await posApi.createOutlet(payload)
      toast.success('Outlet berhasil dibuat.')
    }
    router.push({ name: 'pos' })
  } catch {
    // Error handled by @purdia/http onError
  } finally {
    submitting.value = false
  }
}

onMounted(() => {
  fetchOutlet()
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
          {{ isEdit ? 'Edit Outlet' : 'Buat Outlet Baru' }}
        </h1>
        <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">
          {{ isEdit ? 'Perbarui informasi outlet.' : 'Isi data outlet untuk memulai.' }}
        </p>
      </div>
    </div>

    <!-- Loading -->
    <div v-if="loading" class="mt-6 py-8 text-center text-sm text-gray-400">Memuat data...</div>

    <!-- Form -->
    <div v-else class="mt-8 max-w-xl">
      <form class="space-y-5" @submit.prevent="submitForm">
        <section class="rounded-xl border border-gray-200 bg-white p-6 dark:border-gray-700 dark:bg-gray-800">
          <h2 class="text-base font-semibold text-gray-900 dark:text-white">Informasi Outlet</h2>
          <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">Data dasar outlet kamu.</p>

          <div class="mt-5 space-y-4">
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
          </div>
        </section>

        <section class="rounded-xl border border-gray-200 bg-white p-6 dark:border-gray-700 dark:bg-gray-800">
          <h2 class="text-base font-semibold text-gray-900 dark:text-white">Kontak & Lokasi</h2>
          <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">Informasi kontak untuk struk dan keperluan lainnya.</p>

          <div class="mt-5 space-y-4">
            <BaseInput
              v-model="form.address"
              label="Alamat"
              placeholder="Jl. Contoh No. 1, Kota"
            />
            <BaseInput
              v-model="form.phone"
              label="Nomor Telepon"
              placeholder="08123456789"
              :maxlength="20"
            />
          </div>
        </section>

        <div class="flex justify-end gap-2">
          <BaseButton variant="secondary" size="sm" type="button" @click="router.push({ name: 'pos' })">
            Batal
          </BaseButton>
          <BaseButton variant="primary" size="sm" type="submit" :icon="Save" :loading="submitting">
            {{ isEdit ? 'Simpan Perubahan' : 'Buat Outlet' }}
          </BaseButton>
        </div>
      </form>
    </div>
  </div>
</template>
