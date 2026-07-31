<script setup lang="ts">
import { ref, computed, watch } from 'vue'
import { useToast } from '@purdia/toast'
import BaseButton from '@purdia/ui/src/components/BaseButton.vue'
import BaseInput from '@purdia/ui/src/components/BaseInput.vue'
import BaseSelect from '@purdia/ui/src/components/BaseSelect.vue'
import BaseModal from '@purdia/ui/src/components/BaseModal.vue'
import type { Voucher } from '@/types/pos'
import * as posApi from '@/api/pos'

const props = defineProps<{
  modelValue: boolean
  outletId: number
}>()

const emit = defineEmits<{
  'update:modelValue': [value: boolean]
  saved: []
}>()

const toast = useToast()
const submitting = ref(false)

type Mode = 'single' | 'batch'
const mode = ref<Mode>('single')

const form = ref({
  code: '',
  discount_type: 'percentage' as Voucher['discount_type'],
  discount_value: '',
  min_purchase: '',
  usage_limit: '',
  expires_at: '',
})

const batchForm = ref({
  prefix: '',
  count: '',
  discount_type: 'percentage' as Voucher['discount_type'],
  discount_value: '',
  min_purchase: '',
  usage_limit: '',
  expires_at: '',
})

const typeOptions = [
  { label: 'Persentase', value: 'percentage' },
  { label: 'Nominal Tetap', value: 'fixed' },
]

const isOpen = computed({
  get: () => props.modelValue,
  set: (v) => emit('update:modelValue', v),
})

watch(
  () => props.modelValue,
  (open) => {
    if (open) {
      mode.value = 'single'
      form.value = {
        code: '',
        discount_type: 'percentage',
        discount_value: '',
        min_purchase: '',
        usage_limit: '',
        expires_at: '',
      }
      batchForm.value = {
        prefix: '',
        count: '',
        discount_type: 'percentage',
        discount_value: '',
        min_purchase: '',
        usage_limit: '',
        expires_at: '',
      }
    }
  },
)

async function saveSingle() {
  if (!form.value.code.trim()) return

  submitting.value = true
  try {
    const payload: Partial<Voucher> = {
      code: form.value.code.toUpperCase(),
      discount_type: form.value.discount_type,
      discount_value: parseFloat(form.value.discount_value) || 0,
      min_purchase: form.value.min_purchase ? parseFloat(form.value.min_purchase) : null,
      usage_limit: form.value.usage_limit ? parseInt(form.value.usage_limit) : null,
      expires_at: form.value.expires_at || null,
    }

    await posApi.createVoucher(props.outletId, payload)
    toast.success('Voucher berhasil dibuat.')
    isOpen.value = false
    emit('saved')
  } catch {
    // Error handled globally
  } finally {
    submitting.value = false
  }
}

async function saveBatch() {
  if (!batchForm.value.prefix.trim() || !batchForm.value.count) return

  submitting.value = true
  try {
    const payload = {
      prefix: batchForm.value.prefix.toUpperCase(),
      count: parseInt(batchForm.value.count) || 1,
      discount_type: batchForm.value.discount_type,
      discount_value: parseFloat(batchForm.value.discount_value) || 0,
      min_purchase: batchForm.value.min_purchase ? parseFloat(batchForm.value.min_purchase) : undefined,
      usage_limit: batchForm.value.usage_limit ? parseInt(batchForm.value.usage_limit) : undefined,
      expires_at: batchForm.value.expires_at || undefined,
    }

    await posApi.createVoucherBatch(props.outletId, payload)
    toast.success(`${payload.count} voucher berhasil dibuat.`)
    isOpen.value = false
    emit('saved')
  } catch {
    // Error handled globally
  } finally {
    submitting.value = false
  }
}

function save() {
  if (mode.value === 'single') {
    saveSingle()
  } else {
    saveBatch()
  }
}
</script>

