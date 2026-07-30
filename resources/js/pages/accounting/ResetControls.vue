<script setup lang="ts">
import { ref } from 'vue'
import { post } from '@purdia/http'
import { useToast } from '@purdia/toast'
import BaseButton from '@purdia/ui/src/components/BaseButton.vue'
import BaseModal from '@purdia/ui/src/components/BaseModal.vue'
import { RotateCcw, Trash2, Database } from '@lucide/vue'

const emit = defineEmits<{
  refreshData: []
}>()

const toast = useToast()

const showResetJournalModal = ref(false)
const showResetAllModal = ref(false)
const loadingJournal = ref(false)
const loadingAll = ref(false)
const loadingSample = ref(false)

async function loadSampleData() {
  loadingSample.value = true
  try {
    const res = await post<{ message: string; count: number }>('/accounting/sample-data')
    toast.success(`Berhasil memuat ${res.data.count} jurnal contoh.`)
    emit('refreshData')
  } catch {
    // Error handled by @purdia/http onError
  } finally {
    loadingSample.value = false
  }
}

async function resetJournal() {
  loadingJournal.value = true
  try {
    const res = await post<{ message: string; count: number }>('/accounting/reset/journal', { confirm: true })
    toast.success(`${res.data.count} jurnal berhasil dihapus.`)
    showResetJournalModal.value = false
    emit('refreshData')
  } catch {
    // Error handled by @purdia/http onError
  } finally {
    loadingJournal.value = false
  }
}

async function resetAll() {
  loadingAll.value = true
  try {
    await post<{ message: string }>('/accounting/reset/all', { confirm: true })
    toast.success('Semua data akuntansi berhasil direset.')
    showResetAllModal.value = false
    emit('refreshData')
  } catch {
    // Error handled by @purdia/http onError
  } finally {
    loadingAll.value = false
  }
}
</script>

<template>
  <div class="rounded-xl border border-gray-200 bg-white p-5 dark:border-gray-700 dark:bg-gray-800">
    <h3 class="text-sm font-semibold text-gray-900 dark:text-white">Pengaturan Data</h3>
    <p class="mt-1 text-xs text-gray-500 dark:text-gray-400">
      Muat data contoh untuk belajar, atau reset data untuk memulai dari awal.
    </p>

    <div class="mt-4 flex flex-wrap gap-3">
      <BaseButton
        variant="primary"
        size="sm"
        :icon="Database"
        :loading="loadingSample"
        @click="loadSampleData"
      >
        Muat Data Contoh
      </BaseButton>

      <BaseButton
        variant="warning"
        size="sm"
        :icon="RotateCcw"
        @click="showResetJournalModal = true"
      >
        Reset Jurnal
      </BaseButton>

      <BaseButton
        variant="danger"
        size="sm"
        :icon="Trash2"
        @click="showResetAllModal = true"
      >
        Reset Semua
      </BaseButton>
    </div>
  </div>

  <!-- Reset Jurnal Confirmation Modal -->
  <BaseModal v-model="showResetJournalModal" size="sm" title="Reset Jurnal">
    <p class="text-sm text-gray-600 dark:text-gray-300">
      Apakah Anda yakin ingin menghapus semua jurnal? Data akun akan tetap disimpan.
    </p>
    <template #footer>
      <BaseButton variant="ghost" size="sm" @click="showResetJournalModal = false">
        Batal
      </BaseButton>
      <BaseButton
        variant="warning"
        size="sm"
        :loading="loadingJournal"
        @click="resetJournal"
      >
        Ya, Reset Jurnal
      </BaseButton>
    </template>
  </BaseModal>

  <!-- Reset Semua Warning Modal -->
  <BaseModal v-model="showResetAllModal" size="sm" title="Reset Semua Data">
    <div class="rounded-lg border border-red-200 bg-red-50 p-3 dark:border-red-800 dark:bg-red-900/20">
      <p class="text-sm font-medium text-red-800 dark:text-red-300">
        PERINGATAN
      </p>
      <p class="mt-1 text-sm text-red-700 dark:text-red-400">
        Semua data akuntansi (jurnal DAN akun) akan dihapus dan akun default akan dimuat ulang. Tindakan ini tidak dapat dibatalkan.
      </p>
    </div>
    <template #footer>
      <BaseButton variant="ghost" size="sm" @click="showResetAllModal = false">
        Batal
      </BaseButton>
      <BaseButton
        variant="danger"
        size="sm"
        :loading="loadingAll"
        @click="resetAll"
      >
        Ya, Reset Semua
      </BaseButton>
    </template>
  </BaseModal>
</template>
