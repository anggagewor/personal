<script setup lang="ts">
import { ref, computed } from 'vue'
import { useToast } from '@purdia/toast'
import { secureSet, secureGet, secureRemove } from '@purdia/crypto'
import BaseButton from '@purdia/ui/src/components/BaseButton.vue'
import BaseInput from '@purdia/ui/src/components/BaseInput.vue'
import BaseModal from '@purdia/ui/src/components/BaseModal.vue'
import { Plus, Trash2, Lock, Eye, EyeOff, Copy, ExternalLink, Search } from '@lucide/vue'
import type { VaultEntry } from '@/types/vault'
import * as vaultApi from '@/api/vault'

const toast = useToast()

const entries = ref<VaultEntry[]>([])
const loading = ref(true)
const search = ref('')
const showForm = ref(false)
const editingEntry = ref<VaultEntry | null>(null)
const visiblePasswords = ref<Set<number>>(new Set())
const decryptedPasswords = ref<Map<number, string>>(new Map())

const form = ref({ name: '', username: '', password: '', url: '', notes: '', category: '' })

const filteredEntries = computed(() => {
  if (!search.value) return entries.value
  const q = search.value.toLowerCase()
  return entries.value.filter(
    (e) => e.name.toLowerCase().includes(q) || e.username?.toLowerCase().includes(q) || e.url?.toLowerCase().includes(q),
  )
})

async function fetchEntries() {
  loading.value = true
  try {
    const res = await vaultApi.fetchVaultEntries()
    entries.value = res.data
  } catch {
    // Error handled globally
  } finally {
    loading.value = false
  }
}

async function saveEntry() {
  if (!form.value.name || !form.value.password) return

  // Encrypt password client-side before sending
  const storageKey = `vault_pwd_temp`
  await secureSet(storageKey, form.value.password)
  const encryptedPassword = localStorage.getItem('purdia_' + storageKey) || form.value.password
  secureRemove(storageKey)

  const payload = {
    name: form.value.name,
    username: form.value.username || null,
    encrypted_password: encryptedPassword,
    url: form.value.url || null,
    notes: form.value.notes || null,
    category: form.value.category || null,
  }

  try {
    if (editingEntry.value) {
      await vaultApi.updateVaultEntry(editingEntry.value.id, payload)
      toast.success('Password berhasil diperbarui.')
    } else {
      await vaultApi.createVaultEntry(payload)
      toast.success('Password berhasil disimpan.')
    }
    closeForm()
    fetchEntries()
  } catch {
    // Error handled globally
  }
}

async function deleteEntry(entry: VaultEntry) {
  try {
    await vaultApi.deleteVaultEntry(entry.id)
    toast.success('Password berhasil dihapus.')
    entries.value = entries.value.filter((e) => e.id !== entry.id)
  } catch {
    // Error handled globally
  }
}

async function togglePassword(entry: VaultEntry) {
  if (visiblePasswords.value.has(entry.id)) {
    visiblePasswords.value.delete(entry.id)
    decryptedPasswords.value.delete(entry.id)
    return
  }

  // Decrypt from the encrypted blob
  try {
    const key = `vault_dec_${entry.id}`
    localStorage.setItem('purdia_' + key, entry.encryptedPassword)
    const decrypted = await secureGet(key)
    secureRemove(key)

    if (decrypted) {
      decryptedPasswords.value.set(entry.id, decrypted)
    } else {
      decryptedPasswords.value.set(entry.id, '(gagal dekripsi)')
    }
    visiblePasswords.value.add(entry.id)
  } catch {
    decryptedPasswords.value.set(entry.id, '(gagal dekripsi)')
    visiblePasswords.value.add(entry.id)
  }
}

async function copyPassword(entry: VaultEntry) {
  try {
    const key = `vault_dec_${entry.id}`
    localStorage.setItem('purdia_' + key, entry.encryptedPassword)
    const decrypted = await secureGet(key)
    secureRemove(key)

    if (decrypted) {
      await navigator.clipboard.writeText(decrypted)
      toast.success('Password disalin ke clipboard.')
    } else {
      toast.error('Gagal mendekripsi password.')
    }
  } catch {
    toast.error('Gagal menyalin password.')
  }
}

function openNew() {
  editingEntry.value = null
  form.value = { name: '', username: '', password: '', url: '', notes: '', category: '' }
  showForm.value = true
}

function openEdit(entry: VaultEntry) {
  editingEntry.value = entry
  form.value = {
    name: entry.name,
    username: entry.username ?? '',
    password: '', // User must re-enter password on edit
    url: entry.url ?? '',
    notes: entry.notes ?? '',
    category: entry.category ?? '',
  }
  showForm.value = true
}

function closeForm() {
  showForm.value = false
  editingEntry.value = null
}

fetchEntries()
</script>

