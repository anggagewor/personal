<script setup lang="ts">
import { ref, computed, onMounted } from 'vue'
import { useRoute, useRouter } from 'vue-router'
import { useToast } from '@purdia/toast'
import BaseButton from '@purdia/ui/src/components/BaseButton.vue'
import { ArrowLeft, Pencil, Trash2, Package, CreditCard } from '@lucide/vue'
import type { Supplier, SupplierProduct, SupplierPayment } from '@/types/supplier'
import * as supplierApi from '@/api/supplier'
import SupplierForm from './SupplierForm.vue'

const route = useRoute()
const router = useRouter()
const toast = useToast()

const outletId = computed(() => Number(route.query.outlet))
const supplierId = computed(() => Number(route.query.id))

const supplier = ref<Supplier | null>(null)
const products = ref<SupplierProduct[]>([])
const payments = ref<SupplierPayment[]>([])
const loading = ref(true)
const activeTab = ref<'products' | 'payments'>('products')

// Form modal
const showForm = ref(false)

function formatRupiah(value: number): string {
  return new Intl.NumberFormat('id-ID', { style: 'currency', currency: 'IDR', maximumFractionDigits: 0 }).format(value)
}

async function fetchSupplier() {
  if (!supplierId.value) return
  loading.value = true
  try {
    const res = await supplierApi.fetchSupplier(supplierId.value)
    supplier.value = res.data
  } catch {
    // Error handled globally
  } finally {
    loading.value = false
  }
}

async function fetchProducts() {
  if (!supplierId.value) return
  try {
    const res = await supplierApi.fetchSupplierProducts(supplierId.value)
    products.value = res.data
  } catch {
    // Error handled globally
  }
}

async function fetchPayments() {
  if (!supplierId.value) return
  try {
    const res = await supplierApi.fetchPaymentsBySupplier(supplierId.value)
    payments.value = res.data
  } catch {
    // Error handled globally
  }
}

function openEdit() {
  showForm.value = true
}

async function deleteSupplier() {
  if (!supplier.value) return
  if (!confirm(`Hapus supplier "${supplier.value.name}"? Data pembelian tetap tersimpan.`)) return
  try {
    await supplierApi.deleteSupplier(supplier.value.id)
    toast.success('Supplier berhasil dihapus.')
    router.push({ name: 'supplier.suppliers', query: { outlet: outletId.value } })
  } catch {
    // Error handled globally
  }
}

function goBack() {
  router.push({ name: 'supplier.suppliers', query: { outlet: outletId.value } })
}

function onSaved() {
  fetchSupplier()
}

function formatPaymentMethod(method: string): string {
  switch (method) {
    case 'cash': return 'Tunai'
    case 'bank_transfer': return 'Transfer Bank'
    case 'e_wallet': return 'E-Wallet'
    default: return method
  }
}

onMounted(() => {
  fetchSupplier()
  fetchProducts()
  fetchPayments()
})
</script>

