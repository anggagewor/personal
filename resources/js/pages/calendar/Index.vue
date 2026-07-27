<script setup lang="ts">
import { ref, computed, watch } from 'vue'
import { get, post, put, del } from '@purdia/http'
import BaseCalendar from '@purdia/ui/src/components/BaseCalendar.vue'
import BaseModal from '@purdia/ui/src/components/BaseModal.vue'
import BaseInput from '@purdia/ui/src/components/BaseInput.vue'
import BaseButton from '@purdia/ui/src/components/BaseButton.vue'
import BaseDatePicker from '@purdia/ui/src/components/BaseDatePicker.vue'
import BaseTextarea from '@purdia/ui/src/components/BaseTextarea.vue'
import type { CalendarEvent as UICalendarEvent } from '@purdia/ui/src/components/BaseCalendar.vue'
import CalendarDayView from '@/components/calendar/CalendarDayView.vue'
import CalendarWeekView from '@/components/calendar/CalendarWeekView.vue'
import CalendarYearView from '@/components/calendar/CalendarYearView.vue'
import { Plus, Trash2, Clock, Calendar, X, ChevronLeft, ChevronRight } from '@lucide/vue'

type ViewMode = 'day' | 'week' | 'month' | 'year'

interface CalendarEvent {
  id: number
  title: string
  description: string | null
  start_at: string
  end_at: string | null
  all_day: boolean
  color: string | null
}

interface HolidayItem {
  id: number
  date: string
  summary: string
  description: string | null
  is_national_holiday: boolean
}

const events = ref<CalendarEvent[]>([])
const holidays = ref<HolidayItem[]>([])
const loading = ref(false)
const selectedDate = ref<Date>(new Date())
const showForm = ref(false)
const editingEvent = ref<CalendarEvent | null>(null)
const upcomingFilter = ref<'today' | 'week' | 'month'>('week')
const viewMode = ref<ViewMode>('month')

const viewModeOptions: { key: ViewMode; label: string }[] = [
  { key: 'day', label: 'Hari' },
  { key: 'week', label: 'Minggu' },
  { key: 'month', label: 'Bulan' },
  { key: 'year', label: 'Tahun' },
]

const form = ref({
  title: '',
  description: '',
  start_at: '',
  end_at: '',
  all_day: false,
  color: '',
})

// Holiday dates for calendar highlighting (red styling)
const holidayDates = computed<Date[]>(() => {
  return holidays.value
    .filter((h) => h.is_national_holiday)
    .map((h) => new Date(h.date))
})

// Map API events to BaseCalendar format + merge holidays
const calendarEvents = computed<UICalendarEvent[]>(() => {
  // User events
  const userEvents: UICalendarEvent[] = events.value.map((e) => ({
    id: String(e.id),
    title: e.title,
    date: new Date(e.start_at),
    variant: mapColorToVariant(e.color),
  }))

  // Holidays
  const holidayEvents: UICalendarEvent[] = holidays.value.map((h) => ({
    id: `holiday-${h.id}`,
    title: h.summary,
    date: new Date(h.date),
    variant: h.is_national_holiday ? 'danger' : 'warning',
  }))

  return [...holidayEvents, ...userEvents]
})

function mapColorToVariant(color: string | null): UICalendarEvent['variant'] {
  if (!color) return 'primary'
  if (color.includes('green') || color.includes('emerald')) return 'success'
  if (color.includes('amber') || color.includes('yellow')) return 'warning'
  if (color.includes('red') || color.includes('rose')) return 'danger'
  if (color.includes('cyan') || color.includes('blue')) return 'info'
  return 'primary'
}

// Navigation label for the toolbar
const navigationLabel = computed(() => {
  const d = selectedDate.value
  switch (viewMode.value) {
    case 'day':
      return d.toLocaleDateString('id-ID', { weekday: 'long', day: 'numeric', month: 'long', year: 'numeric' })
    case 'week': {
      const day = d.getDay()
      const mondayOffset = day === 0 ? -6 : 1 - day
      const monday = new Date(d)
      monday.setDate(d.getDate() + mondayOffset)
      const sunday = new Date(monday)
      sunday.setDate(monday.getDate() + 6)
      const opts: Intl.DateTimeFormatOptions = { day: 'numeric', month: 'short' }
      return `${monday.toLocaleDateString('id-ID', opts)} — ${sunday.toLocaleDateString('id-ID', { ...opts, year: 'numeric' })}`
    }
    case 'month':
      return d.toLocaleDateString('id-ID', { month: 'long', year: 'numeric' })
    case 'year':
      return String(d.getFullYear())
  }
})

