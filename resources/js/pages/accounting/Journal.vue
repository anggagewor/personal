<script setup lang="ts">
import { ref, computed, watch } from 'vue'
import { get, post, put, del } from '@purdia/http'
import { useToast } from '@purdia/toast'
import BaseButton from '@purdia/ui/src/components/BaseButton.vue'
import BaseInput from '@purdia/ui/src/components/BaseInput.vue'
import BaseSelect from '@purdia/ui/src/components/BaseSelect.vue'
import BaseModal from '@purdia/ui/src/components/BaseModal.vue'
import BasePagination from '@purdia/ui/src/components/BasePagination.vue'
import { formatCurrency, formatDate } from '@purdia/utils'
import { Plus, Pencil, Trash2, RefreshCw, AlertCircle } from '@lucide/vue'

const toast = useToast()

// --- Interfaces ---

interface JournalLine {
  id?: number
  account_id: number | ''
  debit: number | ''
  credit: number | ''
}

interface JournalEntry {
  id: number
  entry_number: number
  date: string
  description: string
  total_debit: number
  lines: JournalLine[]
  created_at: string
}

interface Account {
  id: number
  code: string
  name: string
  type: string
}

interface PaginationMeta {
  current_page: number
  last_page: number
  per_page: number
  total: number
}

// --- State ---

const entries = ref<JournalEntry[]>([])
const accounts = ref<Account[]>([])
const meta = ref<PaginationMeta>({ current_page: 1, last_page: 1, per_page: 15, total: 0 })
const loading = ref(false)
const showModal = ref(false)
const editingId = ref<number | null>(null)
const submitting = ref(false)

const form = ref({
  date: new Date().toISOString().slice(0, 10),
  description: '',
  lines: [
    { account_id: '' as number | '', debit: '' as number | '', credit: '' as number | '' },
    { account_id: '' as number | '', debit: '' as number | '', credit: '' as number | '' },
  ] as JournalLine[],
})

// --- Computed ---

const accountOptions = computed(() =>
  accounts.value.map((a) => ({ label: `${a.code} - ${a.name}`, value: a.id }))
)

const totalDebit = computed(() =>
  form.value.lines.reduce((sum, line) => sum + (Number(line.debit) || 0), 0)
)

const totalCredit = computed(() =>
  form.value.lines.reduce((sum, line) => sum + (Number(line.credit) || 0), 0)
)

const isBalanced = computed(() => Math.abs(totalDebit.value - totalCredit.value) < 0.001)

const canSubmit = computed(() => {
  if (!form.value.date || !form.value.description.trim()) return false
  if (form.value.lines.length < 2) return false
  if (!isBalanced.value) return false
  if (totalDebit.value === 0) return false
  const hasInvalidLine = form.value.lines.some(
    (line) => !line.account_id || (Number(line.debit) === 0 && Number(line.credit) === 0)
  )
  if (hasInvalidLine) return false
  return true
})

const isEditing = computed(() => editingId.value !== null)

// --- API Calls ---

async function fetchEntries(page = 1) {
  loading.value = true
  try {
    const res = await get<JournalEntry[]>('/accounting/journal-entries', {
      params: { page, per_page: 15 },
    })
    entries.value = res.data
    if (res.meta) meta.value = res.meta as PaginationMeta
  } catch {
    // Error handled globally
  } finally {
    loading.value = false
  }
}

async function fetchAccounts() {
  try {
    const res = await get<Record<string, Account[]>>('/accounting/accounts')
    // API returns accounts grouped by type — flatten into a single list
    const grouped = res.data
    const flat: Account[] = []
    for (const type of Object.keys(grouped)) {
      if (Array.isArray(grouped[type])) {
        flat.push(...grouped[type])
      }
    }
    accounts.value = flat
  } catch {
    // Error handled globally
  }
}

async function submitEntry() {
  if (!canSubmit.value || submitting.value) return
  submitting.value = true

  const payload = {
    date: form.value.date,
    description: form.value.description,
    lines: form.value.lines.map((line) => ({
      account_id: Number(line.account_id),
      debit: Number(line.debit) || 0,
      credit: Number(line.credit) || 0,
    })),
  }

  try {
    if (isEditing.value) {
      await put(`/accounting/journal-entries/${editingId.value}`, payload)
      toast.success('Jurnal berhasil diperbarui.')
    } else {
      await post('/accounting/journal-entries', payload)
      toast.success('Jurnal berhasil dibuat.')
    }
    closeModal()
    fetchEntries(meta.value.current_page)
  } catch (err: unknown) {
    const error = err as { response?: { status?: number; data?: { message?: string } } }
    if (error?.response?.status === 422 && error.response.data?.message) {
      toast.error(error.response.data.message)
    }
  } finally {
    submitting.value = false
  }
}

async function deleteEntry(entry: JournalEntry) {
  if (!confirm(`Hapus jurnal #${entry.entry_number}?`)) return
  try {
    await del(`/accounting/journal-entries/${entry.id}`)
    toast.success('Jurnal berhasil dihapus.')
    fetchEntries(meta.value.current_page)
  } catch {
    // Error handled globally
  }
}

