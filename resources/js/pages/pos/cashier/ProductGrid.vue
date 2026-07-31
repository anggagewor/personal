<script setup lang="ts">
import { ref, computed } from 'vue'
import { formatCurrency } from '@purdia/utils'
import BaseTabs from '@purdia/ui/src/components/BaseTabs.vue'
import BaseInput from '@purdia/ui/src/components/BaseInput.vue'
import BaseModal from '@purdia/ui/src/components/BaseModal.vue'
import { Search, Package } from '@lucide/vue'
import type { Product, Category, ProductVariant } from '@/types/pos'

const props = defineProps<{
  categories: Category[]
  products: Product[]
  loading: boolean
}>()

const emit = defineEmits<{
  'add-to-cart': [product: Product, variantId?: number]
}>()

const search = ref('')
const activeCategory = ref('all')

// Variant picker
const showVariantPicker = ref(false)
const selectedProduct = ref<Product | null>(null)

const categoryTabs = computed(() => {
  const tabs = [{ key: 'all', label: 'Semua' }]
  for (const cat of props.categories) {
    tabs.push({ key: String(cat.id), label: cat.name })
  }
  return tabs
})

const filteredProducts = computed(() => {
  let list = props.products

  // Filter by category
  if (activeCategory.value !== 'all') {
    const catId = Number(activeCategory.value)
    list = list.filter((p) => p.category_id === catId)
  }

  // Filter by search
  if (search.value.trim()) {
    const q = search.value.toLowerCase()
    list = list.filter(
      (p) =>
        p.name.toLowerCase().includes(q) ||
        (p.sku && p.sku.toLowerCase().includes(q)),
    )
  }

  return list
})

function selectProduct(product: Product) {
  if (product.has_variants && product.variants.length > 1) {
    selectedProduct.value = product
    showVariantPicker.value = true
  } else {
    emit('add-to-cart', product)
  }
}

function pickVariant(variant: ProductVariant) {
  if (!selectedProduct.value) return
  emit('add-to-cart', selectedProduct.value, variant.id)
  showVariantPicker.value = false
  selectedProduct.value = null
}
</script>

<template>
  <div class="flex h-full flex-col">
    <!-- Header with search -->
    <div class="sticky top-0 z-10 border-b border-gray-200 bg-white p-4 dark:border-gray-700 dark:bg-gray-800">
      <BaseInput
        v-model="search"
        placeholder="Cari produk..."
        :icon="Search"
        size="md"
      />

      <!-- Category tabs -->
      <div class="mt-3 -mb-px overflow-x-auto">
        <BaseTabs
          v-model="activeCategory"
          :tabs="categoryTabs"
          variant="pills"
          size="sm"
        />
      </div>
    </div>

    <!-- Product grid -->
    <div class="flex-1 overflow-y-auto p-4">
      <!-- Loading -->
      <div v-if="loading" class="flex items-center justify-center py-12">
        <p class="text-sm text-gray-400">Memuat produk...</p>
      </div>

      <!-- Empty -->
      <div v-else-if="!filteredProducts.length" class="flex flex-col items-center py-12 text-center">
        <Package :size="40" class="text-gray-300 dark:text-gray-600" />
        <p class="mt-3 text-sm text-gray-500 dark:text-gray-400">
          {{ search ? 'Produk tidak ditemukan.' : 'Belum ada produk aktif.' }}
        </p>
      </div>

      <!-- Grid -->
      <div v-else class="grid grid-cols-2 gap-3 sm:grid-cols-3 lg:grid-cols-4">
        <button
          v-for="product in filteredProducts"
          :key="product.id"
          class="group flex flex-col rounded-xl border border-gray-200 bg-white p-3 text-left transition-all hover:border-primary-300 hover:shadow-sm active:scale-[0.98] dark:border-gray-700 dark:bg-gray-800 dark:hover:border-primary-600"
          @click="selectProduct(product)"
        >
          <!-- Product image placeholder -->
          <div class="flex h-16 w-full items-center justify-center rounded-lg bg-gray-100 dark:bg-gray-700">
            <Package :size="24" class="text-gray-400" />
          </div>

          <!-- Product info -->
          <h4 class="mt-2 text-xs font-semibold text-gray-900 leading-tight line-clamp-2 dark:text-white">
            {{ product.name }}
          </h4>

          <!-- Variants indicator -->
          <p v-if="product.has_variants && product.variants.length > 1" class="mt-0.5 text-[10px] text-gray-400">
            {{ product.variants.length }} varian
          </p>

          <!-- Price -->
          <p class="mt-auto pt-1 text-sm font-bold text-primary-600 dark:text-primary-400">
            {{ formatCurrency(product.base_price) }}
          </p>
        </button>
      </div>
    </div>

    <!-- Variant Picker Modal -->
    <BaseModal v-model="showVariantPicker" size="sm">
      <template #default>
        <h3 class="text-base font-semibold text-gray-900 dark:text-white">Pilih Varian</h3>
        <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">{{ selectedProduct?.name }}</p>
        <div class="mt-4 space-y-2">
          <button
            v-for="variant in selectedProduct?.variants"
            :key="variant.id"
            class="flex w-full items-center justify-between rounded-lg border border-gray-200 px-4 py-3 text-left transition-colors hover:border-primary-300 hover:bg-primary-50 dark:border-gray-700 dark:hover:border-primary-600 dark:hover:bg-primary-900/20"
            @click="pickVariant(variant)"
          >
            <div>
              <p class="text-sm font-medium text-gray-900 dark:text-white">{{ variant.name }}</p>
              <p v-if="variant.sku" class="text-xs text-gray-400">{{ variant.sku }}</p>
            </div>
            <div class="text-right">
              <p class="text-sm font-bold text-primary-600 dark:text-primary-400">{{ formatCurrency(variant.price) }}</p>
              <p class="text-xs text-gray-400">Stok: {{ variant.stock_quantity }}</p>
            </div>
          </button>
        </div>
      </template>
    </BaseModal>
  </div>
</template>
