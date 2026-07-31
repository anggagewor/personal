<script setup lang="ts">
import { ref, computed } from 'vue'
import { useRoute, useRouter } from 'vue-router'
import { useToast } from '@purdia/toast'
import BaseButton from '@purdia/ui/src/components/BaseButton.vue'
import BaseInput from '@purdia/ui/src/components/BaseInput.vue'
import BaseModal from '@purdia/ui/src/components/BaseModal.vue'
import BaseEmptyState from '@purdia/ui/src/components/BaseEmptyState.vue'
import BaseBadge from '@purdia/ui/src/components/BaseBadge.vue'
import { Plus, QrCode, Trash2, XCircle, LayoutGrid } from '@lucide/vue'
import type { PosTable } from '@/types/pos'
import * as posApi from '@/api/pos'

const route = useRoute()
const router = useRouter()
const toast = useToast()

const outletId = computed(() => Number(route.query.outlet))

const tables = ref<PosTable[]>([])
const loading = ref(true)
const showCreateModal = ref(false)
const submitting = ref(false)
const newTableName = ref('')

async function fetchTables() {
  if (!outletId.value) return
  loading.value = true
  try {
    const res = await posApi.fetchTables(outletId.value)
    tables.value = res.data
  } catch {
    // Error handled globally
  } finally {
    loading.value = false
  }
}

function openCreateModal() {
  newTableName.value = ''
  showCreateModal.value = true
}

async function createTable() {
  if (!newTableName.value.trim()) return
  submitting.value = true
  try {
    await posApi.createTable(outletId.value, { name: newTableName.value.trim() })
    toast.success('Meja berhasil ditambahkan.')
    showCreateModal.value = false
    fetchTables()
  } catch {
    // Error handled globally
  } finally {
    submitting.value = false
  }
}

async function deleteTable(table: PosTable) {
  if (!confirm(`Hapus meja "${table.name}"?`)) return
  try {
    await posApi.deleteTable(table.id)
    toast.success('Meja berhasil dihapus.')
    fetchTables()
  } catch {
    // Error handled globally
  }
}

async function closeSession(table: PosTable) {
  if (!confirm(`Tutup sesi meja "${table.name}"?`)) return
  try {
    await posApi.closeTableSession(table.id)
    toast.success('Sesi meja berhasil ditutup.')
    fetchTables()
  } catch {
    // Error handled globally
  }
}

function viewQrCode(table: PosTable) {
  router.push({ name: 'pos.tables.qr', query: { outlet: outletId.value, table: table.id } })
}

function goToOrderQueue() {
  router.push({ name: 'pos.tables.order-queue', query: { outlet: outletId.value } })
}

function getQrUrl(table: PosTable) {
  return `${window.location.origin}/pos/qr/${table.token}/menu`
}

fetchTables()
</script>

<template>
  <div>
    <!-- Header -->
    <div class="flex items-center justify-between">
      <div>
        <h1 class="text-2xl font-semibold text-gray-900 dark:text-white">Manajemen Meja</h1>
        <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">Kelola meja outlet dan sesi QR Order.</p>
      </div>
      <div class="flex gap-2">
        <BaseButton variant="secondary" size="sm" @click="goToOrderQueue">
          Antrian Pesanan
        </BaseButton>
        <BaseButton variant="primary" size="sm" :icon="Plus" @click="openCreateModal">
          Tambah Meja
        </BaseButton>
      </div>
    </div>

    <!-- Loading -->
    <div v-if="loading" class="mt-6 py-8 text-center text-sm text-gray-400">Memuat data...</div>

    <!-- Empty state -->
    <BaseEmptyState
      v-else-if="!tables.length"
      :icon="LayoutGrid"
      title="Belum ada meja"
      description="Tambahkan meja untuk mulai menggunakan fitur QR Order."
      class="mt-12"
    >
      <BaseButton variant="primary" size="sm" :icon="Plus" @click="openCreateModal">
        Tambah Meja
      </BaseButton>
    </BaseEmptyState>

    <!-- Table grid -->
    <div v-else class="mt-6 grid grid-cols-1 gap-4 sm:grid-cols-2 lg:grid-cols-3">
      <div
        v-for="table in tables"
        :key="table.id"
        class="group relative rounded-xl border border-gray-200 bg-white p-5 transition-shadow hover:shadow-md dark:border-gray-700 dark:bg-gray-800"
      >
        <div class="flex items-start justify-between">
          <div>
            <h3 class="text-sm font-semibold text-gray-900 dark:text-white">{{ table.name }}</h3>
            <p class="mt-1 text-xs text-gray-400 font-mono truncate max-w-[180px]">{{ table.token }}</p>
          </div>
          <BaseBadge
            v-if="table.active_session"
            variant="success"
          >
            Aktif
          </BaseBadge>
          <BaseBadge v-else variant="default">
            Kosong
          </BaseBadge>
        </div>

        <!-- Active session info -->
        <div v-if="table.active_session" class="mt-3 rounded-lg bg-green-50 p-3 text-xs text-green-700 dark:bg-green-900/20 dark:text-green-300">
          <p>Sesi dimulai: {{ new Date(table.active_session.opened_at).toLocaleString('id-ID') }}</p>
        </div>

        <!-- QR URL -->
        <p class="mt-3 text-xs text-gray-400 truncate">{{ getQrUrl(table) }}</p>

        <!-- Actions -->
        <div class="mt-4 flex flex-wrap gap-2">
          <BaseButton variant="secondary" size="xs" :icon="QrCode" @click="viewQrCode(table)">
            QR Code
          </BaseButton>
          <BaseButton
            v-if="table.active_session"
            variant="secondary"
            size="xs"
            :icon="XCircle"
            @click="closeSession(table)"
          >
            Tutup Sesi
          </BaseButton>
          <button
            class="rounded p-1.5 text-gray-400 hover:text-red-500 hover:bg-red-50 dark:hover:bg-red-900/20 transition-colors"
            title="Hapus meja"
            @click="deleteTable(table)"
          >
            <Trash2 :size="14" />
          </button>
        </div>
      </div>
    </div>

    <!-- Create Table Modal -->
    <BaseModal v-model="showCreateModal" size="sm" persistent>
      <template #default>
        <h2 class="text-lg font-semibold text-gray-900 dark:text-white">Tambah Meja Baru</h2>
        <form class="mt-4 space-y-4" @submit.prevent="createTable">
          <BaseInput
            v-model="newTableName"
            label="Nama Meja"
            placeholder="contoh: Meja 1, Lantai 2 - A"
            :maxlength="50"
            required
          />
          <div class="flex justify-end gap-2 pt-2">
            <BaseButton variant="secondary" size="sm" type="button" @click="showCreateModal = false">Batal</BaseButton>
            <BaseButton variant="primary" size="sm" type="submit" :loading="submitting">Simpan</BaseButton>
          </div>
        </form>
      </template>
    </BaseModal>
  </div>
</template>
