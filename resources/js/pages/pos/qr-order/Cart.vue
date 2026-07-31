<script setup lang="ts">
import { ref, computed, onMounted } from 'vue'
import { useRoute, useRouter } from 'vue-router'
import { formatCurrency } from '@purdia/utils'
import { Minus, Plus, Trash2, ArrowLeft, ShoppingCart } from '@lucide/vue'
import * as posApi from '@/api/pos'

const route = useRoute()
const router = useRouter()

const token = computed(() => route.params.token as string)

interface CartItem {
  product_id: number
  variant_id: number | null
  name: string
  variant_name: string | null
  price: number
  quantity: number
}

const cart = ref<CartItem[]>([])
const customerName = ref('')
const notes = ref('')
const submitting = ref(false)
const error = ref('')

const cartTotal = computed(() => cart.value.reduce((sum, item) => sum + item.price * item.quantity, 0))
const cartCount = computed(() => cart.value.reduce((sum, item) => sum + item.quantity, 0))

function increaseQuantity(index: number) {
  cart.value[index].quantity++
  saveCart()
}

function decreaseQuantity(index: number) {
  if (cart.value[index].quantity > 1) {
    cart.value[index].quantity--
  } else {
    cart.value.splice(index, 1)
  }
  saveCart()
}

function removeItem(index: number) {
  cart.value.splice(index, 1)
  saveCart()
}

function saveCart() {
  sessionStorage.setItem(`qr-cart-${token.value}`, JSON.stringify(cart.value))
}

function goBackToMenu() {
  saveCart()
  router.push({ name: 'pos.qr-order.menu', params: { token: token.value } })
}

async function submitOrder() {
  if (!cart.value.length) return
  submitting.value = true
  error.value = ''

  try {
    const payload = {
      items: cart.value.map((item) => ({
        product_id: item.product_id,
        variant_id: item.variant_id ?? undefined,
        quantity: item.quantity,
      })),
      customer_name: customerName.value.trim() || undefined,
      notes: notes.value.trim() || undefined,
    }

    const res = await posApi.createQrOrder(token.value, payload)
    const orderId = (res.data as { id: number }).id

    // Clear cart after successful submission
    sessionStorage.removeItem(`qr-cart-${token.value}`)

    // Navigate to order status page
    router.push({ name: 'pos.qr-order.status', params: { token: token.value, orderId } })
  } catch {
    error.value = 'Gagal mengirim pesanan. Silakan coba lagi.'
  } finally {
    submitting.value = false
  }
}

onMounted(() => {
  const saved = sessionStorage.getItem(`qr-cart-${token.value}`)
  if (saved) {
    try { cart.value = JSON.parse(saved) } catch { /* ignore */ }
  }

  // If cart is empty, go back to menu
  if (!cart.value.length) {
    router.replace({ name: 'pos.qr-order.menu', params: { token: token.value } })
  }
})
</script>

<template>
  <div class="min-h-screen bg-gray-50">
    <!-- Header -->
    <header class="sticky top-0 z-10 border-b border-gray-200 bg-white px-4 py-3 shadow-sm">
      <div class="mx-auto flex max-w-lg items-center gap-3">
        <button
          class="flex h-8 w-8 items-center justify-center rounded-full hover:bg-gray-100"
          @click="goBackToMenu"
        >
          <ArrowLeft :size="18" class="text-gray-600" />
        </button>
        <div>
          <h1 class="text-lg font-bold text-gray-900">Keranjang</h1>
          <p class="text-xs text-gray-500">{{ cartCount }} item</p>
        </div>
      </div>
    </header>

    <div class="mx-auto max-w-lg px-4 pb-32">
      <!-- Empty cart -->
      <div v-if="!cart.length" class="py-16 text-center">
        <ShoppingCart :size="48" class="mx-auto text-gray-300" />
        <p class="mt-3 text-sm text-gray-400">Keranjang kosong</p>
      </div>

      <!-- Cart items -->
      <div v-else class="mt-4 space-y-3">
        <div
          v-for="(item, index) in cart"
          :key="`${item.product_id}-${item.variant_id}`"
          class="flex items-center gap-3 rounded-xl border border-gray-200 bg-white p-4"
        >
          <div class="min-w-0 flex-1">
            <h3 class="text-sm font-semibold text-gray-900">{{ item.name }}</h3>
            <p v-if="item.variant_name" class="text-xs text-gray-500">{{ item.variant_name }}</p>
            <p class="mt-1 text-sm font-bold text-blue-600">{{ formatCurrency(item.price) }}</p>
          </div>

          <!-- Quantity controls -->
          <div class="flex items-center gap-2">
            <button
              class="flex h-7 w-7 items-center justify-center rounded-full border border-gray-200 text-gray-600 transition-colors hover:bg-gray-100"
              @click="decreaseQuantity(index)"
            >
              <Minus :size="14" />
            </button>
            <span class="w-6 text-center text-sm font-semibold text-gray-900">{{ item.quantity }}</span>
            <button
              class="flex h-7 w-7 items-center justify-center rounded-full border border-gray-200 text-gray-600 transition-colors hover:bg-gray-100"
              @click="increaseQuantity(index)"
            >
              <Plus :size="14" />
            </button>
          </div>

          <!-- Remove button -->
          <button
            class="flex h-7 w-7 items-center justify-center rounded-full text-gray-400 hover:text-red-500"
            @click="removeItem(index)"
          >
            <Trash2 :size="14" />
          </button>
        </div>

        <!-- Customer info -->
        <div class="mt-6 space-y-3">
          <div>
            <label class="block text-xs font-medium text-gray-600 mb-1">Nama (opsional)</label>
            <input
              v-model="customerName"
              type="text"
              placeholder="Nama kamu"
              class="w-full rounded-lg border border-gray-200 bg-white px-3 py-2.5 text-sm focus:border-blue-500 focus:outline-none focus:ring-1 focus:ring-blue-500"
            />
          </div>
          <div>
            <label class="block text-xs font-medium text-gray-600 mb-1">Catatan (opsional)</label>
            <textarea
              v-model="notes"
              placeholder="Contoh: tidak pedas, es batu sedikit"
              rows="3"
              class="w-full rounded-lg border border-gray-200 bg-white px-3 py-2.5 text-sm resize-none focus:border-blue-500 focus:outline-none focus:ring-1 focus:ring-blue-500"
            />
          </div>
        </div>

        <!-- Error message -->
        <p v-if="error" class="mt-3 text-center text-sm text-red-500">{{ error }}</p>
      </div>
    </div>

    <!-- Bottom action bar -->
    <div v-if="cart.length" class="fixed bottom-0 inset-x-0 z-20 border-t border-gray-200 bg-white px-4 py-4">
      <div class="mx-auto max-w-lg">
        <!-- Total -->
        <div class="mb-3 flex items-center justify-between">
          <span class="text-sm text-gray-600">Total</span>
          <span class="text-lg font-bold text-gray-900">{{ formatCurrency(cartTotal) }}</span>
        </div>

        <!-- Submit button -->
        <button
          class="w-full rounded-xl bg-blue-600 py-3.5 text-sm font-semibold text-white shadow-lg transition-all active:scale-[0.98] disabled:opacity-50 disabled:cursor-not-allowed"
          :disabled="submitting"
          @click="submitOrder"
        >
          {{ submitting ? 'Mengirim...' : 'Kirim Pesanan' }}
        </button>
      </div>
    </div>
  </div>
</template>
