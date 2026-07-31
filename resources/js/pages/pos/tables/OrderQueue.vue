<script setup lang="ts">
import { ref, computed, onMounted, onUnmounted } from 'vue'
import { useRoute } from 'vue-router'
import { useToast } from '@purdia/toast'
import { formatCurrency } from '@purdia/utils'
import BaseButton from '@purdia/ui/src/components/BaseButton.vue'
import BaseEmptyState from '@purdia/ui/src/components/BaseEmptyState.vue'
import BaseBadge from '@purdia/ui/src/components/BaseBadge.vue'
import { Check, RefreshCw, ClipboardList } from '@lucide/vue'
import type { OrderQueueItem } from '@/types/pos'
import * as posApi from '@/api/pos'

const route = useRoute()
const toast = useToast()

const outletId = computed(() => Number(route.query.outlet))

const orders = ref<OrderQueueItem[]>([])
const loading = ref(true)
const refreshing = ref(false)
let pollInterval: ReturnType<typeof setInterval> | null = null

async function fetchOrders() {
  if (!outletId.value) return
  try {
    const res = await posApi.fetchOrderQueue(outletId.value)
    orders.value = res.data
  } catch {
    // Error handled globally
  } finally {
    loading.value = false
    refreshing.value = false
  }
}

async function acceptOrder(order: OrderQueueItem) {
  try {
    await posApi.acceptOrder(order.id)
    toast.success(`Pesanan dari ${order.table_name} diterima.`)
    fetchOrders()
  } catch {
    // Error handled globally
  }
}

function manualRefresh() {
  refreshing.value = true
  fetchOrders()
}

const pendingOrders = computed(() => orders.value.filter((o) => o.status === 'pending'))
const acceptedOrders = computed(() => orders.value.filter((o) => o.status === 'accepted'))

function formatTime(dateStr: string) {
  return new Date(dateStr).toLocaleTimeString('id-ID', { hour: '2-digit', minute: '2-digit' })
}

onMounted(() => {
  fetchOrders()
  // Poll every 15 seconds for new orders
  pollInterval = setInterval(fetchOrders, 15000)
})

onUnmounted(() => {
  if (pollInterval) clearInterval(pollInterval)
})
</script>

<template>
  <div>
    <!-- Header -->
    <div class="flex items-center justify-between">
      <div>
        <h1 class="text-2xl font-semibold text-gray-900 dark:text-white">Antrian Pesanan</h1>
        <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">
          Pesanan masuk dari QR Order. Auto-refresh tiap 15 detik.
        </p>
      </div>
      <BaseButton variant="secondary" size="sm" :icon="RefreshCw" :loading="refreshing" @click="manualRefresh">
        Refresh
      </BaseButton>
    </div>

    <!-- Loading -->
    <div v-if="loading" class="mt-6 py-8 text-center text-sm text-gray-400">Memuat pesanan...</div>

    <!-- Empty state -->
    <BaseEmptyState
      v-else-if="!orders.length"
      :icon="ClipboardList"
      title="Belum ada pesanan"
      description="Pesanan dari pelanggan QR Order akan muncul di sini."
      class="mt-12"
    />

    <div v-else class="mt-6 space-y-8">
      <!-- Pending Orders -->
      <section v-if="pendingOrders.length">
        <h2 class="mb-3 flex items-center gap-2 text-sm font-semibold text-gray-900 dark:text-white">
          <span class="inline-block h-2 w-2 rounded-full bg-yellow-400"></span>
          Menunggu Diterima ({{ pendingOrders.length }})
        </h2>
        <div class="grid grid-cols-1 gap-4 lg:grid-cols-2">
          <div
            v-for="order in pendingOrders"
            :key="order.id"
            class="rounded-xl border border-yellow-200 bg-yellow-50 p-4 dark:border-yellow-800 dark:bg-yellow-900/10"
          >
            <div class="flex items-start justify-between">
              <div>
                <h3 class="text-sm font-semibold text-gray-900 dark:text-white">{{ order.table_name }}</h3>
                <p class="text-xs text-gray-500 dark:text-gray-400">
                  {{ formatTime(order.created_at) }}
                  <span v-if="order.customer_name"> · {{ order.customer_name }}</span>
                </p>
              </div>
              <BaseBadge variant="warning">Pending</BaseBadge>
            </div>

            <!-- Items -->
            <ul class="mt-3 space-y-1">
              <li v-for="(item, i) in order.items" :key="i" class="flex justify-between text-sm text-gray-700 dark:text-gray-300">
                <span>{{ item.quantity }}× {{ item.name }}</span>
                <span class="text-xs text-gray-400">{{ formatCurrency(item.price * item.quantity) }}</span>
              </li>
            </ul>

            <!-- Notes -->
            <p v-if="order.notes" class="mt-2 rounded-md bg-white/60 p-2 text-xs text-gray-600 italic dark:bg-gray-800/40 dark:text-gray-400">
              "{{ order.notes }}"
            </p>

            <!-- Accept action -->
            <div class="mt-4">
              <BaseButton variant="primary" size="sm" :icon="Check" @click="acceptOrder(order)">
                Terima Pesanan
              </BaseButton>
            </div>
          </div>
        </div>
      </section>

      <!-- Accepted Orders -->
      <section v-if="acceptedOrders.length">
        <h2 class="mb-3 flex items-center gap-2 text-sm font-semibold text-gray-900 dark:text-white">
          <span class="inline-block h-2 w-2 rounded-full bg-green-400"></span>
          Sudah Diterima ({{ acceptedOrders.length }})
        </h2>
        <div class="grid grid-cols-1 gap-4 lg:grid-cols-2">
          <div
            v-for="order in acceptedOrders"
            :key="order.id"
            class="rounded-xl border border-gray-200 bg-white p-4 dark:border-gray-700 dark:bg-gray-800"
          >
            <div class="flex items-start justify-between">
              <div>
                <h3 class="text-sm font-semibold text-gray-900 dark:text-white">{{ order.table_name }}</h3>
                <p class="text-xs text-gray-500 dark:text-gray-400">
                  {{ formatTime(order.created_at) }}
                  <span v-if="order.customer_name"> · {{ order.customer_name }}</span>
                </p>
              </div>
              <BaseBadge variant="success">Diterima</BaseBadge>
            </div>

            <ul class="mt-3 space-y-1">
              <li v-for="(item, i) in order.items" :key="i" class="flex justify-between text-sm text-gray-700 dark:text-gray-300">
                <span>{{ item.quantity }}× {{ item.name }}</span>
                <span class="text-xs text-gray-400">{{ formatCurrency(item.price * item.quantity) }}</span>
              </li>
            </ul>

            <p v-if="order.notes" class="mt-2 rounded-md bg-gray-50 p-2 text-xs text-gray-600 italic dark:bg-gray-700/40 dark:text-gray-400">
              "{{ order.notes }}"
            </p>
          </div>
        </div>
      </section>
    </div>
  </div>
</template>