function navigatePrev() {
  const d = new Date(selectedDate.value)
  switch (viewMode.value) {
    case 'day':
      d.setDate(d.getDate() - 1)
      break
    case 'week':
      d.setDate(d.getDate() - 7)
      break
    case 'month':
      d.setMonth(d.getMonth() - 1)
      break
    case 'year':
      d.setFullYear(d.getFullYear() - 1)
      break
  }
  selectedDate.value = d
}

function navigateNext() {
  const d = new Date(selectedDate.value)
  switch (viewMode.value) {
    case 'day':
      d.setDate(d.getDate() + 1)
      break
    case 'week':
      d.setDate(d.getDate() + 7)
      break
    case 'month':
      d.setMonth(d.getMonth() + 1)
      break
    case 'year':
      d.setFullYear(d.getFullYear() + 1)
      break
  }
  selectedDate.value = d
}

function goToToday() {
  selectedDate.value = new Date()
}

// Upcoming events filtered by today/week/month (includes holidays)
const upcomingEvents = computed(() => {
  const now = new Date()
  const today = new Date(now.getFullYear(), now.getMonth(), now.getDate())

  let endDate: Date
  if (upcomingFilter.value === 'today') {
    endDate = new Date(today.getTime() + 24 * 60 * 60 * 1000)
  } else if (upcomingFilter.value === 'week') {
    endDate = new Date(today.getTime() + 7 * 24 * 60 * 60 * 1000)
  } else {
    endDate = new Date(today.getFullYear(), today.getMonth() + 1, today.getDate())
  }

  // User events
  const userItems = events.value
    .filter((e) => {
      const eventDate = new Date(e.start_at)
      return eventDate >= today && eventDate < endDate
    })
    .map((e) => ({
      id: e.id,
      title: e.title,
      time: e.start_at,
      endTime: e.end_at,
      color: e.color,
      isHoliday: false,
    }))

  // Holidays in range
  const holidayItems = holidays.value
    .filter((h) => {
      const hDate = new Date(h.date)
      return hDate >= today && hDate < endDate
    })
    .map((h) => ({
      id: -1,
      title: h.summary,
      time: h.date,
      endTime: null as string | null,
      color: h.is_national_holiday ? '#ef4444' : '#f59e0b',
      isHoliday: true,
    }))

  return [...holidayItems, ...userItems].sort((a, b) => new Date(a.time).getTime() - new Date(b.time).getTime())
})

// Fetch events and holidays for current viewed period
const viewedDate = ref<Date>(new Date())

async function fetchEvents() {
  loading.value = true
  const year = viewedDate.value.getFullYear()
  const month = viewedDate.value.getMonth()

  let start: string
  let end: string

  if (viewMode.value === 'year') {
    start = `${year}-01-01`
    end = `${year}-12-31`
  } else {
    start = `${year}-${String(month + 1).padStart(2, '0')}-01`
    const lastDay = new Date(year, month + 1, 0).getDate()
    end = `${year}-${String(month + 1).padStart(2, '0')}-${lastDay}`
  }

  try {
    const [eventsRes, holidaysRes] = await Promise.all([
      get<CalendarEvent[]>('/calendar-events', { params: { start, end } }),
      get<HolidayItem[]>('/holidays', { params: { start, end } }),
    ])
    events.value = eventsRes.data
    holidays.value = holidaysRes.data
  } catch { /* */ }
  loading.value = false
}

function onMonthChange(date: Date) {
  viewedDate.value = date
  fetchEvents()
}

function onDateClick(date: Date) {
  editingEvent.value = null
  const iso = `${date.getFullYear()}-${String(date.getMonth() + 1).padStart(2, '0')}-${String(date.getDate()).padStart(2, '0')}`
  const hours = date.getHours() > 0 ? String(date.getHours()).padStart(2, '0') : '09'
  const minutes = date.getMinutes() > 0 ? String(date.getMinutes()).padStart(2, '0') : '00'
  form.value = {
    title: '',
    description: '',
    start_at: `${iso}T${hours}:${minutes}`,
    end_at: '',
    all_day: false,
    color: '',
  }
  showForm.value = true
}

function onEventClick(event: UICalendarEvent) {
  // Ignore holiday clicks
  if (String(event.id).startsWith('holiday-')) return
  const found = events.value.find((e) => String(e.id) === event.id)
  if (!found) return
  openEdit(found)
}

