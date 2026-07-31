<script setup lang="ts">
import { ref, computed, watch } from 'vue'
import { useToast } from '@purdia/toast'
import BaseModal from '@purdia/ui/src/components/BaseModal.vue'
import BaseButton from '@purdia/ui/src/components/BaseButton.vue'
import BaseInput from '@purdia/ui/src/components/BaseInput.vue'
import type { Supplier, SupplierPayload } from '@/types/supplier'
import * as supplierApi from '@/api/supplier'

const props = defineProps<{
  modelValue: boolean
  outletId: number
  supplier: Supplier | null
}>()

const emit = defineEmits<{
  'update:modelValue': [value: boolean]
  saved: []
}>()

const toast = useToast()
const submitting = ref(false)
const errors = ref<Record<string, string[]>>({})

const form = ref<SupplierPayload>({
  name: '',
  address: '',
  phone: '',
  email: '',
  bank_name: '',
  bank_account_number: '',
  bank_account_holder: '',
  notes: '',
})

const isOpen = computed({
  get: () => props.modelValue,
  set: (v) => emit('update:modelValue', v),
})

const isEditing = computed(() => !!props.supplier)

watch(
  () => props.modelValue,
  (open) => {
    if (open) {
      errors.value = {}
      if (props.supplier) {
        form.value = {
          name: props.supplier.name,
          address: props.supplier.address ?? '',
          phone: props.supplier.phone ?? '',
          email: props.supplier.email ?? '',
          bank_name: props.supplier.bank_name ?? '',
          bank_account_number: props.supplier.bank_account_number ?? '',
          bank_account_holder: props.supplier.bank_account_holder ?? '',
          notes: props.supplier.notes ?? '',
        }
      } else {
        form.value = {
          name: '',
          address: '',
          phone: '',
          email: '',
          bank_name: '',
          bank_account_number: '',
          bank_account_holder: '',
          notes: '',
        }
      }
    }
  },
)

async function save() {
  if (!form.value.name.trim()) return
  errors.value = {}

  const payload: SupplierPayload = {
    name: form.value.name.trim(),
    address: form.value.address?.trim() || null,
    phone: form.value.phone?.trim() || null,
    email: form.value.email?.trim() || null,
    bank_name: form.value.bank_name?.trim() || null,
    bank_account_number: form.value.bank_account_number?.trim() || null,
    bank_account_holder: form.value.bank_account_holder?.trim() || null,
    notes: form.value.notes?.trim() || null,
  }

  submitting.value = true
  try {
    if (isEditing.value && props.supplier) {
      await supplierApi.updateSupplier(props.supplier.id, payload)
      toast.success('Supplier berhasil diperbarui.')
    } else {
      await supplierApi.createSupplier(props.outletId, payload)
      toast.success('Supplier berhasil ditambahkan.')
    }
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

function getError(field: string): string | undefined {
  return errors.value[field]?.[0]
}
</script>

<template>
  <BaseModal v-model="isOpen" size="md" persistent>
    <template #default>
      <h2 class="text-lg font-semibold text-gray-900 dark:text-white">
        {{ isEditing ? 'Edit Supplier' : 'Supplier Baru' }}
      </h2>

      <form class="mt-4 space-y-4" @submit.prevent="save">
        <BaseInput
          v-model="form.name"
          label="Nama Supplier"
          placeholder="Nama supplier"
          required
          :error="getError('name')"
        />

        <BaseInput
          v-model="form.address"
          label="Alamat"
          placeholder="Alamat lengkap (opsional)"
          :error="getError('address')"
        />

        <div class="grid grid-cols-1 gap-4 sm:grid-cols-2">
          <BaseInput
            v-model="form.phone"
            label="Telepon"
            placeholder="08xxxxxxxxxx"
            :error="getError('phone')"
          />
          <BaseInput
            v-model="form.email"
            label="Email"
            type="email"
            placeholder="email@contoh.com"
            :error="getError('email')"
          />
        </div>

        <!-- Bank details -->
        <div class="border-t border-gray-200 pt-4 dark:border-gray-700">
          <p class="mb-3 text-sm font-medium text-gray-700 dark:text-gray-300">Rekening Bank</p>
          <div class="space-y-4">
            <BaseInput
              v-model="form.bank_name"
              label="Nama Bank"
              placeholder="contoh: BCA, Mandiri"
              :error="getError('bank_name')"
            />
            <div class="grid grid-cols-1 gap-4 sm:grid-cols-2">
              <BaseInput
                v-model="form.bank_account_number"
                label="No. Rekening"
                placeholder="Nomor rekening"
                :error="getError('bank_account_number')"
              />
              <BaseInput
                v-model="form.bank_account_holder"
                label="Atas Nama"
                placeholder="Nama pemilik rekening"
                :error="getError('bank_account_holder')"
              />
            </div>
          </div>
        </div>

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
          <BaseButton variant="primary" size="sm" type="submit" :disabled="submitting">
            {{ submitting ? 'Menyimpan...' : 'Simpan' }}
          </BaseButton>
        </div>
      </form>
    </template>
  </BaseModal>
</template>
