<script setup lang="ts">
import { ref, computed, onMounted } from 'vue'
import { useRoute, useRouter } from 'vue-router'
import { formatCurrency } from '@purdia/utils'
import BaseInput from '@purdia/ui/src/components/BaseInput.vue'
import { ShoppingCart, Plus, Search } from '@lucide/vue'
import type { Product, Category, Outlet } from '@/types/pos'
import * as posApi from '@/api/pos'

const route = useRoute()
const router = useRouter()

const token = computed(() => route.params.token as string)

const outlet = ref<Outlet | null>(null)
const categories = ref<Category[]>([])
const products = ref<Product[]>([])
const loading = ref(true)
const error = ref('')
const search = ref('')
const selectedCategory = ref<number | null>(null)

// Cart state (local, not Pinia)
interface CartItem {
  product_id: number
  variant_id: number | null
  name: string
  variant_name: string | null
  price: number
  quantity: number
}

const cart = ref<CartItem[]>([])

const cartTotal = computed(() => cart.value.reduce((sum, item) => sum + item.price * item.quantity, 0))
const cartCount = computed(() => cart.value.reduce((sum, item) => sum + item.quantity, 0))

const filteredProducts = computed(() => {
  let list = products.value
  if (selectedCategory.value) {
    list = list.filter((p) => p.category_id === selectedCategory.value)
  }
  if (search.value.trim()) {
    const q = search.value.toLowerCase()
    list = list.filter((p) => p.name.toLowerCase().includes(q))
  }
  return list
})

async function fetchMenu() {
  loading.value = true
  error.value = ''
  try {
    const res = await posApi.fetchQrMenu(token.value)
    outlet.value = res.data.outlet
    categories.value = res.data.categories
    products.value = res.data.products
  } catch {
    error.value = 'Menu tidak dapat dimuat. Pastikan link QR valid.'
  } finally {
    loading.value = false
  }
}

function addToCart(product: Product, variant?: { id: number; name: string; price: number }) {
  const productId = product.id
  const variantId = variant?.id || null
  const price = variant?.price || product.base_price
  const name = product.name
  const variantName = variant?.name || null

  const existing = cart.value.find(
    (item) => item.product_id === productId && item.variant_id === variantId,
  )

  if (existing) {
    existing.quantity++
  } else {
    cart.value.push({
      product_id: productId,
      variant_id: variantId,
      name,
      variant_name: variantName,
      price,
      quantity: 1,
    })
  }
}

function getCartQuantity(productId: number, variantId: number | null = null): number {
  const item = cart.value.find(
    (i) => i.product_id === productId && i.variant_id === variantId,
  )
  return item?.quantity || 0
}

function goToCart() {
  // Store cart in sessionStorage for the cart page
  sessionStorage.setItem(`qr-cart-${token.value}`, JSON.stringify(cart.value))
  router.push({ name: 'pos.qr-order.cart', params: { token: token.value } })
}

onMounted(() => {
  // Restore cart from sessionStorage if exists
  const saved = sessionStorage.getItem(`qr-cart-${token.value}`)
  if (saved) {
    try { cart.value = JSON.parse(saved) } catch { /* ignore */ }
  }
  fetchMenu()
})
</script>