async function editEntry(entry: JournalEntry) {
  try {
    const res = await get<JournalEntry>(`/accounting/journal-entries/${entry.id}`)
    const data = res.data
    editingId.value = data.id
    form.value.date = data.date
    form.value.description = data.description
    form.value.lines = data.lines.map((line) => ({
      account_id: line.account_id,
      debit: line.debit || '',
      credit: line.credit || '',
    }))
    showModal.value = true
  } catch {
    // Error handled globally
  }
}

// --- Line Management ---

function addLine() {
  if (form.value.lines.length >= 20) return
  form.value.lines.push({ account_id: '', debit: '', credit: '' })
}

function removeLine(index: number) {
  if (form.value.lines.length <= 2) return
  form.value.lines.splice(index, 1)
}

// --- Modal ---

function openCreateModal() {
  editingId.value = null
  form.value = {
    date: new Date().toISOString().slice(0, 10),
    description: '',
    lines: [
      { account_id: '', debit: '', credit: '' },
      { account_id: '', debit: '', credit: '' },
    ],
  }
  showModal.value = true
}

function closeModal() {
  showModal.value = false
  editingId.value = null
}

// --- Pagination ---

function goToPage(page: number) {
  fetchEntries(page)
}

// --- Init ---

fetchEntries()
fetchAccounts()
</script>

