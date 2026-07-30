<script setup lang="ts">
import { ref, computed } from 'vue'
import { useToast } from '@purdia/toast'
import BaseButton from '@purdia/ui/src/components/BaseButton.vue'
import BaseInput from '@purdia/ui/src/components/BaseInput.vue'
import BaseSelect from '@purdia/ui/src/components/BaseSelect.vue'
import BaseModal from '@purdia/ui/src/components/BaseModal.vue'
import { Plus, Pencil, Trash2, ChevronDown, ChevronRight } from '@lucide/vue'
import ResetControls from './ResetControls.vue'
import type { Account, GroupedAccounts } from '@/types/accounting'
import * as accountingApi from '@/api/accounting'

const toast = useToast()

const typeLabels: Record<string, string> = {
  asset: 'Aset',
  liability: 'Kewajiban',
  equity: 'Ekuitas',
  revenue: 'Pendapatan',
  expense: 'Beban',
}

const typeOptions = [
  { label: 'Aset', value: 'asset' },
  { label: 'Kewajiban', value: 'liability' },
  { label: 'Ekuitas', value: 'equity' },
  { label: 'Pendapatan', value: 'revenue' },
  { label: 'Beban', value: 'expense' },
]

const accounts = ref<GroupedAccounts>({})
const loading = ref(true)
const showCreateModal = ref(false)
const showEditModal = ref(false)
const showDeleteModal = ref(false)
const submitting = ref(false)
const collapsedSections = ref<Record<string, boolean>>({})

const createForm = ref({ code: '', name: '', type: 'asset', parent_id: '' as number | '' })
const editForm = ref({ id: 0, code: '', name: '', type: '', parent_id: '' as number | '' })
const deleteTarget = ref<Account | null>(null)

// Flat list of all accounts for parent selects
const allAccounts = computed(() => {
  const flat: Account[] = []
  for (const type of Object.keys(accounts.value)) {
    if (Array.isArray(accounts.value[type])) {
      flat.push(...accounts.value[type])
    }
  }
  return flat
})

// Parent options filtered by selected type for create form
const createParentOptions = computed(() => {
  return allAccounts.value
    .filter((a) => a.type === createForm.value.type && a.depth < 3)
    .map((a) => ({ label: `${a.code} - ${a.name}`, value: a.id }))
})

// Parent options filtered by type for edit form (exclude self)
const editParentOptions = computed(() => {
  return allAccounts.value
    .filter((a) => a.type === editForm.value.type && a.depth < 3 && a.id !== editForm.value.id)
    .map((a) => ({ label: `${a.code} - ${a.name}`, value: a.id }))
})

function toggleSection(type: string) {
  collapsedSections.value[type] = !collapsedSections.value[type]
}

async function fetchData() {
  loading.value = true
  try {
    const res = await accountingApi.fetchAccounts()
    accounts.value = res.data
  } catch {
    // Error handled by @purdia/http onError
  } finally {
    loading.value = false
  }
}

function openCreateModal() {
  createForm.value = { code: '', name: '', type: 'asset', parent_id: '' }
  showCreateModal.value = true
}

async function createAccount() {
  if (!createForm.value.code || !createForm.value.name) return
  submitting.value = true
  try {
    await accountingApi.createAccount({
      code: createForm.value.code,
      name: createForm.value.name,
      type: createForm.value.type,
      parent_id: createForm.value.parent_id || null,
    })
    toast.success('Akun berhasil dibuat.')
    showCreateModal.value = false
    fetchData()
  } catch {
    // Error handled by @purdia/http onError
  } finally {
    submitting.value = false
  }
}

function openEditModal(account: Account) {
  editForm.value = {
    id: account.id,
    code: account.code,
    name: account.name,
    type: account.type,
    parent_id: account.parent_id ?? '',
  }
  showEditModal.value = true
}

