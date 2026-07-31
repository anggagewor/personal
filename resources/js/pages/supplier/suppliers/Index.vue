<script setup lang="ts">
import { ref, computed, watch } from 'vue'
import { useRoute, useRouter } from 'vue-router'
import { useToast } from '@purdia/toast'
import BaseButton from '@purdia/ui/src/components/BaseButton.vue'
import BaseInput from '@purdia/ui/src/components/BaseInput.vue'
import { Plus, Search, ChevronLeft, ChevronRight, Truck } from '@lucide/vue'
import type { Supplier } from '@/types/supplier'
import * as supplierApi from '@/api/supplier'
import SupplierForm from './SupplierForm.vue'

const route = useRoute()
const router = useRouter()
const toast = useToast()

const outletId = computed(() => Number(route.query.outlet))

const suppliers = ref<Supplier[]>([])
const loading = ref(true)
const currentPage = ref(1)
const totalPages = ref(1)
const perPage = 20

// Search
const searchQuery = ref('')
const searchTimeout = ref<ReturnType<typeof setTimeout> | null>(null)

// Form modal
const showForm = ref(false)
const editingSupplier = ref<Supplier | null>(null)

function formatRupiah(value: number): string {
  return new Intl.NumberFormat('id-ID', { style: 'currency', currency: 'IDR', maximumFractionDigits: 0 }).format(value)
}

async function fetchSuppliers() {
  if (!outletId.value) return
  loading.value = true
  try {
    if (searchQuery.value.trim()) {
      const res = await supplierApi.searchSuppliers(outletId.value, { search: searchQuery.value.trim() })
      suppliers.value = res.data
      totalPages.value = 1
    } else {
      const res = await supplierApi.fetchSuppliers(outletId.value, { page: currentPage.value, per_page: perPage })
      if (Array.isArray(res.data)) {
        suppliers.value = res.data
      } else {
        const paginated = res.data as unknown as { data: Supplier[]; last_page: number }
        suppliers.value = paginated.data ?? res.data
        totalPages.value = paginated.last_page ?? 1
      }
    }
  } catch {
    // Error handled globally
  } finally {
    loading.value = false
  }
}

function onSearchInput() {
  if (searchTimeout.value) clearTimeout(searchTimeout.value)
  searchTimeout.value = setTimeout(() => {
    currentPage.value = 1
    fetchSuppliers()
  }, 400)
}

function openCreate() {
  editingSupplier.value = null
  showForm.value = true
}

function goToDetail(supplier: Supplier) {
  router.push({ name: 'supplier.suppliers.detail', query: { outlet: outletId.value, id: supplier.id } })
}

function goToPage(page: number) {
  if (page < 1 || page > totalPages.value) return
  currentPage.value = page
}

function onSaved() {
  fetchSuppliers()
}

watch(currentPage, () => fetchSuppliers())

// Initial load
fetchSuppliers()
</script>

<template>
  <div>
    <!-- Header -->
    <div class="flex items-center justify-between">
      <div>
        <h1 class="text-2xl font-semibold text-gray-900 dark:text-white">Supplier</h1>
        <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">Kelola data supplier outlet.</p>
      </div>
      <BaseButton variant="primary" size="sm" :icon="Plus" @click="openCreate">
        Tambah Supplier
      </BaseButton>
    </div>

    <!-- Search -->
    <div class="mt-6 max-w-sm">
      <BaseInput
        v-model="searchQuery"
        placeholder="Cari nama, telepon, atau email..."
        :icon="Search"
        @input="onSearchInput"
      />
    </div>

    <!-- Loading -->
    <div v-if="loading" class="mt-6 py-8 text-center text-sm text-gray-400">Memuat data...</div>

    <!-- Empty state -->
    <div v-else-if="!suppliers.length" class="mt-12 flex flex-col items-center text-center">
      <div class="flex h-16 w-16 items-center justify-center rounded-2xl bg-gray-100 dark:bg-gray-800">
        <Truck :size="28" class="text-gray-400" />
      </div>
      <h3 class="mt-4 text-base font-medium text-gray-900 dark:text-white">
        {{ searchQuery ? 'Supplier tidak ditemukan' : 'Belum ada supplier' }}
      </h3>
      <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">
        {{ searchQuery ? 'Coba kata kunci lain.' : 'Tambahkan supplier pertama untuk outlet ini.' }}
      </p>
      <BaseButton v-if="!searchQuery" variant="primary" size="sm" :icon="Plus" class="mt-4" @click="openCreate">
        Tambah Supplier
      </BaseButton>
    </div>

    <!-- Supplier table -->
    <div v-else class="mt-6 overflow-x-auto rounded-xl border border-gray-200 bg-white dark:border-gray-700 dark:bg-gray-800">
      <table class="w-full text-left text-sm">
        <thead>
          <tr class="border-b border-gray-200 dark:border-gray-700">
            <th class="px-4 py-3 font-medium text-gray-500 dark:text-gray-400">Nama</th>
            <th class="px-4 py-3 font-medium text-gray-500 dark:text-gray-400">Telepon</th>
            <th class="px-4 py-3 font-medium text-gray-500 dark:text-gray-400 text-right">Total Utang</th>
          </tr>
        </thead>
        <tbody>
          <tr
            v-for="supplier in suppliers"
            :key="supplier.id"
            class="border-b border-gray-100 last:border-0 hover:bg-gray-50 dark:border-gray-700/50 dark:hover:bg-gray-700/30 cursor-pointer"
            @click="goToDetail(supplier)"
          >
            <td class="px-4 py-3 font-medium text-gray-900 dark:text-white">
              {{ supplier.name }}
            </td>
            <td class="px-4 py-3 text-gray-600 dark:text-gray-300">
              {{ supplier.phone ?? '—' }}
            </td>
            <td class="px-4 py-3 text-right font-medium" :class="supplier.total_debt > 0 ? 'text-red-600 dark:text-red-400' : 'text-gray-600 dark:text-gray-300'">
              {{ formatRupiah(supplier.total_debt) }}
            </td>
          </tr>
        </tbody>
      </table>

      <!-- Pagination -->
      <div
        v-if="totalPages > 1"
        class="flex items-center justify-between border-t border-gray-200 px-4 py-3 dark:border-gray-700"
      >
        <span class="text-xs text-gray-500 dark:text-gray-400">
          Halaman {{ currentPage }} dari {{ totalPages }}
        </span>
        <div class="flex gap-1">
          <button
            class="rounded p-1.5 text-gray-400 hover:text-gray-600 disabled:opacity-30 dark:hover:text-gray-300"
            :disabled="currentPage <= 1"
            @click="goToPage(currentPage - 1)"
          >
            <ChevronLeft :size="16" />
          </button>
          <button
            class="rounded p-1.5 text-gray-400 hover:text-gray-600 disabled:opacity-30 dark:hover:text-gray-300"
            :disabled="currentPage >= totalPages"
            @click="goToPage(currentPage + 1)"
          >
            <ChevronRight :size="16" />
          </button>
        </div>
      </div>
    </div>

    <!-- Supplier Form Modal -->
    <SupplierForm
      v-model="showForm"
      :outlet-id="outletId"
      :supplier="editingSupplier"
      @saved="onSaved"
    />
  </div>
</template>
