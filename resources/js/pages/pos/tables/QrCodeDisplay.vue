<script setup lang="ts">
import { ref, computed, onMounted } from 'vue'
import { useRoute } from 'vue-router'
import BaseButton from '@purdia/ui/src/components/BaseButton.vue'
import { Printer, Copy, Check } from '@lucide/vue'
import type { PosTable } from '@/types/pos'
import * as posApi from '@/api/pos'

const route = useRoute()

const outletId = computed(() => Number(route.query.outlet))
const tableId = computed(() => Number(route.query.table))

const table = ref<PosTable | null>(null)
const loading = ref(true)
const copied = ref(false)

const qrUrl = computed(() => {
  if (!table.value) return ''
  return `${window.location.origin}/pos/qr/${table.value.token}/menu`
})

const qrImageUrl = computed(() => {
  if (!qrUrl.value) return ''
  // Use QR code API for generating image
  return `https://api.qrserver.com/v1/create-qr-code/?size=300x300&data=${encodeURIComponent(qrUrl.value)}`
})

async function fetchTable() {
  if (!outletId.value) return
  loading.value = true
  try {
    const res = await posApi.fetchTables(outletId.value)
    table.value = res.data.find((t: PosTable) => t.id === tableId.value) || null
  } catch {
    // Error handled globally
  } finally {
    loading.value = false
  }
}

async function copyUrl() {
  if (!qrUrl.value) return
  try {
    await navigator.clipboard.writeText(qrUrl.value)
    copied.value = true
    setTimeout(() => { copied.value = false }, 2000)
  } catch {
    // Fallback: select text
  }
}

function printQr() {
  window.print()
}

onMounted(() => {
  fetchTable()
})
</script>

<template>
  <div>
    <!-- Header (hidden on print) -->
    <div class="flex items-center justify-between print:hidden">
      <div>
        <h1 class="text-2xl font-semibold text-gray-900 dark:text-white">QR Code Meja</h1>
        <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">Cetak QR code untuk ditempelkan di meja.</p>
      </div>
      <div class="flex gap-2">
        <BaseButton variant="secondary" size="sm" :icon="copied ? Check : Copy" @click="copyUrl">
          {{ copied ? 'Tersalin!' : 'Salin URL' }}
        </BaseButton>
        <BaseButton variant="primary" size="sm" :icon="Printer" @click="printQr">
          Cetak
        </BaseButton>
      </div>
    </div>

    <!-- Loading -->
    <div v-if="loading" class="mt-6 py-8 text-center text-sm text-gray-400">Memuat...</div>

    <!-- QR Code Display -->
    <div v-else-if="table" class="mt-8 flex flex-col items-center">
      <div class="rounded-2xl border border-gray-200 bg-white p-8 shadow-sm dark:border-gray-700 dark:bg-gray-800 print:border-0 print:shadow-none">
        <!-- Table name -->
        <h2 class="text-center text-xl font-bold text-gray-900 dark:text-white">
          {{ table.name }}
        </h2>

        <!-- QR Code Image -->
        <div class="mt-6 flex justify-center">
          <img
            :src="qrImageUrl"
            :alt="`QR Code - ${table.name}`"
            class="h-64 w-64 rounded-lg"
          />
        </div>

        <!-- URL display -->
        <p class="mt-4 text-center text-xs text-gray-400 font-mono break-all max-w-[300px]">
          {{ qrUrl }}
        </p>

        <!-- Instructions for customer -->
        <div class="mt-6 rounded-lg bg-gray-50 p-4 text-center dark:bg-gray-700/30 print:bg-gray-100">
          <p class="text-sm font-medium text-gray-700 dark:text-gray-300">Scan QR code untuk memesan</p>
          <p class="mt-1 text-xs text-gray-500 dark:text-gray-400">Buka kamera HP dan arahkan ke QR code di atas</p>
        </div>
      </div>
    </div>

    <!-- Not found -->
    <div v-else class="mt-6 py-8 text-center text-sm text-gray-400">Meja tidak ditemukan.</div>
  </div>
</template>

<style scoped>
@media print {
  /* Hide everything except the QR content during print */
}
</style>
