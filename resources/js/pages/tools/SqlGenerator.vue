<script setup lang="ts">
import { ref, computed } from 'vue'
import { useToast } from '@purdia/toast'
import BaseButton from '@purdia/ui/src/components/BaseButton.vue'
import BaseInput from '@purdia/ui/src/components/BaseInput.vue'
import BaseSelect from '@purdia/ui/src/components/BaseSelect.vue'
import { Plus, Trash2, Copy, Database, RotateCcw } from '@lucide/vue'

const toast = useToast()

// --- Types ---

type SqlDialect = 'mysql' | 'postgresql' | 'sqlite'
type ColumnType = 'INT' | 'BIGINT' | 'VARCHAR' | 'TEXT' | 'BOOLEAN' | 'DATE' | 'DATETIME' | 'TIMESTAMP' | 'DECIMAL' | 'FLOAT' | 'JSON' | 'UUID'

interface Column {
  id: number
  name: string
  type: ColumnType
  length: string
  nullable: boolean
  defaultValue: string
  primaryKey: boolean
  autoIncrement: boolean
  unique: boolean
  index: boolean
}

// --- State ---

const tableName = ref('')
const dialect = ref<SqlDialect>('mysql')
const ifNotExists = ref(true)
const timestamps = ref(true)
const softDeletes = ref(false)
let nextId = 1

const columns = ref<Column[]>([
  createColumn({ name: 'id', type: 'BIGINT', primaryKey: true, autoIncrement: true }),
])

const dialectOptions = [
  { label: 'MySQL / MariaDB', value: 'mysql' },
  { label: 'PostgreSQL', value: 'postgresql' },
  { label: 'SQLite', value: 'sqlite' },
]

const typeOptions: Array<{ label: string; value: ColumnType }> = [
  { label: 'INT', value: 'INT' },
  { label: 'BIGINT', value: 'BIGINT' },
  { label: 'VARCHAR', value: 'VARCHAR' },
  { label: 'TEXT', value: 'TEXT' },
  { label: 'BOOLEAN', value: 'BOOLEAN' },
  { label: 'DATE', value: 'DATE' },
  { label: 'DATETIME', value: 'DATETIME' },
  { label: 'TIMESTAMP', value: 'TIMESTAMP' },
  { label: 'DECIMAL', value: 'DECIMAL' },
  { label: 'FLOAT', value: 'FLOAT' },
  { label: 'JSON', value: 'JSON' },
  { label: 'UUID', value: 'UUID' },
]

// --- Helpers ---

function createColumn(overrides: Partial<Column> = {}): Column {
  return {
    id: nextId++,
    name: '',
    type: 'VARCHAR',
    length: '255',
    nullable: false,
    defaultValue: '',
    primaryKey: false,
    autoIncrement: false,
    unique: false,
    index: false,
    ...overrides,
  }
}

function addColumn() {
  columns.value.push(createColumn())
}

function removeColumn(id: number) {
  columns.value = columns.value.filter((c) => c.id !== id)
}

function reset() {
  tableName.value = ''
  timestamps.value = true
  softDeletes.value = false
  ifNotExists.value = true
  nextId = 1
  columns.value = [createColumn({ name: 'id', type: 'BIGINT', primaryKey: true, autoIncrement: true })]
}

// --- SQL Generation ---

function typeNeedsLength(type: ColumnType): boolean {
  return ['VARCHAR', 'DECIMAL'].includes(type)
}

