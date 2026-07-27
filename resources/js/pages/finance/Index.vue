<script setup lang="ts">
import { ref, computed, watch } from 'vue'
import { get, post, del } from '@purdia/http'
import { useToast } from '@purdia/toast'
import BaseButton from '@purdia/ui/src/components/BaseButton.vue'
import BaseInput from '@purdia/ui/src/components/BaseInput.vue'
import BaseSelect from '@purdia/ui/src/components/BaseSelect.vue'
import BaseModal from '@purdia/ui/src/components/BaseModal.vue'
import { formatCurrency, formatDate } from '@purdia/utils'
import { Plus, Trash2, TrendingUp, TrendingDown, Wallet } from '@lucide/vue'

const toast = useToast()

interface FinanceItem {
  id: number
  type: string
  category: string
  amount: number
  description: string | null
  date: string
}

interface Summary {
  income: number
  expense: number
  balance: number
  by_category: Array<{ category: string; total: number }>
}

const transactions = ref<FinanceItem[]>([])
const summary = ref<Summary>({ income: 0, expense: 0, balance: 0, by_category: [] })
const currentMonth = ref(new Date().toISOString().slice(0, 7))
const showForm = ref(false)
const form = ref({ type: 'expense', category: '', amount: '', description: '', date: new Date().toISOString().slice(0, 10) })

const categories = {
  expense: ['Makanan', 'Transport', 'Hiburan', 'Belanja', 'Tagihan', 'Kesehatan', 'Pendidikan', 'Lainnya'],
  income: ['Gaji', 'Freelance', 'Investasi', 'Hadiah', 'Lainnya'],
}

async function fetchData() {
  try {
    const [txRes, sumRes] = await Promise.all([
      get<FinanceItem[]>('/finances', { params: { month: currentMonth.value, per_page: 50 } }),
      get<Summary>('/finances/summary', { params: { month: currentMonth.value } }),
    ])
    transactions.value = txRes.data
    summary.value = sumRes.data
  } catch {
    // Error toast handled globally by @purdia/http onError
  }
}

async function addTransaction() {
  if (!form.value.category || !form.value.amount) return
  try {
    await post('/finances', { ...form.value, amount: parseFloat(form.value.amount) })
    toast.success('Transaksi berhasil ditambahkan.')
    showForm.value = false
    form.value = { type: 'expense', category: '', amount: '', description: '', date: new Date().toISOString().slice(0, 10) }
    fetchData()
  } catch {
    // Error toast handled globally by @purdia/http onError
  }
}

async function deleteTransaction(item: FinanceItem) {
  try {
    await del(`/finances/${item.id}`)
    toast.success('Transaksi berhasil dihapus.')
    fetchData()
  } catch {
    // Error toast handled globally by @purdia/http onError
  }
}

watch(currentMonth, () => fetchData())
fetchData()
</script>

