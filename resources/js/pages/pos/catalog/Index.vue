<script setup lang="ts">
import { ref, computed, watch, onMounted } from 'vue'
import { useToast } from '@purdia/toast'
import { formatCurrency } from '@purdia/utils'
import BaseButton from '@purdia/ui/src/components/BaseButton.vue'
import BaseInput from '@purdia/ui/src/components/BaseInput.vue'
import { Plus, Search, Package, PackageX, MoreVertical, Pencil, Archive, BarChart3 } from '@lucide/vue'
import { useRoute, useRouter } from 'vue-router'
import CategoryList from './CategoryList.vue'
import ProductForm from './ProductForm.vue'
import StockAdjustment from './StockAdjustment.vue'
import type { Product, Category, Outlet } from '@/types/pos'
import * as posApi from '@/api/pos'
import { usePosOutlet } from '@/composables/usePosOutlet'

const route = useRoute()
const router = useRouter()
const toast = useToast()
const { outletId } = usePosOutlet()

const categories = ref<Category[]>([])
const products = ref<Product[]>([])
const selectedCategoryId = ref<number | null>(null)
const search = ref('')
const loading = ref(true)

// Product form modal
const showProductForm = ref(false)
const editingProduct = ref<Product | null>(null)

// Stock adjustment modal
const showStockAdjustment = ref(false)
const stockProduct = ref<Product | null>(null)

// Dropdown menu
const openMenuId = ref<number | null>(null)

async function fetchCategories() {
  if (!outletId.value) return
  try {
    const res = await posApi.fetchCategories(outletId.value)
    categories.value = res.data
  } catch {
    // Error handled globally
  }
}

async function fetchProducts() {
  if (!outletId.value) return
  loading.value = true
  try {
    const params: { category_id?: number } = {}
    if (selectedCategoryId.value) {
      params.category_id = selectedCategoryId.value
    }
    const res = await posApi.fetchProducts(outletId.value, params)
    products.value = res.data
  } catch {
    // Error handled globally
  } finally {
    loading.value = false
  }
}

const filteredProducts = computed(() => {
  if (!search.value.trim()) return products.value
  const q = search.value.toLowerCase()
  return products.value.filter(
    (p) =>
      p.name.toLowerCase().includes(q) ||
      (p.sku && p.sku.toLowerCase().includes(q)),
  )
})

function onCategorySelect(id: number | null) {
  selectedCategoryId.value = id
}

function onCategoryUpdated() {
  fetchCategories()
}

function openCreateProduct() {
  editingProduct.value = null
  showProductForm.value = true
}

function openEditProduct(product: Product) {
  editingProduct.value = product
  showProductForm.value = true
  openMenuId.value = null
}

function openStockAdjustment(product: Product) {
  stockProduct.value = product
  showStockAdjustment.value = true
  openMenuId.value = null
}

async function deactivateProduct(product: Product) {
  if (!confirm(`Nonaktifkan produk "${product.name}"?`)) return
  openMenuId.value = null

  try {
    await posApi.deactivateProduct(product.id)
    toast.success('Produk berhasil dinonaktifkan.')
    fetchProducts()
  } catch {
    // Error handled globally
  }
}

function toggleMenu(productId: number) {
  openMenuId.value = openMenuId.value === productId ? null : productId
}

function onProductSaved() {
  fetchProducts()
}

function onStockAdjusted() {
  fetchProducts()
}

watch(selectedCategoryId, () => fetchProducts())

watch(outletId, (val) => {
  if (val) {
    fetchCategories()
    fetchProducts()
  }
})

// Initial load
if (outletId.value) {
  fetchCategories()
  fetchProducts()
}
</script>

