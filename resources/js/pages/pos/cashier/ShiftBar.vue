<script setup lang="ts">
import { ref, onMounted } from 'vue'
import { useToast } from '@purdia/toast'
import { formatCurrency } from '@purdia/utils'
import BaseButton from '@purdia/ui/src/components/BaseButton.vue'
import BaseInput from '@purdia/ui/src/components/BaseInput.vue'
import BaseModal from '@purdia/ui/src/components/BaseModal.vue'
import { Clock, DoorOpen, DoorClosed } from '@lucide/vue'
import type { CashierShift } from '@/types/pos'
import * as posApi from '@/api/pos'

const props = defineProps<{
  outletId: number
}>()

const emit = defineEmits<{
  shiftChanged: [shift: CashierShift | null]
}>()

const toast = useToast()

const activeShift = ref<CashierShift | null>(null)
const loading = ref(true)

// Open shift modal
const showOpenModal = ref(false)
const openingAmount = ref(0)
const openingSubmitting = ref(false)

// Close shift modal
const showCloseModal = ref(false)
const closingAmount = ref(0)
const closeNotes = ref('')
const closingSubmitting = ref(false)

async function fetchActiveShift() {
  loading.value = true
  try {
    const res = await posApi.fetchActiveShift(props.outletId)
    activeShift.value = res.data
    emit('shiftChanged', activeShift.value)
  } catch {
    // handled
  } finally {
    loading.value = false
  }
}

function promptOpenShift() {
  openingAmount.value = 0
  showOpenModal.value = true
}

async function submitOpenShift() {
  openingSubmitting.value = true
  try {
    const res = await posApi.openShift(props.outletId, {
      opening_amount: openingAmount.value,
    })
    activeShift.value = res.data
    emit('shiftChanged', activeShift.value)
    showOpenModal.value = false
    toast.success('Shift berhasil dibuka.')
  } catch {
    // handled
  } finally {
    openingSubmitting.value = false
  }
}

function promptCloseShift() {
  closingAmount.value = 0
  closeNotes.value = ''
  showCloseModal.value = true
}

async function submitCloseShift() {
  if (!activeShift.value) return
  closingSubmitting.value = true
  try {
    await posApi.closeShift(activeShift.value.id, {
      closing_amount: closingAmount.value,
      notes: closeNotes.value || undefined,
    })
    activeShift.value = null
    emit('shiftChanged', null)
    showCloseModal.value = false
    toast.success('Shift berhasil ditutup.')
  } catch {
    // handled
  } finally {
    closingSubmitting.value = false
  }
}

onMounted(() => {
  fetchActiveShift()
})
</script>

<template>
  <div class="flex items-center gap-3 rounded-lg border px-3 py-2"
    :class="activeShift
      ? 'border-green-200 bg-green-50 dark:border-green-800 dark:bg-green-900/20'
      : 'border-amber-200 bg-amber-50 dark:border-amber-800 dark:bg-amber-900/20'"
  >
    <Clock :size="16" :class="activeShift ? 'text-green-600 dark:text-green-400' : 'text-amber-600 dark:text-amber-400'" />

    <div v-if="loading" class="text-xs text-gray-500">Memuat shift...</div>

    <template v-else-if="activeShift">
      <div class="flex-1 min-w-0">
        <p class="text-xs font-medium text-green-800 dark:text-green-300 truncate">
          Shift aktif — {{ activeShift.cashier_name }}
        </p>
        <p class="text-[10px] text-green-600 dark:text-green-400">
          Modal: {{ formatCurrency(activeShift.opening_amount) }}
        </p>
      </div>
      <BaseButton variant="secondary" size="xs" :icon="DoorClosed" @click="promptCloseShift">
        Tutup Shift
      </BaseButton>
    </template>

    <template v-else>
      <div class="flex-1">
        <p class="text-xs font-medium text-amber-800 dark:text-amber-300">
          Belum ada shift aktif
        </p>
      </div>
      <BaseButton variant="primary" size="xs" :icon="DoorOpen" @click="promptOpenShift">
        Buka Shift
      </BaseButton>
    </template>
  </div>

  <!-- Open Shift Modal -->
  <BaseModal v-model="showOpenModal" size="sm" persistent>
    <template #default>
      <h2 class="text-lg font-semibold text-gray-900 dark:text-white">Buka Shift</h2>
      <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">Masukkan jumlah uang awal di kasir.</p>
      <form class="mt-4 space-y-4" @submit.prevent="submitOpenShift">
        <BaseInput
          v-model.number="openingAmount"
          label="Modal Awal (Rp)"
          type="number"
          :min="0"
          placeholder="0"
          required
        />
        <div class="flex justify-end gap-2">
          <BaseButton variant="secondary" size="sm" @click="showOpenModal = false">Batal</BaseButton>
          <BaseButton variant="primary" size="sm" :loading="openingSubmitting" type="submit">
            Buka Shift
          </BaseButton>
        </div>
      </form>
    </template>
  </BaseModal>

  <!-- Close Shift Modal -->
  <BaseModal v-model="showCloseModal" size="sm" persistent>
    <template #default>
      <h2 class="text-lg font-semibold text-gray-900 dark:text-white">Tutup Shift</h2>
      <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">Hitung uang tunai aktual di kasir.</p>
      <form class="mt-4 space-y-4" @submit.prevent="submitCloseShift">
        <BaseInput
          v-model.number="closingAmount"
          label="Uang Aktual (Rp)"
          type="number"
          :min="0"
          placeholder="0"
          required
        />
        <BaseInput
          v-model="closeNotes"
          label="Catatan (opsional)"
          placeholder="Catatan penutupan shift..."
        />
        <div class="flex justify-end gap-2">
          <BaseButton variant="secondary" size="sm" @click="showCloseModal = false">Batal</BaseButton>
          <BaseButton variant="primary" size="sm" :loading="closingSubmitting" type="submit">
            Tutup Shift
          </BaseButton>
        </div>
      </form>
    </template>
  </BaseModal>
</template>
