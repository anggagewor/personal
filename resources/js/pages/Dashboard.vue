<script setup lang="ts">
import { ref, onMounted } from 'vue'
import { get, post } from '@purdia/http'
import { useAuthStore } from '@purdia/auth'
import { useToast } from '@purdia/toast'
import {
  FileText, ListTodo, Bookmark, Calendar, Clock, Quote,
  CloudSun, Droplets, Wind, TrendingUp, CheckCircle2, Timer,
  Flame, Send, StickyNote,
} from '@lucide/vue'

const auth = useAuthStore()
const toast = useToast()

// --- Stats ---
const stats = ref({ notes: 0, tasks_pending: 0, bookmarks: 0, events_upcoming: 0 })
const recentTasks = ref<Array<{ id: number; title: string; status: string; priority: string }>>([])
const recentNotes = ref<Array<{ id: number; title: string; updated_at: string }>>([])
const todayQuote = ref<{ id: number; content: string; author: string | null } | null>(null)
const loading = ref(true)

// --- Weather ---
interface WeatherData {
  city: string
  temp: number
  feels_like: number
  humidity: number
  description: string
  icon: string
  wind_speed: number
}
const weather = ref<WeatherData | null>(null)

// --- Weekly Summary ---
interface WeeklySummary {
  tasks_completed: number
  tasks_created: number
  pomodoros_completed: number
  focus_minutes: number
  habits_today: number
  habits_total: number
  notes_created: number
  max_streak: number
}
const weeklySummary = ref<WeeklySummary | null>(null)

// --- Quick Capture ---
const quickNote = ref('')
const quickSaving = ref(false)

async function submitQuickCapture() {
  if (!quickNote.value.trim() || quickSaving.value) return
  quickSaving.value = true
  try {
    await post('/scratchpads', { content: quickNote.value.trim() })
    quickNote.value = ''
    toast.success('Catatan cepat disimpan.')
  } catch {
    // Error toast handled globally by @purdia/http onError
  }
  quickSaving.value = false
}

onMounted(async () => {
  try {
    const [notesRes, tasksRes, bookmarksRes, eventsRes, quoteRes, weatherRes, weeklyRes] = await Promise.allSettled([
      get<unknown[]>('/notes', { params: { per_page: 5 } }),
      get<unknown[]>('/tasks', { params: { per_page: 5, status: 'pending' } }),
      get<Record<string, unknown[]>>('/bookmarks'),
      get<unknown[]>('/calendar-events', { params: { start: new Date().toISOString().slice(0, 10) } }),
      get<{ id: number; content: string; author: string | null }>('/quotes/today'),
      get<WeatherData>('/weather/current'),
      get<WeeklySummary>('/dashboard/weekly-summary'),
    ])

    if (notesRes.status === 'fulfilled') {
      stats.value.notes = (notesRes.value as { meta?: { total: number } }).meta?.total ?? 0
      recentNotes.value = (notesRes.value.data as Array<{ id: number; title: string; updated_at: string }>).slice(0, 3)
    }
    if (tasksRes.status === 'fulfilled') {
      stats.value.tasks_pending = (tasksRes.value as { meta?: { total: number } }).meta?.total ?? 0
      recentTasks.value = (tasksRes.value.data as Array<{ id: number; title: string; status: string; priority: string }>).slice(0, 5)
    }
    if (bookmarksRes.status === 'fulfilled') {
      const grouped = bookmarksRes.value.data as Record<string, unknown[]>
      stats.value.bookmarks = Object.values(grouped).reduce((sum, items) => sum + items.length, 0)
    }
    if (eventsRes.status === 'fulfilled') {
      stats.value.events_upcoming = (eventsRes.value.data as unknown[]).length
    }
    if (quoteRes.status === 'fulfilled') {
      todayQuote.value = quoteRes.value.data as { id: number; content: string; author: string | null } | null
    }
    if (weatherRes.status === 'fulfilled') {
      weather.value = weatherRes.value.data as WeatherData | null
    }
    if (weeklyRes.status === 'fulfilled') {
      weeklySummary.value = weeklyRes.value.data as WeeklySummary
    }
  } catch { /* */ }
  loading.value = false
})

function weatherIconUrl(icon: string): string {
  return `https://openweathermap.org/img/wn/${icon}@2x.png`
}

const statCards = [
  { label: 'Catatan', key: 'notes', icon: FileText, color: 'text-blue-600 bg-blue-100 dark:text-blue-400 dark:bg-blue-900/30' },
  { label: 'Tugas Pending', key: 'tasks_pending', icon: ListTodo, color: 'text-amber-600 bg-amber-100 dark:text-amber-400 dark:bg-amber-900/30' },
  { label: 'Bookmark', key: 'bookmarks', icon: Bookmark, color: 'text-emerald-600 bg-emerald-100 dark:text-emerald-400 dark:bg-emerald-900/30' },
  { label: 'Event Mendatang', key: 'events_upcoming', icon: Calendar, color: 'text-violet-600 bg-violet-100 dark:text-violet-400 dark:bg-violet-900/30' },
]
</script>

