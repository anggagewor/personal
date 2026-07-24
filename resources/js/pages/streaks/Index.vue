<script setup lang="ts">
import { ref, computed, onMounted } from 'vue'
import { get } from '@purdia/http'
import { Flame } from '@lucide/vue'

interface HabitLog {
  date: string
  completed: number
}

interface PomodoroStat {
  date: string
  count: number
}

interface DayData {
  date: string
  habits: number
  pomodoros: number
  total: number
  level: number
}

const habits = ref<HabitLog[]>([])
const pomodoros = ref<PomodoroStat[]>([])
const loading = ref(true)

const weeks = 12
const days = 7

function getLast12WeeksDates(): string[] {
  const dates: string[] = []
  const today = new Date()
  const dayOfWeek = today.getDay()
  const endOfWeek = new Date(today)
  endOfWeek.setDate(today.getDate() + (6 - dayOfWeek))

  const totalDays = weeks * days
  const startDate = new Date(endOfWeek)
  startDate.setDate(endOfWeek.getDate() - totalDays + 1)

  for (let i = 0; i < totalDays; i++) {
    const d = new Date(startDate)
    d.setDate(startDate.getDate() + i)
    dates.push(d.toISOString().split('T')[0])
  }
  return dates
}

const allDates = getLast12WeeksDates()

const grid = computed<DayData[][]>(() => {
  const habitMap = new Map<string, number>()
  const pomodoroMap = new Map<string, number>()

  for (const h of habits.value) {
    habitMap.set(h.date, (habitMap.get(h.date) || 0) + h.completed)
  }
  for (const p of pomodoros.value) {
    pomodoroMap.set(p.date, (pomodoroMap.get(p.date) || 0) + p.count)
  }

  const columns: DayData[][] = []
  for (let col = 0; col < weeks; col++) {
    const week: DayData[] = []
    for (let row = 0; row < days; row++) {
      const idx = col * days + row
      const date = allDates[idx]
      const h = habitMap.get(date) || 0
      const p = pomodoroMap.get(date) || 0
      const total = h + p
      let level = 0
      if (total >= 8) level = 4
      else if (total >= 5) level = 3
      else if (total >= 3) level = 2
      else if (total >= 1) level = 1
      week.push({ date, habits: h, pomodoros: p, total, level })
    }
    columns.push(week)
  }
  return columns
})

const levelColors: Record<number, string> = {
  0: 'bg-gray-100 dark:bg-gray-700',
  1: 'bg-green-200 dark:bg-green-900',
  2: 'bg-green-400 dark:bg-green-700',
  3: 'bg-green-500 dark:bg-green-500',
  4: 'bg-green-700 dark:bg-green-400',
}

const dayLabels = ['Min', 'Sen', 'Sel', 'Rab', 'Kam', 'Jum', 'Sab']

function formatDate(date: string): string {
  return new Date(date).toLocaleDateString('id-ID', { day: 'numeric', month: 'short', year: 'numeric' })
}

onMounted(async () => {
  try {
    const [habitsRes, pomodorosRes] = await Promise.all([
      get('/habits'),
      get('/pomodoros/stats'),
    ])
    habits.value = habitsRes.data?.data || []
    pomodoros.value = pomodorosRes.data?.data || []
  } catch {
    // silently handle
  } finally {
    loading.value = false
  }
})
</script>

<template>
  <div class="space-y-6">
    <!-- Header -->
    <div class="flex items-center gap-3">
      <Flame :size="24" class="text-orange-500" />
      <h1 class="text-2xl font-bold text-gray-900 dark:text-white">Streak Calendar</h1>
    </div>

    <!-- Loading -->
    <div v-if="loading" class="flex items-center justify-center py-20">
      <div class="h-8 w-8 animate-spin rounded-full border-4 border-primary-500 border-t-transparent"></div>
    </div>

    <!-- Heatmap Grid -->
    <div v-else class="overflow-x-auto rounded-xl border border-gray-200 bg-white p-6 dark:border-gray-700 dark:bg-gray-800">
      <div class="flex gap-1">
        <!-- Day labels -->
        <div class="flex flex-col gap-1 pr-2">
          <div
            v-for="label in dayLabels"
            :key="label"
            class="flex h-4 w-8 items-center text-[10px] text-gray-400 dark:text-gray-500"
          >
            {{ label }}
          </div>
        </div>

        <!-- Week columns -->
        <div v-for="(week, colIdx) in grid" :key="colIdx" class="flex flex-col gap-1">
          <div
            v-for="day in week"
            :key="day.date"
            :title="`${formatDate(day.date)} — Habits: ${day.habits}, Pomodoro: ${day.pomodoros}`"
            class="h-4 w-4 rounded-sm transition-colors"
            :class="levelColors[day.level]"
          ></div>
        </div>
      </div>

      <!-- Legend -->
      <div class="mt-6 flex items-center gap-2 text-xs text-gray-500 dark:text-gray-400">
        <span>Kurang</span>
        <div class="h-3 w-3 rounded-sm bg-gray-100 dark:bg-gray-700"></div>
        <div class="h-3 w-3 rounded-sm bg-green-200 dark:bg-green-900"></div>
        <div class="h-3 w-3 rounded-sm bg-green-400 dark:bg-green-700"></div>
        <div class="h-3 w-3 rounded-sm bg-green-500 dark:bg-green-500"></div>
        <div class="h-3 w-3 rounded-sm bg-green-700 dark:bg-green-400"></div>
        <span>Lebih</span>
      </div>
    </div>

    <!-- Summary info -->
    <div v-if="!loading" class="text-sm text-gray-500 dark:text-gray-400">
      <p>Menampilkan aktivitas 12 minggu terakhir berdasarkan habits yang diselesaikan dan sesi pomodoro.</p>
    </div>
  </div>
</template>
