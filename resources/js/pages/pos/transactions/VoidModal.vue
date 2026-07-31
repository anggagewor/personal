<script setup lang="ts">
import { ref, computed, watch } from 'vue'
import { useToast } from '@purdia/toast'
import BaseModal from '@purdia/ui/src/components/BaseModal.vue'
import BaseButton from '@purdia/ui/src/components/BaseButton.vue'
import { AlertTriangle } from '@lucide/vue'
import type { Transaction } from '@/types/pos'
import * as posApi from '@/api/pos'

const props = defineProps<{
  modelValue: boolean
  transaction: Transaction | null
}>()

const emit = defineEmits<{
  'update:modelValue': [value: boolean]
  voided: []
}>()

const toast = useToast()
const submitting = ref(false)
const reason = ref('')

const isOpen = computed({
  get: () => props.modelValue,
  set: (v) => emit('update:modelValue', v),
})

const isOlderThan24h = computed(() => {
  if (!props.transaction) return false
  const created = new Date(props.transaction.created_at)
  const now = new Date()
  const diffMs = now.getTime() - created.getTime()
  return diffMs > 24 * 60 * 60 * 1000
})

watch(
  () => props.modelValue,
  (open) => {
    if (open) {
      reason.value = ''
    }
  },
)

async function submit() {
  if (!props.transaction) return
  if (!reason.value.trim()) return

  submitting.value = true
  try {
    await posApi.voidTransaction(props.transaction.id, { reason: reason.value.trim() })
    toast.success('Transaksi berhasil di-void.')
    isOpen.value = false
    emit('voided')
  } catch {
    // Error handled globally
  } finally {
    submitting.value = false
  }
}
</script>

<template>
  <BaseModal v-model="isOpen" size="sm" persistent>
    <template #default>
      <h2 class="text-lg font-semibold text-gray-900 dark:text-white">Void Transaksi</h2>

      <p class="mt-2 text-sm text-gray-600 dark:text-gray-300">
        Apakah Anda yakin ingin membatalkan transaksi
        <strong>{{ transaction?.transaction_number }}</strong>?
        Stok akan dikembalikan.
      </p>

      <!-- Warning for old transactions -->
      <div
        v-if="isOlderThan24h"
        class="mt-3 flex items-start gap-2 rounded-lg bg-amber-50 p-3 text-sm text-amber-800 dark:bg-amber-900/20 dark:text-amber-300"
      >
        <AlertTriangle :size="16" class="mt-0.5 shrink-0" />
        <span>Transaksi ini lebih dari 24 jam. Pastikan alasan pembatalan jelas.</span>
      </div>

      <form class="mt-4 space-y-4" @submit.prevent="submit">
        <div>
          <label class="mb-1 block text-sm font-medium text-gray-700 dark:text-gray-300">
            Alasan Void <span class="text-red-500">*</span>
          </label>
          <textarea
            v-model="reason"
            rows="3"
            class="w-full rounded-lg border border-gray-300 bg-white px-3 py-2 text-sm text-gray-900 placeholder-gray-400 focus:border-primary-500 focus:outline-none focus:ring-1 focus:ring-primary-500 dark:border-gray-600 dark:bg-gray-700 dark:text-white dark:placeholder-gray-500"
            placeholder="Masukkan alasan pembatalan..."
            required
          ></textarea>
        </div>

        <div class="flex justify-end gap-2 pt-2">
          <BaseButton variant="secondary" size="sm" type="button" @click="isOpen = false">
            Batal
          </BaseButton>
          <BaseButton
            variant="danger"
            size="sm"
            type="submit"
            :disabled="submitting || !reason.trim()"
          >
            {{ submitting ? 'Memproses...' : 'Void Transaksi' }}
          </BaseButton>
        </div>
      </form>
    </template>
  </BaseModal>
</template>
