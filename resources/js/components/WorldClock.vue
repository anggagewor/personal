<script setup lang="ts">
import { ref, onMounted, onUnmounted } from 'vue'
import { Clock, Globe } from '@lucide/vue'
import type { TimezoneEntry } from '@/composables/usePreferences'

interface Props {
  timezones: TimezoneEntry[]
}

const props = defineProps<Props>()

interface ClockDisplay {
  label: string
  timezone: string
  time: string
  date: string
  offset: string
}

const clocks = ref<ClockDisplay[]>([])
let interval: ReturnType<typeof setInterval> | null = null

function formatClock(tz: TimezoneEntry): ClockDisplay {
  const now = new Date()

  const time = now.toLocaleTimeString('id-ID', {
    timeZone: tz.timezone,
    hour: '2-digit',
    minute: '2-digit',
    second: '2-digit',
    hour12: false,
  })

  const date = now.toLocaleDateString('id-ID', {
    timeZone: tz.timezone,
    weekday: 'short',
    day: 'numeric',
    month: 'short',
  })

  // Calculate UTC offset
  const formatter = new Intl.DateTimeFormat('en-US', {
    timeZone: tz.timezone,
    timeZoneName: 'shortOffset',
  })
  const parts = formatter.formatToParts(now)
  const offsetPart = parts.find(p => p.type === 'timeZoneName')
  const offset = offsetPart?.value ?? ''

  return {
    label: tz.label,
    timezone: tz.timezone,
    time,
    date,
    offset,
  }
}

function updateClocks() {
  clocks.value = props.timezones.map(formatClock)
}

onMounted(() => {
  updateClocks()
  interval = setInterval(updateClocks, 1000)
})

onUnmounted(() => {
  if (interval) clearInterval(interval)
})
</script>

<template>
  <div class="rounded-xl border border-gray-200 bg-white p-5 dark:border-gray-700 dark:bg-gray-800">
    <div class="flex items-center justify-between">
      <div class="flex items-center gap-2">
        <Globe :size="16" class="text-gray-500 dark:text-gray-400" />
        <h2 class="text-sm font-semibold text-gray-900 dark:text-white">World Clock</h2>
      </div>
      <router-link
        to="/settings/general"
        class="text-xs text-primary-600 hover:text-primary-700 dark:text-primary-400"
      >
        Atur
      </router-link>
    </div>

    <div v-if="clocks.length" class="mt-4 space-y-3">
      <div
        v-for="clock in clocks"
        :key="clock.timezone"
        class="flex items-center justify-between rounded-lg border border-gray-100 bg-gray-50 px-3 py-2.5 dark:border-gray-700 dark:bg-gray-700/40"
      >
        <div class="flex items-center gap-2.5">
          <Clock :size="14" class="text-gray-400" />
          <div>
            <p class="text-sm font-medium text-gray-900 dark:text-white">{{ clock.label }}</p>
            <p class="text-xs text-gray-500 dark:text-gray-400">{{ clock.date }} · {{ clock.offset }}</p>
          </div>
        </div>
        <p class="text-lg font-bold tabular-nums text-gray-900 dark:text-white">{{ clock.time }}</p>
      </div>
    </div>

    <div v-else class="mt-4 flex flex-col items-center py-4 text-center">
      <Clock :size="28" class="text-gray-300 dark:text-gray-600" />
      <p class="mt-2 text-sm text-gray-400 dark:text-gray-500">Belum ada timezone ditambahkan.</p>
      <router-link
        to="/settings/general"
        class="mt-1 text-xs text-primary-600 hover:text-primary-700 dark:text-primary-400"
      >
        Tambah di Pengaturan
      </router-link>
    </div>
  </div>
</template>
