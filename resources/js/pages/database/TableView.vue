<script setup lang="ts">
import { ref, computed, watch, onMounted } from 'vue'
import { useRoute, useRouter } from 'vue-router'
import { useToast } from '@purdia/toast'
import BaseButton from '@purdia/ui/src/components/BaseButton.vue'
import BaseInput from '@purdia/ui/src/components/BaseInput.vue'
import BaseSelect from '@purdia/ui/src/components/BaseSelect.vue'
import BaseModal from '@purdia/ui/src/components/BaseModal.vue'
import {
  ArrowLeft,
  ChevronLeft,
  ChevronRight,
  Filter,
  Plus,
  Trash2,
  Pencil,
  Save,
  X,
  Columns3,
  ArrowUpDown,
} from '@lucide/vue'
import type { ColumnInfo, TableStructure, RowFilter } from '@/types/database'
import * as dbApi from '@/api/database'

const route = useRoute()
const router = useRouter()
const toast = useToast()

const tableName = computed(() => route.params.table as string)

// State
const structure = ref<TableStructure | null>(null)
const rows = ref<Record<string, unknown>[]>([])
const loading = ref(true)
const totalRows = ref(0)
const currentPage = ref(1)
const lastPage = ref(1)
const perPage = ref(25)
const sortBy = ref('')
const sortDir = ref<'asc' | 'desc'>('desc')

// Filters
const filters = ref<RowFilter[]>([])
const showFilterPanel = ref(false)

// Editing
const editingRow = ref<Record<string, unknown> | null>(null)
const editForm = ref<Record<string, unknown>>({})
const editSubmitting = ref(false)

// Alter table modal
const showAlterModal = ref(false)
const alterForm = ref({
  action: 'add_column' as 'add_column' | 'drop_column' | 'modify_column',
  column: '',
  type: 'VARCHAR(255)',
  nullable: true,
  default: '',
  after: '',
})
const alterSubmitting = ref(false)

// Primary key detection
const primaryKey = computed(() => {
  if (!structure.value) return 'id'
  const pk = structure.value.columns.find(c => c.key === 'PRI')
  return pk?.name ?? 'id'
})

const columns = computed<ColumnInfo[]>(() => structure.value?.columns ?? [])

const columnOptions = computed(() =>
  columns.value.map(c => ({ label: c.name, value: c.name }))
)

const operatorOptions = [
  { label: '=', value: '=' },
  { label: '!=', value: '!=' },
  { label: '>', value: '>' },
  { label: '<', value: '<' },
  { label: '>=', value: '>=' },
  { label: '<=', value: '<=' },
  { label: 'LIKE', value: 'like' },
  { label: 'IS NULL', value: 'is null' },
  { label: 'IS NOT NULL', value: 'is not null' },
]

const alterActionOptions = [
  { label: 'Tambah Kolom', value: 'add_column' },
  { label: 'Hapus Kolom', value: 'drop_column' },
  { label: 'Ubah Kolom', value: 'modify_column' },
]

// --- Fetch ---

async function fetchStructure() {
  try {
    const res = await dbApi.fetchStructure(tableName.value)
    structure.value = res.data
  } catch {
    // handled
  }
}

async function fetchRows() {
  loading.value = true
  try {
    const res = await dbApi.fetchRows(tableName.value, {
      page: currentPage.value,
      per_page: perPage.value,
      sort_by: sortBy.value || undefined,
      sort_dir: sortDir.value,
      filters: filters.value.length > 0 ? filters.value : undefined,
    })
    rows.value = res.data.data ?? res.data
    const meta = (res.data as any).meta ?? res.meta
    if (meta) {
      totalRows.value = meta.total
      currentPage.value = meta.current_page
      lastPage.value = meta.last_page
    }
  } catch {
    // handled
  } finally {
    loading.value = false
  }
}

// --- Sort ---

function toggleSort(column: string) {
  if (sortBy.value === column) {
    sortDir.value = sortDir.value === 'asc' ? 'desc' : 'asc'
  } else {
    sortBy.value = column
    sortDir.value = 'asc'
  }
  currentPage.value = 1
  fetchRows()
}

// --- Pagination ---

function goToPage(page: number) {
  if (page < 1 || page > lastPage.value) return
  currentPage.value = page
  fetchRows()
}

// --- Filters ---