<template>
  <BaseModal v-model="isOpen" size="md" persistent>
    <template #default>
      <h2 class="text-lg font-semibold text-gray-900 dark:text-white">Voucher Baru</h2>

      <!-- Mode toggle -->
      <div class="mt-4 flex gap-1 rounded-lg bg-gray-100 p-1 dark:bg-gray-700">
        <button
          class="flex-1 rounded-md px-3 py-1.5 text-sm font-medium transition-colors"
          :class="mode === 'single' ? 'bg-white text-gray-900 shadow-sm dark:bg-gray-600 dark:text-white' : 'text-gray-500 dark:text-gray-400'"
          @click="mode = 'single'"
        >
          Satuan
        </button>
        <button
          class="flex-1 rounded-md px-3 py-1.5 text-sm font-medium transition-colors"
          :class="mode === 'batch' ? 'bg-white text-gray-900 shadow-sm dark:bg-gray-600 dark:text-white' : 'text-gray-500 dark:text-gray-400'"
          @click="mode = 'batch'"
        >
          Batch
        </button>
      </div>

      <!-- Single voucher form -->
      <form v-if="mode === 'single'" class="mt-4 space-y-4" @submit.prevent="save">
        <BaseInput
          v-model="form.code"
          label="Kode Voucher"
          placeholder="contoh: DISKON10"
          :maxlength="50"
          required
        />

        <div class="grid grid-cols-1 gap-4 sm:grid-cols-2">
          <BaseSelect
            v-model="form.discount_type"
            label="Tipe Diskon"
            :options="typeOptions"
            :clearable="false"
            required
          />
          <BaseInput
            v-model="form.discount_value"
            :label="form.discount_type === 'percentage' ? 'Nilai (%)' : 'Nilai (Rp)'"
            type="number"
            :placeholder="form.discount_type === 'percentage' ? '10' : '5000'"
            required
          />
        </div>

        <div class="grid grid-cols-1 gap-4 sm:grid-cols-2">
          <BaseInput
            v-model="form.min_purchase"
            label="Min. Pembelian (Rp)"
            type="number"
            placeholder="Opsional"
          />
          <BaseInput
            v-model="form.usage_limit"
            label="Batas Penggunaan"
            type="number"
            placeholder="Opsional (tak terbatas)"
          />
        </div>

        <BaseInput
          v-model="form.expires_at"
          label="Kedaluwarsa"
          type="date"
        />

        <div class="flex justify-end gap-2 pt-2">
          <BaseButton variant="secondary" size="sm" type="button" @click="isOpen = false">Batal</BaseButton>
          <BaseButton variant="primary" size="sm" type="submit" :disabled="submitting">
            {{ submitting ? 'Menyimpan...' : 'Simpan' }}
          </BaseButton>
        </div>
      </form>

      <!-- Batch voucher form -->
      <form v-else class="mt-4 space-y-4" @submit.prevent="save">
        <div class="grid grid-cols-1 gap-4 sm:grid-cols-2">
          <BaseInput
            v-model="batchForm.prefix"
            label="Prefix Kode"
            placeholder="contoh: PROMO"
            :maxlength="20"
            required
          />
          <BaseInput
            v-model="batchForm.count"
            label="Jumlah Voucher"
            type="number"
            placeholder="10"
            required
          />
        </div>

        <div class="grid grid-cols-1 gap-4 sm:grid-cols-2">
          <BaseSelect
            v-model="batchForm.discount_type"
            label="Tipe Diskon"
            :options="typeOptions"
            :clearable="false"
            required
          />
          <BaseInput
            v-model="batchForm.discount_value"
            :label="batchForm.discount_type === 'percentage' ? 'Nilai (%)' : 'Nilai (Rp)'"
            type="number"
            :placeholder="batchForm.discount_type === 'percentage' ? '10' : '5000'"
            required
          />
        </div>

        <div class="grid grid-cols-1 gap-4 sm:grid-cols-2">
          <BaseInput
            v-model="batchForm.min_purchase"
            label="Min. Pembelian (Rp)"
            type="number"
            placeholder="Opsional"
          />
          <BaseInput
            v-model="batchForm.usage_limit"
            label="Batas Penggunaan (per voucher)"
            type="number"
            placeholder="Opsional (tak terbatas)"
          />
        </div>

        <BaseInput
          v-model="batchForm.expires_at"
          label="Kedaluwarsa"
          type="date"
        />

        <p class="text-xs text-gray-500 dark:text-gray-400">
          Kode akan digenerate otomatis dengan format: <strong>{{ batchForm.prefix || 'PREFIX' }}-XXXXX</strong>
        </p>

        <div class="flex justify-end gap-2 pt-2">
          <BaseButton variant="secondary" size="sm" type="button" @click="isOpen = false">Batal</BaseButton>
          <BaseButton variant="primary" size="sm" type="submit" :disabled="submitting">
            {{ submitting ? 'Menyimpan...' : `Buat ${batchForm.count || '0'} Voucher` }}
          </BaseButton>
        </div>
      </form>
    </template>
  </BaseModal>
</template>