<template>
  <div>
    <!-- Back button -->
    <button
      class="mb-4 flex items-center gap-1 text-sm text-gray-500 hover:text-gray-700 dark:text-gray-400 dark:hover:text-gray-200"
      @click="goBack"
    >
      <ArrowLeft :size="16" />
      Kembali ke daftar supplier
    </button>

    <!-- Loading -->
    <div v-if="loading" class="py-8 text-center text-sm text-gray-400">Memuat data...</div>

    <template v-else-if="supplier">
      <!-- Header -->
      <div class="flex items-start justify-between">
        <div>
          <h1 class="text-2xl font-semibold text-gray-900 dark:text-white">{{ supplier.name }}</h1>
          <p v-if="supplier.address" class="mt-1 text-sm text-gray-500 dark:text-gray-400">{{ supplier.address }}</p>
        </div>
        <div class="flex gap-2">
          <BaseButton variant="secondary" size="sm" :icon="Pencil" @click="openEdit">
            Edit
          </BaseButton>
          <BaseButton variant="danger" size="sm" :icon="Trash2" @click="deleteSupplier">
            Hapus
          </BaseButton>
        </div>
      </div>

      <!-- Info cards -->
      <div class="mt-6 grid grid-cols-1 gap-4 sm:grid-cols-2 lg:grid-cols-4">
        <div class="rounded-xl border border-gray-200 bg-white p-4 dark:border-gray-700 dark:bg-gray-800">
          <p class="text-xs text-gray-500 dark:text-gray-400">Telepon</p>
          <p class="mt-1 text-sm font-medium text-gray-900 dark:text-white">{{ supplier.phone ?? '—' }}</p>
        </div>
        <div class="rounded-xl border border-gray-200 bg-white p-4 dark:border-gray-700 dark:bg-gray-800">
          <p class="text-xs text-gray-500 dark:text-gray-400">Email</p>
          <p class="mt-1 text-sm font-medium text-gray-900 dark:text-white">{{ supplier.email ?? '—' }}</p>
        </div>
        <div class="rounded-xl border border-gray-200 bg-white p-4 dark:border-gray-700 dark:bg-gray-800">
          <p class="text-xs text-gray-500 dark:text-gray-400">Rekening Bank</p>
          <p class="mt-1 text-sm font-medium text-gray-900 dark:text-white">
            <template v-if="supplier.bank_name">
              {{ supplier.bank_name }} — {{ supplier.bank_account_number }}
              <span class="block text-xs text-gray-500 dark:text-gray-400">a.n. {{ supplier.bank_account_holder }}</span>
            </template>
            <template v-else>—</template>
          </p>
        </div>
        <div class="rounded-xl border border-red-200 bg-red-50 p-4 dark:border-red-900/50 dark:bg-red-900/20">
          <p class="text-xs text-red-600 dark:text-red-400">Total Utang</p>
          <p class="mt-1 text-sm font-semibold text-red-700 dark:text-red-300">{{ formatRupiah(supplier.total_debt) }}</p>
        </div>
      </div>

      <!-- Notes -->
      <div v-if="supplier.notes" class="mt-4 rounded-xl border border-gray-200 bg-white p-4 dark:border-gray-700 dark:bg-gray-800">
        <p class="text-xs text-gray-500 dark:text-gray-400">Catatan</p>
        <p class="mt-1 text-sm text-gray-700 dark:text-gray-300">{{ supplier.notes }}</p>
      </div>

      <!-- Tabs -->
      <div class="mt-8 border-b border-gray-200 dark:border-gray-700">
        <nav class="flex gap-6">
          <button
            class="border-b-2 pb-2 text-sm font-medium transition-colors"
            :class="activeTab === 'products' ? 'border-primary-600 text-primary-600 dark:border-primary-400 dark:text-primary-400' : 'border-transparent text-gray-500 hover:text-gray-700 dark:text-gray-400 dark:hover:text-gray-200'"
            @click="activeTab = 'products'"
          >
            <Package :size="14" class="mr-1 inline" />
            Produk Terkait
          </button>
          <button
            class="border-b-2 pb-2 text-sm font-medium transition-colors"
            :class="activeTab === 'payments' ? 'border-primary-600 text-primary-600 dark:border-primary-400 dark:text-primary-400' : 'border-transparent text-gray-500 hover:text-gray-700 dark:text-gray-400 dark:hover:text-gray-200'"
            @click="activeTab = 'payments'"
          >
            <CreditCard :size="14" class="mr-1 inline" />
            Riwayat Pembayaran
          </button>
        </nav>
      </div>

      <!-- Products tab -->
      <div v-if="activeTab === 'products'" class="mt-4">
        <div v-if="!products.length" class="py-8 text-center text-sm text-gray-400">
          Belum ada produk terkait.
        </div>
        <div v-else class="overflow-x-auto rounded-xl border border-gray-200 bg-white dark:border-gray-700 dark:bg-gray-800">
          <table class="w-full text-left text-sm">
            <thead>
              <tr class="border-b border-gray-200 dark:border-gray-700">
                <th class="px-4 py-3 font-medium text-gray-500 dark:text-gray-400">Produk</th>
                <th class="px-4 py-3 font-medium text-gray-500 dark:text-gray-400">Varian</th>
                <th class="px-4 py-3 font-medium text-gray-500 dark:text-gray-400 text-right">Harga Beli Default</th>
              </tr>
            </thead>
            <tbody>
              <tr
                v-for="product in products"
                :key="product.id"
                class="border-b border-gray-100 last:border-0 dark:border-gray-700/50"
              >
                <td class="px-4 py-3 font-medium text-gray-900 dark:text-white">{{ product.product_name }}</td>
                <td class="px-4 py-3 text-gray-600 dark:text-gray-300">{{ product.variant_name }}</td>
                <td class="px-4 py-3 text-right text-gray-600 dark:text-gray-300">
                  {{ product.default_unit_cost != null ? formatRupiah(product.default_unit_cost) : '—' }}
                </td>
              </tr>
            </tbody>
          </table>
        </div>
      </div>

      <!-- Payments tab -->
      <div v-if="activeTab === 'payments'" class="mt-4">
        <div v-if="!payments.length" class="py-8 text-center text-sm text-gray-400">
          Belum ada riwayat pembayaran.
        </div>
        <div v-else class="overflow-x-auto rounded-xl border border-gray-200 bg-white dark:border-gray-700 dark:bg-gray-800">
          <table class="w-full text-left text-sm">
            <thead>
              <tr class="border-b border-gray-200 dark:border-gray-700">
                <th class="px-4 py-3 font-medium text-gray-500 dark:text-gray-400">Tanggal</th>
                <th class="px-4 py-3 font-medium text-gray-500 dark:text-gray-400">Metode</th>
                <th class="px-4 py-3 font-medium text-gray-500 dark:text-gray-400 text-right">Jumlah</th>
                <th class="px-4 py-3 font-medium text-gray-500 dark:text-gray-400">Catatan</th>
              </tr>
            </thead>
            <tbody>
              <tr
                v-for="payment in payments"
                :key="payment.id"
                class="border-b border-gray-100 last:border-0 dark:border-gray-700/50"
              >
                <td class="px-4 py-3 text-gray-900 dark:text-white">{{ payment.payment_date }}</td>
                <td class="px-4 py-3 text-gray-600 dark:text-gray-300">{{ formatPaymentMethod(payment.payment_method) }}</td>
                <td class="px-4 py-3 text-right font-medium text-gray-900 dark:text-white">{{ formatRupiah(payment.amount) }}</td>
                <td class="px-4 py-3 text-gray-500 dark:text-gray-400">{{ payment.notes ?? '—' }}</td>
              </tr>
            </tbody>
          </table>
        </div>
      </div>
    </template>

    <!-- Supplier Form Modal (edit mode) -->
    <SupplierForm
      v-model="showForm"
      :outlet-id="outletId"
      :supplier="supplier"
      @saved="onSaved"
    />
  </div>
</template>
