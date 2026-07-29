<script setup lang="ts">
import { ref, computed } from 'vue'
import { get, put } from '@purdia/http'
import { useToast } from '@purdia/toast'
import { formatDate } from '@purdia/utils'
import BaseButton from '@purdia/ui/src/components/BaseButton.vue'
import { ArrowLeft, GripVertical } from '@lucide/vue'
import { useRouter } from 'vue-router'

const router = useRouter()
const toast = useToast()

interface Task {
  id: number
  title: string
  description: string | null
  status: 'pending' | 'in_progress' | 'completed'
  priority: 'low' | 'medium' | 'high'
  due_date: string | null
  position: number
}

type ColumnStatus = 'pending' | 'in_progress' | 'completed'

interface Column {
  id: ColumnStatus
  label: string
  color: string
  headerBg: string
}

const columns: Column[] = [
  { id: 'pending', label: 'To Do', color: 'border-t-gray-400', headerBg: 'bg-gray-50 dark:bg-gray-800/50' },
  { id: 'in_progress', label: 'In Progress', color: 'border-t-blue-500', headerBg: 'bg-blue-50 dark:bg-blue-900/10' },
  { id: 'completed', label: 'Selesai', color: 'border-t-emerald-500', headerBg: 'bg-emerald-50 dark:bg-emerald-900/10' },
]

const tasks = ref<Task[]>([])
const loading = ref(true)
const draggingTask = ref<Task | null>(null)
const dragOverColumn = ref<ColumnStatus | null>(null)

const tasksByStatus = computed(() => {
  const grouped: Record<ColumnStatus, Task[]> = { pending: [], in_progress: [], completed: [] }
  for (const task of tasks.value) {
    if (grouped[task.status]) {
      grouped[task.status].push(task)
    }
  }
  return grouped
})

const priorityColors: Record<string, string> = {
  low: 'bg-gray-100 text-gray-600 dark:bg-gray-700 dark:text-gray-400',
  medium: 'bg-amber-100 text-amber-700 dark:bg-amber-900/30 dark:text-amber-300',
  high: 'bg-red-100 text-red-700 dark:bg-red-900/30 dark:text-red-300',
}

const priorityLabels: Record<string, string> = { low: 'Low', medium: 'Medium', high: 'High' }

async function fetchTasks() {
  loading.value = true
  try {
    const res = await get<Task[]>('/tasks')
    tasks.value = res.data
  } catch {
    // Error handled globally
  } finally {
    loading.value = false
  }
}

async function moveTask(task: Task, newStatus: ColumnStatus) {
  if (task.status === newStatus) return

  const oldStatus = task.status
  task.status = newStatus // Optimistic update

  try {
    await put(`/tasks/${task.id}`, { status: newStatus })
    toast.success(`Task dipindah ke ${columns.find((c) => c.id === newStatus)?.label}.`)
  } catch {
    task.status = oldStatus // Revert on error
  }
}

// Drag & Drop handlers
function onDragStart(e: DragEvent, task: Task) {
  draggingTask.value = task
  if (e.dataTransfer) {
    e.dataTransfer.effectAllowed = 'move'
    e.dataTransfer.setData('text/plain', String(task.id))
  }
}

function onDragEnd() {
  draggingTask.value = null
  dragOverColumn.value = null
}

function onDragOver(e: DragEvent, columnId: ColumnStatus) {
  e.preventDefault()
  if (e.dataTransfer) e.dataTransfer.dropEffect = 'move'
  dragOverColumn.value = columnId
}

function onDragLeave() {
  dragOverColumn.value = null
}

function onDrop(e: DragEvent, columnId: ColumnStatus) {
  e.preventDefault()
  dragOverColumn.value = null
  if (draggingTask.value) {
    moveTask(draggingTask.value, columnId)
    draggingTask.value = null
  }
}

fetchTasks()
</script>

<template>
  <div>
    <div class="flex items-center justify-between">
      <div class="flex items-center gap-3">
        <BaseButton variant="ghost" size="sm" :icon="ArrowLeft" @click="router.push('/tasks')" />
        <div>
          <h1 class="text-2xl font-semibold text-gray-900 dark:text-white">Kanban Board</h1>
          <p class="mt-0.5 text-sm text-gray-500 dark:text-gray-400">Drag & drop untuk pindahkan tugas.</p>
        </div>
      </div>
    </div>

    <!-- Kanban columns -->
    <div class="mt-6 grid grid-cols-1 gap-4 lg:grid-cols-3">
      <div
        v-for="col in columns"
        :key="col.id"
        class="flex flex-col rounded-xl border border-gray-200 border-t-4 dark:border-gray-700"
        :class="[col.color, dragOverColumn === col.id ? 'ring-2 ring-primary-500 ring-offset-2 dark:ring-offset-gray-900' : '']"
        @dragover="onDragOver($event, col.id)"
        @dragleave="onDragLeave"
        @drop="onDrop($event, col.id)"
      >
        <!-- Column header -->
        <div class="flex items-center justify-between rounded-t-lg px-4 py-3" :class="col.headerBg">
          <h2 class="text-sm font-semibold text-gray-700 dark:text-gray-200">{{ col.label }}</h2>
          <span class="rounded-full bg-white px-2 py-0.5 text-xs font-medium text-gray-600 shadow-sm dark:bg-gray-700 dark:text-gray-300">
            {{ tasksByStatus[col.id].length }}
          </span>
        </div>

        <!-- Column body -->
        <div class="flex-1 space-y-2 p-3 min-h-[200px]">
          <div
            v-for="task in tasksByStatus[col.id]"
            :key="task.id"
            draggable="true"
            class="cursor-grab rounded-lg border border-gray-200 bg-white p-3 shadow-sm transition-shadow hover:shadow-md active:cursor-grabbing dark:border-gray-600 dark:bg-gray-800"
            :class="draggingTask?.id === task.id ? 'opacity-50' : ''"
            @dragstart="onDragStart($event, task)"
            @dragend="onDragEnd"
          >
            <div class="flex items-start gap-2">
              <GripVertical :size="14" class="mt-0.5 shrink-0 text-gray-300 dark:text-gray-600" />
              <div class="min-w-0 flex-1">
                <p class="text-sm font-medium text-gray-900 dark:text-white" :class="task.status === 'completed' ? 'line-through opacity-60' : ''">
                  {{ task.title }}
                </p>
                <p v-if="task.description" class="mt-1 text-xs text-gray-400 line-clamp-2">{{ task.description }}</p>
                <div class="mt-2 flex items-center gap-2">
                  <span class="rounded px-1.5 py-0.5 text-xs" :class="priorityColors[task.priority]">{{ priorityLabels[task.priority] }}</span>
                  <span v-if="task.due_date" class="text-xs text-gray-400">{{ formatDate(task.due_date, { day: 'numeric', month: 'short' }) }}</span>
                </div>
              </div>
            </div>
          </div>

          <div v-if="!tasksByStatus[col.id].length" class="flex items-center justify-center py-8">
            <p class="text-xs text-gray-400">Kosong</p>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>
