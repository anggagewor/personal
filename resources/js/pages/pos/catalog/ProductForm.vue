<script setup lang="ts">
import { ref, computed, watch } from 'vue'
import { useToast } from '@purdia/toast'
import BaseButton from '@purdia/ui/src/components/BaseButton.vue'
import BaseInput from '@purdia/ui/src/components/BaseInput.vue'
import BaseSelect from '@purdia/ui/src/components/BaseSelect.vue'
import BaseModal from '@purdia/ui/src/components/BaseModal.vue'
import { Plus, Trash2 } from '@lucide/vue'
import type { Product, ProductVariant, Category } from '@/types/pos'
import * as posApi from '@/api/pos'

const props = defineProps<{
  modelValue: boolean
  outletId: number
  categories: Category[]
  editingProduct: Product | null
}>()

const emit = defineEmits<{
  'update:modelValue': [value: boolean]
  saved: []
}>()

const toast = useToast()
const submitting = ref(false)

const form = ref({
  name: '',
  category_id: '' as number | '',
  base_price: '',
  sku: '',
  has_variants: false,
  track_stock: false,
})

const variants = ref<{ name: string; sku: string; price: string; stock_quantity: string }[]>([])

const categoryOptions = computed(() =>
  props.categories.map((c) => ({ label: c.name, value: c.id })),
)

const isOpen = computed({
  get: () => props.modelValue,
  set: (v) => emit('update:modelValue', v),
})

watch(
  () => props.modelValue,
  (open) => {
    if (open) {
      if (props.editingProduct) {
        form.value = {
          name: props.editingProduct.name,
          category_id: props.editingProduct.category_id,
          base_price: String(props.editingProduct.base_price),
          sku: props.editingProduct.sku ?? '',
          has_variants: props.editingProduct.has_variants,
          track_stock: props.editingProduct.track_stock,
        }
        variants.value = props.editingProduct.variants.map((v) => ({
          name: v.name,
          sku: v.sku ?? '',
          price: String(v.price),
          stock_quantity: String(v.stock_quantity),
        }))
      } else {
        form.value = { name: '', category_id: '', base_price: '', sku: '', has_variants: false, track_stock: false }
        variants.value = []
      }
    }
  },
)

function addVariant() {
  variants.value.push({ name: '', sku: '', price: '', stock_quantity: '0' })
}

function removeVariant(idx: number) {
  variants.value.splice(idx, 1)
}

async function save() {
  if (!form.value.name.trim() || !form.value.category_id) return

  submitting.value = true
  try {
    const payload: Partial<Product> = {
      name: form.value.name,
      category_id: Number(form.value.category_id),
      base_price: parseFloat(form.value.base_price) || 0,
      sku: form.value.sku || null,
      has_variants: form.value.has_variants,
      track_stock: form.value.track_stock,
      variants: form.value.has_variants
        ? variants.value.map((v) => ({
            name: v.name,
            sku: v.sku || null,
            price: parseFloat(v.price) || 0,
            stock_quantity: parseInt(v.stock_quantity) || 0,
          })) as ProductVariant[]
        : [],
    }

    if (props.editingProduct) {
      await posApi.updateProduct(props.editingProduct.id, payload)
      toast.success('Produk berhasil diperbarui.')
    } else {
      await posApi.createProduct(props.outletId, payload)
      toast.success('Produk berhasil ditambahkan.')
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
  <BaseModal v-model="isOpen" size="lg" persistent>
    <template #default>
      <h2 class="text-lg font-semibold text-gray-900 dark:text-white">
        {{ editingProduct ? 'Edit Produk' : 'Produk Baru' }}
      </h2>

      <form class="mt-4 space-y-4" @submit.prevent="save">
        <div class="grid grid-cols-1 gap-4 sm:grid-cols-2">
          <BaseInput v-model="form.name" label="Nama Produk" placeholder="contoh: Kopi Susu" required />
          <BaseSelect
            v-model="form.category_id"
            label="Kategori"
            :options="categoryOptions"
            placeholder="Pilih kategori"
            required
          />
        </div>

        <div class="grid grid-cols-1 gap-4 sm:grid-cols-2">
          <BaseInput v-model="form.base_price" label="Harga Dasar (Rp)" type="number" placeholder="15000" required />
          <BaseInput v-model="form.sku" label="SKU (opsional)" placeholder="PRD-001" />
        </div>

        <!-- Toggles -->
        <div class="flex flex-wrap gap-6">
          <label class="flex items-center gap-2 cursor-pointer">
            <input
              v-model="form.track_stock"
              type="checkbox"
              class="h-4 w-4 rounded border-gray-300 text-primary-600 focus:ring-primary-500"
            />
            <span class="text-sm text-gray-700 dark:text-gray-300">Lacak Stok</span>
          </label>
          <label class="flex items-center gap-2 cursor-pointer">
            <input
              v-model="form.has_variants"
              type="checkbox"
              class="h-4 w-4 rounded border-gray-300 text-primary-600 focus:ring-primary-500"
            />
            <span class="text-sm text-gray-700 dark:text-gray-300">Punya Varian</span>
          </label>
        </div>

        <!-- Variants section -->
        <div v-if="form.has_variants" class="rounded-lg border border-gray-200 p-4 dark:border-gray-700">
          <div class="flex items-center justify-between mb-3">
            <h3 class="text-sm font-medium text-gray-700 dark:text-gray-300">Varian Produk</h3>
            <BaseButton variant="ghost" size="sm" :icon="Plus" @click="addVariant">Tambah</BaseButton>
          </div>

          <div v-if="variants.length" class="space-y-3">
            <div
              v-for="(variant, idx) in variants"
              :key="idx"
              class="flex items-start gap-3 rounded-lg border border-gray-100 bg-gray-50 p-3 dark:border-gray-600 dark:bg-gray-800"
            >
              <div class="grid flex-1 grid-cols-2 gap-3 sm:grid-cols-4">
                <BaseInput v-model="variant.name" placeholder="Nama varian" required />
                <BaseInput v-model="variant.price" placeholder="Harga" type="number" required />
                <BaseInput v-model="variant.sku" placeholder="SKU" />
                <BaseInput v-model="variant.stock_quantity" placeholder="Stok" type="number" />
              </div>
              <button
                type="button"
                class="mt-2 rounded p-1 text-gray-400 hover:text-red-600"
                @click="removeVariant(idx)"
              >
                <Trash2 :size="16" />
              </button>
            </div>
          </div>

          <p v-else class="text-xs text-gray-400">Belum ada varian. Klik "Tambah" untuk menambahkan.</p>
        </div>

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
