<script setup lang="ts">
import { ref, computed, watch } from 'vue'
import { useToast } from '@purdia/toast'
import BaseButton from '@purdia/ui/src/components/BaseButton.vue'
import BaseInput from '@purdia/ui/src/components/BaseInput.vue'
import BaseSelect from '@purdia/ui/src/components/BaseSelect.vue'
import BaseModal from '@purdia/ui/src/components/BaseModal.vue'
import BaseCheckbox from '@purdia/ui/src/components/BaseCheckbox.vue'
import { Plus, Trash2, Upload, X } from '@lucide/vue'
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

// Image state
const imageFile = ref<File | null>(null)
const imagePreview = ref<string | null>(null)
const existingImage = ref<string | null>(null)
const removeImage = ref(false)

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
      // Reset image state
      imageFile.value = null
      imagePreview.value = null
      removeImage.value = false

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
        existingImage.value = props.editingProduct.image
      } else {
        form.value = { name: '', category_id: '', base_price: '', sku: '', has_variants: false, track_stock: false }
        variants.value = []
        existingImage.value = null
      }
    }
  },
)

function onFileChange(event: Event) {
  const input = event.target as HTMLInputElement
  const file = input.files?.[0]
  if (!file) return

  imageFile.value = file
  removeImage.value = false

  // Generate preview
  const reader = new FileReader()
  reader.onload = (e) => {
    imagePreview.value = e.target?.result as string
  }
  reader.readAsDataURL(file)
}

function clearImage() {
  imageFile.value = null
  imagePreview.value = null
  if (existingImage.value) {
    removeImage.value = true
  }
  existingImage.value = null
}

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
      await posApi.updateProduct(props.editingProduct.id, payload, imageFile.value, removeImage.value)
      toast.success('Produk berhasil diperbarui.')
    } else {
      await posApi.createProduct(props.outletId, payload, imageFile.value)
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
        <!-- Image upload -->
        <div>
          <label class="mb-1 block text-sm font-medium text-gray-700 dark:text-gray-300">Gambar Produk</label>
          <div class="flex items-start gap-4">
            <!-- Preview -->
            <div class="relative h-24 w-24 shrink-0 overflow-hidden rounded-lg border border-gray-200 bg-gray-100 dark:border-gray-600 dark:bg-gray-700">
              <img
                v-if="imagePreview || (existingImage && !removeImage)"
                :src="imagePreview || existingImage!"
                alt="Preview"
                class="h-full w-full object-cover"
              />
              <div v-else class="flex h-full w-full items-center justify-center">
                <Upload :size="24" class="text-gray-400" />
              </div>
              <!-- Remove button -->
              <button
                v-if="imagePreview || (existingImage && !removeImage)"
                type="button"
                class="absolute -right-1 -top-1 flex h-5 w-5 items-center justify-center rounded-full bg-red-500 text-white shadow hover:bg-red-600"
                @click="clearImage"
              >
                <X :size="12" />
              </button>
            </div>
            <!-- Upload input -->
            <div class="flex-1">
              <label
                class="inline-flex cursor-pointer items-center gap-2 rounded-lg border border-gray-300 px-3 py-2 text-sm font-medium text-gray-700 transition hover:bg-gray-50 dark:border-gray-600 dark:text-gray-300 dark:hover:bg-gray-700"
              >
                <Upload :size="16" />
                Pilih Gambar
                <input
                  type="file"
                  accept="image/jpeg,image/png,image/webp"
                  class="hidden"
                  @change="onFileChange"
                />
              </label>
              <p class="mt-1 text-xs text-gray-400">JPG, PNG, atau WebP. Maks 2MB.</p>
            </div>
          </div>
        </div>

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
          <BaseCheckbox v-model="form.track_stock" label="Lacak Stok" />
          <BaseCheckbox v-model="form.has_variants" label="Punya Varian" />
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