<template>
  <div>
    <div class="flex items-start justify-between">
      <div>
        <h1 class="text-2xl font-semibold text-gray-900 dark:text-white">Dashboard</h1>
        <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">Selamat datang, {{ auth.user?.name ?? 'User' }}.</p>
      </div>

      <!-- Weather mini -->
      <div v-if="weather" class="hidden items-center gap-2 rounded-lg border border-gray-200 bg-white px-3 py-2 sm:flex dark:border-gray-700 dark:bg-gray-800">
        <img :src="weatherIconUrl(weather.icon)" :alt="weather.description" class="h-8 w-8" />
        <div class="text-right">
          <p class="text-sm font-semibold text-gray-900 dark:text-white">{{ weather.temp }}°C</p>
          <p class="text-xs text-gray-500 dark:text-gray-400">{{ weather.city }}</p>
        </div>
      </div>
    </div>

    <!-- Quick Capture -->
    <div class="mt-5">
      <form class="flex items-center gap-2" @submit.prevent="submitQuickCapture">
        <div class="relative flex-1">
          <StickyNote :size="16" class="absolute left-3 top-1/2 -translate-y-1/2 text-gray-400" />
          <input
            v-model="quickNote"
            type="text"
            class="w-full rounded-xl border border-gray-200 bg-white py-2.5 pl-10 pr-4 text-sm text-gray-900 placeholder:text-gray-400 focus:border-primary-500 focus:outline-none focus:ring-1 focus:ring-primary-500 dark:border-gray-700 dark:bg-gray-800 dark:text-white dark:placeholder:text-gray-500 dark:focus:border-primary-500"
            placeholder="Tulis catatan cepat..."
            @keydown.enter.prevent="submitQuickCapture"
          />
        </div>
        <button
          type="submit"
          :disabled="!quickNote.trim() || quickSaving"
          class="flex h-10 w-10 shrink-0 items-center justify-center rounded-xl bg-primary-600 text-white transition-colors hover:bg-primary-700 disabled:opacity-40 disabled:cursor-not-allowed"
        >
          <Send :size="16" />
        </button>
      </form>
      <p class="mt-1 text-xs text-gray-400 dark:text-gray-500">Tekan Enter untuk simpan ke Scratchpad.</p>
    </div>

    <!-- Stats -->
    <div class="mt-6 grid grid-cols-1 gap-4 sm:grid-cols-2 lg:grid-cols-4">
      <div
        v-for="card in statCards"
        :key="card.key"
        class="rounded-xl border border-gray-200 bg-white p-5 dark:border-gray-700 dark:bg-gray-800"
      >
        <div class="flex items-center gap-3">
          <div class="flex h-10 w-10 items-center justify-center rounded-lg" :class="card.color">
            <component :is="card.icon" :size="20" />
          </div>
          <div>
            <p class="text-2xl font-bold text-gray-900 dark:text-white">{{ stats[card.key as keyof typeof stats] }}</p>
            <p class="text-xs text-gray-500 dark:text-gray-400">{{ card.label }}</p>
          </div>
        </div>
      </div>
    </div>

    <!-- Widgets grid -->
    <div class="mt-8 grid grid-cols-1 gap-6 lg:grid-cols-2">
      <!-- Quote of the Day -->
      <div v-if="todayQuote" class="rounded-xl border border-primary-200 bg-primary-50 p-5 dark:border-primary-800 dark:bg-primary-900/20 lg:col-span-2">
        <div class="flex items-start gap-3">
          <div class="flex h-9 w-9 shrink-0 items-center justify-center rounded-lg bg-primary-100 text-primary-600 dark:bg-primary-900/40 dark:text-primary-400">
            <Quote :size="18" />
          </div>
          <div class="flex-1">
            <p class="text-lg font-medium italic text-gray-900 dark:text-white">"{{ todayQuote.content }}"</p>
            <div class="mt-2 flex items-center justify-between">
              <p v-if="todayQuote.author" class="text-sm text-gray-600 dark:text-gray-400">— {{ todayQuote.author }}</p>
              <router-link to="/quotes" class="text-xs text-primary-600 hover:text-primary-700 dark:text-primary-400">Lihat semua quotes</router-link>
            </div>
          </div>
        </div>
      </div>

      <!-- Weather Detail -->
      <div v-if="weather" class="rounded-xl border border-gray-200 bg-white p-5 dark:border-gray-700 dark:bg-gray-800">
        <h2 class="text-sm font-semibold text-gray-900 dark:text-white">Cuaca Saat Ini</h2>
        <div class="mt-3 flex items-center gap-4">
          <img :src="weatherIconUrl(weather.icon)" :alt="weather.description" class="h-14 w-14" />
          <div>
            <p class="text-3xl font-bold text-gray-900 dark:text-white">{{ weather.temp }}°C</p>
            <p class="text-sm capitalize text-gray-500 dark:text-gray-400">{{ weather.description }}</p>
          </div>
        </div>
        <div class="mt-4 grid grid-cols-3 gap-3">
          <div class="flex items-center gap-1.5 text-xs text-gray-500 dark:text-gray-400">
            <CloudSun :size="14" class="text-gray-400" />
            <span>Terasa {{ weather.feels_like }}°C</span>
          </div>
          <div class="flex items-center gap-1.5 text-xs text-gray-500 dark:text-gray-400">
            <Droplets :size="14" class="text-blue-400" />
            <span>{{ weather.humidity }}%</span>
          </div>
          <div class="flex items-center gap-1.5 text-xs text-gray-500 dark:text-gray-400">
            <Wind :size="14" class="text-gray-400" />
            <span>{{ weather.wind_speed }} km/h</span>
          </div>
        </div>
      </div>

      <!-- Weekly Summary -->
      <div v-if="weeklySummary" class="rounded-xl border border-gray-200 bg-white p-5 dark:border-gray-700 dark:bg-gray-800">
        <div class="flex items-center justify-between">
          <h2 class="text-sm font-semibold text-gray-900 dark:text-white">Ringkasan Minggu Ini</h2>
          <TrendingUp :size="16" class="text-emerald-500" />
        </div>
        <div class="mt-4 grid grid-cols-2 gap-4">
          <div class="flex items-center gap-2">
            <CheckCircle2 :size="16" class="text-emerald-500" />
            <div>
              <p class="text-lg font-bold text-gray-900 dark:text-white">{{ weeklySummary.tasks_completed }}</p>
              <p class="text-xs text-gray-500 dark:text-gray-400">Task selesai</p>
            </div>
          </div>
          <div class="flex items-center gap-2">
            <Timer :size="16" class="text-blue-500" />
            <div>
              <p class="text-lg font-bold text-gray-900 dark:text-white">{{ weeklySummary.pomodoros_completed }}</p>
              <p class="text-xs text-gray-500 dark:text-gray-400">Pomodoro</p>
            </div>
          </div>
          <div class="flex items-center gap-2">
            <Clock :size="16" class="text-violet-500" />
            <div>
              <p class="text-lg font-bold text-gray-900 dark:text-white">{{ weeklySummary.focus_minutes }}m</p>
              <p class="text-xs text-gray-500 dark:text-gray-400">Fokus</p>
            </div>
          </div>
          <div class="flex items-center gap-2">
            <Flame :size="16" class="text-amber-500" />
            <div>
              <p class="text-lg font-bold text-gray-900 dark:text-white">{{ weeklySummary.max_streak }}</p>
              <p class="text-xs text-gray-500 dark:text-gray-400">Max streak</p>
            </div>
          </div>
        </div>
        <div class="mt-4 flex items-center justify-between rounded-lg bg-gray-50 px-3 py-2 dark:bg-gray-900/50">
          <span class="text-xs text-gray-500 dark:text-gray-400">Habit hari ini</span>
          <span class="text-sm font-semibold text-gray-900 dark:text-white">{{ weeklySummary.habits_today }}/{{ weeklySummary.habits_total }}</span>
        </div>
      </div>

      <!-- Recent Tasks -->
      <div class="rounded-xl border border-gray-200 bg-white p-5 dark:border-gray-700 dark:bg-gray-800">
        <div class="flex items-center justify-between">
          <h2 class="text-sm font-semibold text-gray-900 dark:text-white">Tugas Terbaru</h2>
          <router-link to="/tasks" class="text-xs text-primary-600 hover:text-primary-700 dark:text-primary-400">Lihat semua</router-link>
        </div>
        <div v-if="recentTasks.length" class="mt-4 space-y-3">
          <div v-for="task in recentTasks" :key="task.id" class="flex items-center gap-3">
            <div class="h-2 w-2 rounded-full" :class="task.priority === 'high' ? 'bg-red-500' : task.priority === 'medium' ? 'bg-amber-500' : 'bg-gray-400'" />
            <span class="flex-1 truncate text-sm text-gray-700 dark:text-gray-300">{{ task.title }}</span>
          </div>
        </div>
        <p v-else class="mt-4 text-sm text-gray-400">Tidak ada tugas pending.</p>
      </div>

      <!-- Recent Notes -->
      <div class="rounded-xl border border-gray-200 bg-white p-5 dark:border-gray-700 dark:bg-gray-800">
        <div class="flex items-center justify-between">
          <h2 class="text-sm font-semibold text-gray-900 dark:text-white">Catatan Terbaru</h2>
          <router-link to="/notes" class="text-xs text-primary-600 hover:text-primary-700 dark:text-primary-400">Lihat semua</router-link>
        </div>
        <div v-if="recentNotes.length" class="mt-4 space-y-3">
          <div v-for="note in recentNotes" :key="note.id" class="flex items-center justify-between">
            <span class="flex-1 truncate text-sm text-gray-700 dark:text-gray-300">{{ note.title }}</span>
            <span class="flex items-center gap-1 text-xs text-gray-400"><Clock :size="12" />{{ new Date(note.updated_at).toLocaleDateString('id-ID') }}</span>
          </div>
        </div>
        <p v-else class="mt-4 text-sm text-gray-400">Belum ada catatan.</p>
      </div>
    </div>
  </div>
</template>