function openEdit(event: CalendarEvent) {
  editingEvent.value = event
  form.value = {
    title: event.title,
    description: event.description ?? '',
    start_at: event.start_at.slice(0, 16),
    end_at: event.end_at?.slice(0, 16) ?? '',
    all_day: event.all_day,
    color: event.color ?? '',
  }
  showForm.value = true
}

function openNew() {
  const today = new Date()
  const iso = `${today.getFullYear()}-${String(today.getMonth() + 1).padStart(2, '0')}-${String(today.getDate()).padStart(2, '0')}`
  editingEvent.value = null
  form.value = { title: '', description: '', start_at: `${iso}T09:00`, end_at: '', all_day: false, color: '' }
  showForm.value = true
}

async function saveEvent() {
  if (!form.value.title.trim() || !form.value.start_at) return
  const payload = {
    ...form.value,
    end_at: form.value.end_at || null,
    description: form.value.description || null,
    color: form.value.color || null,
  }

  if (editingEvent.value) {
    await put(`/calendar-events/${editingEvent.value.id}`, payload)
  } else {
    await post('/calendar-events', payload)
  }
  showForm.value = false
  editingEvent.value = null
  fetchEvents()
}

async function deleteEvent(event?: CalendarEvent) {
  const target = event ?? editingEvent.value
  if (!target) return
  await del(`/calendar-events/${target.id}`)
  if (!event) {
    showForm.value = false
    editingEvent.value = null
  }
  fetchEvents()
}

function formatEventTime(dateStr: string): string {
  const d = new Date(dateStr)
  return d.toLocaleString('id-ID', { weekday: 'short', day: 'numeric', month: 'short', hour: '2-digit', minute: '2-digit' })
}

function formatTimeOnly(dateStr: string): string {
  const d = new Date(dateStr)
  return d.toLocaleTimeString('id-ID', { hour: '2-digit', minute: '2-digit' })
}

// When switching to year view from month-click in year view, navigate to that month
function onYearMonthClick(date: Date) {
  selectedDate.value = date
  viewMode.value = 'month'
}

// When clicking a date in year view, switch to day view
function onYearDateClick(date: Date) {
  selectedDate.value = date
  viewMode.value = 'day'
}

watch(selectedDate, () => {
  viewedDate.value = selectedDate.value
  fetchEvents()
})

watch(viewMode, () => {
  fetchEvents()
})

fetchEvents()
</script>

