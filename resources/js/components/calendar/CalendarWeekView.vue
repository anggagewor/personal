<script setup lang="ts">
import { computed } from 'vue'
import type { CalendarEvent } from '@purdia/ui/src/components/BaseCalendar.vue'

interface Props {
  date: Date
  events?: CalendarEvent[]
  holidays?: Date[]
}

const props = withDefaults(defineProps<Props>(), {
  events: () => [],
  holidays: () => [],
})

const emit = defineEmits<{
  'event-click': [event: CalendarEvent]
  'date-click': [date: Date]
}>()

const hours = Array.from({ length: 24 }, (_, i) => i)

// Get the week days (Monday to Sunday) based on the selected date
const weekDays = computed(() => {
  const d = new Date(props.date)
  const day = d.getDay()
  // Start from Monday (day 1), if Sunday (0) shift back 6
  const mondayOffset = day === 0 ? -6 : 1 - day
  const monday = new Date(d)
  monday.setDate(d.getDate() + mondayOffset)

  return Array.from({ length: 7 }, (_, i) => {
    const date = new Date(monday)
    date.setDate(monday.getDate() + i)
    return date
  })
})

const weekLabel = computed(() => {
  const first = weekDays.value[0]
  const last = weekDays.value[6]
  const opts: Intl.DateTimeFormatOptions = { day: 'numeric', month: 'short' }
  return `${first.toLocaleDateString('id-ID', opts)} — ${last.toLocaleDateString('id-ID', { ...opts, year: 'numeric' })}`
})

function isSameDay(a: Date, b: Date): boolean {
  return (
    a.getFullYear() === b.getFullYear() &&
    a.getMonth() === b.getMonth() &&
    a.getDate() === b.getDate()
  )
}

function isToday(date: Date): boolean {
  return isSameDay(date, new Date())
}

function isSunday(date: Date): boolean {
  return date.getDay() === 0
}

function isHoliday(date: Date): boolean {
  return props.holidays.some((h) => isSameDay(new Date(h), date))
}

function eventsForDayAtHour(date: Date, hour: number): CalendarEvent[] {
  return props.events.filter((e) => {
    const eventDate = new Date(e.date)
    return isSameDay(eventDate, date) && eventDate.getHours() === hour
  })
}

function formatHour(hour: number): string {
  return `${String(hour).padStart(2, '0')}:00`
}

function formatDayHeader(date: Date): string {
  return date.toLocaleDateString('id-ID', { weekday: 'short', day: 'numeric' })
}

function onCellClick(date: Date, hour: number) {
  const d = new Date(date)
  d.setHours(hour, 0, 0, 0)
  emit('date-click', d)
}

const eventColors: Record<string, string> = {
  primary: 'bg-primary-100 border-primary-400 text-primary-800 dark:bg-primary-900/30 dark:border-primary-600 dark:text-primary-300',
  success: 'bg-emerald-100 border-emerald-400 text-emerald-800 dark:bg-emerald-900/30 dark:border-emerald-600 dark:text-emerald-300',
  warning: 'bg-amber-100 border-amber-400 text-amber-800 dark:bg-amber-900/30 dark:border-amber-600 dark:text-amber-300',
  danger: 'bg-red-100 border-red-400 text-red-800 dark:bg-red-900/30 dark:border-red-600 dark:text-red-300',
  info: 'bg-cyan-100 border-cyan-400 text-cyan-800 dark:bg-cyan-900/30 dark:border-cyan-600 dark:text-cyan-300',
}
</script>

<template>
  <div class="select-none">
    <!-- Week label -->
    <div class="mb-4 text-center">
      <h3 class="text-base font-semibold text-gray-900 dark:text-white">{{ weekLabel }}</h3>
    </div>

    <!-- Grid -->
    <div class="border border-gray-200 rounded-lg overflow-hidden dark:border-gray-700">
      <!-- Day headers -->
      <div class="grid grid-cols-[4rem_repeat(7,1fr)] border-b border-gray-200 dark:border-gray-700 bg-gray-50 dark:bg-gray-800/50">
        <div class="border-r border-gray-200 dark:border-gray-700" />
        <div
          v-for="day in weekDays"
          :key="day.toISOString()"
          class="px-1 py-2 text-center text-xs font-medium border-r border-gray-100 dark:border-gray-700 last:border-r-0"
          :class="{
            'text-primary-600 dark:text-primary-400 font-semibold': isToday(day),
            'text-red-500 dark:text-red-400': (isSunday(day) || isHoliday(day)) && !isToday(day),
            'text-gray-600 dark:text-gray-300': !isToday(day) && !isSunday(day) && !isHoliday(day),
          }"
        >
          {{ formatDayHeader(day) }}
          <div v-if="isToday(day)" class="mx-auto mt-0.5 h-1 w-1 rounded-full bg-primary-500" />
        </div>
      </div>

      <!-- Time rows (scrollable) -->
      <div class="max-h-[600px] overflow-y-auto">
        <div
          v-for="hour in hours"
          :key="hour"
          class="grid grid-cols-[4rem_repeat(7,1fr)] border-b border-gray-100 dark:border-gray-700 last:border-b-0 min-h-[2.75rem]"
        >
          <!-- Time label -->
          <div class="px-2 py-1 text-[11px] text-gray-400 dark:text-gray-500 text-right border-r border-gray-100 dark:border-gray-700 flex items-start justify-end pt-1">
            {{ formatHour(hour) }}
          </div>

          <!-- Day cells -->
          <div
            v-for="day in weekDays"
            :key="`${day.toISOString()}-${hour}`"
            class="border-r border-gray-100 dark:border-gray-700 last:border-r-0 px-0.5 py-0.5 hover:bg-gray-50 dark:hover:bg-gray-800/50 cursor-pointer transition-colors"
            @click="onCellClick(day, hour)"
          >
            <div
              v-for="event in eventsForDayAtHour(day, hour)"
              :key="event.id"
              class="rounded px-1 py-0.5 text-[10px] font-medium border-l-2 cursor-pointer truncate mb-0.5"
              :class="eventColors[event.variant || 'primary']"
              @click.stop="emit('event-click', event)"
            >
              {{ event.title }}
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>