async function updateAccount() {
  if (!editForm.value.name) return
  submitting.value = true
  try {
    await accountingApi.updateAccount(editForm.value.id, {
      name: editForm.value.name,
      parent_id: editForm.value.parent_id || null,
    })
    toast.success('Akun berhasil diperbarui.')
    showEditModal.value = false
    fetchData()
  } catch {
    // Error handled by @purdia/http onError
  } finally {
    submitting.value = false
  }
}

function openDeleteModal(account: Account) {
  deleteTarget.value = account
  showDeleteModal.value = true
}

async function deleteAccount() {
  if (!deleteTarget.value) return
  submitting.value = true
  try {
    await accountingApi.deleteAccount(deleteTarget.value.id)
    toast.success('Akun berhasil dihapus.')
    showDeleteModal.value = false
    deleteTarget.value = null
    fetchData()
  } catch {
    // Error handled by @purdia/http onError (409 for in-use)
  } finally {
    submitting.value = false
  }
}

fetchData()
</script>

<template>
  <div>
    <!-- Header -->
    <div class="flex items-center justify-between">
      <div>
        <h1 class="text-2xl font-semibold text-gray-900 dark:text-white">Chart of Accounts</h1>
        <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">Daftar akun yang digunakan dalam pencatatan akuntansi.</p>
      </div>
      <BaseButton variant="primary" size="sm" :icon="Plus" @click="openCreateModal">
        Tambah Akun
      </BaseButton>
    </div>

    <!-- Loading -->
    <div v-if="loading" class="mt-6 py-8 text-center text-sm text-gray-400">Memuat data...</div>

    <!-- Empty state -->
    <div v-else-if="!Object.keys(accounts).length" class="mt-6 py-8 text-center text-sm text-gray-400">
      Belum ada akun. Muat data contoh untuk memulai.
    </div>

    <!-- COA Tree -->
    <div v-else class="mt-6 space-y-4">
      <div
        v-for="(items, type) in accounts"
        :key="type"
        class="rounded-xl border border-gray-200 bg-white dark:border-gray-700 dark:bg-gray-800"
      >
        <!-- Section Header -->
        <button
          class="flex w-full items-center justify-between px-5 py-3 text-left"
          @click="toggleSection(type as string)"
        >
          <div class="flex items-center gap-2">
            <component
              :is="collapsedSections[type as string] ? ChevronRight : ChevronDown"
              :size="16"
              class="text-gray-400"
            />
            <h3 class="text-sm font-semibold text-gray-700 dark:text-gray-300">
              {{ typeLabels[type as string] || type }}
            </h3>
            <span class="rounded-full bg-gray-100 px-2 py-0.5 text-xs text-gray-500 dark:bg-gray-700 dark:text-gray-400">
              {{ items.length }}
            </span>
          </div>
        </button>

        <!-- Account List -->
        <div v-if="!collapsedSections[type as string]" class="border-t border-gray-100 dark:border-gray-700">
          <div class="divide-y divide-gray-50 dark:divide-gray-700/50">
            <div
              v-for="account in items"
              :key="account.id"
              class="group flex items-center justify-between px-5 py-2.5 hover:bg-gray-50 dark:hover:bg-gray-700/30"
              :style="{ paddingLeft: 20 + (account.depth - 1) * 20 + 'px' }"
            >
              <div class="flex items-center gap-3">
                <span class="font-mono text-xs text-gray-500 dark:text-gray-400">{{ account.code }}</span>
                <span class="text-sm text-gray-900 dark:text-white">{{ account.name }}</span>
              </div>
              <div class="flex items-center gap-1 opacity-0 group-hover:opacity-100 transition-opacity">
                <button
                  class="rounded p-1.5 text-gray-400 hover:text-primary-600 hover:bg-primary-50 dark:hover:bg-primary-900/20 transition-colors"
                  @click.stop="openEditModal(account)"
                  title="Edit"
                >
                  <Pencil :size="14" />
                </button>
                <button
                  class="rounded p-1.5 text-gray-400 hover:text-red-600 hover:bg-red-50 dark:hover:bg-red-900/20 transition-colors"
                  @click.stop="openDeleteModal(account)"
                  title="Hapus"
                >
                  <Trash2 :size="14" />
                </button>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>

    <!-- Reset & Sample Data Controls -->
    <div class="mt-8">
      <ResetControls @refresh-data="fetchData" />
    </div>

    <!-- Create Modal -->
    <BaseModal v-model="showCreateModal" size="md" persistent>
      <template #default>
        <h2 class="text-lg font-semibold text-gray-900 dark:text-white">Tambah Akun</h2>
        <form class="mt-4 space-y-4" @submit.prevent="createAccount">
          <BaseInput
            v-model="createForm.code"
            label="Kode Akun"
            placeholder="contoh: 1500"
            :maxlength="10"
            required
          />
          <BaseInput
            v-model="createForm.name"
            label="Nama Akun"
            placeholder="contoh: Investasi"
            :maxlength="100"
            required
          />
          <BaseSelect
            v-model="createForm.type"
            label="Tipe Akun"
            :options="typeOptions"
            :clearable="false"
            required
          />
          <BaseSelect
            v-model="createForm.parent_id"
            label="Akun Induk (opsional)"
            :options="createParentOptions"
            placeholder="Tanpa induk"
          />
          <div class="flex justify-end gap-2 pt-2">
            <BaseButton variant="secondary" size="sm" type="button" @click="showCreateModal = false">Batal</BaseButton>
            <BaseButton variant="primary" size="sm" type="submit" :loading="submitting">Simpan</BaseButton>
          </div>
        </form>
      </template>
    </BaseModal>

    <!-- Edit Modal -->
    <BaseModal v-model="showEditModal" size="md" persistent>
      <template #default>
        <h2 class="text-lg font-semibold text-gray-900 dark:text-white">Edit Akun</h2>
        <form class="mt-4 space-y-4" @submit.prevent="updateAccount">
          <BaseInput
            :model-value="editForm.code"
            label="Kode Akun"
            disabled
          />
          <BaseSelect
            :model-value="editForm.type"
            label="Tipe Akun"
            :options="typeOptions"
            disabled
          />
          <BaseInput
            v-model="editForm.name"
            label="Nama Akun"
            :maxlength="100"
            required
          />
          <BaseSelect
            v-model="editForm.parent_id"
            label="Akun Induk (opsional)"
            :options="editParentOptions"
            placeholder="Tanpa induk"
          />
          <div class="flex justify-end gap-2 pt-2">
            <BaseButton variant="secondary" size="sm" type="button" @click="showEditModal = false">Batal</BaseButton>
            <BaseButton variant="primary" size="sm" type="submit" :loading="submitting">Simpan</BaseButton>
          </div>
        </form>
      </template>
    </BaseModal>

    <!-- Delete Confirmation Modal -->
    <BaseModal v-model="showDeleteModal" size="sm">
      <template #default>
        <h2 class="text-lg font-semibold text-gray-900 dark:text-white">Hapus Akun</h2>
        <p class="mt-2 text-sm text-gray-600 dark:text-gray-300">
          Apakah Anda yakin ingin menghapus akun
          <span class="font-medium">{{ deleteTarget?.code }} — {{ deleteTarget?.name }}</span>?
        </p>
        <p class="mt-1 text-xs text-gray-500 dark:text-gray-400">
          Akun yang sudah digunakan dalam jurnal tidak dapat dihapus.
        </p>
      </template>
      <template #footer>
        <BaseButton variant="ghost" size="sm" @click="showDeleteModal = false">Batal</BaseButton>
        <BaseButton variant="danger" size="sm" :loading="submitting" @click="deleteAccount">Hapus</BaseButton>
      </template>
    </BaseModal>
  </div>
</template>
