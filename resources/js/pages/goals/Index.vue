<script setup lang="ts">
import { ref } from 'vue'
import { get, post, put, del } from '@purdia/http'
import BaseButton from '@purdia/ui/src/components/BaseButton.vue'
import BaseInput from '@purdia/ui/src/components/BaseInput.vue'
import BaseModal from '@purdia/ui/src/components/BaseModal.vue'
import BaseProgress from '@purdia/ui/src/components/BaseProgress.vue'
import BaseTextarea from '@purdia/ui/src/components/BaseTextarea.vue'
import { Plus, Trash2, Check, Target } from '@lucide/vue'

interface Milestone {
  id: number
  title: string
  is_completed: boolean
  position: number
}

interface Goal {
  id: number
  title: string
  description: string | null
  target_date: string | null
  status: string
  progress: number
  milestones: Milestone[]
}

const goals = ref<Goal[]>([])
const showForm = ref(false)
const form = ref({ title: '', description: '', target_date: '', milestones: [{ title: '' }] })
const newMilestone = ref<Record<number, string>>({})

async function fetchGoals() {
  try {
    const res = await get<Goal[]>('/goals')
    goals.value = res.data
  } catch { /* */ }
}

function addMilestoneField() {
  form.value.milestones.push({ title: '' })
}

async function createGoal() {
  if (!form.value.title.trim()) return
  const payload = {
    title: form.value.title,
    description: form.value.description || null,
    target_date: form.value.target_date || null,
    milestones: form.value.milestones.filter((m) => m.title.trim()),
  }
  await post('/goals', payload)
  showForm.value = false
  form.value = { title: '', description: '', target_date: '', milestones: [{ title: '' }] }
  fetchGoals()
}

async function toggleMilestone(milestone: Milestone) {
  try {
    const res = await post<Goal>(`/milestones/${milestone.id}/toggle`)
    const idx = goals.value.findIndex((g) => g.milestones.some((m) => m.id === milestone.id))
    if (idx >= 0) goals.value[idx] = res.data
  } catch { /* */ }
}

async function addMilestoneToGoal(goal: Goal) {
  const title = newMilestone.value[goal.id]
  if (!title?.trim()) return
  await post(`/goals/${goal.id}/milestones`, { title })
  newMilestone.value[goal.id] = ''
  fetchGoals()
}

async function deleteGoal(goal: Goal) {
  await del(`/goals/${goal.id}`)
  goals.value = goals.value.filter((g) => g.id !== goal.id)
}

fetchGoals()
</script>

<template>
  <div>
    <div class="flex items-center justify-between">
      <div>
        <h1 class="text-2xl font-semibold text-gray-900 dark:text-white">Goals</h1>
        <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">Target dan milestones jangka panjang.</p>
      </div>
      <BaseButton variant="primary" size="sm" :icon="Plus" @click="showForm = true">Goal Baru</BaseButton>
    </div>

    <div class="mt-6 space-y-4">
      <div v-for="goal in goals" :key="goal.id"
        class="rounded-xl border border-gray-200 bg-white p-5 dark:border-gray-700 dark:bg-gray-800"
      >
        <div class="flex items-start justify-between">
          <div>
            <h3 class="font-semibold text-gray-900 dark:text-white">{{ goal.title }}</h3>
            <p v-if="goal.description" class="mt-1 text-sm text-gray-500">{{ goal.description }}</p>
            <p v-if="goal.target_date" class="mt-1 text-xs text-gray-400">Target: {{ new Date(goal.target_date).toLocaleDateString('id-ID', { day: 'numeric', month: 'long', year: 'numeric' }) }}</p>
          </div>
          <button class="rounded p-1.5 text-gray-400 hover:text-red-500" @click="deleteGoal(goal)"><Trash2 :size="14" /></button>
        </div>

        <!-- Progress -->
        <div class="mt-3">
          <div class="flex items-center justify-between text-xs text-gray-500 mb-1">
            <span>Progress</span>
            <span>{{ goal.progress }}%</span>
          </div>
          <BaseProgress :value="goal.progress" size="sm" />
        </div>

        <!-- Milestones -->
        <div class="mt-4 space-y-2">
          <div v-for="ms in goal.milestones" :key="ms.id" class="flex items-center gap-3">
            <button
              class="flex h-5 w-5 shrink-0 items-center justify-center rounded border transition-colors"
              :class="ms.is_completed ? 'border-emerald-500 bg-emerald-500 text-white' : 'border-gray-300 dark:border-gray-600'"
              @click="toggleMilestone(ms)"
            ><Check v-if="ms.is_completed" :size="12" /></button>
            <span class="text-sm" :class="ms.is_completed ? 'text-gray-400 line-through' : 'text-gray-700 dark:text-gray-300'">{{ ms.title }}</span>
          </div>
        </div>

        <!-- Add milestone inline -->
        <div class="mt-3 flex gap-2">
          <input
            v-model="newMilestone[goal.id]"
            class="flex-1 rounded-lg border border-gray-200 px-3 py-1.5 text-sm dark:border-gray-600 dark:bg-gray-900 dark:text-white"
            placeholder="Tambah milestone..."
            @keyup.enter="addMilestoneToGoal(goal)"
          />
          <BaseButton variant="secondary" size="sm" @click="addMilestoneToGoal(goal)">+</BaseButton>
        </div>
      </div>
    </div>

    <div v-if="!goals.length" class="mt-12 text-center"><p class="text-gray-400">Belum ada goal. Tentukan targetmu!</p></div>

    <!-- Create modal -->
    <BaseModal v-model="showForm" size="md">
      <template #default>
        <div class="p-6">
          <h2 class="text-lg font-semibold text-gray-900 dark:text-white">Goal Baru</h2>
          <form class="mt-4 space-y-4" @submit.prevent="createGoal">
            <BaseInput v-model="form.title" label="Judul Goal" placeholder="Contoh: Belajar Go" required />
            <BaseTextarea v-model="form.description" label="Deskripsi (opsional)" :rows="2" />
            <BaseInput v-model="form.target_date" label="Target Tanggal" type="date" />
            <div>
              <p class="text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Milestones</p>
              <div class="space-y-2">
                <div v-for="(ms, i) in form.milestones" :key="i" class="flex gap-2">
                  <input v-model="ms.title" class="flex-1 rounded-lg border border-gray-200 px-3 py-1.5 text-sm dark:border-gray-600 dark:bg-gray-900 dark:text-white" :placeholder="`Milestone ${i + 1}`" />
                </div>
              </div>
              <button type="button" class="mt-2 text-xs text-primary-600 hover:underline" @click="addMilestoneField">+ Tambah milestone</button>
            </div>
            <div class="flex justify-end gap-2 pt-2">
              <BaseButton variant="secondary" size="sm" type="button" @click="showForm = false">Batal</BaseButton>
              <BaseButton variant="primary" size="sm" type="submit">Buat Goal</BaseButton>
            </div>
          </form>
        </div>
      </template>
    </BaseModal>
  </div>
</template>
