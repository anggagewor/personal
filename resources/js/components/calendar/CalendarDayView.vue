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
  'time-click': [date: Date]
}>()

const hours = Array.from({ length: 24 }, (_, i) => i)

const dayLabel = computed(() => {
  return props.date.toLocaleDateString('id-ID', {
    weekday: 'long',
    day: 'numeric',
    month: 'long',
    year: 'numeric',
  })
})

const isToday = computed(() => {
  const today = new Date()
  return (
    props.date.getFullYear() === today.getFullYear() &&
    props.date.getMonth() === today.getMonth() &&
    props.date.getDate() === today.getDate()
  )
})

const isSunday = computed(() => props.date.getDay() === 0)

const isHoliday = computed(() => {
  return props.holidays.some(
    (h) =>
      new Date(h).getFullYear() === props.date.getFullYear() &&
      new Date(h).getMonth() === props.date.getMonth() &&
      new Date(h).getDate() === props.date.getDate(),
  )
})

const dayEvents = computed(() => {
  return props.events.filter((e) => {
    const eventDate = new Date(e.date)
    return (
      eventDate.getFullYear() === props.date.getFullYear() &&
      eventDate.getMonth() === props.date.getMonth() &&
      eventDate.getDate() === props.date.getDate()
    )
  })
})

function eventsAtHour(hour: number): CalendarEvent[] {
  return dayEvents.value.filter((e) => new Date(e.date).getHours() === hour)
}

function formatHour(hour: number): string {
  return `${String(hour).padStart(2, '0')}:00`
}

function onTimeClick(hour: number) {
  const d = new Date(props.date)
  d.setHours(hour, 0, 0, 0)
  emit('time-click', d)
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
    <!-- Day header -->
    <div class="mb-4 text-center">
      <h3
        class="text-lg font-semibold"
        :class="{
          'text-primary-600 dark:text-primary-400': isToday,
          'text-red-500 dark:text-red-400': (isSunday || isHoliday) && !isToday,
          'text-gray-900 dark:text-white': !isToday && !isSunday && !isHoliday,
        }"
      >
        {{ dayLabel }}
      </h3>
      <span v-if="isToday" class="text-xs text-primary-500 font-medium">Hari ini</span>
    </div>

    <!-- Time grid -->
    <div class="relative border border-gray-200 rounded-lg overflow-hidden dark:border-gray-700">
      <div
        v-for="hour in hours"
        :key="hour"
        class="flex border-b border-gray-100 dark:border-gray-700 last:border-b-0 min-h-[3rem] hover:bg-gray-50 dark:hover:bg-gray-800/50 cursor-pointer transition-colors"
        @click="onTimeClick(hour)"
      >
        <!-- Time label -->
        <div class="w-16 shrink-0 px-2 py-2 text-xs text-gray-400 dark:text-gray-500 text-right border-r border-gray-100 dark:border-gray-700">
          {{ formatHour(hour) }}
        </div>

        <!-- Events area -->
        <div class="flex-1 px-2 py-1 flex flex-col gap-1">
          <div
            v-for="event in eventsAtHour(hour)"
            :key="event.id"
            class="rounded px-2 py-1 text-xs font-medium border-l-2 cursor-pointer truncate"
            :class="eventColors[event.variant || 'primary']"
            @click.stop="emit('event-click', event)"
          >
            {{ event.title }}
          </div>
        </div>
      </div>
    </div>
  </div>
</template>