<template>
  <div>
    <!-- Page header -->
    <div class="flex items-center justify-between">
      <div>
        <h1 class="text-2xl font-semibold text-gray-900 dark:text-white">Katalog Produk</h1>
        <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">Kelola kategori dan produk outlet.</p>
      </div>
      <BaseButton variant="primary" size="sm" :icon="Plus" @click="openCreateProduct">
        Produk Baru
      </BaseButton>
    </div>

    <!-- Main content: sidebar + product grid -->
    <div class="mt-6 flex gap-6">
      <!-- Category sidebar -->
      <div class="w-64 shrink-0 rounded-xl border border-gray-200 bg-white dark:border-gray-700 dark:bg-gray-800">
        <CategoryList
          :outlet-id="outletId"
          :categories="categories"
          :selected-category-id="selectedCategoryId"
          @select="onCategorySelect"
          @updated="onCategoryUpdated"
        />
      </div>

      <!-- Product list -->
      <div class="flex-1 min-w-0">
        <!-- Search bar -->
        <div class="mb-4">
          <BaseInput
            v-model="search"
            placeholder="Cari produk..."
            :icon="Search"
            size="md"
          />
        </div>

        <!-- Product grid -->
        <div v-if="filteredProducts.length" class="grid grid-cols-1 gap-4 sm:grid-cols-2 lg:grid-cols-3">
          <div
            v-for="product in filteredProducts"
            :key="product.id"
            class="group relative rounded-xl border border-gray-200 bg-white p-4 transition-shadow hover:shadow-md dark:border-gray-700 dark:bg-gray-800"
          >
            <!-- Status badge -->
            <span
              v-if="product.status === 'inactive'"
              class="absolute right-3 top-3 rounded-full bg-red-100 px-2 py-0.5 text-xs font-medium text-red-700 dark:bg-red-900/30 dark:text-red-300"
            >
              Nonaktif
            </span>

            <!-- Product info -->
            <div class="flex items-start gap-3">
              <div class="flex h-10 w-10 shrink-0 items-center justify-center rounded-lg bg-gray-100 dark:bg-gray-700">
                <Package :size="20" class="text-gray-400" />
              </div>
              <div class="min-w-0 flex-1">
                <h3 class="text-sm font-semibold text-gray-900 truncate dark:text-white">{{ product.name }}</h3>
                <p class="text-xs text-gray-400">{{ product.sku || 'Tanpa SKU' }}</p>
              </div>
            </div>

            <!-- Price -->
            <p class="mt-3 text-lg font-bold text-gray-900 dark:text-white">
              {{ formatCurrency(product.base_price) }}
            </p>

            <!-- Meta info -->
            <div class="mt-2 flex items-center gap-3 text-xs text-gray-400">
              <span v-if="product.has_variants">{{ product.variants.length }} varian</span>
              <span v-if="product.track_stock" class="flex items-center gap-1">
                <BarChart3 :size="12" />
                Stok: {{ product.variants.reduce((s, v) => s + v.stock_quantity, 0) }}
              </span>
            </div>

            <!-- Actions menu -->
            <div class="absolute right-3 bottom-3">
              <button
                class="rounded p-1 text-gray-400 opacity-0 group-hover:opacity-100 hover:bg-gray-100 dark:hover:bg-gray-700"
                @click="toggleMenu(product.id)"
              >
                <MoreVertical :size="16" />
              </button>

              <!-- Dropdown -->
              <Transition name="fade">
                <div
                  v-if="openMenuId === product.id"
                  class="absolute right-0 bottom-8 z-10 w-40 rounded-lg border border-gray-200 bg-white py-1 shadow-lg dark:border-gray-600 dark:bg-gray-800"
                >
                  <button
                    class="flex w-full items-center gap-2 px-3 py-2 text-sm text-gray-700 hover:bg-gray-100 dark:text-gray-300 dark:hover:bg-gray-700"
                    @click="openEditProduct(product)"
                  >
                    <Pencil :size="14" /> Edit
                  </button>
                  <button
                    v-if="product.track_stock"
                    class="flex w-full items-center gap-2 px-3 py-2 text-sm text-gray-700 hover:bg-gray-100 dark:text-gray-300 dark:hover:bg-gray-700"
                    @click="openStockAdjustment(product)"
                  >
                    <BarChart3 :size="14" /> Sesuaikan Stok
                  </button>
                  <button
                    v-if="product.status === 'active'"
                    class="flex w-full items-center gap-2 px-3 py-2 text-sm text-red-600 hover:bg-gray-100 dark:text-red-400 dark:hover:bg-gray-700"
                    @click="deactivateProduct(product)"
                  >
                    <Archive :size="14" /> Nonaktifkan
                  </button>
                </div>
              </Transition>
            </div>
          </div>
        </div>

        <!-- Empty state -->
        <div v-else-if="!loading" class="flex flex-col items-center py-12 text-center">
          <PackageX :size="48" class="text-gray-300 dark:text-gray-600" />
          <p class="mt-3 text-sm text-gray-500 dark:text-gray-400">
            {{ search ? 'Produk tidak ditemukan.' : 'Belum ada produk. Tambahkan produk pertama!' }}
          </p>
        </div>
      </div>
    </div>

    <!-- Product Form Modal -->
    <ProductForm
      v-model="showProductForm"
      :outlet-id="outletId"
      :categories="categories"
      :editing-product="editingProduct"
      @saved="onProductSaved"
    />

    <!-- Stock Adjustment Modal -->
    <StockAdjustment
      v-model="showStockAdjustment"
      :product="stockProduct"
      @adjusted="onStockAdjusted"
    />
  </div>
</template>

<style scoped>
.fade-enter-active, .fade-leave-active { transition: opacity 150ms ease; }
.fade-enter-from, .fade-leave-to { opacity: 0; }
</style>
