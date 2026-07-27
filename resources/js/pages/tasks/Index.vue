<script setup lang="ts">
import { ref, computed } from 'vue'
import { get, post, put, del } from '@purdia/http'
import { useToast } from '@purdia/toast'
import { formatDate } from '@purdia/utils'
import { Plus, Trash2, ListTodo, Check } from '@lucide/vue'

const toast = useToast()

interface Task {
  id: number
  title: string
  description: string | null
  status: 'pending' | 'in_progress' | 'completed'
  priority: 'low' | 'medium' | 'high'
  due_date: string | null
  position: number
  created_at: string
}

const tasks = ref<Task[]>([])
const loading = ref(false)
const filter = ref<'all' | 'pending' | 'in_progress' | 'completed'>('all')
const showForm = ref(false)
const editingTask = ref<Task | null>(null)

const form = ref({ title: '', description: '', priority: 'medium' as Task['priority'], due_date: '' })

const statusLabels: Record<string, string> = { pending: 'To Do', in_progress: 'Proses', completed: 'Selesai' }
const statusColors: Record<string, string> = {
  pending: 'bg-gray-100 text-gray-700 dark:bg-gray-700 dark:text-gray-300',
  in_progress: 'bg-blue-100 text-blue-700 dark:bg-blue-900/30 dark:text-blue-300',
  completed: 'bg-emerald-100 text-emerald-700 dark:bg-emerald-900/30 dark:text-emerald-300',
}
const priorityColors: Record<string, string> = {
  low: 'bg-gray-100 text-gray-600 dark:bg-gray-700 dark:text-gray-400',
  medium: 'bg-amber-100 text-amber-700 dark:bg-amber-900/30 dark:text-amber-300',
  high: 'bg-red-100 text-red-700 dark:bg-red-900/30 dark:text-red-300',
}

async function fetchTasks() {
  loading.value = true
  const params: Record<string, string> = {}
  if (filter.value !== 'all') params.status = filter.value
  try {
    const response = await get<Task[]>('/tasks', { params })
    tasks.value = response.data
  } catch {
    // Error toast handled globally by @purdia/http onError
  }
  loading.value = false
}

async function saveTask() {
  if (!form.value.title.trim()) return
  const payload = { ...form.value, due_date: form.value.due_date || null }

  try {
    if (editingTask.value) {
      const response = await put<Task>(`/tasks/${editingTask.value.id}`, payload)
      const idx = tasks.value.findIndex((t) => t.id === editingTask.value!.id)
      if (idx >= 0) tasks.value[idx] = response.data
      toast.success('Task berhasil diperbarui.')
    } else {
      const response = await post<Task>('/tasks', payload)
      tasks.value.unshift(response.data)
      toast.success('Task berhasil dibuat.')
    }
    closeForm()
  } catch {
    // Error toast handled globally by @purdia/http onError
  }
}

async function updateStatus(task: Task, status: Task['status']) {
  try {
    await put<Task>(`/tasks/${task.id}`, { status })
    task.status = status
  } catch {
    // Error toast handled globally by @purdia/http onError
  }
}

async function deleteTask(task: Task) {
  try {
    await del(`/tasks/${task.id}`)
    tasks.value = tasks.value.filter((t) => t.id !== task.id)
    toast.success('Task berhasil dihapus.')
  } catch {
    // Error toast handled globally by @purdia/http onError
  }
}

function openNew() {
  editingTask.value = null
  form.value = { title: '', description: '', priority: 'medium', due_date: '' }
  showForm.value = true
}

function openEdit(task: Task) {
  editingTask.value = task
  form.value = { title: task.title, description: task.description ?? '', priority: task.priority, due_date: task.due_date ?? '' }
  showForm.value = true
}

function closeForm() {
  showForm.value = false
  editingTask.value = null
}

const filteredTasks = computed(() => {
  if (filter.value === 'all') return tasks.value
  return tasks.value.filter((t) => t.status === filter.value)
})

fetchTasks()
</script>

