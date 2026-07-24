<script setup lang="ts">
import { ref } from 'vue'
import { get, post, del } from '@purdia/http'
import BaseButton from '@purdia/ui/src/components/BaseButton.vue'
import BaseEditor from '@purdia/ui/src/components/BaseEditor.vue'
import { ChevronLeft, ChevronRight, Smile, Meh, Frown, Zap, Battery } from '@lucide/vue'

interface JournalEntry {
  id: number
  date: string
  content: string
  mood: string | null
}

const today = new Date()
const selectedDate = ref(today.toISOString().slice(0, 10))
const entry = ref<JournalEntry | null>(null)
const content = ref('')
const mood = ref<string | null>(null)
const saving = ref(false)

const moods = [
  { key: 'happy', label: '😊', text: 'Senang' },
  { key: 'energized', label: '⚡', text: 'Energik' },
  { key: 'neutral', label: '😐', text: 'Biasa' },
  { key: 'stressed', label: '😰', text: 'Stress' },
  { key: 'sad', label: '😢', text: 'Sedih' },
]

async function fetchEntry() {
  try {
    const res = await get<JournalEntry | null>(`/journals/${selectedDate.value}`)
    entry.value = res.data
    content.value = res.data?.content ?? ''
    mood.value = res.data?.mood ?? null
  } catch {
    entry.value = null
    content.value = ''
    mood.value = null
  }
}

async function saveEntry() {
  if (!content.value.trim()) return
  saving.value = true
  try {
    await post('/journals', { date: selectedDate.value, content: content.value, mood: mood.value })
    fetchEntry()
  } catch { /* */ }
  saving.value = false
}

function prevDay() {
  const d = new Date(selectedDate.value)
  d.setDate(d.getDate() - 1)
  selectedDate.value = d.toISOString().slice(0, 10)
  fetchEntry()
}

function nextDay() {
  const d = new Date(selectedDate.value)
  d.setDate(d.getDate() + 1)
  selectedDate.value = d.toISOString().slice(0, 10)
  fetchEntry()
}

function goToday() {
  selectedDate.value = new Date().toISOString().slice(0, 10)
  fetchEntry()
}

const dateLabel = (d: string) => new Date(d + 'T00:00:00').toLocaleDateString('id-ID', { weekday: 'long', day: 'numeric', month: 'long', year: 'numeric' })

fetchEntry()
</script>

<template>
  <div>
    <h1 class="text-2xl font-semibold text-gray-900 dark:text-white">Jurnal Harian</h1>
    <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">Tulis refleksi harianmu.</p>

    <!-- Date nav -->
    <div class="mt-6 flex items-center gap-3">
      <button class="rounded-lg p-2 text-gray-500 hover:bg-gray-100 dark:hover:bg-gray-700" @click="prevDay"><ChevronLeft :size="18" /></button>
      <div class="text-center flex-1">
        <p class="text-sm font-semibold text-gray-900 dark:text-white">{{ dateLabel(selectedDate) }}</p>
      </div>
      <button class="rounded-lg p-2 text-gray-500 hover:bg-gray-100 dark:hover:bg-gray-700" @click="nextDay"><ChevronRight :size="18" /></button>
      <button class="rounded-lg px-3 py-1.5 text-xs font-medium text-primary-600 hover:bg-primary-50 dark:hover:bg-primary-900/20" @click="goToday">Hari Ini</button>
    </div>

    <!-- Mood selector -->
    <div class="mt-6">
      <p class="text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Mood hari ini:</p>
      <div class="flex gap-2">
        <button v-for="m in moods" :key="m.key"
          class="flex flex-col items-center gap-1 rounded-lg px-3 py-2 text-xs transition-colors"
          :class="mood === m.key ? 'bg-primary-100 ring-2 ring-primary-500 dark:bg-primary-900/30' : 'bg-gray-50 hover:bg-gray-100 dark:bg-gray-800 dark:hover:bg-gray-700'"
          @click="mood = mood === m.key ? null : m.key"
        >
          <span class="text-lg">{{ m.label }}</span>
          <span class="text-gray-500">{{ m.text }}</span>
        </button>
      </div>
    </div>

    <!-- Content -->
    <div class="mt-6">
      <BaseEditor
        v-model="content"
        placeholder="Apa yang terjadi hari ini? Apa yang kamu pelajari?"
        variant="default"
        size="lg"
      />
    </div>

    <div class="mt-4 flex justify-end">
      <BaseButton variant="primary" size="sm" :loading="saving" @click="saveEntry">Simpan</BaseButton>
    </div>
  </div>
</template>