<template>
  <div>
    <div class="flex items-center justify-between">
      <div>
        <h1 class="text-2xl font-semibold text-gray-900 dark:text-white">Keuangan</h1>
        <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">Catat pemasukan dan pengeluaran.</p>
      </div>
      <div class="flex items-center gap-3">
        <input type="month" v-model="currentMonth" class="rounded-lg border border-gray-300 bg-white px-3 py-1.5 text-sm dark:border-gray-600 dark:bg-gray-800 dark:text-white" />
        <BaseButton variant="primary" size="sm" :icon="Plus" @click="showForm = true">Transaksi</BaseButton>
      </div>
    </div>

    <!-- Summary cards -->
    <div class="mt-6 grid grid-cols-1 gap-4 sm:grid-cols-3">
      <div class="rounded-xl border border-gray-200 bg-white p-5 dark:border-gray-700 dark:bg-gray-800">
        <div class="flex items-center gap-3">
          <div class="flex h-10 w-10 items-center justify-center rounded-lg bg-emerald-100 text-emerald-600 dark:bg-emerald-900/30 dark:text-emerald-400"><TrendingUp :size="20" /></div>
          <div>
            <p class="text-lg font-bold text-gray-900 dark:text-white">{{ formatCurrency(summary.income) }}</p>
            <p class="text-xs text-gray-500">Pemasukan</p>
          </div>
        </div>
      </div>
      <div class="rounded-xl border border-gray-200 bg-white p-5 dark:border-gray-700 dark:bg-gray-800">
        <div class="flex items-center gap-3">
          <div class="flex h-10 w-10 items-center justify-center rounded-lg bg-red-100 text-red-600 dark:bg-red-900/30 dark:text-red-400"><TrendingDown :size="20" /></div>
          <div>
            <p class="text-lg font-bold text-gray-900 dark:text-white">{{ formatCurrency(summary.expense) }}</p>
            <p class="text-xs text-gray-500">Pengeluaran</p>
          </div>
        </div>
      </div>
      <div class="rounded-xl border border-gray-200 bg-white p-5 dark:border-gray-700 dark:bg-gray-800">
        <div class="flex items-center gap-3">
          <div class="flex h-10 w-10 items-center justify-center rounded-lg bg-blue-100 text-blue-600 dark:bg-blue-900/30 dark:text-blue-400"><Wallet :size="20" /></div>
          <div>
            <p class="text-lg font-bold" :class="summary.balance >= 0 ? 'text-emerald-600 dark:text-emerald-400' : 'text-red-600 dark:text-red-400'">{{ formatCurrency(summary.balance) }}</p>
            <p class="text-xs text-gray-500">Saldo</p>
          </div>
        </div>
      </div>
    </div>

    <!-- Transactions list -->
    <div class="mt-6 rounded-xl border border-gray-200 bg-white dark:border-gray-700 dark:bg-gray-800">
      <div class="divide-y divide-gray-100 dark:divide-gray-700">
        <div v-for="tx in transactions" :key="tx.id" class="group flex items-center gap-4 px-5 py-3">
          <div class="h-2.5 w-2.5 shrink-0 rounded-full" :class="tx.type === 'income' ? 'bg-emerald-500' : 'bg-red-500'" />
          <div class="flex-1 min-w-0">
            <p class="text-sm font-medium text-gray-900 dark:text-white">{{ tx.category }}</p>
            <p v-if="tx.description" class="text-xs text-gray-400 truncate">{{ tx.description }}</p>
          </div>
          <span class="text-sm font-semibold" :class="tx.type === 'income' ? 'text-emerald-600 dark:text-emerald-400' : 'text-red-600 dark:text-red-400'">
            {{ tx.type === 'income' ? '+' : '-' }}{{ formatCurrency(tx.amount) }}
          </span>
          <span class="text-xs text-gray-400">{{ formatDate(tx.date, { day: 'numeric', month: 'short' }) }}</span>
          <button class="rounded p-1 text-gray-400 opacity-0 group-hover:opacity-100 hover:text-red-500" @click="deleteTransaction(tx)"><Trash2 :size="14" /></button>
        </div>
      </div>
      <div v-if="!transactions.length" class="py-8 text-center text-sm text-gray-400">Belum ada transaksi bulan ini.</div>
    </div>

    <!-- Form modal -->
    <BaseModal v-model="showForm" size="md">
      <template #default>
        <h2 class="text-lg font-semibold text-gray-900 dark:text-white">Tambah Transaksi</h2>
        <form class="mt-4 space-y-4" @submit.prevent="addTransaction">
          <div class="flex gap-2">
            <button type="button" v-for="t in ['expense', 'income']" :key="t"
              class="flex-1 rounded-lg px-4 py-2 text-sm font-medium transition-colors"
              :class="form.type === t ? (t === 'expense' ? 'bg-red-500 text-white' : 'bg-emerald-500 text-white') : 'bg-gray-100 text-gray-600 dark:bg-gray-700 dark:text-gray-300'"
              @click="form.type = t; form.category = ''"
            >{{ t === 'expense' ? 'Pengeluaran' : 'Pemasukan' }}</button>
          </div>
          <BaseSelect v-model="form.category" label="Kategori" :options="categories[form.type as 'expense' | 'income'].map(c => ({ label: c, value: c }))" required />
          <BaseInput v-model="form.amount" label="Jumlah (Rp)" type="number" placeholder="50000" required />
          <BaseInput v-model="form.description" label="Keterangan" placeholder="Opsional" />
          <BaseInput v-model="form.date" label="Tanggal" type="date" required />
          <div class="flex justify-end gap-2 pt-2">
            <BaseButton variant="secondary" size="sm" type="button" @click="showForm = false">Batal</BaseButton>
            <BaseButton variant="primary" size="sm" type="submit">Simpan</BaseButton>
          </div>
        </form>
      </template>
    </BaseModal>
  </div>
</template>
