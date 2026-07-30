<script setup lang="ts">
import { ref, computed } from 'vue'
import { useToast } from '@purdia/toast'
import { formatDate } from '@purdia/utils'
import BaseButton from '@purdia/ui/src/components/BaseButton.vue'
import BaseInput from '@purdia/ui/src/components/BaseInput.vue'
import BaseModal from '@purdia/ui/src/components/BaseModal.vue'
import {
  FolderPlus,
  Upload,
  Download,
  Trash2,
  Folder,
  FileText,
  Image,
  Film,
  Music,
  File,
  ArrowLeft,
  ExternalLink,
  RefreshCw,
  HardDrive,
  CloudOff,
  Loader2,
} from '@lucide/vue'
import type { DriveFile, ConnectionStatus } from '@/types/drive'
import * as driveApi from '@/api/drive'

const toast = useToast()

const files = ref<DriveFile[]>([])
const connectionStatus = ref<ConnectionStatus>({ connected: false })
const loading = ref(true)
const connecting = ref(false)
const folderStack = ref<Array<{ id: string; name: string }>>([])
const showNewFolder = ref(false)
const newFolderName = ref('')
const uploading = ref(false)

const currentFolderId = computed(() => {
  return folderStack.value.length > 0
    ? folderStack.value[folderStack.value.length - 1].id
    : null
})

async function checkConnection() {
  try {
    const res = await driveApi.fetchStatus()
    connectionStatus.value = res.data
    if (res.data.connected) {
      fetchFiles()
    } else {
      loading.value = false
    }
  } catch {
    loading.value = false
  }
}

async function connectDrive() {
  connecting.value = true
  try {
    const res = await driveApi.fetchAuthUrl()
    // Open OAuth popup
    const popup = window.open(res.data.url, 'google-oauth', 'width=600,height=700,left=200,top=100')

    // Listen for callback
    const handleMessage = async (event: MessageEvent) => {
      if (event.data?.type === 'google-oauth-callback' && event.data?.code) {
        window.removeEventListener('message', handleMessage)
        popup?.close()

        try {
          await driveApi.submitCallback(event.data.code)
          toast.success('Google Drive berhasil terhubung!')
          connectionStatus.value.connected = true
          fetchFiles()
        } catch {
          // Error handled globally
        }
      }
    }

    window.addEventListener('message', handleMessage)

    // Fallback: check if popup closed without auth
    const checkClosed = setInterval(() => {
      if (popup?.closed) {
        clearInterval(checkClosed)
        connecting.value = false
        window.removeEventListener('message', handleMessage)
      }
    }, 1000)
  } catch {
    // Error handled globally
  } finally {
    connecting.value = false
  }
}

async function disconnectDrive() {
  try {
    await driveApi.disconnect()
    connectionStatus.value = { connected: false }
    files.value = []
    folderStack.value = []
    toast.success('Google Drive berhasil diputus.')
  } catch {
    // Error handled globally
  }
}

async function fetchFiles() {
  loading.value = true
  try {
    const params: Record<string, string> = {}
    if (currentFolderId.value) params.folder_id = currentFolderId.value

    const res = await driveApi.fetchFiles(params)
    files.value = res.data
  } catch {
    // Error handled globally
  } finally {
    loading.value = false
  }
}

function openFolder(file: DriveFile) {
  folderStack.value.push({ id: file.id, name: file.name })
  fetchFiles()
}

function goBack() {
  folderStack.value.pop()
  fetchFiles()
}

function goToRoot() {
  folderStack.value = []
  fetchFiles()
}

async function uploadFile(event: Event) {
  const target = event.target as HTMLInputElement
  const file = target.files?.[0]
  if (!file) return

  uploading.value = true
  try {
    const formData = new FormData()
    formData.append('file', file)
    if (currentFolderId.value) formData.append('folder_id', currentFolderId.value)

    await driveApi.uploadFile(formData)
    toast.success('File berhasil diupload.')
    fetchFiles()
  } catch {
    // Error handled globally
  } finally {
    uploading.value = false
    target.value = ''
  }
}

async function downloadFile(file: DriveFile) {
  try {
    const response = await driveApi.downloadFile(file.id)
    const blob = new Blob([response.data as unknown as BlobPart])
    const url = URL.createObjectURL(blob)
    const a = document.createElement('a')
    a.href = url
    a.download = file.name
    a.click()
    URL.revokeObjectURL(url)
  } catch {
    // Fallback: open webViewLink
    if (file.webViewLink) window.open(file.webViewLink, '_blank')
  }
}

