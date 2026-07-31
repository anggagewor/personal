<script setup lang="ts">
import { ref, watch } from 'vue'
import { useToast } from '@purdia/toast'
import { formatNumber } from '@purdia/utils'
import BaseSelect from '@purdia/ui/src/components/BaseSelect.vue'
import BaseBadge from '@purdia/ui/src/components/BaseBadge.vue'
import BaseSpinner from '@purdia/ui/src/components/BaseSpinner.vue'
import BaseEmptyState from '@purdia/ui/src/components/BaseEmptyState.vue'
import { Search, FileText, ChevronDown, ChevronRight, RefreshCw, HardDrive } from '@lucide/vue'
import type { LogEntry, LogFile, LogLevel, LogMeta } from '@/types/log-reader'
import * as logApi from '@/api/log-reader'

const toast = useToast()

const files = ref<LogFile[]>([])
const entries = ref<LogEntry[]>([])
const meta = ref<LogMeta | null>(null)
const selectedFile = ref('')
const selectedLevel = ref<LogLevel | ''>('')
const search = ref('')
const loading = ref(false)
const loadingMore = ref(false)
const expandedRows = ref<Set<number>>(new Set())

const levelOptions = [
  { label: 'Semua Level', value: '' },
  { label: 'Emergency', value: 'emergency' },
  { label: 'Alert', value: 'alert' },
  { label: 'Critical', value: 'critical' },
  { label: 'Error', value: 'error' },
  { label: 'Warning', value: 'warning' },
  { label: 'Notice', value: 'notice' },
  { label: 'Info', value: 'info' },
  { label: 'Debug', value: 'debug' },
]

const levelColorMap: Record<string, string> = {
  red: 'danger',
  orange: 'warning',
  yellow: 'warning',
  blue: 'info',
  green: 'success',
  gray: 'default',
}

async function fetchFiles() {
  try {
    const response = await logApi.fetchLogFiles()
    files.value = response.data
    if (files.value.length && !selectedFile.value) {
      selectedFile.value = files.value[0].name
    }
  } catch {
    // Error toast handled globally
  }
}

async function fetchEntries(append = false) {
  if (!selectedFile.value) return

  if (append) {
    loadingMore.value = true
  } else {
    loading.value = true
    entries.value = []
    expandedRows.value.clear()
  }

  try {
    const params: Record<string, unknown> = {
      file: selectedFile.value,
      per_page: 30,
    }

    if (append && meta.value?.next_offset) {
      params.offset = meta.value.next_offset
    }

    if (selectedLevel.value) {
      params.level = selectedLevel.value
    }

    if (search.value.trim()) {
      params.search = search.value.trim()
    }

    const response = await logApi.fetchLogEntries(params as Parameters<typeof logApi.fetchLogEntries>[0])

    if (append) {
      entries.value = [...entries.value, ...response.data]
    } else {
      entries.value = response.data
    }

    meta.value = response.meta
  } catch {
    // Error toast handled globally
  }

  loading.value = false
  loadingMore.value = false
}

function loadMore() {
  if (meta.value?.has_more && !loadingMore.value) {
    fetchEntries(true)
  }
}

function refresh() {
  fetchEntries(false)
  toast.success('Log di-refresh.')
}

function toggleRow(index: number) {
  if (expandedRows.value.has(index)) {
    expandedRows.value.delete(index)
  } else {
    expandedRows.value.add(index)
  }
}

function formatFileSize(bytes: number): string {
  if (bytes < 1024) return `${bytes} B`
  if (bytes < 1024 * 1024) return `${(bytes / 1024).toFixed(1)} KB`
  if (bytes < 1024 * 1024 * 1024) return `${(bytes / (1024 * 1024)).toFixed(1)} MB`
  return `${(bytes / (1024 * 1024 * 1024)).toFixed(2)} GB`
}

let searchTimer: ReturnType<typeof setTimeout> | null = null
function onSearch() {
  if (searchTimer) clearTimeout(searchTimer)
  searchTimer = setTimeout(() => fetchEntries(false), 400)
}

watch(selectedFile, () => fetchEntries(false))
watch(selectedLevel, () => fetchEntries(false))

fetchFiles()
</script>

