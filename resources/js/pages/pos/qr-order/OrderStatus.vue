<script setup lang="ts">
import { ref, computed, onMounted, onUnmounted } from 'vue'
import { useRoute, useRouter } from 'vue-router'
import { formatCurrency } from '@purdia/utils'
import { Clock, CheckCircle, XCircle, ArrowLeft, RefreshCw } from '@lucide/vue'
import * as posApi from '@/api/pos'

const route = useRoute()
const router = useRouter()

const token = computed(() => route.params.token as string)
const orderId = computed(() => Number(route.params.orderId))

interface OrderDetail {
  id: number
  status: 'pending' | 'accepted' | 'completed' | 'cancelled'
  items: { product_id: number; variant_id: number | null; quantity: number; name: string; price: number }[]
  customer_name: string | null
  notes: string | null
  created_at: string
}

const order = ref<OrderDetail | null>(null)
const loading = ref(true)
const error = ref('')
let pollInterval: ReturnType<typeof setInterval> | null = null

const statusConfig = {
  pending: { label: 'Menunggu Konfirmasi', icon: Clock, color: 'text-yellow-500', bg: 'bg-yellow-50', border: 'border-yellow-200' },
  accepted: { label: 'Pesanan Diterima', icon: CheckCircle, color: 'text-green-500', bg: 'bg-green-50', border: 'border-green-200' },
  completed: { label: 'Pesanan Selesai', icon: CheckCircle, color: 'text-blue-500', bg: 'bg-blue-50', border: 'border-blue-200' },
  cancelled: { label: 'Pesanan Dibatalkan', icon: XCircle, color: 'text-red-500', bg: 'bg-red-50', border: 'border-red-200' },
}

const currentStatus = computed(() => {
  if (!order.value) return statusConfig.pending
  return statusConfig[order.value.status] || statusConfig.pending
})

const orderTotal = computed(() => {
  if (!order.value) return 0
  return order.value.items.reduce((sum, item) => sum + item.price * item.quantity, 0)
})

async function fetchOrderStatus() {
  try {
    const res = await posApi.fetchQrOrder(token.value, orderId.value)
    order.value = res.data as OrderDetail
  } catch {
    error.value = 'Gagal memuat status pesanan.'
  } finally {
    loading.value = false
  }
}

function goBackToMenu() {
  router.push({ name: 'pos.qr-order.menu', params: { token: token.value } })
}

onMounted(() => {
  fetchOrderStatus()
  // Poll every 10 seconds for status updates
  pollInterval = setInterval(fetchOrderStatus, 10000)
})

onUnmounted(() => {
  if (pollInterval) clearInterval(pollInterval)
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
        <h1 class="text-lg font-bold text-gray-900">Status Pesanan</h1>
      </div>
    </header>

    <div class="mx-auto max-w-lg px-4 py-6">
      <!-- Loading -->
      <div v-if="loading" class="py-16 text-center">
        <RefreshCw :size="24" class="mx-auto animate-spin text-gray-400" />
        <p class="mt-3 text-sm text-gray-400">Memuat status pesanan...</p>
      </div>

      <!-- Error -->
      <div v-else-if="error" class="py-16 text-center">
        <p class="text-sm text-red-500">{{ error }}</p>
      </div>

      <!-- Order status -->
      <div v-else-if="order">
        <!-- Status card -->
        <div
          class="rounded-xl border p-6 text-center"
          :class="[currentStatus.bg, currentStatus.border]"
        >
          <component :is="currentStatus.icon" :size="48" :class="currentStatus.color" class="mx-auto" />
          <h2 class="mt-3 text-lg font-bold text-gray-900">{{ currentStatus.label }}</h2>
          <p v-if="order.status === 'pending'" class="mt-1 text-sm text-gray-500">
            Pesanan kamu sedang menunggu konfirmasi dari outlet.
          </p>
          <p v-else-if="order.status === 'accepted'" class="mt-1 text-sm text-gray-500">
            Pesanan sedang diproses. Mohon tunggu sebentar.
          </p>
          <p v-else-if="order.status === 'completed'" class="mt-1 text-sm text-gray-500">
            Pesanan sudah selesai. Terima kasih!
          </p>
          <p v-else-if="order.status === 'cancelled'" class="mt-1 text-sm text-gray-500">
            Maaf, pesanan kamu dibatalkan.
          </p>
        </div>

        <!-- Auto refresh indicator -->
        <p v-if="order.status === 'pending' || order.status === 'accepted'" class="mt-3 text-center text-xs text-gray-400">
          <RefreshCw :size="12" class="inline-block mr-1" />
          Status diperbarui otomatis tiap 10 detik
        </p>

        <!-- Order details -->
        <div class="mt-6 rounded-xl border border-gray-200 bg-white p-4">
          <h3 class="text-sm font-semibold text-gray-900">Detail Pesanan</h3>

          <div v-if="order.customer_name" class="mt-2 text-xs text-gray-500">
            Nama: {{ order.customer_name }}
          </div>

          <ul class="mt-3 space-y-2">
            <li
              v-for="(item, i) in order.items"
              :key="i"
              class="flex items-center justify-between text-sm"
            >
              <span class="text-gray-700">{{ item.quantity }}× {{ item.name }}</span>
              <span class="text-gray-500">{{ formatCurrency(item.price * item.quantity) }}</span>
            </li>
          </ul>

          <!-- Total -->
          <div class="mt-3 border-t border-gray-100 pt-3 flex items-center justify-between">
            <span class="text-sm font-semibold text-gray-900">Total</span>
            <span class="text-sm font-bold text-blue-600">{{ formatCurrency(orderTotal) }}</span>
          </div>

          <!-- Notes -->
          <p v-if="order.notes" class="mt-3 rounded-lg bg-gray-50 p-3 text-xs text-gray-600 italic">
            "{{ order.notes }}"
          </p>
        </div>

        <!-- Order time -->
        <p class="mt-4 text-center text-xs text-gray-400">
          Dipesan: {{ new Date(order.created_at).toLocaleString('id-ID') }}
        </p>

        <!-- Back to menu button -->
        <button
          class="mt-6 w-full rounded-xl border border-gray-200 bg-white py-3 text-sm font-medium text-gray-700 transition-colors hover:bg-gray-50"
          @click="goBackToMenu"
        >
          Pesan Lagi
        </button>
      </div>
    </div>
  </div>
</template>