async function deleteFile(file: DriveFile) {
  try {
    await driveApi.deleteFile(file.id)
    files.value = files.value.filter((f) => f.id !== file.id)
    toast.success('File berhasil dihapus.')
  } catch {
    // Error handled globally
  }
}

async function createFolder() {
  if (!newFolderName.value.trim()) return
  try {
    await driveApi.createFolder({
      name: newFolderName.value.trim(),
      parent_id: currentFolderId.value,
    })
    toast.success('Folder berhasil dibuat.')
    showNewFolder.value = false
    newFolderName.value = ''
    fetchFiles()
  } catch {
    // Error handled globally
  }
}

function getFileIcon(mimeType: string) {
  if (mimeType.includes('folder')) return Folder
  if (mimeType.includes('image')) return Image
  if (mimeType.includes('video')) return Film
  if (mimeType.includes('audio')) return Music
  if (mimeType.includes('text') || mimeType.includes('document') || mimeType.includes('pdf')) return FileText
  return File
}

function formatSize(bytes: number | null): string {
  if (!bytes) return '-'
  if (bytes < 1024) return bytes + ' B'
  if (bytes < 1024 * 1024) return (bytes / 1024).toFixed(1) + ' KB'
  return (bytes / (1024 * 1024)).toFixed(1) + ' MB'
}

checkConnection()
</script>

