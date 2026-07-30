<script setup lang="ts">
import { ref, watch } from 'vue'
import { useToast } from '@purdia/toast'
import { formatCurrency } from '@purdia/utils'
import BaseButton from '@purdia/ui/src/components/BaseButton.vue'
import BaseInput from '@purdia/ui/src/components/BaseInput.vue'
import BaseSelect from '@purdia/ui/src/components/BaseSelect.vue'
import BaseModal from '@purdia/ui/src/components/BaseModal.vue'
import BaseProgress from '@purdia/ui/src/components/BaseProgress.vue'
import { Plus, Trash2, PiggyBank, AlertTriangle } from '@lucide/vue'
import type { BudgetItem, BudgetSummary } from '@/types/budget'
import * as budgetsApi from '@/api/budgets'

const toast = useToast()

const summary = ref<BudgetSummary>({ budgets: [], total_budget: 0, total_spent: 0, total_remaining: 0 })
const currentMonth = ref(new Date().toISOString().slice(0, 7))
const showForm = ref(false)
const loading = ref(true)
const form = ref({ category: '', amount: '', month: currentMonth.value })

const expenseCategories = ['Makanan', 'Transport', 'Hiburan', 'Belanja', 'Tagihan', 'Kesehatan', 'Pendidikan', 'Lainnya']

async function fetchData() {
  loading.value = true
  try {
    const res = await budgetsApi.fetchBudgetSummary({ month: currentMonth.value })
    summary.value = res.data
  } catch {
    // Error handled globally
  } finally {
    loading.value = false
  }
}

async function addBudget() {
  if (!form.value.category || !form.value.amount) return
  try {
    await budgetsApi.createBudget({ category: form.value.category, amount: parseFloat(form.value.amount), month: currentMonth.value })
    toast.success('Budget berhasil disimpan.')
    showForm.value = false
    form.value = { category: '', amount: '', month: currentMonth.value }
    fetchData()
  } catch {
    // Error handled globally
  }
}

async function deleteBudget(item: BudgetItem) {
  try {
    await budgetsApi.deleteBudget(item.id)
    toast.success('Budget berhasil dihapus.')
    fetchData()
  } catch {
    // Error handled globally
  }
}

function progressColor(item: BudgetItem): string {
  if (item.is_exceeded) return 'bg-red-500'
  if (item.is_near_limit) return 'bg-amber-500'
  return 'bg-emerald-500'
}

watch(currentMonth, () => fetchData())
fetchData()
</script>

