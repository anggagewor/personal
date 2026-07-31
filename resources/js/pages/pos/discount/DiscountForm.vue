<script setup lang="ts">
import { ref, computed, watch } from 'vue'
import { useToast } from '@purdia/toast'
import BaseButton from '@purdia/ui/src/components/BaseButton.vue'
import BaseInput from '@purdia/ui/src/components/BaseInput.vue'
import BaseSelect from '@purdia/ui/src/components/BaseSelect.vue'
import BaseModal from '@purdia/ui/src/components/BaseModal.vue'
import type { Discount } from '@/types/pos'
import * as posApi from '@/api/pos'

const props = defineProps<{
  modelValue: boolean
  outletId: number
  editingDiscount: Discount | null
}>()

const emit = defineEmits<{
  'update:modelValue': [value: boolean]
  saved: []
}>()

const toast = useToast()
const submitting = ref(false)

const form = ref({
  name: '',
  type: 'percentage' as Discount['type'],
  value: '',
  min_purchase: '',
  member_only: false,
  priority: '0',
  start_date: '',
  end_date: '',
  buy_quantity: '',
  get_quantity: '',
  product_id: '',
})

const typeOptions = [
  { label: 'Persentase', value: 'percentage' },
  { label: 'Nominal Tetap', value: 'fixed' },
  { label: 'Beli X Gratis Y', value: 'buy_x_get_y' },
]

const isOpen = computed({
  get: () => props.modelValue,
  set: (v) => emit('update:modelValue', v),
})

const isBuyXGetY = computed(() => form.value.type === 'buy_x_get_y')

watch(
  () => props.modelValue,
  (open) => {
    if (open) {
      if (props.editingDiscount) {
        const d = props.editingDiscount
        form.value = {
          name: d.name,
          type: d.type,
          value: String(d.value),
          min_purchase: d.min_purchase ? String(d.min_purchase) : '',
          member_only: d.member_only,
          priority: String(d.priority),
          start_date: d.start_date ?? '',
          end_date: d.end_date ?? '',
          buy_quantity: d.buy_quantity ? String(d.buy_quantity) : '',
          get_quantity: d.get_quantity ? String(d.get_quantity) : '',
          product_id: d.product_id ? String(d.product_id) : '',
        }
      } else {
        form.value = {
          name: '',
          type: 'percentage',
          value: '',
          min_purchase: '',
          member_only: false,
          priority: '0',
          start_date: '',
          end_date: '',
          buy_quantity: '',
          get_quantity: '',
          product_id: '',
        }
      }
    }
  },
)

async function save() {
  if (!form.value.name.trim()) return

  submitting.value = true
  try {
    const payload: Partial<Discount> = {
      name: form.value.name,
      type: form.value.type,
      value: parseFloat(form.value.value) || 0,
      min_purchase: form.value.min_purchase ? parseFloat(form.value.min_purchase) : null,
      member_only: form.value.member_only,
      priority: parseInt(form.value.priority) || 0,
      start_date: form.value.start_date || null,
      end_date: form.value.end_date || null,
      buy_quantity: isBuyXGetY.value ? (parseInt(form.value.buy_quantity) || null) : null,
      get_quantity: isBuyXGetY.value ? (parseInt(form.value.get_quantity) || null) : null,
      product_id: form.value.product_id ? parseInt(form.value.product_id) : null,
    }

    if (props.editingDiscount) {
      await posApi.updateDiscount(props.editingDiscount.id, payload)
      toast.success('Diskon berhasil diperbarui.')
    } else {
      await posApi.createDiscount(props.outletId, payload)
      toast.success('Diskon berhasil dibuat.')
    }

    isOpen.value = false
    emit('saved')
  } catch {
    // Error handled globally
  } finally {
    submitting.value = false
  }
}
</script>

<template>
  <BaseModal v-model="isOpen" size="md" persistent>
    <template #default>
      <h2 class="text-lg font-semibold text-gray-900 dark:text-white">
        {{ editingDiscount ? 'Edit Diskon' : 'Diskon Baru' }}
      </h2>

      <form class="mt-4 space-y-4" @submit.prevent="save">
        <BaseInput v-model="form.name" label="Nama Diskon" placeholder="contoh: Diskon Weekend" required />

        <div class="grid grid-cols-1 gap-4 sm:grid-cols-2">
          <BaseSelect
            v-model="form.type"
            label="Tipe"
            :options="typeOptions"
            :clearable="false"
            required
          />
          <BaseInput
            v-if="!isBuyXGetY"
            v-model="form.value"
            :label="form.type === 'percentage' ? 'Nilai (%)' : 'Nilai (Rp)'"
            type="number"
            :placeholder="form.type === 'percentage' ? '10' : '5000'"
            required
          />
        </div>

        <!-- Buy X Get Y fields -->
        <div v-if="isBuyXGetY" class="grid grid-cols-1 gap-4 sm:grid-cols-3">
          <BaseInput
            v-model="form.buy_quantity"
            label="Beli (qty)"
            type="number"
            placeholder="3"
            required
          />
          <BaseInput
            v-model="form.get_quantity"
            label="Gratis (qty)"
            type="number"
            placeholder="1"
            required
          />
          <BaseInput
            v-model="form.product_id"
            label="ID Produk (opsional)"
            type="number"
            placeholder="Kosongkan = semua"
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
            v-model="form.priority"
            label="Prioritas"
            type="number"
            placeholder="0"
          />
        </div>

        <!-- Date range -->
        <div class="grid grid-cols-1 gap-4 sm:grid-cols-2">
          <BaseInput
            v-model="form.start_date"
            label="Mulai Berlaku"
            type="date"
          />
          <BaseInput
            v-model="form.end_date"
            label="Berakhir"
            type="date"
          />
        </div>

        <!-- Member only toggle -->
        <label class="flex items-center gap-2 cursor-pointer">
          <input
            v-model="form.member_only"
            type="checkbox"
            class="h-4 w-4 rounded border-gray-300 text-primary-600 focus:ring-primary-500"
          />
          <span class="text-sm text-gray-700 dark:text-gray-300">Khusus Member</span>
        </label>

        <!-- Actions -->
        <div class="flex justify-end gap-2 pt-2">
          <BaseButton variant="secondary" size="sm" type="button" @click="isOpen = false">Batal</BaseButton>
          <BaseButton variant="primary" size="sm" type="submit" :disabled="submitting">
            {{ submitting ? 'Menyimpan...' : 'Simpan' }}
          </BaseButton>
        </div>
      </form>
    </template>
  </BaseModal>
</template>
