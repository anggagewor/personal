<script setup lang="ts">
import { ref } from 'vue'
import { get } from '@purdia/http'
import { formatDate } from '@purdia/utils'
import { Activity, Clock } from '@lucide/vue'

interface Log {
  id: number
  action: string
  description: string
  metadata: Record<string, unknown> | null
  created_at: string
}

const logs = ref<Log[]>([])
const loading = ref(false)
const page = ref(1)
const lastPage = ref(1)

async function fetchLogs() {
  loading.value = true
  try {
    const response = await get<Log[]>('/activity-logs', { params: { page: page.value, per_page: 20 } })
    logs.value = response.data
    if (response.meta) {
      lastPage.value = response.meta.last_page
    }
  } catch { /* */ }
  loading.value = false
}

function prevPage() {
  if (page.value > 1) { page.value--; fetchLogs() }
}
function nextPage() {
  if (page.value < lastPage.value) { page.value++; fetchLogs() }
}

fetchLogs()
</script>

<template>
  <div>
    <h1 class="text-2xl font-semibold text-gray-900 dark:text-white">Aktivitas</h1>
    <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">Log aktivitas akun kamu.</p>

    <div v-if="logs.length" class="mt-6 space-y-3">
      <div
        v-for="log in logs"
        :key="log.id"
        class="flex items-start gap-3 rounded-lg border border-gray-200 bg-white px-4 py-3 dark:border-gray-700 dark:bg-gray-800"
      >
        <div class="flex h-8 w-8 shrink-0 items-center justify-center rounded-full bg-primary-50 dark:bg-primary-900/30">
          <Activity :size="16" class="text-primary-600 dark:text-primary-400" />
        </div>
        <div class="flex-1 min-w-0">
          <p class="text-sm text-gray-900 dark:text-white">{{ log.description }}</p>
          <div class="mt-1 flex items-center gap-1.5 text-xs text-gray-400">
            <Clock :size="12" />
            {{ formatDate(log.created_at, { day: 'numeric', month: 'short', year: 'numeric', hour: '2-digit', minute: '2-digit' }) }}
          </div>
        </div>
        <span class="rounded bg-gray-100 px-2 py-0.5 text-xs font-medium text-gray-600 dark:bg-gray-700 dark:text-gray-300">
          {{ log.action }}
        </span>
      </div>
    </div>

    <!-- Empty -->
    <div v-else-if="!loading" class="mt-12 flex flex-col items-center text-center">
      <Activity :size="48" class="text-gray-300 dark:text-gray-600" />
      <p class="mt-3 text-sm text-gray-500 dark:text-gray-400">Belum ada aktivitas tercatat.</p>
    </div>

    <!-- Pagination -->
    <div v-if="lastPage > 1" class="mt-6 flex items-center justify-center gap-2">
      <button class="rounded-lg border border-gray-300 px-3 py-1.5 text-sm disabled:opacity-50 dark:border-gray-600 dark:text-gray-300" :disabled="page <= 1" @click="prevPage">Sebelumnya</button>
      <span class="text-sm text-gray-500">{{ page }} / {{ lastPage }}</span>
      <button class="rounded-lg border border-gray-300 px-3 py-1.5 text-sm disabled:opacity-50 dark:border-gray-600 dark:text-gray-300" :disabled="page >= lastPage" @click="nextPage">Selanjutnya</button>
    </div>
  </div>
</template>