<template>
  <div>
    <!-- Header -->
    <div class="flex items-center justify-between">
      <div>
        <h1 class="text-2xl font-semibold text-gray-900 dark:text-white">Jurnal Umum</h1>
        <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">
          Catat transaksi dengan double-entry bookkeeping.
        </p>
      </div>
      <BaseButton variant="primary" size="sm" :icon="Plus" @click="openCreateModal">
        Buat Jurnal
      </BaseButton>
    </div>

    <!-- Loading -->
    <div v-if="loading" class="mt-8 flex justify-center">
      <RefreshCw :size="20" class="animate-spin text-gray-400" />
    </div>

    <!-- Table -->
    <div v-else class="mt-6 overflow-x-auto rounded-xl border border-gray-200 bg-white dark:border-gray-700 dark:bg-gray-800">
      <table class="w-full text-left text-sm">
        <thead class="border-b border-gray-100 bg-gray-50 dark:border-gray-700 dark:bg-gray-800/50">
          <tr>
            <th class="px-5 py-3 font-medium text-gray-600 dark:text-gray-400">No.</th>
            <th class="px-5 py-3 font-medium text-gray-600 dark:text-gray-400">Tanggal</th>
            <th class="px-5 py-3 font-medium text-gray-600 dark:text-gray-400">Keterangan</th>
            <th class="px-5 py-3 font-medium text-gray-600 dark:text-gray-400 text-right">Total Debit</th>
            <th class="px-5 py-3 font-medium text-gray-600 dark:text-gray-400 text-center">Aksi</th>
          </tr>
        </thead>
        <tbody class="divide-y divide-gray-100 dark:divide-gray-700">
          <tr v-for="entry in entries" :key="entry.id" class="group hover:bg-gray-50 dark:hover:bg-gray-700/30">
            <td class="px-5 py-3 text-gray-900 dark:text-white font-medium">{{ entry.entry_number }}</td>
            <td class="px-5 py-3 text-gray-600 dark:text-gray-300">{{ formatDate(entry.date, { day: 'numeric', month: 'short', year: 'numeric' }) }}</td>
            <td class="px-5 py-3 text-gray-600 dark:text-gray-300 max-w-xs truncate">{{ entry.description }}</td>
            <td class="px-5 py-3 text-gray-900 dark:text-white font-medium text-right">{{ formatCurrency(entry.total_debit) }}</td>
            <td class="px-5 py-3 text-center">
              <div class="flex items-center justify-center gap-1">
                <button
                  class="rounded p-1.5 text-gray-400 hover:text-primary-600 hover:bg-primary-50 dark:hover:bg-primary-900/20 transition-colors"
                  @click="editEntry(entry)"
                  title="Edit"
                >
                  <Pencil :size="15" />
                </button>
                <button
                  class="rounded p-1.5 text-gray-400 hover:text-red-600 hover:bg-red-50 dark:hover:bg-red-900/20 transition-colors"
                  @click="deleteEntry(entry)"
                  title="Hapus"
                >
                  <Trash2 :size="15" />
                </button>
              </div>
            </td>
          </tr>
        </tbody>
      </table>

      <!-- Empty state -->
      <div v-if="!entries.length" class="py-12 text-center text-sm text-gray-400">
        Belum ada jurnal. Klik "Buat Jurnal" untuk memulai.
      </div>
    </div>

    <!-- Pagination -->
    <div v-if="meta.last_page > 1" class="mt-6">
      <BasePagination
        :current-page="meta.current_page"
        :total-pages="meta.last_page"
        @update:current-page="goToPage"
      />
    </div>

    <!-- Create/Edit Modal -->
    <BaseModal v-model="showModal" size="lg" persistent>
      <template #default>
        <h2 class="text-lg font-semibold text-gray-900 dark:text-white">
          {{ isEditing ? 'Edit Jurnal' : 'Buat Jurnal Baru' }}
        </h2>

        <form class="mt-4 space-y-4" @submit.prevent="submitEntry">
          <!-- Date & Description -->
          <div class="grid grid-cols-1 gap-4 sm:grid-cols-2">
            <BaseInput v-model="form.date" label="Tanggal" type="date" required />
            <BaseInput
              v-model="form.description"
              label="Keterangan"
              placeholder="Deskripsi transaksi"
              :maxlength="255"
              required
            />
          </div>

          <!-- Lines Table -->
          <div class="mt-4">
            <div class="flex items-center justify-between mb-2">
              <label class="text-sm font-medium text-gray-700 dark:text-gray-300">Baris Jurnal</label>
              <button
                v-if="form.lines.length < 20"
                type="button"
                class="inline-flex items-center gap-1 text-xs font-medium text-primary-600 hover:text-primary-700 dark:text-primary-400"
                @click="addLine"
              >
                <Plus :size="14" />
                Tambah Baris
              </button>
            </div>

            <div class="overflow-x-auto rounded-lg border border-gray-200 dark:border-gray-700">
              <table class="w-full text-sm">
                <thead class="bg-gray-50 dark:bg-gray-800/50">
                  <tr>
                    <th class="px-3 py-2 text-left font-medium text-gray-600 dark:text-gray-400 min-w-[200px]">Akun</th>
                    <th class="px-3 py-2 text-right font-medium text-gray-600 dark:text-gray-400 w-36">Debit</th>
                    <th class="px-3 py-2 text-right font-medium text-gray-600 dark:text-gray-400 w-36">Kredit</th>
                    <th class="px-3 py-2 w-10"></th>
                  </tr>
                </thead>
                <tbody class="divide-y divide-gray-100 dark:divide-gray-700">
                  <tr v-for="(line, index) in form.lines" :key="index">
                    <td class="px-3 py-2">
                      <BaseSelect
                        v-model="line.account_id"
                        :options="accountOptions"
                        placeholder="Pilih akun..."
                        :clearable="false"
                        size="sm"
                      />
                    </td>
                    <td class="px-3 py-2">
                      <input
                        v-model.number="line.debit"
                        type="number"
                        min="0"
                        step="0.01"
                        placeholder="0"
                        class="w-full rounded-md border border-gray-300 bg-white px-2.5 py-1.5 text-sm text-right focus:border-primary-500 focus:outline-none focus:ring-1 focus:ring-primary-500 dark:border-gray-600 dark:bg-gray-800 dark:text-white"
                      />
                    </td>
                    <td class="px-3 py-2">
                      <input
                        v-model.number="line.credit"
                        type="number"
                        min="0"
                        step="0.01"
                        placeholder="0"
                        class="w-full rounded-md border border-gray-300 bg-white px-2.5 py-1.5 text-sm text-right focus:border-primary-500 focus:outline-none focus:ring-1 focus:ring-primary-500 dark:border-gray-600 dark:bg-gray-800 dark:text-white"
                      />
                    </td>
                    <td class="px-3 py-2 text-center">
                      <button
                        v-if="form.lines.length > 2"
                        type="button"
                        class="rounded p-1 text-gray-400 hover:text-red-500 transition-colors"
                        @click="removeLine(index)"
                        title="Hapus baris"
                      >
                        <Trash2 :size="14" />
                      </button>
                    </td>
                  </tr>
                </tbody>
                <tfoot class="border-t border-gray-200 bg-gray-50 dark:border-gray-700 dark:bg-gray-800/50">
                  <tr>
                    <td class="px-3 py-2 text-right font-medium text-gray-700 dark:text-gray-300">Total</td>
                    <td class="px-3 py-2 text-right font-bold text-gray-900 dark:text-white">{{ formatCurrency(totalDebit) }}</td>
                    <td class="px-3 py-2 text-right font-bold text-gray-900 dark:text-white">{{ formatCurrency(totalCredit) }}</td>
                    <td></td>
                  </tr>
                  <tr>
                    <td colspan="4" class="px-3 py-2">
                      <div
                        class="flex items-center gap-2 text-xs font-medium"
                        :class="isBalanced ? 'text-emerald-600 dark:text-emerald-400' : 'text-red-600 dark:text-red-400'"
                      >
                        <AlertCircle v-if="!isBalanced" :size="14" />
                        <span v-if="isBalanced">✓ Seimbang</span>
                        <span v-else>Tidak seimbang — selisih {{ formatCurrency(Math.abs(totalDebit - totalCredit)) }}</span>
                      </div>
                    </td>
                  </tr>
                </tfoot>
              </table>
            </div>
          </div>

          <!-- Actions -->
          <div class="flex justify-end gap-2 pt-2">
            <BaseButton variant="secondary" size="sm" type="button" @click="closeModal">
              Batal
            </BaseButton>
            <BaseButton variant="primary" size="sm" type="submit" :disabled="!canSubmit || submitting">
              {{ isEditing ? 'Simpan Perubahan' : 'Simpan' }}
            </BaseButton>
          </div>
        </form>
      </template>
    </BaseModal>
  </div>
</template>
