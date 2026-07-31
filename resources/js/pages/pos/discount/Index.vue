<script setup lang="ts">
import { ref, computed } from 'vue'
import { useRoute } from 'vue-router'
import { useToast } from '@purdia/toast'
import { formatCurrency } from '@purdia/utils'
import BaseButton from '@purdia/ui/src/components/BaseButton.vue'
import BaseBadge from '@purdia/ui/src/components/BaseBadge.vue'
import { Plus, Pencil, Trash2, Percent, Tag } from '@lucide/vue'
import type { Discount } from '@/types/pos'
import * as posApi from '@/api/pos'
import DiscountForm from './DiscountForm.vue'

const route = useRoute()
const toast = useToast()

const outletId = computed(() => Number(route.query.outlet))

const discounts = ref<Discount[]>([])
const loading = ref(true)

// Form modal
const showForm = ref(false)
const editingDiscount = ref<Discount | null>(null)

async function fetchDiscounts() {
  if (!outletId.value) return
  loading.value = true
  try {
    const res = await posApi.fetchDiscounts(outletId.value)
    discounts.value = res.data
  } catch {
    // Error handled globally
  } finally {
    loading.value = false
  }
}

function openCreate() {
  editingDiscount.value = null
  showForm.value = true
}

function openEdit(discount: Discount) {
  editingDiscount.value = discount
  showForm.value = true
}

async function toggleActive(discount: Discount) {
  try {
    await posApi.updateDiscount(discount.id, { is_active: !discount.is_active })
    discount.is_active = !discount.is_active
    toast.success(discount.is_active ? 'Diskon diaktifkan.' : 'Diskon dinonaktifkan.')
  } catch {
    // Error handled globally
  }
}

async function deleteDiscount(discount: Discount) {
  if (!confirm(`Hapus diskon "${discount.name}"?`)) return
  try {
    await posApi.deleteDiscount(discount.id)
    toast.success('Diskon berhasil dihapus.')
    fetchDiscounts()
  } catch {
    // Error handled globally
  }
}

function typeLabel(type: Discount['type']) {
  switch (type) {
    case 'percentage': return 'Persentase'
    case 'fixed': return 'Nominal'
    case 'buy_x_get_y': return 'Beli X Gratis Y'
  }
}

function valueLabel(discount: Discount) {
  if (discount.type === 'percentage') return `${discount.value}%`
  if (discount.type === 'fixed') return formatCurrency(discount.value)
  return `Beli ${discount.buy_quantity ?? 0} Gratis ${discount.get_quantity ?? 0}`
}

function onSaved() {
  fetchDiscounts()
}

// Initial load
fetchDiscounts()
</script>

<template>
  <div>
    <!-- Header -->
    <div class="flex items-center justify-between">
      <div>
        <h1 class="text-2xl font-semibold text-gray-900 dark:text-white">Diskon</h1>
        <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">Kelola aturan diskon outlet.</p>
      </div>
      <BaseButton variant="primary" size="sm" :icon="Plus" @click="openCreate">
        Diskon Baru
      </BaseButton>
    </div>

    <!-- Loading -->
    <div v-if="loading" class="mt-6 py-8 text-center text-sm text-gray-400">Memuat data...</div>

    <!-- Empty state -->
    <div v-else-if="!discounts.length" class="mt-12 flex flex-col items-center text-center">
      <div class="flex h-16 w-16 items-center justify-center rounded-2xl bg-gray-100 dark:bg-gray-800">
        <Percent :size="28" class="text-gray-400" />
      </div>
      <h3 class="mt-4 text-base font-medium text-gray-900 dark:text-white">Belum ada diskon</h3>
      <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">Buat aturan diskon pertama untuk outlet ini.</p>
      <BaseButton variant="primary" size="sm" :icon="Plus" class="mt-4" @click="openCreate">
        Buat Diskon
      </BaseButton>
    </div>

    <!-- Discount list -->
    <div v-else class="mt-6 space-y-3">
      <div
        v-for="discount in discounts"
        :key="discount.id"
        class="group flex items-center justify-between rounded-xl border border-gray-200 bg-white p-4 transition-shadow hover:shadow-md dark:border-gray-700 dark:bg-gray-800"
      >
        <div class="flex items-center gap-4">
          <!-- Icon -->
          <div class="flex h-10 w-10 shrink-0 items-center justify-center rounded-lg bg-primary-100 text-primary-600 dark:bg-primary-900/30 dark:text-primary-400">
            <Tag :size="20" />
          </div>

          <!-- Info -->
          <div>
            <div class="flex items-center gap-2">
              <h3 class="text-sm font-semibold text-gray-900 dark:text-white">{{ discount.name }}</h3>
              <BaseBadge :variant="discount.is_active ? 'success' : 'neutral'" size="sm">
                {{ discount.is_active ? 'Aktif' : 'Nonaktif' }}
              </BaseBadge>
              <BaseBadge v-if="discount.member_only" variant="info" size="sm">Member</BaseBadge>
            </div>
            <div class="mt-1 flex flex-wrap items-center gap-3 text-xs text-gray-500 dark:text-gray-400">
              <span>{{ typeLabel(discount.type) }}: <strong>{{ valueLabel(discount) }}</strong></span>
              <span v-if="discount.min_purchase">Min. {{ formatCurrency(discount.min_purchase) }}</span>
              <span>Prioritas: {{ discount.priority }}</span>
              <span v-if="discount.start_date || discount.end_date">
                {{ discount.start_date ?? '∞' }} — {{ discount.end_date ?? '∞' }}
              </span>
            </div>
          </div>
        </div>

        <!-- Actions -->
        <div class="flex items-center gap-2">
          <!-- Toggle active -->
          <button
            class="relative h-6 w-11 rounded-full transition-colors"
            :class="discount.is_active ? 'bg-primary-500' : 'bg-gray-300 dark:bg-gray-600'"
            @click="toggleActive(discount)"
            :title="discount.is_active ? 'Nonaktifkan' : 'Aktifkan'"
          >
            <span
              class="absolute top-0.5 left-0.5 h-5 w-5 rounded-full bg-white shadow transition-transform"
              :class="{ 'translate-x-5': discount.is_active }"
            />
          </button>

          <button
            class="rounded p-1.5 text-gray-400 opacity-0 group-hover:opacity-100 hover:text-primary-600 transition-all"
            @click="openEdit(discount)"
            title="Edit"
          >
            <Pencil :size="14" />
          </button>
          <button
            class="rounded p-1.5 text-gray-400 opacity-0 group-hover:opacity-100 hover:text-red-500 transition-all"
            @click="deleteDiscount(discount)"
            title="Hapus"
          >
            <Trash2 :size="14" />
          </button>
        </div>
      </div>
    </div>

    <!-- Discount Form Modal -->
    <DiscountForm
      v-model="showForm"
      :outlet-id="outletId"
      :editing-discount="editingDiscount"
      @saved="onSaved"
    />
  </div>
</template>
