<script setup lang="ts">
import { ref, watch } from 'vue'
import { formatDate } from '@purdia/utils'
import { Shield, Clock, Filter, ChevronDown, ChevronUp } from '@lucide/vue'
import type { AuditLogEntry, AuditLogFilters } from '@/types/audit-log'
import * as auditLogApi from '@/api/audit-log'

const logs = ref<AuditLogEntry[]>([])
const loading = ref(false)
const page = ref(1)
const lastPage = ref(1)
const total = ref(0)
const showFilters = ref(false)
const expandedId = ref<number | null>(null)

const filters = ref<AuditLogFilters>({
  event: '',
  auditable_type: '',
  tags: '',
  date_from: '',
  date_to: '',
  per_page: 20,
})

const eventOptions = [
  { value: '', label: 'Semua Event' },
  { value: 'created', label: 'Created' },
  { value: 'updated', label: 'Updated' },
  { value: 'deleted', label: 'Deleted' },
  { value: 'restored', label: 'Restored' },
  { value: 'login', label: 'Login' },
  { value: 'logout', label: 'Logout' },
  { value: 'custom', label: 'Custom' },
]

async function fetchLogs() {
  loading.value = true
  try {
    const params: AuditLogFilters = {
      page: page.value,
      per_page: filters.value.per_page,
    }
    if (filters.value.event) params.event = filters.value.event
    if (filters.value.auditable_type) params.auditable_type = filters.value.auditable_type
    if (filters.value.tags) params.tags = filters.value.tags
    if (filters.value.date_from) params.date_from = filters.value.date_from
    if (filters.value.date_to) params.date_to = filters.value.date_to

    const response = await auditLogApi.fetchAuditLogs(params)
    logs.value = response.data
    if (response.meta) {
      lastPage.value = response.meta.last_page
      total.value = response.meta.total
    }
  } catch { /* */ }
  loading.value = false
}

function applyFilters() {
  page.value = 1
  fetchLogs()
}

function resetFilters() {
  filters.value = { event: '', auditable_type: '', tags: '', date_from: '', date_to: '', per_page: 20 }
  page.value = 1
  fetchLogs()
}

function prevPage() {
  if (page.value > 1) { page.value--; fetchLogs() }
}

function nextPage() {
  if (page.value < lastPage.value) { page.value++; fetchLogs() }
}

function toggleExpand(id: number) {
  expandedId.value = expandedId.value === id ? null : id
}

function eventBadgeClass(event: string): string {
  const map: Record<string, string> = {
    created: 'bg-green-100 text-green-700 dark:bg-green-900/30 dark:text-green-400',
    updated: 'bg-blue-100 text-blue-700 dark:bg-blue-900/30 dark:text-blue-400',
    deleted: 'bg-red-100 text-red-700 dark:bg-red-900/30 dark:text-red-400',
    restored: 'bg-purple-100 text-purple-700 dark:bg-purple-900/30 dark:text-purple-400',
    login: 'bg-yellow-100 text-yellow-700 dark:bg-yellow-900/30 dark:text-yellow-400',
    logout: 'bg-gray-100 text-gray-700 dark:bg-gray-700 dark:text-gray-300',
    custom: 'bg-orange-100 text-orange-700 dark:bg-orange-900/30 dark:text-orange-400',
  }
  return map[event] || 'bg-gray-100 text-gray-700 dark:bg-gray-700 dark:text-gray-300'
}

function methodBadgeClass(method: string | null): string {
  if (!method) return ''
  const map: Record<string, string> = {
    GET: 'text-green-600 dark:text-green-400',
    POST: 'text-blue-600 dark:text-blue-400',
    PUT: 'text-yellow-600 dark:text-yellow-400',
    PATCH: 'text-yellow-600 dark:text-yellow-400',
    DELETE: 'text-red-600 dark:text-red-400',
  }
  return map[method] || 'text-gray-600 dark:text-gray-400'
}

fetchLogs()
</script>