function mapType(type: ColumnType, length: string, d: SqlDialect): string {
  switch (d) {
    case 'mysql':
      if (type === 'UUID') return 'CHAR(36)'
      if (type === 'BOOLEAN') return 'TINYINT(1)'
      if (type === 'VARCHAR') return `VARCHAR(${length || 255})`
      if (type === 'DECIMAL') return `DECIMAL(${length || '10,2'})`
      return type
    case 'postgresql':
      if (type === 'UUID') return 'UUID'
      if (type === 'BOOLEAN') return 'BOOLEAN'
      if (type === 'BIGINT') return 'BIGINT'
      if (type === 'INT') return 'INTEGER'
      if (type === 'VARCHAR') return `VARCHAR(${length || 255})`
      if (type === 'DECIMAL') return `NUMERIC(${length || '10,2'})`
      if (type === 'DATETIME') return 'TIMESTAMP'
      if (type === 'FLOAT') return 'REAL'
      if (type === 'JSON') return 'JSONB'
      return type
    case 'sqlite':
      if (type === 'UUID') return 'TEXT'
      if (type === 'BOOLEAN') return 'INTEGER'
      if (type === 'VARCHAR') return 'TEXT'
      if (type === 'BIGINT' || type === 'INT') return 'INTEGER'
      if (type === 'DECIMAL' || type === 'FLOAT') return 'REAL'
      if (type === 'DATETIME' || type === 'TIMESTAMP' || type === 'DATE') return 'TEXT'
      if (type === 'JSON') return 'TEXT'
      return type
    default:
      return type
  }
}

function autoIncrementKeyword(d: SqlDialect): string {
  switch (d) {
    case 'mysql': return 'AUTO_INCREMENT'
    case 'postgresql': return '' // use SERIAL/BIGSERIAL instead
    case 'sqlite': return 'AUTOINCREMENT'
  }
}

function buildColumnDef(col: Column, d: SqlDialect): string {
  const parts: string[] = []

  // Column name
  parts.push(`  ${quote(col.name, d)}`)

  // Type — for PG with auto_increment, use BIGSERIAL
  if (d === 'postgresql' && col.autoIncrement) {
    parts.push(col.type === 'INT' ? 'SERIAL' : 'BIGSERIAL')
  } else {
    parts.push(mapType(col.type, col.length, d))
  }

  // Constraints
  if (col.primaryKey && d === 'sqlite') {
    parts.push('PRIMARY KEY')
    if (col.autoIncrement) parts.push('AUTOINCREMENT')
  } else {
    if (col.autoIncrement && d === 'mysql') parts.push('AUTO_INCREMENT')
    if (!col.nullable) parts.push('NOT NULL')
    else parts.push('NULL')
  }

  if (col.unique && !col.primaryKey) parts.push('UNIQUE')

  if (col.defaultValue) {
    const val = col.defaultValue
    if (val.toUpperCase() === 'NULL') parts.push('DEFAULT NULL')
    else if (val.toUpperCase() === 'CURRENT_TIMESTAMP') parts.push(`DEFAULT ${val.toUpperCase()}`)
    else if (['INT', 'BIGINT', 'DECIMAL', 'FLOAT', 'BOOLEAN'].includes(col.type)) parts.push(`DEFAULT ${val}`)
    else parts.push(`DEFAULT '${val}'`)
  }

  return parts.join(' ')
}

function quote(name: string, d: SqlDialect): string {
  if (!name) return '``'
  switch (d) {
    case 'mysql': return `\`${name}\``
    case 'postgresql': return `"${name}"`
    case 'sqlite': return `"${name}"`
  }
}

const generatedSql = computed(() => {
  if (!tableName.value.trim()) return ''

  const d = dialect.value
  const lines: string[] = []
  const constraints: string[] = []

  // CREATE TABLE
  const ifne = ifNotExists.value ? 'IF NOT EXISTS ' : ''
  lines.push(`CREATE TABLE ${ifne}${quote(tableName.value.trim(), d)} (`)

  // Columns
  const colDefs: string[] = []
  const pks: string[] = []
  const indexes: string[] = []

  for (const col of columns.value) {
    if (!col.name.trim()) continue
    colDefs.push(buildColumnDef(col, d))
    if (col.primaryKey && d !== 'sqlite') pks.push(quote(col.name, d))
    if (col.index && !col.primaryKey && !col.unique) {
      indexes.push(col.name)
    }
  }

  // Timestamps
  if (timestamps.value) {
    const tsType = d === 'sqlite' ? 'TEXT' : 'TIMESTAMP'
    colDefs.push(`  ${quote('created_at', d)} ${tsType} NULL`)
    colDefs.push(`  ${quote('updated_at', d)} ${tsType} NULL`)
  }

  // Soft deletes
  if (softDeletes.value) {
    const tsType = d === 'sqlite' ? 'TEXT' : 'TIMESTAMP'
    colDefs.push(`  ${quote('deleted_at', d)} ${tsType} NULL`)
  }

  // Primary key constraint (not for sqlite — it's inline)
  if (pks.length && d !== 'sqlite') {
    colDefs.push(`  PRIMARY KEY (${pks.join(', ')})`)
  }

  lines.push(colDefs.join(',\n'))

  // Close
  if (d === 'mysql') {
    lines.push(') ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;')
  } else {
    lines.push(');')
  }

  // Indexes (separate statements)
  for (const idxCol of indexes) {
    const idxName = `idx_${tableName.value.trim()}_${idxCol}`
    lines.push('')
    lines.push(`CREATE INDEX ${quote(idxName, d)} ON ${quote(tableName.value.trim(), d)} (${quote(idxCol, d)});`)
  }

  return lines.join('\n')
})