<template>
  <div>
    <div class="flex items-center justify-between">
      <div>
        <h1 class="text-2xl font-semibold text-gray-900 dark:text-white">Tugas</h1>
        <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">Kelola to-do list kamu.</p>
      </div>
      <button class="flex items-center gap-1.5 rounded-lg bg-primary-600 px-4 py-2 text-sm font-medium text-white hover:bg-primary-700" @click="openNew">
        <Plus :size="16" /> Tugas Baru
      </button>
    </div>

    <!-- Filter tabs -->
    <div class="mt-5 flex gap-1 rounded-lg border border-gray-200 bg-gray-50 p-1 dark:border-gray-700 dark:bg-gray-800">
      <button
        v-for="f in (['all', 'pending', 'in_progress', 'completed'] as const)"
        :key="f"
        class="rounded-md px-3 py-1.5 text-xs font-medium transition-colors"
        :class="filter === f ? 'bg-white text-gray-900 shadow-sm dark:bg-gray-700 dark:text-white' : 'text-gray-500 hover:text-gray-700 dark:text-gray-400'"
        @click="filter = f; fetchTasks()"
      >
        {{ f === 'all' ? 'Semua' : statusLabels[f] }}
      </button>
    </div>

    <!-- Task list -->
    <div v-if="filteredTasks.length" class="mt-5 space-y-2">
      <div
        v-for="task in filteredTasks"
        :key="task.id"
        class="group flex items-center gap-3 rounded-lg border border-gray-200 bg-white px-4 py-3 dark:border-gray-700 dark:bg-gray-800"
      >
        <!-- Status checkbox -->
        <button
          class="flex h-5 w-5 shrink-0 items-center justify-center rounded-full border-2 transition-colors"
          :class="task.status === 'completed' ? 'border-emerald-500 bg-emerald-500 text-white' : 'border-gray-300 hover:border-primary-500 dark:border-gray-600'"
          @click="updateStatus(task, task.status === 'completed' ? 'pending' : 'completed')"
        >
          <Check v-if="task.status === 'completed'" :size="12" />
        </button>

        <!-- Content -->
        <div class="flex-1 min-w-0 cursor-pointer" @click="openEdit(task)">
          <p class="text-sm font-medium text-gray-900 dark:text-white" :class="task.status === 'completed' ? 'line-through opacity-50' : ''">
            {{ task.title }}
          </p>
          <div class="mt-1 flex items-center gap-2">
            <span class="rounded px-1.5 py-0.5 text-xs" :class="statusColors[task.status]">{{ statusLabels[task.status] }}</span>
            <span class="rounded px-1.5 py-0.5 text-xs" :class="priorityColors[task.priority]">{{ task.priority }}</span>
            <span v-if="task.due_date" class="text-xs text-gray-400">{{ formatDate(task.due_date) }}</span>
          </div>
        </div>

        <!-- Delete -->
        <button class="rounded p-1 text-gray-400 opacity-0 group-hover:opacity-100 hover:text-red-600 transition-opacity" @click="deleteTask(task)">
          <Trash2 :size="16" />
        </button>
      </div>
    </div>

    <!-- Empty -->
    <div v-else-if="!loading" class="mt-12 flex flex-col items-center text-center">
      <ListTodo :size="48" class="text-gray-300 dark:text-gray-600" />
      <p class="mt-3 text-sm text-gray-500 dark:text-gray-400">Belum ada tugas.</p>
    </div>

    <!-- Form modal -->
    <Teleport to="body">
      <Transition name="modal">
        <div v-if="showForm" class="fixed inset-0 z-[200] flex items-center justify-center bg-black/50 p-4" @click.self="closeForm">
          <div class="w-full max-w-md rounded-xl bg-white px-5 py-4 shadow-xl dark:bg-gray-800">
            <h2 class="text-lg font-semibold text-gray-900 dark:text-white">{{ editingTask ? 'Edit Tugas' : 'Tugas Baru' }}</h2>
            <form class="mt-4 space-y-4" @submit.prevent="saveTask">
              <input v-model="form.title" type="text" placeholder="Judul tugas" required class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm focus:border-primary-500 focus:outline-none focus:ring-1 focus:ring-primary-500 dark:border-gray-600 dark:bg-gray-700 dark:text-white" />
              <textarea v-model="form.description" rows="3" placeholder="Deskripsi (opsional)" class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm focus:border-primary-500 focus:outline-none focus:ring-1 focus:ring-primary-500 dark:border-gray-600 dark:bg-gray-700 dark:text-white" />
              <div class="grid grid-cols-2 gap-3">
                <select v-model="form.priority" class="rounded-lg border border-gray-300 px-3 py-2 text-sm dark:border-gray-600 dark:bg-gray-700 dark:text-white">
                  <option value="low">Low</option>
                  <option value="medium">Medium</option>
                  <option value="high">High</option>
                </select>
                <input v-model="form.due_date" type="date" class="rounded-lg border border-gray-300 px-3 py-2 text-sm dark:border-gray-600 dark:bg-gray-700 dark:text-white" />
              </div>
              <div class="flex justify-end gap-2">
                <button type="button" class="rounded-lg border border-gray-300 px-4 py-2 text-sm font-medium text-gray-700 hover:bg-gray-50 dark:border-gray-600 dark:text-gray-300 dark:hover:bg-gray-700" @click="closeForm">Batal</button>
                <button type="submit" class="rounded-lg bg-primary-600 px-4 py-2 text-sm font-medium text-white hover:bg-primary-700">Simpan</button>
              </div>
            </form>
          </div>
        </div>
      </Transition>
    </Teleport>
  </div>
</template>

<style scoped>
.modal-enter-active, .modal-leave-active { transition: opacity 200ms ease; }
.modal-enter-from, .modal-leave-to { opacity: 0; }
</style>
