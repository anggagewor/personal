<script setup lang="ts">
import { ref, computed, onMounted } from 'vue'
import { useRouter } from 'vue-router'
import BaseInput from '@purdia/ui/src/components/BaseInput.vue'
import { Database, Search, Table2 } from '@lucide/vue'
import type { TableInfo } from '@/types/database'
import * as dbApi from '@/api/database'

const router = useRouter()

const tables = ref<TableInfo[]>([])
const loading = ref(true)
const search = ref('')

const filteredTables = computed(() => {
  if (!search.value.trim()) return tables.value
  const q = search.value.toLowerCase()
  return tables.value.filter(t => t.name.toLowerCase().includes(q))
})

const totalRows = computed(() => tables.value.reduce((sum, t) => sum + t.rows, 0))

async function fetchTables() {
  loading.value = true
  try {
    const res = await dbApi.fetchTables()
    tables.value = res.data
  } catch {
    // handled by @purdia/http
  } finally {
    loading.value = false
  }
}

function openTable(table: TableInfo) {
  router.push({ name: 'database.table', params: { table: table.name } })
}

onMounted(fetchTables)
</script>

<template>
  <div>
    <!-- Header -->
    <div class="flex items-center justify-between">
      <div>
        <h1 class="text-2xl font-semibold text-gray-900 dark:text-white">Database Manager</h1>
        <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">
          {{ tables.length }} tabel, {{ totalRows.toLocaleString('id-ID') }} total rows
        </p>
      </div>
    </div>

    <!-- Search -->
    <div class="mt-6 max-w-sm">
      <BaseInput v-model="search" placeholder="Cari tabel..." :icon="Search" />
    </div>

    <!-- Loading -->
    <div v-if="loading" class="mt-6 py-8 text-center text-sm text-gray-400">Memuat data...</div>

    <!-- Empty -->
    <div v-else-if="!filteredTables.length" class="mt-12 flex flex-col items-center text-center">
      <div class="flex h-16 w-16 items-center justify-center rounded-2xl bg-gray-100 dark:bg-gray-800">
        <Database :size="28" class="text-gray-400" />
      </div>
      <h3 class="mt-4 text-base font-medium text-gray-900 dark:text-white">
        {{ search ? 'Tabel tidak ditemukan' : 'Tidak ada tabel' }}
      </h3>
    </div>

    <!-- Table Grid -->
    <div v-else class="mt-6 grid grid-cols-1 gap-3 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4">
      <button
        v-for="table in filteredTables"
        :key="table.name"
        class="flex items-center gap-3 rounded-xl border border-gray-200 bg-white p-4 text-left transition-all hover:border-primary-300 hover:shadow-sm dark:border-gray-700 dark:bg-gray-800 dark:hover:border-primary-600"
        @click="openTable(table)"
      >
        <div class="flex h-9 w-9 shrink-0 items-center justify-center rounded-lg bg-primary-50 text-primary-600 dark:bg-primary-900/30 dark:text-primary-400">
          <Table2 :size="18" />
        </div>
        <div class="min-w-0 flex-1">
          <p class="truncate text-sm font-medium text-gray-900 dark:text-white">{{ table.name }}</p>
          <p class="text-xs text-gray-500 dark:text-gray-400">{{ table.rows.toLocaleString('id-ID') }} rows</p>
        </div>
      </button>
    </div>
  </div>
</template>
