<script setup lang="ts">
import { ref, computed, onBeforeUnmount } from 'vue'
import BaseButton from '@purdia/ui/src/components/BaseButton.vue'
import BaseSelect from '@purdia/ui/src/components/BaseSelect.vue'
import { Play, Square, RotateCcw, Coffee } from '@lucide/vue'
import type { PomodoroSession, PomodoroStats } from '@/types/pomodoro'
import * as pomodoroApi from '@/api/pomodoro'

const stats = ref<PomodoroStats>({ today: 0, week: 0, total_minutes_week: 0 })
const activeSession = ref<PomodoroSession | null>(null)
const timeLeft = ref(25 * 60) // seconds
const isRunning = ref(false)
const isBreak = ref(false)
const duration = ref(25)
let timer: ReturnType<typeof setInterval> | null = null

const displayTime = computed(() => {
  const m = Math.floor(timeLeft.value / 60)
  const s = timeLeft.value % 60
  return `${String(m).padStart(2, '0')}:${String(s).padStart(2, '0')}`
})

const progress = computed(() => {
  const total = (isBreak.value ? 5 : duration.value) * 60
  return ((total - timeLeft.value) / total) * 100
})

async function startPomodoro() {
  try {
    const res = await pomodoroApi.startSession(duration.value)
    activeSession.value = res.data
    timeLeft.value = duration.value * 60
    isRunning.value = true
    isBreak.value = false
    startTimer()
  } catch { /* */ }
}

function startTimer() {
  if (timer) clearInterval(timer)
  timer = setInterval(() => {
    if (timeLeft.value <= 0) {
      clearInterval(timer!)
      timer = null
      isRunning.value = false
      if (!isBreak.value) {
        completePomodoro()
      } else {
        isBreak.value = false
      }
      return
    }
    timeLeft.value--
  }, 1000)
}

async function completePomodoro() {
  if (!activeSession.value) return
  try {
    await pomodoroApi.completeSession(activeSession.value.id)
    activeSession.value = null
    fetchStats()
    // Start break
    isBreak.value = true
    timeLeft.value = 5 * 60
    startTimer()
    isRunning.value = true
  } catch { /* */ }
}

async function cancelPomodoro() {
  if (timer) clearInterval(timer)
  timer = null
  isRunning.value = false
  if (activeSession.value) {
    await pomodoroApi.cancelSession(activeSession.value.id)
    activeSession.value = null
  }
  timeLeft.value = duration.value * 60
  isBreak.value = false
}

function reset() {
  if (timer) clearInterval(timer)
  timer = null
  isRunning.value = false
  isBreak.value = false
  timeLeft.value = duration.value * 60
  activeSession.value = null
}

async function fetchStats() {
  try {
    const res = await pomodoroApi.fetchStats()
    stats.value = res.data
  } catch { /* */ }
}

onBeforeUnmount(() => {
  if (timer) clearInterval(timer)
})

fetchStats()
</script>

<template>
  <div>
    <h1 class="text-2xl font-semibold text-gray-900 dark:text-white">Pomodoro Timer</h1>
    <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">Fokus kerja, istirahat teratur.</p>

    <div class="mt-8 flex flex-col items-center">
      <!-- Timer circle -->
      <div class="relative flex h-64 w-64 items-center justify-center rounded-full border-8 border-gray-100 dark:border-gray-700">
        <svg class="absolute inset-0 -rotate-90" viewBox="0 0 100 100">
          <circle cx="50" cy="50" r="45" fill="none" stroke="currentColor" stroke-width="4"
            :class="isBreak ? 'text-emerald-500' : 'text-primary-500'"
            :stroke-dasharray="283"
            :stroke-dashoffset="283 - (283 * progress) / 100"
            stroke-linecap="round"
          />
        </svg>
        <div class="text-center z-10">
          <p class="text-5xl font-bold text-gray-900 dark:text-white font-mono">{{ displayTime }}</p>
          <p class="mt-1 text-sm text-gray-500">{{ isBreak ? 'Istirahat' : isRunning ? 'Fokus' : 'Siap' }}</p>
        </div>
      </div>

      <!-- Controls -->
      <div class="mt-8 flex items-center gap-3">
        <BaseButton v-if="!isRunning" variant="primary" :icon="Play" @click="startPomodoro">
          Mulai
        </BaseButton>
        <BaseButton v-if="isRunning" variant="danger" :icon="Square" @click="cancelPomodoro">
          Stop
        </BaseButton>
        <BaseButton variant="secondary" :icon="RotateCcw" @click="reset">
          Reset
        </BaseButton>
      </div>

      <!-- Duration picker -->
      <div v-if="!isRunning" class="mt-4 flex items-center gap-3">
        <span class="text-sm text-gray-500">Durasi:</span>
        <div class="flex gap-2">
          <button v-for="d in [15, 25, 45, 60]" :key="d"
            class="rounded-lg px-3 py-1 text-sm font-medium transition-colors"
            :class="duration === d ? 'bg-primary-500 text-white' : 'bg-gray-100 text-gray-700 hover:bg-gray-200 dark:bg-gray-700 dark:text-gray-300'"
            @click="duration = d; timeLeft = d * 60"
          >
            {{ d }}m
          </button>
        </div>
      </div>
    </div>

    <!-- Stats -->
    <div class="mt-10 grid grid-cols-3 gap-4">
      <div class="rounded-xl border border-gray-200 bg-white p-4 text-center dark:border-gray-700 dark:bg-gray-800">
        <p class="text-2xl font-bold text-gray-900 dark:text-white">{{ stats.today }}</p>
        <p class="text-xs text-gray-500">Hari Ini</p>
      </div>
      <div class="rounded-xl border border-gray-200 bg-white p-4 text-center dark:border-gray-700 dark:bg-gray-800">
        <p class="text-2xl font-bold text-gray-900 dark:text-white">{{ stats.week }}</p>
        <p class="text-xs text-gray-500">Minggu Ini</p>
      </div>
      <div class="rounded-xl border border-gray-200 bg-white p-4 text-center dark:border-gray-700 dark:bg-gray-800">
        <p class="text-2xl font-bold text-gray-900 dark:text-white">{{ Math.round(stats.total_minutes_week / 60 * 10) / 10 }}h</p>
        <p class="text-xs text-gray-500">Jam Fokus</p>
      </div>
    </div>
  </div>
</template>
