<script setup lang="ts">
import { download } from '@purdia/http'
import BaseButton from '@purdia/ui/src/components/BaseButton.vue'
import { Download, FileText, ListTodo, Bookmark, Wallet, Database } from '@lucide/vue'

interface ExportOption {
  label: string
  description: string
  icon: typeof Download
  endpoint: string
  filename: string
}

const exports: ExportOption[] = [
  {
    label: 'Catatan (JSON)',
    description: 'Semua catatan termasuk konten dan metadata.',
    icon: FileText,
    endpoint: '/export/notes',
    filename: 'notes.json',
  },
  {
    label: 'Tugas (JSON)',
    description: 'Semua tugas beserta status dan prioritas.',
    icon: ListTodo,
    endpoint: '/export/tasks',
    filename: 'tasks.json',
  },
  {
    label: 'Bookmark (JSON)',
    description: 'Semua bookmark termasuk URL dan deskripsi.',
    icon: Bookmark,
    endpoint: '/export/bookmarks',
    filename: 'bookmarks.json',
  },
  {
    label: 'Keuangan (CSV)',
    description: 'Semua transaksi dalam format CSV.',
    icon: Wallet,
    endpoint: '/export/finance',
    filename: 'finance.csv',
  },
  {
    label: 'Semua Data (JSON)',
    description: 'Export lengkap semua data kamu dalam satu file.',
    icon: Database,
    endpoint: '/export/all',
    filename: 'all-data.json',
  },
]

async function handleExport(option: ExportOption) {
  try {
    await download(option.endpoint, option.filename)
  } catch {
    // handle error
  }
}
</script>

<template>
  <div class="space-y-6">
    <!-- Header -->
    <div>
      <div class="flex items-center gap-3">
        <Download :size="24" class="text-gray-600 dark:text-gray-400" />
        <h1 class="text-2xl font-bold text-gray-900 dark:text-white">Export & Backup</h1>
      </div>
      <p class="mt-2 text-sm text-gray-500 dark:text-gray-400">
        Download data kamu dalam berbagai format. File akan langsung terunduh ke perangkat.
      </p>
    </div>

    <!-- Export options -->
    <div class="grid gap-4 sm:grid-cols-2">
      <div
        v-for="option in exports"
        :key="option.endpoint"
        class="flex flex-col justify-between rounded-xl border border-gray-200 bg-white p-5 dark:border-gray-700 dark:bg-gray-800"
      >
        <div class="mb-4">
          <div class="flex items-center gap-3">
            <component :is="option.icon" :size="20" class="text-gray-600 dark:text-gray-400" />
            <h3 class="text-sm font-semibold text-gray-900 dark:text-white">{{ option.label }}</h3>
          </div>
          <p class="mt-2 text-xs text-gray-500 dark:text-gray-400">{{ option.description }}</p>
        </div>
        <BaseButton variant="secondary" size="sm" :icon="Download" @click="handleExport(option)">
          Download
        </BaseButton>
      </div>
    </div>

    <!-- Info -->
    <div class="rounded-lg border border-blue-200 bg-blue-50 p-4 text-sm text-blue-700 dark:border-blue-800 dark:bg-blue-900/20 dark:text-blue-300">
      <p>Data export berisi semua informasi yang kamu simpan di aplikasi ini. Simpan file backup di tempat yang aman.</p>
    </div>
  </div>
</template>