<template>
  <div>
    <div class="flex items-center justify-between">
      <div>
        <h1 class="text-2xl font-semibold text-gray-900 dark:text-white">Password Vault</h1>
        <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">Simpan kredensial dengan aman (terenkripsi).</p>
      </div>
      <BaseButton variant="primary" size="sm" :icon="Plus" @click="openNew">Tambah</BaseButton>
    </div>

    <!-- Search -->
    <div class="relative mt-5">
      <Search :size="16" class="absolute left-3 top-1/2 -translate-y-1/2 text-gray-400" />
      <input
        v-model="search"
        type="text"
        placeholder="Cari password..."
        class="w-full rounded-lg border border-gray-300 bg-white py-2 pl-10 pr-4 text-sm focus:border-primary-500 focus:outline-none focus:ring-1 focus:ring-primary-500 dark:border-gray-600 dark:bg-gray-800 dark:text-white"
      />
    </div>

    <!-- Entries list -->
    <div v-if="filteredEntries.length" class="mt-5 space-y-2">
      <div
        v-for="entry in filteredEntries"
        :key="entry.id"
        class="group rounded-lg border border-gray-200 bg-white px-5 py-4 dark:border-gray-700 dark:bg-gray-800"
      >
        <div class="flex items-center gap-4">
          <div class="flex h-10 w-10 shrink-0 items-center justify-center rounded-lg bg-primary-100 text-primary-600 dark:bg-primary-900/30 dark:text-primary-400">
            <Lock :size="18" />
          </div>
          <div class="flex-1 min-w-0">
            <div class="flex items-center gap-2">
              <p class="text-sm font-semibold text-gray-900 dark:text-white cursor-pointer" @click="openEdit(entry)">{{ entry.name }}</p>
              <span v-if="entry.category" class="rounded bg-gray-100 px-1.5 py-0.5 text-xs text-gray-500 dark:bg-gray-700 dark:text-gray-400">{{ entry.category }}</span>
            </div>
            <p v-if="entry.username" class="text-xs text-gray-500 dark:text-gray-400">{{ entry.username }}</p>
            <div class="mt-1 flex items-center gap-1">
              <span class="font-mono text-xs text-gray-400">
                {{ visiblePasswords.has(entry.id) ? decryptedPasswords.get(entry.id) : '••••••••••' }}
              </span>
            </div>
          </div>
          <div class="flex items-center gap-1">
            <button class="rounded p-1.5 text-gray-400 hover:bg-gray-100 hover:text-gray-600 dark:hover:bg-gray-700" title="Toggle visibility" @click="togglePassword(entry)">
              <component :is="visiblePasswords.has(entry.id) ? EyeOff : Eye" :size="14" />
            </button>
            <button class="rounded p-1.5 text-gray-400 hover:bg-gray-100 hover:text-gray-600 dark:hover:bg-gray-700" title="Copy password" @click="copyPassword(entry)">
              <Copy :size="14" />
            </button>
            <a v-if="entry.url" :href="entry.url" target="_blank" class="rounded p-1.5 text-gray-400 hover:bg-gray-100 hover:text-gray-600 dark:hover:bg-gray-700" title="Open URL">
              <ExternalLink :size="14" />
            </a>
            <button class="rounded p-1.5 text-gray-400 opacity-0 group-hover:opacity-100 hover:text-red-500" @click="deleteEntry(entry)">
              <Trash2 :size="14" />
            </button>
          </div>
        </div>
      </div>
    </div>

    <div v-else-if="!loading" class="mt-12 flex flex-col items-center text-center">
      <Lock :size="48" class="text-gray-300 dark:text-gray-600" />
      <p class="mt-3 text-sm text-gray-500 dark:text-gray-400">Belum ada password tersimpan.</p>
    </div>

    <!-- Form modal -->
    <BaseModal v-model="showForm" size="md">
      <template #default>
        <h2 class="text-lg font-semibold text-gray-900 dark:text-white">{{ editingEntry ? 'Edit Password' : 'Tambah Password' }}</h2>
        <form class="mt-4 space-y-4" @submit.prevent="saveEntry">
          <BaseInput v-model="form.name" label="Nama / Layanan" placeholder="Google, GitHub, dll" required />
          <BaseInput v-model="form.username" label="Username / Email" placeholder="user@example.com" />
          <BaseInput v-model="form.password" label="Password" type="password" :placeholder="editingEntry ? 'Masukkan password baru' : 'Password'" required />
          <BaseInput v-model="form.url" label="URL (opsional)" placeholder="https://..." />
          <BaseInput v-model="form.category" label="Kategori (opsional)" placeholder="Sosial Media, Work, dll" />
          <BaseInput v-model="form.notes" label="Catatan (opsional)" placeholder="Catatan tambahan" />
          <div class="flex justify-end gap-2 pt-2">
            <BaseButton variant="secondary" size="sm" type="button" @click="closeForm">Batal</BaseButton>
            <BaseButton variant="primary" size="sm" type="submit">Simpan</BaseButton>
          </div>
        </form>
      </template>
    </BaseModal>
  </div>
</template>
