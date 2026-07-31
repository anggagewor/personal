<script setup lang="ts">
import { ref, computed, watch } from 'vue'
import { useToast } from '@purdia/toast'
import BaseModal from '@purdia/ui/src/components/BaseModal.vue'
import BaseButton from '@purdia/ui/src/components/BaseButton.vue'
import BaseInput from '@purdia/ui/src/components/BaseInput.vue'
import type { Member, MemberPayload } from '@/types/pos'
import * as posApi from '@/api/pos'

const props = defineProps<{
  modelValue: boolean
  outletId: number
  editingMember: Member | null
}>()

const emit = defineEmits<{
  'update:modelValue': [value: boolean]
  saved: []
}>()

const toast = useToast()
const submitting = ref(false)

const form = ref<MemberPayload>({
  name: '',
  phone: '',
  email: '',
})

const isOpen = computed({
  get: () => props.modelValue,
  set: (v) => emit('update:modelValue', v),
})

const isEditing = computed(() => !!props.editingMember)

watch(
  () => props.modelValue,
  (open) => {
    if (open) {
      if (props.editingMember) {
        form.value = {
          name: props.editingMember.name,
          phone: props.editingMember.phone,
          email: props.editingMember.email ?? '',
        }
      } else {
        form.value = { name: '', phone: '', email: '' }
      }
    }
  },
)

async function save() {
  if (!form.value.name.trim() || !form.value.phone.trim()) return

  const payload: MemberPayload = {
    name: form.value.name.trim(),
    phone: form.value.phone.trim(),
    email: form.value.email?.trim() || undefined,
  }

  submitting.value = true
  try {
    if (isEditing.value && props.editingMember) {
      await posApi.updateMember(props.editingMember.id, payload)
      toast.success('Member berhasil diperbarui.')
    } else {
      await posApi.createMember(props.outletId, payload)
      toast.success('Member berhasil ditambahkan.')
    }
    isOpen.value = false
    emit('saved')
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
      <h2 class="text-lg font-semibold text-gray-900 dark:text-white">
        {{ isEditing ? 'Edit Member' : 'Member Baru' }}
      </h2>

      <form class="mt-4 space-y-4" @submit.prevent="save">
        <BaseInput
          v-model="form.name"
          label="Nama"
          placeholder="Nama lengkap"
          required
        />

        <BaseInput
          v-model="form.phone"
          label="No. Telepon"
          placeholder="08xxxxxxxxxx"
          required
        />

        <BaseInput
          v-model="form.email"
          label="Email"
          type="email"
          placeholder="email@contoh.com (opsional)"
        />

        <div class="flex justify-end gap-2 pt-2">
          <BaseButton variant="secondary" size="sm" type="button" @click="isOpen = false">
            Batal
          </BaseButton>
          <BaseButton variant="primary" size="sm" type="submit" :disabled="submitting">
            {{ submitting ? 'Menyimpan...' : 'Simpan' }}
          </BaseButton>
        </div>
      </form>
    </template>
  </BaseModal>
</template>