<template>
  <div class="min-h-screen bg-gray-50">
    <!-- Header -->
    <header class="sticky top-0 z-10 border-b border-gray-200 bg-white px-4 py-3 shadow-sm">
      <div class="mx-auto max-w-lg">
        <h1 class="text-lg font-bold text-gray-900">{{ outlet?.name || 'Menu' }}</h1>
        <p v-if="outlet?.address" class="text-xs text-gray-500">{{ outlet.address }}</p>
      </div>
    </header>

    <!-- Loading -->
    <div v-if="loading" class="flex items-center justify-center py-16">
      <p class="text-sm text-gray-400">Memuat menu...</p>
    </div>

    <!-- Error -->
    <div v-else-if="error" class="mx-auto max-w-lg px-4 py-16 text-center">
      <p class="text-sm text-red-500">{{ error }}</p>
    </div>

    <!-- Menu content -->
    <div v-else class="mx-auto max-w-lg px-4 pb-24">
      <!-- Search -->
      <div class="mt-4">
        <BaseInput
          v-model="search"
          placeholder="Cari menu..."
          :icon="Search"
          size="md"
        />
      </div>

      <!-- Category pills -->
      <div v-if="categories.length" class="mt-4 flex gap-2 overflow-x-auto pb-2">
        <button
          class="shrink-0 rounded-full px-3 py-1.5 text-xs font-medium transition-colors"
          :class="selectedCategory === null
            ? 'bg-blue-600 text-white'
            : 'bg-white text-gray-600 border border-gray-200 hover:bg-gray-100'"
          @click="selectedCategory = null"
        >
          Semua
        </button>
        <button
          v-for="cat in categories"
          :key="cat.id"
          class="shrink-0 rounded-full px-3 py-1.5 text-xs font-medium transition-colors"
          :class="selectedCategory === cat.id
            ? 'bg-blue-600 text-white'
            : 'bg-white text-gray-600 border border-gray-200 hover:bg-gray-100'"
          @click="selectedCategory = cat.id"
        >
          {{ cat.name }}
        </button>
      </div>

      <!-- Products list -->
      <div class="mt-4 space-y-3">
        <div
          v-for="product in filteredProducts"
          :key="product.id"
          class="rounded-xl border border-gray-200 bg-white p-4"
        >
          <div class="flex items-start justify-between gap-3">
            <div class="min-w-0 flex-1">
              <h3 class="text-sm font-semibold text-gray-900">{{ product.name }}</h3>
              <p v-if="!product.has_variants" class="mt-1 text-sm font-bold text-blue-600">
                {{ formatCurrency(product.base_price) }}
              </p>
            </div>

            <!-- Add button (no variants) -->
            <button
              v-if="!product.has_variants"
              class="relative flex h-8 w-8 shrink-0 items-center justify-center rounded-full bg-blue-600 text-white shadow-sm transition-transform active:scale-95"
              @click="addToCart(product)"
            >
              <Plus :size="16" />
              <span
                v-if="getCartQuantity(product.id)"
                class="absolute -right-1 -top-1 flex h-4 w-4 items-center justify-center rounded-full bg-red-500 text-[10px] font-bold text-white"
              >
                {{ getCartQuantity(product.id) }}
              </span>
            </button>
          </div>

          <!-- Variants -->
          <div v-if="product.has_variants && product.variants.length" class="mt-3 space-y-2">
            <div
              v-for="variant in product.variants"
              :key="variant.id"
              class="flex items-center justify-between rounded-lg bg-gray-50 px-3 py-2"
            >
              <div>
                <span class="text-xs text-gray-700">{{ variant.name }}</span>
                <span class="ml-2 text-xs font-bold text-blue-600">{{ formatCurrency(variant.price) }}</span>
              </div>
              <button
                class="relative flex h-7 w-7 items-center justify-center rounded-full bg-blue-600 text-white transition-transform active:scale-95"
                @click="addToCart(product, { id: variant.id, name: variant.name, price: variant.price })"
              >
                <Plus :size="14" />
                <span
                  v-if="getCartQuantity(product.id, variant.id)"
                  class="absolute -right-1 -top-1 flex h-4 w-4 items-center justify-center rounded-full bg-red-500 text-[10px] font-bold text-white"
                >
                  {{ getCartQuantity(product.id, variant.id) }}
                </span>
              </button>
            </div>
          </div>
        </div>

        <!-- Empty product list -->
        <div v-if="!filteredProducts.length" class="py-8 text-center">
          <p class="text-sm text-gray-400">Tidak ada menu ditemukan.</p>
        </div>
      </div>
    </div>

    <!-- Floating cart button -->
    <div v-if="cartCount > 0" class="fixed bottom-0 inset-x-0 z-20 p-4 bg-gradient-to-t from-gray-50 via-gray-50">
      <div class="mx-auto max-w-lg">
        <button
          class="flex w-full items-center justify-between rounded-xl bg-blue-600 px-5 py-3.5 text-white shadow-lg transition-transform active:scale-[0.98]"
          @click="goToCart"
        >
          <div class="flex items-center gap-2">
            <ShoppingCart :size="18" />
            <span class="text-sm font-semibold">{{ cartCount }} item</span>
          </div>
          <span class="text-sm font-bold">{{ formatCurrency(cartTotal) }}</span>
        </button>
      </div>
    </div>
  </div>
</template>
