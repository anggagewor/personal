<script setup lang="ts">
import { computed } from 'vue'
import { formatCurrency } from '@purdia/utils'
import { usePosCartStore } from '@/stores/pos-cart'
import BaseButton from '@purdia/ui/src/components/BaseButton.vue'
import { ShoppingCart, Trash2, Plus, Minus } from '@lucide/vue'
import type { Outlet } from '@/types/pos'

defineProps<{
  outlet: Outlet | null
}>()

const emit = defineEmits<{
  checkout: []
}>()

const cartStore = usePosCartStore()

const hasItems = computed(() => cartStore.items.length > 0)

function increment(productId: number, variantId: number | null) {
  const item = cartStore.items.find(
    (i) => i.product_id === productId && i.product_variant_id === variantId,
  )
  if (item) {
    cartStore.updateQuantity(productId, variantId, item.quantity + 1)
  }
}

function decrement(productId: number, variantId: number | null) {
  const item = cartStore.items.find(
    (i) => i.product_id === productId && i.product_variant_id === variantId,
  )
  if (item) {
    cartStore.updateQuantity(productId, variantId, item.quantity - 1)
  }
}

function removeItem(productId: number, variantId: number | null) {
  cartStore.removeItem(productId, variantId)
}

function clearCart() {
  cartStore.clearCart()
}
</script>

<template>
  <div class="flex h-full flex-col">
    <!-- Cart header -->
    <div class="flex items-center justify-between border-b border-gray-200 px-4 py-3 dark:border-gray-700">
      <div class="flex items-center gap-2">
        <ShoppingCart :size="18" class="text-gray-600 dark:text-gray-300" />
        <h3 class="text-sm font-semibold text-gray-900 dark:text-white">Keranjang</h3>
        <span v-if="cartStore.itemCount" class="rounded-full bg-primary-100 px-2 py-0.5 text-xs font-medium text-primary-700 dark:bg-primary-900/30 dark:text-primary-300">
          {{ cartStore.itemCount }}
        </span>
      </div>
      <BaseButton
        v-if="hasItems"
        variant="ghost"
        size="xs"
        class="text-red-500 hover:text-red-600 dark:text-red-400 dark:hover:text-red-300"
        @click="clearCart"
      >
        Kosongkan
      </BaseButton>
    </div>

    <!-- Cart items -->
    <div class="flex-1 overflow-y-auto px-4 py-2">
      <!-- Empty state -->
      <div v-if="!hasItems" class="flex flex-col items-center justify-center h-full text-center">
        <ShoppingCart :size="32" class="text-gray-300 dark:text-gray-600" />
        <p class="mt-2 text-xs text-gray-400">Keranjang kosong</p>
        <p class="text-[10px] text-gray-400">Klik produk untuk menambahkan</p>
      </div>

      <!-- Items list -->
      <div v-else class="space-y-2">
        <div
          v-for="item in cartStore.items"
          :key="`${item.product_id}-${item.product_variant_id}`"
          class="rounded-lg border border-gray-100 bg-gray-50 p-3 dark:border-gray-700 dark:bg-gray-900/50"
        >
          <div class="flex items-start justify-between gap-2">
            <div class="min-w-0 flex-1">
              <p class="text-sm font-medium text-gray-900 truncate dark:text-white">
                {{ item.product_name }}
              </p>
              <p v-if="item.variant_name" class="text-xs text-gray-400">
                {{ item.variant_name }}
              </p>
              <p class="mt-0.5 text-xs text-gray-500 dark:text-gray-400">
                {{ formatCurrency(item.unit_price) }}
              </p>
            </div>
            <button
              class="shrink-0 rounded p-1 text-gray-400 hover:text-red-500 dark:hover:text-red-400"
              @click="removeItem(item.product_id, item.product_variant_id)"
            >
              <Trash2 :size="14" />
            </button>
          </div>

          <!-- Quantity controls -->
          <div class="mt-2 flex items-center justify-between">
            <div class="flex items-center gap-1">
              <button
                class="flex h-6 w-6 items-center justify-center rounded border border-gray-300 text-gray-600 hover:bg-gray-200 dark:border-gray-600 dark:text-gray-300 dark:hover:bg-gray-700"
                @click="decrement(item.product_id, item.product_variant_id)"
              >
                <Minus :size="12" />
              </button>
              <span class="w-8 text-center text-sm font-medium text-gray-900 dark:text-white">
                {{ item.quantity }}
              </span>
              <button
                class="flex h-6 w-6 items-center justify-center rounded border border-gray-300 text-gray-600 hover:bg-gray-200 dark:border-gray-600 dark:text-gray-300 dark:hover:bg-gray-700"
                @click="increment(item.product_id, item.product_variant_id)"
              >
                <Plus :size="12" />
              </button>
            </div>
            <p class="text-sm font-semibold text-gray-900 dark:text-white">
              {{ formatCurrency(item.subtotal) }}
            </p>
          </div>
        </div>
      </div>
    </div>

    <!-- Cart summary + checkout button -->
    <div class="border-t border-gray-200 px-4 py-3 dark:border-gray-700">
      <!-- Discount info -->
      <div v-if="cartStore.discountTotal > 0" class="mb-2 flex items-center justify-between text-xs">
        <span class="text-gray-500 dark:text-gray-400">Diskon</span>
        <span class="text-red-500">-{{ formatCurrency(cartStore.discountTotal) }}</span>
      </div>

      <!-- Subtotal -->
      <div class="flex items-center justify-between text-xs">
        <span class="text-gray-500 dark:text-gray-400">Subtotal</span>
        <span class="text-gray-700 dark:text-gray-300">{{ formatCurrency(cartStore.subtotal) }}</span>
      </div>

      <!-- Total -->
      <div class="mt-1 flex items-center justify-between">
        <span class="text-sm font-semibold text-gray-900 dark:text-white">Total</span>
        <span class="text-lg font-bold text-primary-600 dark:text-primary-400">{{ formatCurrency(cartStore.total) }}</span>
      </div>

      <!-- Checkout button -->
      <BaseButton
        variant="primary"
        size="md"
        class="mt-3 w-full"
        :disabled="!hasItems"
        @click="emit('checkout')"
      >
        Bayar ({{ formatCurrency(cartStore.total) }})
      </BaseButton>
    </div>
  </div>
</template>
