<script setup lang="ts">
import { ref, computed, watch } from 'vue'
import { useToast } from '@purdia/toast'
import BaseButton from '@purdia/ui/src/components/BaseButton.vue'
import BaseInput from '@purdia/ui/src/components/BaseInput.vue'
import BaseSelect from '@purdia/ui/src/components/BaseSelect.vue'
import BaseModal from '@purdia/ui/src/components/BaseModal.vue'
import type { Product, ProductVariant } from '@/types/pos'
import * as posApi from '@/api/pos'

const props = defineProps<{
  modelValue: boolean
  product: Product | null
}>()

const emit = defineEmits<{
  'update:modelValue': [value: boolean]
  adjusted: []
}>()

const toast = useToast()
const submitting = ref(false)

const form = ref({
  variant_id: '' as number | '',
  quantity: '',
  type: 'adjust' as 'set' | 'adjust',
  reason: '',
})

const isOpen = computed({
  get: () => props.modelValue,
  set: (v) => emit('update:modelValue', v),
})

const typeOptions = [
  { label: 'Sesuaikan (tambah/kurang)', value: 'adjust' },
  { label: 'Set langsung', value: 'set' },
]

const variantOptions = computed(() => {
  if (!props.product || !props.product.has_variants) return []
  return props.product.variants.map((v) => ({
    label: `${v.name} (stok: ${v.stock_quantity})`,
    value: v.id,
  }))
})

const currentStock = computed(() => {
  if (!props.product) return 0
  if (props.product.has_variants && form.value.variant_id) {
    const variant = props.product.variants.find((v) => v.id === Number(form.value.variant_id))
    return variant?.stock_quantity ?? 0
  }
  // For non-variant products, show first variant stock (default variant)
  return props.product.variants[0]?.stock_quantity ?? 0
})

watch(
  () => props.modelValue,
  (open) => {
    if (open) {
      form.value = { variant_id: '', quantity: '', type: 'adjust', reason: '' }
    }
  },
)

async function save() {
  const qty = parseInt(form.value.quantity)
  if (isNaN(qty) || qty === 0) return
  if (!props.product) return

  submitting.value = true
  try {
    await posApi.adjustStock(props.product.id, {
      variant_id: form.value.variant_id ? Number(form.value.variant_id) : undefined,
      quantity: qty,
      type: form.value.type,
      reason: form.value.reason || undefined,
    })
    toast.success('Stok berhasil disesuaikan.')
    isOpen.value = false
    emit('adjusted')
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
      <h2 class="text-lg font-semibold text-gray-900 dark:text-white">Sesuaikan Stok</h2>

      <div v-if="product" class="mt-3 rounded-lg bg-gray-50 px-4 py-3 dark:bg-gray-800">
        <p class="text-sm font-medium text-gray-900 dark:text-white">{{ product.name }}</p>
        <p class="mt-0.5 text-xs text-gray-500">Stok saat ini: <span class="font-semibold">{{ currentStock }}</span></p>
      </div>

      <form class="mt-4 space-y-4" @submit.prevent="save">
        <!-- Variant selector (only for products with variants) -->
        <BaseSelect
          v-if="product?.has_variants"
          v-model="form.variant_id"
          label="Pilih Varian"
          :options="variantOptions"
          placeholder="Pilih varian"
          required
        />

        <BaseSelect
          v-model="form.type"
          label="Tipe Penyesuaian"
          :options="typeOptions"
        />

        <BaseInput
          v-model="form.quantity"
          label="Jumlah"
          type="number"
          :placeholder="form.type === 'adjust' ? 'contoh: 5 atau -3' : 'contoh: 100'"
          required
        />

        <BaseInput
          v-model="form.reason"
          label="Alasan (opsional)"
          placeholder="contoh: Restok dari supplier"
        />

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
