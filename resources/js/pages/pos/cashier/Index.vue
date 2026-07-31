<script setup lang="ts">
import { ref, computed, onMounted, watch } from 'vue'
import { useRoute } from 'vue-router'
import { usePosCartStore } from '@/stores/pos-cart'
import { usePosOutlet } from '@/composables/usePosOutlet'
import * as posApi from '@/api/pos'
import type { Product, Category, PaymentMethod, Outlet } from '@/types/pos'
import ProductGrid from './ProductGrid.vue'
import CartPanel from './CartPanel.vue'
import CheckoutModal from './CheckoutModal.vue'
import ReceiptPreview from './ReceiptPreview.vue'

const route = useRoute()
const cartStore = usePosCartStore()

const { outletId } = usePosOutlet()
const outlet = ref<Outlet | null>(null)
const categories = ref<Category[]>([])
const products = ref<Product[]>([])
const paymentMethods = ref<PaymentMethod[]>([])
const loading = ref(true)

// Checkout modal
const showCheckout = ref(false)

// Receipt preview
const showReceipt = ref(false)
const receiptData = ref<Record<string, unknown> | null>(null)

async function fetchData() {
  if (!outletId.value) return
  loading.value = true
  try {
    const [outletRes, catRes, prodRes, pmRes] = await Promise.all([
      posApi.fetchOutlets(),
      posApi.fetchCategories(outletId.value),
      posApi.fetchProducts(outletId.value, { status: 'active' }),
      posApi.fetchPaymentMethods(outletId.value),
    ])
    outlet.value = outletRes.data.find((o) => o.id === outletId.value) || null
    categories.value = catRes.data
    products.value = prodRes.data
    paymentMethods.value = pmRes.data.filter((pm) => pm.is_active)

    // Set payment flow from outlet config
    if (outlet.value) {
      cartStore.setPaymentFlow(outlet.value.payment_flow === 'pay_later' ? 'pay_later' : 'pay_first')
      cartStore.setOutletId(outlet.value.id)
    }
  } catch {
    // Error handled by @purdia/http
  } finally {
    loading.value = false
  }
}

function handleAddToCart(product: Product, variantId?: number) {
  const variant = variantId
    ? product.variants.find((v) => v.id === variantId)
    : product.variants[0]

  if (!variant) return

  cartStore.addItem({
    product_id: product.id,
    product_variant_id: variant.id,
    product_name: product.name,
    variant_name: product.has_variants ? variant.name : null,
    quantity: 1,
    unit_price: variant.price,
    image: product.image,
  })
}

function openCheckout() {
  showCheckout.value = true
}

function onCheckoutComplete(transaction: Record<string, unknown>) {
  showCheckout.value = false
  receiptData.value = transaction
  showReceipt.value = true
  cartStore.clearCart()
}

function onReceiptClose() {
  showReceipt.value = false
  receiptData.value = null
}

onMounted(() => {
  if (outletId.value) {
    cartStore.setOutletId(outletId.value)
    fetchData()
  }
})

watch(outletId, (val) => {
  if (val) {
    cartStore.setOutletId(val)
    fetchData()
  }
})
</script>

<template>
  <div class="flex h-[calc(100vh-7rem)] gap-0 overflow-hidden rounded-xl border border-gray-200 bg-white dark:border-gray-700 dark:bg-gray-800">
    <!-- Product Grid (left ~65%) -->
    <div class="flex-[2] overflow-y-auto border-r border-gray-200 dark:border-gray-700">
      <ProductGrid
        :categories="categories"
        :products="products"
        :loading="loading"
        @add-to-cart="handleAddToCart"
      />
    </div>

    <!-- Cart Panel (right ~35%) -->
    <div class="flex-[1] flex flex-col overflow-hidden">
      <CartPanel
        :outlet="outlet"
        @checkout="openCheckout"
      />
    </div>
  </div>

  <!-- Checkout Modal -->
  <CheckoutModal
    v-model="showCheckout"
    :outlet="outlet"
    :payment-methods="paymentMethods"
    :outlet-id="outletId"
    @completed="onCheckoutComplete"
  />

  <!-- Receipt Preview -->
  <ReceiptPreview
    v-model="showReceipt"
    :receipt="receiptData"
    @close="onReceiptClose"
  />
</template>