// --- Copy ---

async function copyToClipboard() {
  if (!generatedSql.value) return
  try {
    await navigator.clipboard.writeText(generatedSql.value)
    toast.success('SQL disalin ke clipboard.')
  } catch {
    toast.error('Gagal menyalin.')
  }
}
</script>

<template>
  <div>
    <div class="flex items-center justify-between">
      <div>
        <h1 class="text-2xl font-semibold text-gray-900 dark:text-white">SQL Generator</h1>
        <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">Generate CREATE TABLE statement dari form.</p>
      </div>
      <BaseButton variant="secondary" size="sm" :icon="RotateCcw" @click="reset">Reset</BaseButton>
    </div>

    <!-- Config -->
    <div class="mt-6 grid grid-cols-1 gap-4 sm:grid-cols-3">
      <BaseInput v-model="tableName" label="Nama Tabel" placeholder="contoh: expenses" />
      <BaseSelect v-model="dialect" label="Dialect" :options="dialectOptions" :clearable="false" />
      <div class="flex items-end gap-4">
        <label class="flex items-center gap-2 text-sm text-gray-700 dark:text-gray-300">
          <input v-model="ifNotExists" type="checkbox" class="rounded border-gray-300 text-primary-600 focus:ring-primary-500" />
          IF NOT EXISTS
        </label>
        <label class="flex items-center gap-2 text-sm text-gray-700 dark:text-gray-300">
          <input v-model="timestamps" type="checkbox" class="rounded border-gray-300 text-primary-600 focus:ring-primary-500" />
          Timestamps
        </label>
        <label class="flex items-center gap-2 text-sm text-gray-700 dark:text-gray-300">
          <input v-model="softDeletes" type="checkbox" class="rounded border-gray-300 text-primary-600 focus:ring-primary-500" />
          Soft Delete
        </label>
      </div>
    </div>

    <!-- Columns -->
    <div class="mt-6">
      <div class="flex items-center justify-between mb-3">
        <h2 class="text-sm font-semibold text-gray-900 dark:text-white">Kolom</h2>
        <BaseButton variant="secondary" size="sm" :icon="Plus" @click="addColumn">Tambah Kolom</BaseButton>
      </div>

      <div class="overflow-x-auto rounded-xl border border-gray-200 bg-white dark:border-gray-700 dark:bg-gray-800">
        <table class="w-full text-sm">
          <thead class="border-b border-gray-100 bg-gray-50 dark:border-gray-700 dark:bg-gray-800/50">
            <tr>
              <th class="px-3 py-2.5 text-left font-medium text-gray-600 dark:text-gray-400 min-w-[140px]">Nama</th>
              <th class="px-3 py-2.5 text-left font-medium text-gray-600 dark:text-gray-400 w-32">Tipe</th>
              <th class="px-3 py-2.5 text-left font-medium text-gray-600 dark:text-gray-400 w-20">Length</th>
              <th class="px-3 py-2.5 text-center font-medium text-gray-600 dark:text-gray-400 w-14">PK</th>
              <th class="px-3 py-2.5 text-center font-medium text-gray-600 dark:text-gray-400 w-14">AI</th>
              <th class="px-3 py-2.5 text-center font-medium text-gray-600 dark:text-gray-400 w-14">Null</th>
              <th class="px-3 py-2.5 text-center font-medium text-gray-600 dark:text-gray-400 w-14">UQ</th>
              <th class="px-3 py-2.5 text-center font-medium text-gray-600 dark:text-gray-400 w-14">IDX</th>
              <th class="px-3 py-2.5 text-left font-medium text-gray-600 dark:text-gray-400 w-28">Default</th>
              <th class="px-3 py-2.5 w-10"></th>
            </tr>
          </thead>
          <tbody class="divide-y divide-gray-100 dark:divide-gray-700">
            <tr v-for="col in columns" :key="col.id" class="hover:bg-gray-50 dark:hover:bg-gray-700/30">
              <td class="px-3 py-2">
                <input
                  v-model="col.name"
                  type="text"
                  placeholder="column_name"
                  class="w-full rounded-md border border-gray-300 bg-white px-2.5 py-1.5 text-sm focus:border-primary-500 focus:outline-none focus:ring-1 focus:ring-primary-500 dark:border-gray-600 dark:bg-gray-800 dark:text-white"
                />
              </td>
              <td class="px-3 py-2">
                <BaseSelect
                  v-model="col.type"
                  :options="typeOptions"
                  :clearable="false"
                  :searchable="true"
                  size="sm"
                />
              </td>
              <td class="px-3 py-2">
                <input
                  v-model="col.length"
                  type="text"
                  :disabled="!typeNeedsLength(col.type)"
                  placeholder="—"
                  class="w-full rounded-md border border-gray-300 bg-white px-2 py-1.5 text-sm text-center disabled:opacity-40 dark:border-gray-600 dark:bg-gray-800 dark:text-white"
                />
              </td>
              <td class="px-3 py-2 text-center">
                <input v-model="col.primaryKey" type="checkbox" class="rounded border-gray-300 text-primary-600" />
              </td>
              <td class="px-3 py-2 text-center">
                <input v-model="col.autoIncrement" type="checkbox" class="rounded border-gray-300 text-primary-600" />
              </td>
              <td class="px-3 py-2 text-center">
                <input v-model="col.nullable" type="checkbox" class="rounded border-gray-300 text-primary-600" />
              </td>
              <td class="px-3 py-2 text-center">
                <input v-model="col.unique" type="checkbox" class="rounded border-gray-300 text-primary-600" />
              </td>
              <td class="px-3 py-2 text-center">
                <input v-model="col.index" type="checkbox" class="rounded border-gray-300 text-primary-600" />
              </td>
              <td class="px-3 py-2">
                <input
                  v-model="col.defaultValue"
                  type="text"
                  placeholder="—"
                  class="w-full rounded-md border border-gray-300 bg-white px-2 py-1.5 text-sm dark:border-gray-600 dark:bg-gray-800 dark:text-white"
                />
              </td>
              <td class="px-3 py-2 text-center">
                <button
                  v-if="columns.length > 1"
                  class="rounded p-1 text-gray-400 hover:text-red-500"
                  @click="removeColumn(col.id)"
                >
                  <Trash2 :size="14" />
                </button>
              </td>
            </tr>
          </tbody>
        </table>
      </div>
    </div>

    <!-- Generated SQL -->
    <div class="mt-6">
      <div class="flex items-center justify-between mb-2">
        <h2 class="text-sm font-semibold text-gray-900 dark:text-white">Hasil SQL</h2>
        <BaseButton v-if="generatedSql" variant="secondary" size="sm" :icon="Copy" @click="copyToClipboard">
          Copy
        </BaseButton>
      </div>
      <div class="rounded-xl border border-gray-200 bg-gray-900 p-5 dark:border-gray-700">
        <pre v-if="generatedSql" class="whitespace-pre-wrap text-sm font-mono text-green-400 leading-relaxed">{{ generatedSql }}</pre>
        <p v-else class="text-sm text-gray-500 italic">Isi nama tabel dan kolom di atas untuk generate SQL.</p>
      </div>
    </div>
  </div>
</template>
