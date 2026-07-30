<script setup lang="ts">
import { ref, computed } from 'vue'
import { useToast } from '@purdia/toast'
import { debounce } from '@purdia/utils'
import BaseInput from '@purdia/ui/src/components/BaseInput.vue'
import BaseModal from '@purdia/ui/src/components/BaseModal.vue'
import BaseButton from '@purdia/ui/src/components/BaseButton.vue'
import BasePagination from '@purdia/ui/src/components/BasePagination.vue'
import { Quote, Search, RefreshCw, Plus, Trash2 } from '@lucide/vue'
import type { QuoteItem, QuoteMeta } from '@/types/quote'
import * as quotesApi from '@/api/quotes'

const toast = useToast()

const quotes = ref<QuoteItem[]>([])
const todayQuote = ref<QuoteItem | null>(null)
const meta = ref<QuoteMeta>({ current_page: 1, last_page: 1, per_page: 10, total: 0 })
const search = ref('')
const loading = ref(false)
const showForm = ref(false)
const form = ref({ content: '', author: '' })

async function fetchToday() {
  try {
    const res = await quotesApi.fetchTodayQuote()
    todayQuote.value = res.data
  } catch {
    // Error toast handled globally
  }
}

async function fetchQuotes(page = 1) {
  loading.value = true
  try {
    const params: Record<string, unknown> = { page, per_page: 10 }
    if (search.value) params.search = search.value
    const res = await quotesApi.fetchQuotes(params as any)
    quotes.value = res.data
    if (res.meta) meta.value = res.meta
  } catch {
    // Error toast handled globally
  }
  loading.value = false
}

async function addQuote() {
  if (!form.value.content.trim()) return
  try {
    await quotesApi.createQuote({ content: form.value.content, author: form.value.author || null })
    toast.success('Quote berhasil ditambahkan.')
    form.value = { content: '', author: '' }
    showForm.value = false
    fetchQuotes(1)
  } catch {
    // Error toast handled globally
  }
}

async function deleteQuote(quote: QuoteItem) {
  try {
    await quotesApi.deleteQuote(quote.id)
    toast.success('Quote berhasil dihapus.')
    quotes.value = quotes.value.filter((q) => q.id !== quote.id)
  } catch {
    // Error toast handled globally
  }
}

const hasQuotes = computed(() => quotes.value.length > 0)

const onSearch = debounce(() => fetchQuotes(1), 300)

function goToPage(page: number) {
  fetchQuotes(page)
}

fetchToday()
fetchQuotes()
</script>

<template>
  <div>
    <div class="flex items-center justify-between">
      <div>
        <h1 class="text-2xl font-semibold text-gray-900 dark:text-white">Quotes</h1>
        <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">Kumpulan kutipan motivasi harian.</p>
      </div>
      <BaseButton variant="primary" size="sm" :icon="Plus" @click="showForm = true">Tambah</BaseButton>
    </div>

    <!-- Quote of the Day -->
    <div v-if="todayQuote" class="mt-6 rounded-xl border border-primary-200 bg-primary-50 p-6 dark:border-primary-800 dark:bg-primary-900/20">
      <div class="flex items-start gap-3">
        <div class="flex h-10 w-10 shrink-0 items-center justify-center rounded-lg bg-primary-100 text-primary-600 dark:bg-primary-900/40 dark:text-primary-400">
          <Quote :size="20" />
        </div>
        <div>
          <p class="text-xs font-medium uppercase tracking-wide text-primary-600 dark:text-primary-400">Quote Hari Ini</p>
          <p class="mt-2 text-lg font-medium italic text-gray-900 dark:text-white">"{{ todayQuote.content }}"</p>
          <p v-if="todayQuote.author" class="mt-2 text-sm text-gray-600 dark:text-gray-400">— {{ todayQuote.author }}</p>
        </div>
      </div>
    </div>

    <!-- Search -->
    <div class="mt-6">
      <BaseInput
        v-model="search"
        placeholder="Cari kutipan atau penulis..."
        :icon="Search"
        @input="onSearch"
      />
    </div>

    <!-- Loading -->
    <div v-if="loading" class="mt-8 flex justify-center">
      <RefreshCw :size="20" class="animate-spin text-gray-400" />
    </div>

    <!-- Quote List -->
    <div v-else-if="hasQuotes" class="mt-6 space-y-4">
      <div
        v-for="quote in quotes"
        :key="quote.id"
        class="group rounded-xl border border-gray-200 bg-white p-5 dark:border-gray-700 dark:bg-gray-800"
      >
        <div class="flex items-start justify-between gap-3">
          <div class="flex-1">
            <p class="text-sm italic text-gray-700 dark:text-gray-300">"{{ quote.content }}"</p>
            <p v-if="quote.author" class="mt-2 text-xs text-gray-500 dark:text-gray-400">— {{ quote.author }}</p>
          </div>
          <button class="shrink-0 rounded p-1 text-gray-400 opacity-0 group-hover:opacity-100 hover:text-red-600 transition-opacity" @click="deleteQuote(quote)">
            <Trash2 :size="16" />
          </button>
        </div>
      </div>
    </div>

    <!-- Empty -->
    <div v-else class="mt-12 text-center">
      <Quote :size="40" class="mx-auto text-gray-300 dark:text-gray-600" />
      <p class="mt-3 text-gray-400">{{ search ? 'Tidak ada kutipan yang cocok.' : 'Belum ada kutipan.' }}</p>
    </div>

    <!-- Pagination -->
    <div v-if="meta.last_page > 1" class="mt-6">
      <BasePagination
        :current-page="meta.current_page"
        :total-pages="meta.last_page"
        @update:current-page="goToPage"
      />
    </div>

    <!-- Add Quote Modal -->
    <BaseModal v-model="showForm" size="sm">
      <template #default>
        <h2 class="text-lg font-semibold text-gray-900 dark:text-white">Tambah Quote</h2>
        <form class="mt-4 space-y-4" @submit.prevent="addQuote">
          <div>
            <label class="mb-1 block text-sm font-medium text-gray-700 dark:text-gray-300">Kutipan</label>
            <textarea
              v-model="form.content"
              rows="3"
              required
              placeholder="Tulis kutipan motivasi..."
              class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm focus:border-primary-500 focus:outline-none focus:ring-1 focus:ring-primary-500 dark:border-gray-600 dark:bg-gray-700 dark:text-white"
            />
          </div>
          <BaseInput v-model="form.author" label="Penulis (opsional)" placeholder="Contoh: Albert Einstein" />
          <div class="flex justify-end gap-2 pt-2">
            <BaseButton variant="secondary" size="sm" type="button" @click="showForm = false">Batal</BaseButton>
            <BaseButton variant="primary" size="sm" type="submit">Simpan</BaseButton>
          </div>
        </form>
      </template>
    </BaseModal>
  </div>
</template>