function addFilter() {
  filters.value.push({ column: columns.value[0]?.name ?? '', operator: '=', value: '' })
}

function removeFilter(index: number) {
  filters.value.splice(index, 1)
}

function applyFilters() {
  currentPage.value = 1
  fetchRows()
}

function clearFilters() {
  filters.value = []
  currentPage.value = 1
  fetchRows()
}

// --- Edit Row ---

function startEdit(row: Record<string, unknown>) {
  editingRow.value = row
  editForm.value = { ...row }
}

function cancelEdit() {
  editingRow.value = null
  editForm.value = {}
}

async function saveEdit() {
  if (!editingRow.value) return
  editSubmitting.value = true

  // Only send changed fields
  const changes: Record<string, unknown> = {}
  for (const key of Object.keys(editForm.value)) {
    if (editForm.value[key] !== editingRow.value[key]) {
      changes[key] = editForm.value[key]
    }
  }

  if (Object.keys(changes).length === 0) {
    cancelEdit()
    return
  }

  try {
    await dbApi.updateRow(tableName.value, {
      primary_key: primaryKey.value,
      primary_value: editingRow.value[primaryKey.value],
      data: changes,
    })
    toast.success('Row berhasil diperbarui.')
    cancelEdit()
    fetchRows()
  } catch {
    toast.error('Gagal memperbarui row.')
  } finally {
    editSubmitting.value = false
  }
}

// --- Delete Row ---

async function confirmDelete(row: Record<string, unknown>) {
  const pkVal = row[primaryKey.value]
  if (!confirm(`Hapus row dengan ${primaryKey.value} = ${pkVal}?`)) return

  try {
    await dbApi.deleteRow(tableName.value, {
      primary_key: primaryKey.value,
      primary_value: pkVal,
    })
    toast.success('Row berhasil dihapus.')
    fetchRows()
  } catch {
    toast.error('Gagal menghapus row.')
  }
}

// --- Alter Table ---

function openAlterModal() {
  alterForm.value = {
    action: 'add_column',
    column: '',
    type: 'VARCHAR(255)',
    nullable: true,
    default: '',
    after: '',
  }
  showAlterModal.value = true
}

async function submitAlter() {
  alterSubmitting.value = true
  try {
    await dbApi.alterTable(tableName.value, {
      action: alterForm.value.action,
      column: alterForm.value.column,
      type: alterForm.value.type || undefined,
      nullable: alterForm.value.nullable,
      default: alterForm.value.default || null,
      after: alterForm.value.after || null,
    })
    toast.success('Tabel berhasil diubah.')
    showAlterModal.value = false
    await fetchStructure()
    fetchRows()
  } catch {
    // error handled by http
  } finally {
    alterSubmitting.value = false
  }
}

// --- Helpers ---

function formatCellValue(value: unknown): string {
  if (value === null) return 'NULL'
  if (typeof value === 'object') return JSON.stringify(value)
  return String(value)
}

function truncate(str: string, len: number): string {
  if (str.length <= len) return str
  return str.slice(0, len) + '...'
}

function goBack() {
  router.push({ name: 'database' })
}

// --- Init ---

watch(tableName, () => {
  currentPage.value = 1
  filters.value = []
  sortBy.value = ''
  sortDir.value = 'desc'
  fetchStructure()
  fetchRows()
})

onMounted(() => {
  fetchStructure()
  fetchRows()
})
</script>

