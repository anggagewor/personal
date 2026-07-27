<script setup lang="ts">
import { computed } from 'vue'
import type { CalendarEvent } from '@purdia/ui/src/components/BaseCalendar.vue'

interface Props {
  year: number
  events?: CalendarEvent[]
  holidays?: Date[]
}

const props = withDefaults(defineProps<Props>(), {
  events: () => [],
  holidays: () => [],
})

const emit = defineEmits<{
  'date-click': [date: Date]
  'month-click': [date: Date]
}>()

const months = Array.from({ length: 12 }, (_, i) => i)

const today = new Date()

function getMonthName(month: number): string {
  return new Date(props.year, month).toLocaleDateString('id-ID', { month: 'long' })
}

function isSameDay(a: Date, b: Date): boolean {
  return (
    a.getFullYear() === b.getFullYear() &&
    a.getMonth() === b.getMonth() &&
    a.getDate() === b.getDate()
  )
}

function isToday(date: Date): boolean {
  return isSameDay(date, today)
}

function isSunday(date: Date): boolean {
  return date.getDay() === 0
}

function isHoliday(date: Date): boolean {
  return props.holidays.some((h) => isSameDay(new Date(h), date))
}

function hasEvents(date: Date): boolean {
  return props.events.some((e) => isSameDay(new Date(e.date), date))
}

interface MiniDay {
  date: Date
  day: number
  isCurrentMonth: boolean
  isToday: boolean
  isSunday: boolean
  isHoliday: boolean
  hasEvents: boolean
}

function getMonthDays(month: number): MiniDay[] {
  const firstDay = new Date(props.year, month, 1)
  const lastDay = new Date(props.year, month + 1, 0)
  const startPadding = firstDay.getDay()
  const days: MiniDay[] = []

  // Previous month padding
  const prevMonthLastDay = new Date(props.year, month, 0).getDate()
  for (let i = startPadding - 1; i >= 0; i--) {
    const date = new Date(props.year, month - 1, prevMonthLastDay - i)
    days.push({
      date,
      day: date.getDate(),
      isCurrentMonth: false,
      isToday: isToday(date),
      isSunday: isSunday(date),
      isHoliday: isHoliday(date),
      hasEvents: hasEvents(date),
    })
  }

  // Current month days
  for (let d = 1; d <= lastDay.getDate(); d++) {
    const date = new Date(props.year, month, d)
    days.push({
      date,
      day: d,
      isCurrentMonth: true,
      isToday: isToday(date),
      isSunday: isSunday(date),
      isHoliday: isHoliday(date),
      hasEvents: hasEvents(date),
    })
  }

  // Next month padding to fill 6 rows
  const remaining = 42 - days.length
  for (let i = 1; i <= remaining; i++) {
    const date = new Date(props.year, month + 1, i)
    days.push({
      date,
      day: i,
      isCurrentMonth: false,
      isToday: isToday(date),
      isSunday: isSunday(date),
      isHoliday: isHoliday(date),
      hasEvents: hasEvents(date),
    })
  }

  return days
}

const isCurrentYear = computed(() => props.year === today.getFullYear())

const weekDayHeaders = ['M', 'S', 'S', 'R', 'K', 'J', 'S']
</script>

<template>
  <div class="select-none">
    <!-- Year header -->
    <div class="mb-4 text-center">
      <h3 class="text-lg font-semibold" :class="isCurrentYear ? 'text-primary-600 dark:text-primary-400' : 'text-gray-900 dark:text-white'">
        {{ year }}
      </h3>
    </div>

    <!-- 12-month grid -->
    <div class="grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-4 gap-4">
      <div
        v-for="month in months"
        :key="month"
        class="rounded-lg border border-gray-200 p-3 dark:border-gray-700 hover:border-gray-300 dark:hover:border-gray-600 transition-colors"
      >
        <!-- Month title -->
        <button
          class="w-full text-left text-sm font-semibold mb-2 hover:text-primary-600 dark:hover:text-primary-400 transition-colors"
          :class="{
            'text-primary-600 dark:text-primary-400': month === today.getMonth() && isCurrentYear,
            'text-gray-900 dark:text-white': !(month === today.getMonth() && isCurrentYear),
          }"
          @click="emit('month-click', new Date(year, month, 1))"
        >
          {{ getMonthName(month) }}
        </button>

        <!-- Mini weekday headers -->
        <div class="grid grid-cols-7 mb-0.5">
          <div
            v-for="(wd, idx) in weekDayHeaders"
            :key="idx"
            class="text-center text-[9px] font-medium"
            :class="idx === 6 ? 'text-red-400' : 'text-gray-400'"
          >
            {{ wd }}
          </div>
        </div>

        <!-- Mini calendar days -->
        <div class="grid grid-cols-7">
          <div
            v-for="(day, idx) in getMonthDays(month)"
            :key="idx"
            class="relative flex items-center justify-center h-5 cursor-pointer"
            @click="emit('date-click', day.date)"
          >
            <span
              class="flex items-center justify-center w-4 h-4 rounded-full text-[9px] leading-none"
              :class="{
                'bg-primary-500 text-white font-bold': day.isToday,
                'text-red-500 dark:text-red-400': (day.isSunday || day.isHoliday) && day.isCurrentMonth && !day.isToday,
                'text-gray-700 dark:text-gray-300': day.isCurrentMonth && !day.isToday && !day.isSunday && !day.isHoliday,
                'text-gray-300 dark:text-gray-600': !day.isCurrentMonth,
              }"
            >
              {{ day.day }}
            </span>
            <!-- Event indicator dot -->
            <span
              v-if="day.hasEvents && day.isCurrentMonth"
              class="absolute bottom-0 left-1/2 -translate-x-1/2 w-1 h-1 rounded-full bg-primary-400"
            />
          </div>
        </div>
      </div>
    </div>
  </div>
</template>
