<script setup lang="ts">
import { ref } from 'vue'
import { get, post, download } from '@purdia/http'
import { useToast } from '@purdia/toast'
import BaseButton from '@purdia/ui/src/components/BaseButton.vue'
import { Download, FileText, ListTodo, Bookmark, Wallet, Database, HardDrive, Cloud, RefreshCw, BookOpen, Loader2 } from '@lucide/vue'

const toast = useToast()

interface ExportOption {
  label: string
  description: string
  icon: typeof Download
  endpoint: string
  filename: string
}

interface DriveStatus {
  connected: boolean
  email?: string
}

const driveStatus = ref<DriveStatus>({ connected: false })
const backingUp = ref(false)
const syncingNotes = ref(false)
const loadingDrive = ref(true)

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

async function checkDriveStatus() {
  loadingDrive.value = true
  try {
    const res = await get<DriveStatus>('/drive/status')
    driveStatus.value = res.data
  } catch {
    driveStatus.value = { connected: false }
  } finally {
    loadingDrive.value = false
  }
}

async function backupToDrive() {
  backingUp.value = true
  try {
    await post('/drive/backup')
    toast.success('Backup berhasil diupload ke Google Drive (folder: Purdia Backups).')
  } catch {
    // Error handled globally
  } finally {
    backingUp.value = false
  }
}

async function syncNotesToDrive() {
  syncingNotes.value = true
  try {
    const res = await post<{ synced_count: number }>('/drive/sync-notes')
    toast.success(`${res.data.synced_count} catatan berhasil disinkronkan ke Google Drive.`)
  } catch {
    // Error handled globally
  } finally {
    syncingNotes.value = false
  }
}

checkDriveStatus()
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
        Download data kamu dalam berbagai format atau backup ke Google Drive.
      </p>
    </div>

    <!-- Google Drive Backup Section -->
    <div class="rounded-xl border border-gray-200 bg-white p-6 dark:border-gray-700 dark:bg-gray-800">
      <div class="flex items-center gap-3">
        <div class="flex h-10 w-10 items-center justify-center rounded-lg bg-blue-100 text-blue-600 dark:bg-blue-900/30 dark:text-blue-400">
          <HardDrive :size="20" />
        </div>
        <div>
          <h2 class="text-base font-semibold text-gray-900 dark:text-white">Google Drive</h2>
          <p v-if="driveStatus.connected" class="text-xs text-gray-500">
            Terhubung: <span class="font-medium">{{ driveStatus.email }}</span>
          </p>
          <p v-else class="text-xs text-gray-500">Belum terhubung — hubungkan di halaman Google Drive.</p>
        </div>
      </div>

      <div v-if="driveStatus.connected" class="mt-5 grid gap-3 sm:grid-cols-2">
        <button
          class="flex items-center gap-3 rounded-lg border border-gray-200 p-4 text-left transition-colors hover:border-primary-300 hover:bg-primary-50 dark:border-gray-700 dark:hover:border-primary-700 dark:hover:bg-primary-900/10"
          :disabled="backingUp"
          @click="backupToDrive"
        >
          <Cloud :size="20" class="shrink-0 text-blue-500" />
          <div>
            <p class="text-sm font-medium text-gray-900 dark:text-white">
              <Loader2 v-if="backingUp" :size="14" class="mr-1 inline animate-spin" />
              Backup Semua Data
            </p>
            <p class="text-xs text-gray-500">Upload file JSON lengkap ke folder "Purdia Backups".</p>
          </div>
        </button>

        <button
          class="flex items-center gap-3 rounded-lg border border-gray-200 p-4 text-left transition-colors hover:border-primary-300 hover:bg-primary-50 dark:border-gray-700 dark:hover:border-primary-700 dark:hover:bg-primary-900/10"
          :disabled="syncingNotes"
          @click="syncNotesToDrive"
        >
          <BookOpen :size="20" class="shrink-0 text-emerald-500" />
          <div>
            <p class="text-sm font-medium text-gray-900 dark:text-white">
              <Loader2 v-if="syncingNotes" :size="14" class="mr-1 inline animate-spin" />
              Sync Catatan
            </p>
            <p class="text-xs text-gray-500">Upload semua catatan sebagai file HTML ke "Purdia Notes".</p>
          </div>
        </button>
      </div>

      <div v-else-if="!loadingDrive" class="mt-4">
        <router-link
          to="/drive"
          class="inline-flex items-center gap-2 rounded-lg bg-primary-600 px-4 py-2 text-sm font-medium text-white hover:bg-primary-700"
        >
          <HardDrive :size="16" />
          Hubungkan Google Drive
        </router-link>
      </div>
    </div>

    <!-- Local Export options -->
    <div>
      <h2 class="mb-4 text-base font-semibold text-gray-900 dark:text-white">Download Lokal</h2>
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
    </div>

    <!-- Info -->
    <div class="rounded-lg border border-blue-200 bg-blue-50 p-4 text-sm text-blue-700 dark:border-blue-800 dark:bg-blue-900/20 dark:text-blue-300">
      <p>Data export berisi semua informasi yang kamu simpan di aplikasi ini. Simpan file backup di tempat yang aman.</p>
    </div>
  </div>
</template>