<template>
  <div>
    <!-- Header -->
    <div class="flex items-center gap-3">
      <button
        class="rounded-lg p-2 text-gray-400 hover:bg-gray-100 hover:text-gray-600 dark:hover:bg-gray-700 dark:hover:text-gray-300"
        @click="goBack"
      >
        <ArrowLeft :size="20" />
      </button>
      <div class="flex-1">
        <h1 class="text-2xl font-semibold text-gray-900 dark:text-white font-mono">{{ tableName }}</h1>
        <p class="mt-0.5 text-sm text-gray-500 dark:text-gray-400">
          {{ totalRows.toLocaleString('id-ID') }} rows · {{ columns.length }} columns
        </p>
      </div>
      <div class="flex gap-2">
        <BaseButton variant="secondary" size="sm" :icon="Filter" @click="showFilterPanel = !showFilterPanel">
          Filter
        </BaseButton>
        <BaseButton variant="secondary" size="sm" :icon="Columns3" @click="openAlterModal">
          Alter
        </BaseButton>
      </div>
    </div>

    <!-- Filter Panel -->
    <div v-if="showFilterPanel" class="mt-4 rounded-xl border border-gray-200 bg-white p-4 dark:border-gray-700 dark:bg-gray-800">
      <div class="flex items-center justify-between">
        <p class="text-sm font-medium text-gray-700 dark:text-gray-300">Filter</p>
        <div class="flex gap-2">
          <BaseButton variant="secondary" size="xs" @click="addFilter">+ Tambah</BaseButton>
          <BaseButton v-if="filters.length" variant="secondary" size="xs" @click="clearFilters">Reset</BaseButton>
          <BaseButton variant="primary" size="xs" @click="applyFilters">Terapkan</BaseButton>
        </div>
      </div>

      <div v-if="filters.length" class="mt-3 space-y-2">
        <div v-for="(filter, idx) in filters" :key="idx" class="flex items-center gap-2">
          <BaseSelect
            v-model="filter.column"
            :options="columnOptions"
            placeholder="Kolom"
            class="w-40"
          />
          <BaseSelect
            v-model="filter.operator"
            :options="operatorOptions"
            placeholder="Operator"
            class="w-32"
          />
          <BaseInput
            v-if="!['is null', 'is not null'].includes(filter.operator)"
            v-model="filter.value"
            placeholder="Nilai..."
            class="flex-1"
          />
          <button class="p-1.5 text-gray-400 hover:text-red-500" @click="removeFilter(idx)">
            <X :size="16" />
          </button>
        </div>
      </div>

      <p v-else class="mt-3 text-sm text-gray-400">Klik "Tambah" untuk menambahkan filter.</p>
    </div>

    <!-- Loading -->
    <div v-if="loading" class="mt-6 py-8 text-center text-sm text-gray-400">Memuat data...</div>

    <!-- Data Table -->
    <div v-else class="mt-4 overflow-x-auto rounded-xl border border-gray-200 dark:border-gray-700">
      <table class="w-full text-left text-xs">
        <thead class="border-b border-gray-200 bg-gray-50 dark:border-gray-700 dark:bg-gray-800/50">
          <tr>
            <th class="sticky left-0 z-10 bg-gray-50 px-3 py-2.5 font-medium text-gray-500 dark:bg-gray-800/50 dark:text-gray-400">
              #
            </th>
            <th
              v-for="col in columns"
              :key="col.name"
              class="cursor-pointer whitespace-nowrap px-3 py-2.5 font-medium text-gray-500 hover:text-gray-700 dark:text-gray-400 dark:hover:text-gray-200"
              @click="toggleSort(col.name)"
            >
              <span class="inline-flex items-center gap-1">
                {{ col.name }}
                <ArrowUpDown v-if="sortBy !== col.name" :size="12" class="opacity-30" />
                <span v-else class="text-primary-600 dark:text-primary-400">
                  {{ sortDir === 'asc' ? '↑' : '↓' }}
                </span>
              </span>
              <span class="ml-1 text-[10px] text-gray-400">{{ col.type.split('(')[0] }}</span>
            </th>
            <th class="sticky right-0 z-10 bg-gray-50 px-3 py-2.5 text-center font-medium text-gray-500 dark:bg-gray-800/50 dark:text-gray-400">
              Aksi
            </th>
          </tr>
        </thead>
        <tbody class="divide-y divide-gray-100 bg-white dark:divide-gray-700/50 dark:bg-gray-800">
          <tr v-if="!rows.length">
            <td :colspan="columns.length + 2" class="px-3 py-8 text-center text-sm text-gray-400">
              Tidak ada data.
            </td>
          </tr>
          <tr v-for="(row, rowIdx) in rows" :key="rowIdx" class="hover:bg-gray-50 dark:hover:bg-gray-700/20">
            <td class="sticky left-0 z-10 bg-white px-3 py-2 text-gray-400 dark:bg-gray-800">
              {{ (currentPage - 1) * perPage + rowIdx + 1 }}
            </td>

            <!-- Normal mode -->
            <template v-if="editingRow !== row">
              <td
                v-for="col in columns"
                :key="col.name"
                class="max-w-[200px] truncate whitespace-nowrap px-3 py-2 font-mono text-gray-700 dark:text-gray-300"
                :class="{ 'text-gray-400 italic': row[col.name] === null }"
                :title="formatCellValue(row[col.name])"
              >
                {{ truncate(formatCellValue(row[col.name]), 50) }}
              </td>
            </template>

            <!-- Edit mode -->
            <template v-else>
              <td v-for="col in columns" :key="col.name" class="px-2 py-1">
                <input
                  v-if="col.key !== 'PRI'"
                  v-model="editForm[col.name]"
                  class="w-full rounded border border-gray-300 bg-white px-2 py-1 font-mono text-xs text-gray-900 focus:border-primary-500 focus:outline-none focus:ring-1 focus:ring-primary-500 dark:border-gray-600 dark:bg-gray-700 dark:text-white"
                />
                <span v-else class="px-1 font-mono text-gray-500">{{ row[col.name] }}</span>
              </td>
            </template>

            <!-- Actions -->
            <td class="sticky right-0 z-10 bg-white px-3 py-2 dark:bg-gray-800">
              <div class="flex items-center justify-center gap-1">
                <template v-if="editingRow !== row">
                  <button
                    class="rounded p-1 text-gray-400 hover:bg-blue-50 hover:text-blue-600 dark:hover:bg-blue-900/20"
                    title="Edit"
                    @click="startEdit(row)"
                  >
                    <Pencil :size="14" />
                  </button>
                  <button
                    class="rounded p-1 text-gray-400 hover:bg-red-50 hover:text-red-600 dark:hover:bg-red-900/20"
                    title="Hapus"
                    @click="confirmDelete(row)"
                  >
                    <Trash2 :size="14" />
                  </button>
                </template>
                <template v-else>
                  <button
                    class="rounded p-1 text-green-600 hover:bg-green-50 dark:hover:bg-green-900/20"
                    title="Simpan"
                    :disabled="editSubmitting"
                    @click="saveEdit"
                  >
                    <Save :size="14" />
                  </button>
                  <button
                    class="rounded p-1 text-gray-400 hover:bg-gray-100 dark:hover:bg-gray-700"
                    title="Batal"
                    @click="cancelEdit"
                  >
                    <X :size="14" />
                  </button>
                </template>
              </div>
            </td>
          </tr>
        </tbody>
      </table>
    </div>

    <!-- Pagination -->
    <div v-if="lastPage > 1" class="mt-3 flex items-center justify-between">
      <span class="text-xs text-gray-500 dark:text-gray-400">
        Halaman {{ currentPage }} dari {{ lastPage }} ({{ totalRows.toLocaleString('id-ID') }} total)
      </span>
      <div class="flex gap-1">
        <button
          class="rounded p-1.5 text-gray-400 hover:text-gray-600 disabled:opacity-30 dark:hover:text-gray-300"
          :disabled="currentPage <= 1"
          @click="goToPage(currentPage - 1)"
        >
          <ChevronLeft :size="16" />
        </button>
        <button
          class="rounded p-1.5 text-gray-400 hover:text-gray-600 disabled:opacity-30 dark:hover:text-gray-300"
          :disabled="currentPage >= lastPage"
          @click="goToPage(currentPage + 1)"
        >
          <ChevronRight :size="16" />
        </button>
      </div>
    </div>

    <!-- Structure summary (collapsible) -->
    <details class="mt-6 rounded-xl border border-gray-200 bg-white dark:border-gray-700 dark:bg-gray-800">
      <summary class="cursor-pointer px-4 py-3 text-sm font-medium text-gray-700 dark:text-gray-300">
        Struktur Tabel ({{ columns.length }} kolom)
      </summary>
      <div class="overflow-x-auto border-t border-gray-200 dark:border-gray-700">
        <table class="w-full text-left text-xs">
          <thead>
            <tr class="border-b border-gray-200 dark:border-gray-700">
              <th class="px-4 py-2 font-medium text-gray-500 dark:text-gray-400">Kolom</th>
              <th class="px-4 py-2 font-medium text-gray-500 dark:text-gray-400">Tipe</th>
              <th class="px-4 py-2 font-medium text-gray-500 dark:text-gray-400">Nullable</th>
              <th class="px-4 py-2 font-medium text-gray-500 dark:text-gray-400">Key</th>
              <th class="px-4 py-2 font-medium text-gray-500 dark:text-gray-400">Default</th>
              <th class="px-4 py-2 font-medium text-gray-500 dark:text-gray-400">Extra</th>
            </tr>
          </thead>
          <tbody class="divide-y divide-gray-100 dark:divide-gray-700/50">
            <tr v-for="col in columns" :key="col.name">
              <td class="px-4 py-2 font-mono font-medium text-gray-900 dark:text-white">{{ col.name }}</td>
              <td class="px-4 py-2 font-mono text-gray-600 dark:text-gray-300">{{ col.type }}</td>
              <td class="px-4 py-2 text-gray-600 dark:text-gray-300">{{ col.nullable ? 'Ya' : 'Tidak' }}</td>
              <td class="px-4 py-2">
                <span v-if="col.key === 'PRI'" class="rounded bg-yellow-100 px-1.5 py-0.5 text-[10px] font-medium text-yellow-800 dark:bg-yellow-900/30 dark:text-yellow-400">PK</span>
                <span v-else-if="col.key === 'UNI'" class="rounded bg-blue-100 px-1.5 py-0.5 text-[10px] font-medium text-blue-800 dark:bg-blue-900/30 dark:text-blue-400">UNI</span>
                <span v-else-if="col.key === 'MUL'" class="rounded bg-gray-100 px-1.5 py-0.5 text-[10px] font-medium text-gray-600 dark:bg-gray-700 dark:text-gray-400">IDX</span>
              </td>
              <td class="px-4 py-2 font-mono text-gray-500 dark:text-gray-400">{{ col.default ?? '—' }}</td>
              <td class="px-4 py-2 text-gray-500 dark:text-gray-400">{{ col.extra || '—' }}</td>
            </tr>
          </tbody>
        </table>
      </div>
    </details>

    <!-- Alter Table Modal -->
    <BaseModal v-model="showAlterModal" size="md" persistent>
      <template #default>
        <h2 class="text-lg font-semibold text-gray-900 dark:text-white">Alter Table: {{ tableName }}</h2>

        <form class="mt-4 space-y-4" @submit.prevent="submitAlter">
          <BaseSelect
            v-model="alterForm.action"
            :options="alterActionOptions"
            label="Aksi"
            :clearable="false"
          />

          <BaseInput
            v-model="alterForm.column"
            label="Nama Kolom"
            placeholder="nama_kolom"
            required
          />

          <BaseInput
            v-if="alterForm.action !== 'drop_column'"
            v-model="alterForm.type"
            label="Tipe Data"
            placeholder="VARCHAR(255), INT, TEXT, DECIMAL(15,2)..."
          />

          <div v-if="alterForm.action !== 'drop_column'" class="flex items-center gap-4">
            <label class="flex items-center gap-2 text-sm text-gray-700 dark:text-gray-300">
              <input v-model="alterForm.nullable" type="checkbox" class="rounded border-gray-300 dark:border-gray-600" />
              Nullable
            </label>
          </div>

          <BaseInput
            v-if="alterForm.action !== 'drop_column'"
            v-model="alterForm.default"
            label="Default Value (kosongkan jika tidak ada)"
            placeholder="NULL, 0, '', ..."
          />

          <BaseSelect
            v-if="alterForm.action === 'add_column'"
            v-model="alterForm.after"
            :options="[{ label: '(Di akhir)', value: '' }, ...columnOptions]"
            label="Setelah Kolom"
          />

          <div v-if="alterForm.action === 'drop_column'" class="rounded-lg bg-red-50 p-3 text-sm text-red-700 dark:bg-red-900/20 dark:text-red-400">
            Peringatan: Menghapus kolom akan menghilangkan semua data di kolom tersebut secara permanen!
          </div>

          <div class="flex justify-end gap-2 pt-2">
            <BaseButton variant="secondary" size="sm" type="button" @click="showAlterModal = false">
              Batal
            </BaseButton>
            <BaseButton
              :variant="alterForm.action === 'drop_column' ? 'danger' : 'primary'"
              size="sm"
              type="submit"
              :disabled="alterSubmitting || !alterForm.column"
            >
              {{ alterSubmitting ? 'Memproses...' : 'Eksekusi' }}
            </BaseButton>
          </div>
        </form>
      </template>
    </BaseModal>
  </div>
</template>
