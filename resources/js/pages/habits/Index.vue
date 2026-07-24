<script setup lang="ts">
import { ref } from 'vue'
import { get, post, put, del } from '@purdia/http'
import BaseButton from '@purdia/ui/src/components/BaseButton.vue'
import BaseInput from '@purdia/ui/src/components/BaseInput.vue'
import BaseModal from '@purdia/ui/src/components/BaseModal.vue'
import { Plus, Trash2, Flame, Check } from '@lucide/vue'

interface Habit {
  id: number
  name: string
  icon: string | null
  color: string | null
  frequency: string
  completed_today: boolean
  streak: number
}

const habits = ref<Habit[]>([])
const showForm = ref(false)
const form = ref({ name: '', color: '' })

async function fetchHabits() {
  try {
    const res = await get<Habit[]>('/habits')
    habits.value = res.data
  } catch { /* */ }
}

async function addHabit() {
  if (!form.value.name.trim()) return
  await post('/habits', { name: form.value.name, color: form.value.color || null })
  form.value = { name: '', color: '' }
  showForm.value = false
  fetchHabits()
}

async function toggleHabit(habit: Habit) {
  try {
    const res = await post<{ completed: boolean; streak: number }>(`/habits/${habit.id}/toggle`)
    habit.completed_today = res.data.completed
    habit.streak = res.data.streak
  } catch { /* */ }
}

async function deleteHabit(habit: Habit) {
  await del(`/habits/${habit.id}`)
  habits.value = habits.value.filter((h) => h.id !== habit.id)
}

fetchHabits()
</script>

<template>
  <div>
    <div class="flex items-center justify-between">
      <div>
        <h1 class="text-2xl font-semibold text-gray-900 dark:text-white">Habit Tracker</h1>
        <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">Bangun kebiasaan baik setiap hari.</p>
      </div>
      <BaseButton variant="primary" size="sm" :icon="Plus" @click="showForm = true">Habit Baru</BaseButton>
    </div>

    <div class="mt-6 space-y-3">
      <div
        v-for="habit in habits"
        :key="habit.id"
        class="group flex items-center gap-4 rounded-xl border border-gray-200 bg-white px-5 py-4 dark:border-gray-700 dark:bg-gray-800"
      >
        <!-- Toggle -->
        <button
          class="flex h-8 w-8 shrink-0 items-center justify-center rounded-full border-2 transition-colors"
          :class="habit.completed_today ? 'border-emerald-500 bg-emerald-500 text-white' : 'border-gray-300 text-transparent hover:border-emerald-400 dark:border-gray-600'"
          @click="toggleHabit(habit)"
        >
          <Check :size="16" />
        </button>

        <!-- Info -->
        <div class="flex-1">
          <p class="font-medium text-gray-900 dark:text-white" :class="habit.completed_today ? 'line-through opacity-60' : ''">{{ habit.name }}</p>
        </div>

        <!-- Streak -->
        <div v-if="habit.streak > 0" class="flex items-center gap-1 text-sm text-amber-600 dark:text-amber-400">
          <Flame :size="14" />
          <span>{{ habit.streak }}</span>
        </div>

        <!-- Delete -->
        <button
          class="rounded p-1.5 text-gray-400 opacity-0 group-hover:opacity-100 hover:text-red-500"
          @click="deleteHabit(habit)"
        >
          <Trash2 :size="14" />
        </button>
      </div>
    </div>

    <div v-if="!habits.length" class="mt-12 text-center">
      <p class="text-gray-400">Belum ada habit. Tambahkan kebiasaan yang ingin dibangun.</p>
    </div>

    <BaseModal v-model="showForm" size="sm">
      <template #default>
        <div class="p-6">
          <h2 class="text-lg font-semibold text-gray-900 dark:text-white">Habit Baru</h2>
          <form class="mt-4 space-y-4" @submit.prevent="addHabit">
            <BaseInput v-model="form.name" label="Nama Habit" placeholder="Contoh: Olahraga 30 menit" required />
            <div class="flex justify-end gap-2">
              <BaseButton variant="secondary" size="sm" type="button" @click="showForm = false">Batal</BaseButton>
              <BaseButton variant="primary" size="sm" type="submit">Simpan</BaseButton>
            </div>
          </form>
        </div>
      </template>
    </BaseModal>
  </div>
</template>