<template>
  <div>
    <div class="flex items-center justify-between">
      <div>
        <h1 class="text-2xl font-semibold text-gray-900 dark:text-white">Budget</h1>
        <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">Atur batas pengeluaran per kategori.</p>
      </div>
      <div class="flex items-center gap-3">
        <input type="month" v-model="currentMonth" class="rounded-lg border border-gray-300 bg-white px-3 py-1.5 text-sm dark:border-gray-600 dark:bg-gray-800 dark:text-white" />
        <BaseButton variant="primary" size="sm" :icon="Plus" @click="showForm = true">Budget</BaseButton>
      </div>
    </div>

    <!-- Summary cards -->
    <div class="mt-6 grid grid-cols-1 gap-4 sm:grid-cols-3">
      <div class="rounded-xl border border-gray-200 bg-white p-5 dark:border-gray-700 dark:bg-gray-800">
        <div class="flex items-center gap-3">
          <div class="flex h-10 w-10 items-center justify-center rounded-lg bg-blue-100 text-blue-600 dark:bg-blue-900/30 dark:text-blue-400"><PiggyBank :size="20" /></div>
          <div>
            <p class="text-lg font-bold text-gray-900 dark:text-white">{{ formatCurrency(summary.total_budget) }}</p>
            <p class="text-xs text-gray-500">Total Budget</p>
          </div>
        </div>
      </div>
      <div class="rounded-xl border border-gray-200 bg-white p-5 dark:border-gray-700 dark:bg-gray-800">
        <div class="flex items-center gap-3">
          <div class="flex h-10 w-10 items-center justify-center rounded-lg bg-red-100 text-red-600 dark:bg-red-900/30 dark:text-red-400"><AlertTriangle :size="20" /></div>
          <div>
            <p class="text-lg font-bold text-gray-900 dark:text-white">{{ formatCurrency(summary.total_spent) }}</p>
            <p class="text-xs text-gray-500">Total Terpakai</p>
          </div>
        </div>
      </div>
      <div class="rounded-xl border border-gray-200 bg-white p-5 dark:border-gray-700 dark:bg-gray-800">
        <div class="flex items-center gap-3">
          <div class="flex h-10 w-10 items-center justify-center rounded-lg bg-emerald-100 text-emerald-600 dark:bg-emerald-900/30 dark:text-emerald-400"><PiggyBank :size="20" /></div>
          <div>
            <p class="text-lg font-bold text-emerald-600 dark:text-emerald-400">{{ formatCurrency(summary.total_remaining) }}</p>
            <p class="text-xs text-gray-500">Sisa Budget</p>
          </div>
        </div>
      </div>
    </div>

    <!-- Budget list -->
    <div class="mt-6 space-y-3">
      <div
        v-for="item in summary.budgets"
        :key="item.id"
        class="group rounded-xl border border-gray-200 bg-white p-5 dark:border-gray-700 dark:bg-gray-800"
      >
        <div class="flex items-center justify-between">
          <div class="flex items-center gap-3">
            <h3 class="text-sm font-semibold text-gray-900 dark:text-white">{{ item.category }}</h3>
            <span v-if="item.is_exceeded" class="rounded-full bg-red-100 px-2 py-0.5 text-xs font-medium text-red-700 dark:bg-red-900/30 dark:text-red-300">Melebihi!</span>
            <span v-else-if="item.is_near_limit" class="rounded-full bg-amber-100 px-2 py-0.5 text-xs font-medium text-amber-700 dark:bg-amber-900/30 dark:text-amber-300">Hampir penuh</span>
          </div>
          <div class="flex items-center gap-3">
            <span class="text-sm text-gray-500">{{ formatCurrency(item.spent) }} / {{ formatCurrency(item.amount) }}</span>
            <button class="rounded p-1 text-gray-400 opacity-0 group-hover:opacity-100 hover:text-red-500" @click="deleteBudget(item)"><Trash2 :size="14" /></button>
          </div>
        </div>
        <div class="mt-3 h-2 w-full overflow-hidden rounded-full bg-gray-100 dark:bg-gray-700">
          <div class="h-full rounded-full transition-all" :class="progressColor(item)" :style="{ width: `${Math.min(item.percent_used, 100)}%` }" />
        </div>
        <div class="mt-1.5 flex justify-between text-xs text-gray-400">
          <span>{{ item.percent_used }}% terpakai</span>
          <span>Sisa: {{ formatCurrency(item.remaining) }}</span>
        </div>
      </div>
    </div>

    <div v-if="!loading && !summary.budgets.length" class="mt-12 flex flex-col items-center text-center">
      <PiggyBank :size="48" class="text-gray-300 dark:text-gray-600" />
      <p class="mt-3 text-sm text-gray-500 dark:text-gray-400">Belum ada budget untuk bulan ini.</p>
    </div>

    <!-- Form modal -->
    <BaseModal v-model="showForm" size="md">
      <template #default>
        <h2 class="text-lg font-semibold text-gray-900 dark:text-white">Tambah Budget</h2>
        <form class="mt-4 space-y-4" @submit.prevent="addBudget">
          <BaseSelect v-model="form.category" label="Kategori" :options="expenseCategories.map(c => ({ label: c, value: c }))" required />
          <BaseInput v-model="form.amount" label="Batas Pengeluaran (Rp)" type="number" placeholder="500000" required />
          <div class="flex justify-end gap-2 pt-2">
            <BaseButton variant="secondary" size="sm" type="button" @click="showForm = false">Batal</BaseButton>
            <BaseButton variant="primary" size="sm" type="submit">Simpan</BaseButton>
          </div>
        </form>
      </template>
    </BaseModal>
  </div>
</template>
