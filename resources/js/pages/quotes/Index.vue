<script setup lang="ts">
import { ref, computed } from 'vue'
import { get } from '@purdia/http'
import BaseInput from '@purdia/ui/src/components/BaseInput.vue'
import BasePagination from '@purdia/ui/src/components/BasePagination.vue'
import { Quote, Search, RefreshCw } from '@lucide/vue'

interface QuoteItem {
  id: number
  content: string
  author: string | null
}

interface QuoteMeta {
  current_page: number
  last_page: number
  per_page: number
  total: number
}

const quotes = ref<QuoteItem[]>([])
const todayQuote = ref<QuoteItem | null>(null)
const meta = ref<QuoteMeta>({ current_page: 1, last_page: 1, per_page: 10, total: 0 })
const search = ref('')
const loading = ref(false)

let searchTimer: ReturnType<typeof setTimeout> | null = null

async function fetchToday() {
  try {
    const res = await get<QuoteItem | null>('/quotes/today')
    todayQuote.value = res.data
  } catch { /* */ }
}

async function fetchQuotes(page = 1) {
  loading.value = true
  try {
    const params: Record<string, unknown> = { page, per_page: 10 }
    if (search.value) params.search = search.value
    const res = await get<QuoteItem[]>('/quotes', { params })
    quotes.value = res.data
    if (res.meta) meta.value = res.meta
  } catch { /* */ }
  loading.value = false
}

function onSearch() {
  if (searchTimer) clearTimeout(searchTimer)
  searchTimer = setTimeout(() => fetchQuotes(1), 300)
}

function goToPage(page: number) {
  fetchQuotes(page)
}

const hasQuotes = computed(() => quotes.value.length > 0)

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
        class="rounded-xl border border-gray-200 bg-white p-5 dark:border-gray-700 dark:bg-gray-800"
      >
        <p class="text-sm italic text-gray-700 dark:text-gray-300">"{{ quote.content }}"</p>
        <p v-if="quote.author" class="mt-2 text-xs text-gray-500 dark:text-gray-400">— {{ quote.author }}</p>
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
  </div>
</template>