<template>
  <div>
    <div class="flex items-center justify-between">
      <div>
        <h1 class="text-2xl font-semibold text-gray-900 dark:text-white">Kalender</h1>
        <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">Jadwal dan event kamu.</p>
      </div>
      <BaseButton variant="primary" size="sm" :icon="Plus" @click="openNew">
        Event Baru
      </BaseButton>
    </div>

    <!-- Calendar -->
    <div class="mt-6 rounded-xl border border-gray-200 bg-white p-5 dark:border-gray-700 dark:bg-gray-800">
      <!-- Toolbar: View mode switcher + Navigation -->
      <div class="flex flex-col sm:flex-row items-start sm:items-center justify-between gap-3 mb-5">
        <!-- Navigation -->
        <div class="flex items-center gap-2">
          <button
            type="button"
            class="p-1.5 rounded-lg text-gray-500 hover:bg-gray-100 hover:text-gray-700 cursor-pointer dark:text-gray-400 dark:hover:bg-gray-700 dark:hover:text-gray-200 transition-colors"
            aria-label="Sebelumnya"
            @click="navigatePrev"
          >
            <ChevronLeft :size="18" />
          </button>
          <button
            type="button"
            class="px-3 py-1 rounded-lg text-sm font-semibold text-gray-900 dark:text-white hover:bg-gray-100 dark:hover:bg-gray-700 transition-colors min-w-[10rem] text-center"
            @click="goToToday"
            title="Ke hari ini"
          >
            {{ navigationLabel }}
          </button>
          <button
            type="button"
            class="p-1.5 rounded-lg text-gray-500 hover:bg-gray-100 hover:text-gray-700 cursor-pointer dark:text-gray-400 dark:hover:bg-gray-700 dark:hover:text-gray-200 transition-colors"
            aria-label="Berikutnya"
            @click="navigateNext"
          >
            <ChevronRight :size="18" />
          </button>
        </div>

        <!-- View mode switcher -->
        <div class="flex gap-1 rounded-lg border border-gray-200 bg-gray-50 p-0.5 dark:border-gray-700 dark:bg-gray-800">
          <button
            v-for="opt in viewModeOptions"
            :key="opt.key"
            class="rounded-md px-3 py-1.5 text-xs font-medium transition-colors cursor-pointer"
            :class="viewMode === opt.key ? 'bg-white text-gray-900 shadow-sm dark:bg-gray-700 dark:text-white' : 'text-gray-500 hover:text-gray-700 dark:text-gray-400 dark:hover:text-gray-200'"
            @click="viewMode = opt.key"
          >
            {{ opt.label }}
          </button>
        </div>
      </div>

      <!-- Day View -->
      <CalendarDayView
        v-if="viewMode === 'day'"
        :date="selectedDate"
        :events="calendarEvents"
        :holidays="holidayDates"
        @event-click="onEventClick"
        @time-click="onDateClick"
      />

      <!-- Week View -->
      <CalendarWeekView
        v-if="viewMode === 'week'"
        :date="selectedDate"
        :events="calendarEvents"
        :holidays="holidayDates"
        @event-click="onEventClick"
        @date-click="onDateClick"
      />

      <!-- Month View (existing BaseCalendar) -->
      <BaseCalendar
        v-if="viewMode === 'month'"
        v-model="selectedDate"
        :events="calendarEvents"
        :holidays="holidayDates"
        variant="default"
        @date-click="onDateClick"
        @event-click="onEventClick"
        @month-change="onMonthChange"
      />

      <!-- Year View -->
      <CalendarYearView
        v-if="viewMode === 'year'"
        :year="selectedDate.getFullYear()"
        :events="calendarEvents"
        :holidays="holidayDates"
        @date-click="onYearDateClick"
        @month-click="onYearMonthClick"
      />

      <!-- Legend -->
      <div class="mt-4 flex flex-wrap items-center gap-4 border-t border-gray-100 pt-3 dark:border-gray-700">
        <span class="flex items-center gap-1.5 text-xs text-gray-500"><span class="h-2 w-2 rounded-full bg-red-500" /> Libur Nasional / Minggu</span>
        <span class="flex items-center gap-1.5 text-xs text-gray-500"><span class="h-2 w-2 rounded-full bg-amber-500" /> Perayaan</span>
        <span class="flex items-center gap-1.5 text-xs text-gray-500"><span class="h-2 w-2 rounded-full bg-primary-500" /> Event Kamu</span>
      </div>
    </div>

    <!-- Holidays this month -->
    <div v-if="holidays.length" class="mt-6 rounded-xl border border-gray-200 bg-white p-5 dark:border-gray-700 dark:bg-gray-800">
      <h2 class="text-base font-semibold text-gray-900 dark:text-white">Hari Libur Bulan Ini</h2>
      <div class="mt-3 space-y-2">
        <div
          v-for="h in holidays"
          :key="h.id"
          class="flex items-center gap-3 rounded-lg border border-gray-100 px-4 py-2.5 dark:border-gray-700"
        >
          <div
            class="h-2.5 w-2.5 shrink-0 rounded-full"
            :class="h.is_national_holiday ? 'bg-red-500' : 'bg-amber-500'"
          />
          <div class="flex-1 min-w-0">
            <p class="text-sm font-medium text-gray-900 dark:text-white">{{ h.summary }}</p>
            <p class="text-xs text-gray-400">{{ new Date(h.date).toLocaleDateString('id-ID', { weekday: 'long', day: 'numeric', month: 'long' }) }}</p>
          </div>
          <span
            class="shrink-0 rounded px-1.5 py-0.5 text-[10px] font-medium"
            :class="h.is_national_holiday ? 'bg-red-100 text-red-700 dark:bg-red-900/30 dark:text-red-300' : 'bg-amber-100 text-amber-700 dark:bg-amber-900/30 dark:text-amber-300'"
          >
            {{ h.is_national_holiday ? 'Libur' : 'Perayaan' }}
          </span>
        </div>
      </div>
    </div>

    <!-- Upcoming Events -->
    <div class="mt-6 rounded-xl border border-gray-200 bg-white p-5 dark:border-gray-700 dark:bg-gray-800">
      <div class="flex items-center justify-between">
        <h2 class="text-base font-semibold text-gray-900 dark:text-white">Event Mendatang</h2>
        <!-- Filter tabs -->
        <div class="flex gap-1 rounded-lg border border-gray-200 bg-gray-50 p-0.5 dark:border-gray-700 dark:bg-gray-800">
          <button
            v-for="f in (['today', 'week', 'month'] as const)"
            :key="f"
            class="rounded-md px-2.5 py-1 text-xs font-medium transition-colors"
            :class="upcomingFilter === f ? 'bg-white text-gray-900 shadow-sm dark:bg-gray-700 dark:text-white' : 'text-gray-500 hover:text-gray-700 dark:text-gray-400'"
            @click="upcomingFilter = f"
          >
            {{ f === 'today' ? 'Hari Ini' : f === 'week' ? 'Minggu Ini' : 'Bulan Ini' }}
          </button>
        </div>
      </div>

      <!-- Event list -->
      <div v-if="upcomingEvents.length" class="mt-4 space-y-2">
        <div
          v-for="(event, idx) in upcomingEvents"
          :key="`${event.id}-${idx}`"
          class="group flex items-center gap-3 rounded-lg border border-gray-100 px-4 py-3 transition-colors hover:border-gray-200 dark:border-gray-700 dark:hover:border-gray-600"
        >
          <!-- Color dot -->
          <div
            class="h-2.5 w-2.5 shrink-0 rounded-full"
            :style="{ backgroundColor: event.color ?? 'var(--color-primary-500)' }"
          />

          <!-- Content -->
          <div
            class="flex-1 min-w-0"
            :class="!event.isHoliday ? 'cursor-pointer' : ''"
            @click="!event.isHoliday && openEdit(events.find((e) => e.id === event.id)!)"
          >
            <p class="text-sm font-medium text-gray-900 truncate dark:text-white">
              {{ event.title }}
              <span v-if="event.isHoliday" class="ml-1.5 inline-flex rounded bg-red-100 px-1.5 py-0.5 text-[10px] font-medium text-red-700 dark:bg-red-900/30 dark:text-red-300">
                {{ event.color === '#ef4444' ? 'Libur' : 'Perayaan' }}
              </span>
            </p>
            <div class="mt-0.5 flex items-center gap-1.5 text-xs text-gray-400">
              <Clock :size="12" />
              <span>{{ formatEventTime(event.time) }}</span>
              <span v-if="event.endTime">— {{ formatTimeOnly(event.endTime) }}</span>
            </div>
          </div>

          <!-- Cancel/Delete event (only for user events) -->
          <button
            v-if="!event.isHoliday"
            class="rounded p-1.5 text-gray-400 opacity-0 group-hover:opacity-100 transition-opacity hover:bg-red-50 hover:text-red-600 dark:hover:bg-red-900/20"
            title="Hapus event"
            @click="deleteEvent(events.find((e) => e.id === event.id)!)"
          >
            <X :size="16" />
          </button>
        </div>
      </div>

      <!-- Empty state -->
      <div v-else class="mt-4 flex items-center gap-3 py-4">
        <Calendar :size="20" class="text-gray-300 dark:text-gray-600" />
        <p class="text-sm text-gray-400">
          {{ upcomingFilter === 'today' ? 'Tidak ada event hari ini.' : upcomingFilter === 'week' ? 'Tidak ada event minggu ini.' : 'Tidak ada event bulan ini.' }}
        </p>
      </div>
    </div>

    <!-- Event form modal -->
    <BaseModal v-model="showForm" size="md">
      <template #default>
        <div class="flex items-center justify-between">
          <h2 class="text-lg font-semibold text-gray-900 dark:text-white">
            {{ editingEvent ? 'Edit Event' : 'Event Baru' }}
          </h2>
          <button
            v-if="editingEvent"
            class="rounded p-1.5 text-gray-400 hover:bg-red-50 hover:text-red-600 dark:hover:bg-red-900/20"
            title="Hapus event"
            @click="deleteEvent()"
          >
            <Trash2 :size="16" />
          </button>
        </div>

        <form class="mt-4 space-y-4" @submit.prevent="saveEvent">
          <BaseInput
            v-model="form.title"
            label="Judul"
            placeholder="Judul event"
            required
          />

          <div class="grid grid-cols-2 gap-3">
            <BaseDatePicker
              v-model="form.start_at"
              mode="datetime"
              label="Mulai"
              placeholder="Pilih tanggal & waktu"
            />
            <BaseDatePicker
              v-model="form.end_at"
              mode="datetime"
              label="Selesai (opsional)"
              placeholder="Pilih tanggal & waktu"
            />
          </div>

          <BaseTextarea
            v-model="form.description"
            label="Deskripsi"
            placeholder="Deskripsi event (opsional)"
            :rows="3"
          />

          <div class="flex justify-end gap-2 pt-2">
            <BaseButton variant="secondary" size="sm" type="button" @click="showForm = false">
              Batal
            </BaseButton>
            <BaseButton variant="primary" size="sm" type="submit">
              Simpan
            </BaseButton>
          </div>
        </form>
      </template>
    </BaseModal>
  </div>
</template>