<template>
  <div>
    <div class="flex items-center justify-between">
      <div>
        <h1 class="text-2xl font-semibold text-gray-900 dark:text-white">Audit Log</h1>
        <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">Riwayat perubahan data secara detail.</p>
      </div>
      <button
        class="flex items-center gap-2 rounded-lg border border-gray-300 px-3 py-2 text-sm text-gray-700 hover:bg-gray-50 dark:border-gray-600 dark:text-gray-300 dark:hover:bg-gray-800"
        @click="showFilters = !showFilters"
      >
        <Filter :size="16" />
        Filter
      </button>
    </div>

    <!-- Filters -->
    <Transition name="slide">
      <div v-if="showFilters" class="mt-4 rounded-lg border border-gray-200 bg-white p-4 dark:border-gray-700 dark:bg-gray-800">
        <div class="grid grid-cols-1 gap-4 sm:grid-cols-2 lg:grid-cols-4">
          <div>
            <label class="mb-1 block text-xs font-medium text-gray-600 dark:text-gray-400">Event</label>
            <select
              v-model="filters.event"
              class="w-full rounded-lg border border-gray-300 bg-white px-3 py-2 text-sm dark:border-gray-600 dark:bg-gray-700 dark:text-gray-200"
            >
              <option v-for="opt in eventOptions" :key="opt.value" :value="opt.value">{{ opt.label }}</option>
            </select>
          </div>
          <div>
            <label class="mb-1 block text-xs font-medium text-gray-600 dark:text-gray-400">Entity Type</label>
            <input
              v-model="filters.auditable_type"
              type="text"
              placeholder="e.g. note, task"
              class="w-full rounded-lg border border-gray-300 bg-white px-3 py-2 text-sm dark:border-gray-600 dark:bg-gray-700 dark:text-gray-200"
            />
          </div>
          <div>
            <label class="mb-1 block text-xs font-medium text-gray-600 dark:text-gray-400">Dari Tanggal</label>
            <input
              v-model="filters.date_from"
              type="date"
              class="w-full rounded-lg border border-gray-300 bg-white px-3 py-2 text-sm dark:border-gray-600 dark:bg-gray-700 dark:text-gray-200"
            />
          </div>
          <div>
            <label class="mb-1 block text-xs font-medium text-gray-600 dark:text-gray-400">Sampai Tanggal</label>
            <input
              v-model="filters.date_to"
              type="date"
              class="w-full rounded-lg border border-gray-300 bg-white px-3 py-2 text-sm dark:border-gray-600 dark:bg-gray-700 dark:text-gray-200"
            />
          </div>
        </div>
        <div class="mt-4 flex gap-2">
          <button
            class="rounded-lg bg-primary-600 px-4 py-2 text-sm font-medium text-white hover:bg-primary-700"
            @click="applyFilters"
          >
            Terapkan
          </button>
          <button
            class="rounded-lg border border-gray-300 px-4 py-2 text-sm text-gray-700 hover:bg-gray-50 dark:border-gray-600 dark:text-gray-300 dark:hover:bg-gray-800"
            @click="resetFilters"
          >
            Reset
          </button>
        </div>
      </div>
    </Transition>

    <!-- Total -->
    <p v-if="total > 0" class="mt-4 text-xs text-gray-500 dark:text-gray-400">
      {{ total }} entri ditemukan
    </p>

    <!-- Loading -->
    <div v-if="loading" class="mt-8 flex justify-center">
      <div class="h-6 w-6 animate-spin rounded-full border-2 border-primary-600 border-t-transparent"></div>
    </div>

    <!-- Log list -->
    <div v-else-if="logs.length" class="mt-4 space-y-2">
      <div
        v-for="log in logs"
        :key="log.id"
        class="rounded-lg border border-gray-200 bg-white dark:border-gray-700 dark:bg-gray-800"
      >
        <!-- Header row -->
        <button
          class="flex w-full items-center gap-3 px-4 py-3 text-left"
          @click="toggleExpand(log.id)"
        >
          <div class="flex h-8 w-8 shrink-0 items-center justify-center rounded-full bg-primary-50 dark:bg-primary-900/30">
            <Shield :size="16" class="text-primary-600 dark:text-primary-400" />
          </div>
          <div class="flex-1 min-w-0">
            <div class="flex items-center gap-2">
              <span class="text-sm font-medium text-gray-900 dark:text-white">{{ log.auditable_type }}</span>
              <span v-if="log.auditable_id" class="text-xs text-gray-400">#{{ log.auditable_id }}</span>
            </div>
            <div class="mt-0.5 flex items-center gap-2 text-xs text-gray-500 dark:text-gray-400">
              <Clock :size="12" />
              {{ formatDate(log.created_at, { day: 'numeric', month: 'short', year: 'numeric', hour: '2-digit', minute: '2-digit' }) }}
              <span v-if="log.method" :class="['font-mono font-medium', methodBadgeClass(log.method)]">{{ log.method }}</span>
              <span v-if="log.tags" class="rounded bg-gray-100 px-1.5 py-0.5 text-xs dark:bg-gray-700">{{ log.tags }}</span>
            </div>
          </div>
          <span :class="['rounded px-2 py-0.5 text-xs font-medium', eventBadgeClass(log.event)]">
            {{ log.event }}
          </span>
          <component :is="expandedId === log.id ? ChevronUp : ChevronDown" :size="16" class="text-gray-400" />
        </button>

        <!-- Expanded detail -->
        <div v-if="expandedId === log.id" class="border-t border-gray-100 px-4 py-3 dark:border-gray-700">
          <div class="grid grid-cols-1 gap-4 text-xs lg:grid-cols-2">
            <!-- Changed fields -->
            <div v-if="log.changed_fields && Object.keys(log.changed_fields).length">
              <p class="mb-2 font-medium text-gray-700 dark:text-gray-300">Perubahan:</p>
              <div class="space-y-1">
                <div v-for="(change, field) in log.changed_fields" :key="field" class="flex items-center gap-2">
                  <span class="font-mono text-gray-600 dark:text-gray-400">{{ field }}:</span>
                  <span class="rounded bg-red-50 px-1.5 py-0.5 text-red-700 line-through dark:bg-red-900/20 dark:text-red-400">{{ change.old ?? '—' }}</span>
                  <span class="text-gray-400">→</span>
                  <span class="rounded bg-green-50 px-1.5 py-0.5 text-green-700 dark:bg-green-900/20 dark:text-green-400">{{ change.new ?? '—' }}</span>
                </div>
              </div>
            </div>

            <!-- New values (created) -->
            <div v-else-if="log.event === 'created' && log.new_values">
              <p class="mb-2 font-medium text-gray-700 dark:text-gray-300">Data baru:</p>
              <pre class="overflow-x-auto rounded bg-gray-50 p-2 text-xs text-gray-700 dark:bg-gray-900 dark:text-gray-300">{{ JSON.stringify(log.new_values, null, 2) }}</pre>
            </div>

            <!-- Old values (deleted) -->
            <div v-else-if="log.event === 'deleted' && log.old_values">
              <p class="mb-2 font-medium text-gray-700 dark:text-gray-300">Data dihapus:</p>
              <pre class="overflow-x-auto rounded bg-gray-50 p-2 text-xs text-gray-700 dark:bg-gray-900 dark:text-gray-300">{{ JSON.stringify(log.old_values, null, 2) }}</pre>
            </div>

            <!-- Metadata -->
            <div v-if="log.metadata && Object.keys(log.metadata).length">
              <p class="mb-2 font-medium text-gray-700 dark:text-gray-300">Metadata:</p>
              <pre class="overflow-x-auto rounded bg-gray-50 p-2 text-xs text-gray-700 dark:bg-gray-900 dark:text-gray-300">{{ JSON.stringify(log.metadata, null, 2) }}</pre>
            </div>

            <!-- Request info -->
            <div>
              <p class="mb-2 font-medium text-gray-700 dark:text-gray-300">Request:</p>
              <div class="space-y-1 text-gray-600 dark:text-gray-400">
                <p v-if="log.url"><span class="font-medium">URL:</span> {{ log.url }}</p>
                <p v-if="log.ip_address"><span class="font-medium">IP:</span> {{ log.ip_address }}</p>
                <p v-if="log.user_agent" class="truncate"><span class="font-medium">UA:</span> {{ log.user_agent }}</p>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>

    <!-- Empty -->
    <div v-else-if="!loading" class="mt-12 flex flex-col items-center text-center">
      <Shield :size="48" class="text-gray-300 dark:text-gray-600" />
      <p class="mt-3 text-sm text-gray-500 dark:text-gray-400">Belum ada audit log tercatat.</p>
    </div>

    <!-- Pagination -->
    <div v-if="lastPage > 1" class="mt-6 flex items-center justify-center gap-2">
      <button
        class="rounded-lg border border-gray-300 px-3 py-1.5 text-sm disabled:opacity-50 dark:border-gray-600 dark:text-gray-300"
        :disabled="page <= 1"
        @click="prevPage"
      >
        Sebelumnya
      </button>
      <span class="text-sm text-gray-500">{{ page }} / {{ lastPage }}</span>
      <button
        class="rounded-lg border border-gray-300 px-3 py-1.5 text-sm disabled:opacity-50 dark:border-gray-600 dark:text-gray-300"
        :disabled="page >= lastPage"
        @click="nextPage"
      >
        Selanjutnya
      </button>
    </div>
  </div>
</template>

<style scoped>
.slide-enter-active,
.slide-leave-active {
  transition: all 0.2s ease;
}
.slide-enter-from,
.slide-leave-to {
  opacity: 0;
  transform: translateY(-8px);
}
</style>