<template>
  <div>
    <div class="flex items-center justify-between">
      <div>
        <h1 class="text-2xl font-semibold text-gray-900 dark:text-white">Google Drive</h1>
        <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">
          <template v-if="connectionStatus.connected">
            Terhubung sebagai <span class="font-medium text-gray-700 dark:text-gray-300">{{ connectionStatus.email }}</span>
          </template>
          <template v-else>Hubungkan akun Google untuk mengelola file.</template>
        </p>
      </div>
      <div class="flex items-center gap-2">
        <template v-if="connectionStatus.connected">
          <BaseButton variant="secondary" size="sm" :icon="RefreshCw" @click="fetchFiles">Refresh</BaseButton>
          <BaseButton variant="ghost" size="sm" class="text-red-500" @click="disconnectDrive">Putuskan</BaseButton>
        </template>
        <template v-else>
          <BaseButton variant="primary" size="sm" :icon="HardDrive" :disabled="connecting" @click="connectDrive">
            {{ connecting ? 'Menghubungkan...' : 'Hubungkan Google Drive' }}
          </BaseButton>
        </template>
      </div>
    </div>

    <!-- Not connected state -->
    <div v-if="!connectionStatus.connected && !loading" class="mt-16 flex flex-col items-center text-center">
      <CloudOff :size="56" class="text-gray-300 dark:text-gray-600" />
      <h2 class="mt-4 text-lg font-medium text-gray-700 dark:text-gray-300">Google Drive belum terhubung</h2>
      <p class="mt-2 max-w-sm text-sm text-gray-500 dark:text-gray-400">
        Hubungkan akun Google kamu untuk browse, upload, download file, backup data, dan sync catatan.
      </p>
      <BaseButton variant="primary" size="md" class="mt-6" :icon="HardDrive" :disabled="connecting" @click="connectDrive">
        Hubungkan Sekarang
      </BaseButton>
    </div>

    <!-- Connected: File manager -->
    <template v-if="connectionStatus.connected">
      <!-- Toolbar -->
      <div class="mt-5 flex items-center justify-between rounded-lg border border-gray-200 bg-gray-50 px-4 py-2 dark:border-gray-700 dark:bg-gray-800">
        <div class="flex items-center gap-2">
          <button
            v-if="folderStack.length > 0"
            class="rounded p-1 text-gray-500 hover:bg-gray-200 dark:hover:bg-gray-700"
            @click="goBack"
          >
            <ArrowLeft :size="16" />
          </button>
          <nav class="flex items-center gap-1 text-sm">
            <button class="text-primary-600 hover:underline dark:text-primary-400" @click="goToRoot">Drive</button>
            <template v-for="(folder, idx) in folderStack" :key="folder.id">
              <span class="text-gray-400">/</span>
              <button
                class="max-w-[120px] truncate text-primary-600 hover:underline dark:text-primary-400"
                @click="folderStack = folderStack.slice(0, idx + 1); fetchFiles()"
              >{{ folder.name }}</button>
            </template>
          </nav>
        </div>
        <div class="flex items-center gap-2">
          <BaseButton variant="ghost" size="sm" :icon="FolderPlus" @click="showNewFolder = true">Folder</BaseButton>
          <label class="cursor-pointer">
            <BaseButton variant="primary" size="sm" :icon="uploading ? Loader2 : Upload" :disabled="uploading" as="span">
              {{ uploading ? 'Uploading...' : 'Upload' }}
            </BaseButton>
            <input type="file" class="hidden" @change="uploadFile" :disabled="uploading" />
          </label>
        </div>
      </div>

      <!-- File list -->
      <div v-if="loading" class="mt-6 flex items-center justify-center py-12">
        <Loader2 :size="24" class="animate-spin text-primary-500" />
      </div>

      <div v-else-if="files.length" class="mt-4 rounded-xl border border-gray-200 bg-white dark:border-gray-700 dark:bg-gray-800">
        <div class="divide-y divide-gray-100 dark:divide-gray-700">
          <div
            v-for="file in files"
            :key="file.id"
            class="group flex items-center gap-4 px-5 py-3 hover:bg-gray-50 dark:hover:bg-gray-750"
          >
            <!-- Icon -->
            <component
              :is="getFileIcon(file.mimeType)"
              :size="20"
              class="shrink-0"
              :class="file.mimeType.includes('folder') ? 'text-amber-500' : 'text-gray-400'"
            />

            <!-- Name -->
            <div class="flex-1 min-w-0">
              <button
                v-if="file.mimeType.includes('folder')"
                class="text-sm font-medium text-gray-900 hover:text-primary-600 dark:text-white dark:hover:text-primary-400"
                @click="openFolder(file)"
              >{{ file.name }}</button>
              <p v-else class="text-sm font-medium text-gray-900 dark:text-white truncate">{{ file.name }}</p>
            </div>

            <!-- Size -->
            <span class="hidden text-xs text-gray-400 sm:block">{{ formatSize(file.size) }}</span>

            <!-- Modified -->
            <span class="hidden text-xs text-gray-400 md:block">
              {{ file.modifiedTime ? formatDate(file.modifiedTime, { day: 'numeric', month: 'short', year: 'numeric' }) : '-' }}
            </span>

            <!-- Actions -->
            <div class="flex items-center gap-1 opacity-0 group-hover:opacity-100 transition-opacity">
              <a
                v-if="file.webViewLink"
                :href="file.webViewLink"
                target="_blank"
                class="rounded p-1.5 text-gray-400 hover:bg-gray-100 hover:text-gray-600 dark:hover:bg-gray-700"
                title="Buka di Google Drive"
              >
                <ExternalLink :size="14" />
              </a>
              <button
                v-if="!file.mimeType.includes('folder')"
                class="rounded p-1.5 text-gray-400 hover:bg-gray-100 hover:text-gray-600 dark:hover:bg-gray-700"
                title="Download"
                @click="downloadFile(file)"
              >
                <Download :size="14" />
              </button>
              <button
                class="rounded p-1.5 text-gray-400 hover:bg-gray-100 hover:text-red-500 dark:hover:bg-gray-700"
                title="Hapus"
                @click="deleteFile(file)"
              >
                <Trash2 :size="14" />
              </button>
            </div>
          </div>
        </div>
      </div>

      <div v-else class="mt-12 flex flex-col items-center text-center">
        <Folder :size="48" class="text-gray-300 dark:text-gray-600" />
        <p class="mt-3 text-sm text-gray-500 dark:text-gray-400">Folder ini kosong.</p>
      </div>
    </template>

    <!-- New folder modal -->
    <BaseModal v-model="showNewFolder" size="sm">
      <template #default>
        <h2 class="text-lg font-semibold text-gray-900 dark:text-white">Buat Folder Baru</h2>
        <form class="mt-4 space-y-4" @submit.prevent="createFolder">
          <BaseInput v-model="newFolderName" label="Nama Folder" placeholder="Folder baru" required autofocus />
          <div class="flex justify-end gap-2">
            <BaseButton variant="secondary" size="sm" type="button" @click="showNewFolder = false">Batal</BaseButton>
            <BaseButton variant="primary" size="sm" type="submit">Buat</BaseButton>
          </div>
        </form>
      </template>
    </BaseModal>
  </div>
</template>