<template>
  <div>
    <!-- Header -->
    <div class="flex items-center justify-between">
      <div>
        <h1 class="text-2xl font-semibold text-gray-900 dark:text-white">Log Viewer</h1>
        <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">
          Baca log Laravel secara efisien — file besar tetap aman.
        </p>
      </div>
      <button
        class="flex items-center gap-1.5 rounded-lg border border-gray-300 px-3 py-2 text-sm font-medium text-gray-700 hover:bg-gray-50 dark:border-gray-600 dark:text-gray-300 dark:hover:bg-gray-700"
        @click="refresh"
      >
        <RefreshCw :size="14" />
        Refresh
      </button>
    </div>

    <!-- Filters -->
    <div class="mt-5 flex flex-col gap-3 sm:flex-row sm:items-center">
      <!-- File selector -->
      <BaseSelect
        v-model="selectedFile"
        :options="files.map(f => ({ label: `${f.name} (${formatFileSize(f.size)})`, value: f.name }))"
        placeholder="Pilih file log..."
        class="w-full sm:w-64"
      />

      <!-- Level filter -->
      <BaseSelect
        v-model="selectedLevel"
        :options="levelOptions"
        class="w-full sm:w-44"
      />

      <!-- Search -->
      <div class="relative flex-1">
        <Search :size="16" class="absolute left-3 top-1/2 -translate-y-1/2 text-gray-400" />
        <input
          v-model="search"
          type="text"
          placeholder="Cari pesan error..."
          class="w-full rounded-lg border border-gray-300 py-2 pl-9 pr-4 text-sm focus:border-primary-500 focus:outline-none focus:ring-1 focus:ring-primary-500 dark:border-gray-600 dark:bg-gray-700 dark:text-white"
          @input="onSearch"
        />
      </div>
    </div>

    <!-- File info -->
    <div v-if="meta" class="mt-3 flex items-center gap-4 text-xs text-gray-500 dark:text-gray-400">
      <span class="flex items-center gap-1">
        <HardDrive :size="12" />
        Ukuran file: {{ formatFileSize(meta.file_size) }}
      </span>
      <span>{{ formatNumber(entries.length) }} entri dimuat</span>
    </div>

    <!-- Entries -->
    <div v-if="loading" class="mt-8 flex justify-center">
      <BaseSpinner size="lg" />
    </div>

    <div v-else-if="entries.length" class="mt-4 space-y-1">
      <div
        v-for="(entry, idx) in entries"
        :key="idx"
        class="rounded-lg border border-gray-200 bg-white dark:border-gray-700 dark:bg-gray-800"
      >
        <!-- Entry row -->
        <div
          class="flex cursor-pointer items-center gap-3 px-3 py-2 hover:bg-gray-50 dark:hover:bg-gray-700/50"
          @click="toggleRow(idx)"
        >
          <component
            :is="expandedRows.has(idx) ? ChevronDown : ChevronRight"
            :size="14"
            class="shrink-0 text-gray-400"
          />

          <span class="shrink-0 font-mono text-xs text-gray-500 dark:text-gray-400">
            {{ entry.datetime }}
          </span>

          <BaseBadge
            :variant="levelColorMap[entry.level_color] || 'default'"
            size="sm"
            class="shrink-0 uppercase"
          >
            {{ entry.level }}
          </BaseBadge>

          <span class="truncate text-sm text-gray-900 dark:text-white">
            {{ entry.message }}
          </span>

          <span
            v-if="entry.has_stack_trace"
            class="ml-auto shrink-0 rounded bg-gray-100 px-1.5 py-0.5 text-xs text-gray-500 dark:bg-gray-700 dark:text-gray-400"
          >
            stack
          </span>
        </div>

        <!-- Expanded detail -->
        <div v-if="expandedRows.has(idx)" class="border-t border-gray-200 px-4 py-3 dark:border-gray-700">
          <div class="space-y-2">
            <div>
              <span class="text-xs font-medium text-gray-500 dark:text-gray-400">Environment:</span>
              <span class="ml-2 text-sm text-gray-900 dark:text-white">{{ entry.environment }}</span>
            </div>
            <div>
              <span class="text-xs font-medium text-gray-500 dark:text-gray-400">Pesan:</span>
              <p class="mt-1 text-sm text-gray-900 dark:text-white break-all">{{ entry.message }}</p>
            </div>
            <div v-if="entry.has_stack_trace">
              <span class="text-xs font-medium text-gray-500 dark:text-gray-400">Stack Trace:</span>
              <pre class="mt-1 max-h-64 overflow-auto rounded-md bg-gray-900 p-3 text-xs text-green-400 dark:bg-gray-950">{{ entry.stack_trace }}</pre>
            </div>
            <div v-if="Object.keys(entry.context).length">
              <span class="text-xs font-medium text-gray-500 dark:text-gray-400">Context:</span>
              <pre class="mt-1 max-h-32 overflow-auto rounded-md bg-gray-100 p-2 text-xs text-gray-700 dark:bg-gray-700 dark:text-gray-300">{{ JSON.stringify(entry.context, null, 2) }}</pre>
            </div>
          </div>
        </div>
      </div>

      <!-- Load more -->
      <div v-if="meta?.has_more" class="flex justify-center pt-4">
        <button
          class="flex items-center gap-2 rounded-lg border border-gray-300 px-4 py-2 text-sm font-medium text-gray-700 hover:bg-gray-50 dark:border-gray-600 dark:text-gray-300 dark:hover:bg-gray-700"
          :disabled="loadingMore"
          @click="loadMore"
        >
          <BaseSpinner v-if="loadingMore" size="sm" />
          <span>{{ loadingMore ? 'Memuat...' : 'Muat lebih banyak' }}</span>
        </button>
      </div>
    </div>

    <!-- Empty state -->
    <BaseEmptyState
      v-else-if="!loading && selectedFile"
      class="mt-12"
    >
      <template #icon>
        <FileText :size="48" class="text-gray-300 dark:text-gray-600" />
      </template>
      <template #title>Tidak ada log ditemukan</template>
      <template #description>
        {{ search || selectedLevel ? 'Coba ubah filter pencarian.' : 'File log kosong atau belum ada entri.' }}
      </template>
    </BaseEmptyState>
  </div>
</template>
