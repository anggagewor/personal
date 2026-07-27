<script setup lang="ts">
import { ref, onMounted } from 'vue'
import { get } from '@purdia/http'
import { usePreferences, type TimezoneEntry } from '@/composables/usePreferences'
import { useAuthStore } from '@purdia/auth'
import { useToast } from '@purdia/toast'
import BaseInput from '@purdia/ui/src/components/BaseInput.vue'
import BaseButton from '@purdia/ui/src/components/BaseButton.vue'
import { Plus, Trash2, Clock, Globe } from '@lucide/vue'

const auth = useAuthStore()
const preferences = usePreferences()
const toast = useToast()

const locale = ref('id')
const timezones = ref<TimezoneEntry[]>([])

// --- Timezone form ---
const newLabel = ref('')
const newTimezone = ref('')
const timezoneSearch = ref('')
const showTimezoneDropdown = ref(false)

const popularTimezones = [
  { value: 'Asia/Jakarta', label: 'Jakarta (WIB)' },
  { value: 'Asia/Makassar', label: 'Makassar (WITA)' },
  { value: 'Asia/Jayapura', label: 'Jayapura (WIT)' },
  { value: 'Europe/Istanbul', label: 'Istanbul' },
  { value: 'America/New_York', label: 'New York (ET)' },
  { value: 'America/Chicago', label: 'Chicago (CT)' },
  { value: 'America/Los_Angeles', label: 'Los Angeles (PT)' },
  { value: 'Europe/London', label: 'London (GMT)' },
  { value: 'Europe/Berlin', label: 'Berlin (CET)' },
  { value: 'Asia/Tokyo', label: 'Tokyo (JST)' },
  { value: 'Asia/Shanghai', label: 'Shanghai (CST)' },
  { value: 'Asia/Hong_Kong', label: 'Hong Kong (HKT)' },
  { value: 'Asia/Singapore', label: 'Singapore (SGT)' },
  { value: 'Asia/Dubai', label: 'Dubai (GST)' },
  { value: 'Australia/Sydney', label: 'Sydney (AEST)' },
  { value: 'Pacific/Auckland', label: 'Auckland (NZST)' },
  { value: 'Asia/Kolkata', label: 'India (IST)' },
  { value: 'Asia/Seoul', label: 'Seoul (KST)' },
]

const allTimezones = Intl.supportedValuesOf('timeZone')

const filteredTimezones = ref<string[]>([])

function onTimezoneSearchInput() {
  const q = timezoneSearch.value.toLowerCase()
  if (!q) {
    filteredTimezones.value = []
    showTimezoneDropdown.value = false
    return
  }
  filteredTimezones.value = allTimezones
    .filter(tz => tz.toLowerCase().includes(q))
    .slice(0, 15)
  showTimezoneDropdown.value = filteredTimezones.value.length > 0
}

function selectTimezone(tz: string) {
  newTimezone.value = tz
  timezoneSearch.value = tz
  showTimezoneDropdown.value = false
  // Auto-fill label from last part of timezone
  if (!newLabel.value) {
    newLabel.value = tz.split('/').pop()?.replace(/_/g, ' ') ?? tz
  }
}

function selectPopularTimezone(tz: { value: string; label: string }) {
  newTimezone.value = tz.value
  newLabel.value = tz.label
  timezoneSearch.value = tz.value
  showTimezoneDropdown.value = false
}

function addTimezone() {
  if (!newTimezone.value || !newLabel.value) return
  if (timezones.value.length >= 10) {
    toast.error('Maksimal 10 timezone.')
    return
  }
  if (timezones.value.some(t => t.timezone === newTimezone.value)) {
    toast.error('Timezone sudah ada di daftar.')
    return
  }

  timezones.value.push({ label: newLabel.value, timezone: newTimezone.value })
  saveTimezones()
  newLabel.value = ''
  newTimezone.value = ''
  timezoneSearch.value = ''
}

function removeTimezone(index: number) {
  timezones.value.splice(index, 1)
  saveTimezones()
}

function saveTimezones() {
  preferences.save({ timezones: timezones.value })
  toast.success('Timezone berhasil disimpan.')
}

// --- Locale ---
const localeOptions = [
  { value: 'id', label: 'Bahasa Indonesia' },
  { value: 'en', label: 'English' },
]

function updateLocale(value: string) {
  locale.value = value
  preferences.save({ locale: value })
}

// --- Init ---
onMounted(async () => {
  locale.value = localStorage.getItem('app_locale') ?? 'id'

  // Load saved timezones from preferences
  try {
    const response = await get<Record<string, unknown>>('/preferences')
    const prefs = response.data
    if (Array.isArray(prefs.timezones)) {
      timezones.value = prefs.timezones as TimezoneEntry[]
    }
  } catch {
    // Use empty defaults
  }
})
</script>

<template>
  <div>
    <h1 class="text-2xl font-semibold text-gray-900 dark:text-white">Pengaturan Umum</h1>
    <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">Konfigurasi dasar aplikasi.</p>

    <div class="mt-8 max-w-2xl space-y-8">
      <!-- Profile section -->
      <section class="rounded-xl border border-gray-200 bg-white p-6 dark:border-gray-700 dark:bg-gray-800">
        <h2 class="text-base font-semibold text-gray-900 dark:text-white">Profil</h2>
        <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">Informasi akun kamu.</p>

        <div class="mt-5 space-y-4">
          <div>
            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">Nama</label>
            <p class="mt-1 text-sm text-gray-900 dark:text-white">{{ auth.user?.name ?? '-' }}</p>
          </div>
          <div>
            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">Email</label>
            <p class="mt-1 text-sm text-gray-900 dark:text-white">{{ auth.user?.email ?? '-' }}</p>
          </div>
        </div>
      </section>

      <!-- Language section -->
      <section class="rounded-xl border border-gray-200 bg-white p-6 dark:border-gray-700 dark:bg-gray-800">
        <h2 class="text-base font-semibold text-gray-900 dark:text-white">Bahasa</h2>
        <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">Pilih bahasa tampilan aplikasi.</p>

        <div class="mt-5">
          <div class="flex flex-wrap gap-3">
            <button
              v-for="opt in localeOptions"
              :key="opt.value"
              class="rounded-lg border px-4 py-2 text-sm font-medium transition-colors"
              :class="
                locale === opt.value
                  ? 'border-primary-500 bg-primary-50 text-primary-700 dark:border-primary-400 dark:bg-primary-900/30 dark:text-primary-300'
                  : 'border-gray-200 text-gray-700 hover:border-gray-300 hover:bg-gray-50 dark:border-gray-600 dark:text-gray-300 dark:hover:border-gray-500 dark:hover:bg-gray-700'
              "
              @click="updateLocale(opt.value)"
            >
              {{ opt.label }}
            </button>
          </div>
        </div>
      </section>

      <!-- Timezone section -->
      <section class="rounded-xl border border-gray-200 bg-white p-6 dark:border-gray-700 dark:bg-gray-800">
        <div class="flex items-center gap-2">
          <Globe :size="18" class="text-gray-500 dark:text-gray-400" />
          <h2 class="text-base font-semibold text-gray-900 dark:text-white">World Clock</h2>
        </div>
        <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">Tambahkan timezone yang ingin ditampilkan di dashboard (maks 10).</p>

        <!-- Saved timezones list -->
        <div v-if="timezones.length" class="mt-5 space-y-2">
          <div
            v-for="(tz, idx) in timezones"
            :key="tz.timezone"
            class="flex items-center justify-between rounded-lg border border-gray-100 bg-gray-50 px-4 py-2.5 dark:border-gray-700 dark:bg-gray-700/50"
          >
            <div class="flex items-center gap-3">
              <Clock :size="14" class="text-gray-400" />
              <div>
                <p class="text-sm font-medium text-gray-900 dark:text-white">{{ tz.label }}</p>
                <p class="text-xs text-gray-500 dark:text-gray-400">{{ tz.timezone }}</p>
              </div>
            </div>
            <button
              class="rounded p-1 text-gray-400 hover:text-red-500 transition-colors"
              @click="removeTimezone(idx)"
              title="Hapus"
            >
              <Trash2 :size="14" />
            </button>
          </div>
        </div>

        <!-- Popular timezones quick add -->
        <div class="mt-5">
          <p class="text-xs font-medium text-gray-500 dark:text-gray-400 mb-2">Timezone populer:</p>
          <div class="flex flex-wrap gap-2">
            <button
              v-for="tz in popularTimezones.filter(p => !timezones.some(t => t.timezone === p.value))"
              :key="tz.value"
              class="rounded-md border border-gray-200 px-2.5 py-1 text-xs text-gray-600 hover:border-primary-300 hover:bg-primary-50 hover:text-primary-700 transition-colors dark:border-gray-600 dark:text-gray-400 dark:hover:border-primary-500 dark:hover:bg-primary-900/20 dark:hover:text-primary-300"
              @click="selectPopularTimezone(tz)"
            >
              {{ tz.label }}
            </button>
          </div>
        </div>

        <!-- Add form -->
        <div class="mt-5 rounded-lg border border-gray-200 bg-gray-50 p-4 dark:border-gray-700 dark:bg-gray-700/30">
          <p class="text-sm font-medium text-gray-700 dark:text-gray-300 mb-3">Tambah Timezone</p>
          <div class="space-y-3">
            <!-- Timezone search -->
            <div class="relative">
              <label class="block text-xs font-medium text-gray-600 dark:text-gray-400 mb-1">Timezone</label>
              <input
                v-model="timezoneSearch"
                type="text"
                class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm focus:border-primary-500 focus:outline-none focus:ring-1 focus:ring-primary-500 dark:border-gray-600 dark:bg-gray-700 dark:text-white"
                placeholder="Ketik untuk cari timezone... (misal: Istanbul)"
                @input="onTimezoneSearchInput"
                @focus="onTimezoneSearchInput"
              />
              <!-- Dropdown -->
              <div
                v-if="showTimezoneDropdown"
                class="absolute z-10 mt-1 max-h-48 w-full overflow-y-auto rounded-lg border border-gray-200 bg-white shadow-lg dark:border-gray-600 dark:bg-gray-800"
              >
                <button
                  v-for="tz in filteredTimezones"
                  :key="tz"
                  type="button"
                  class="block w-full px-3 py-2 text-left text-sm text-gray-700 hover:bg-primary-50 hover:text-primary-700 dark:text-gray-300 dark:hover:bg-primary-900/20 dark:hover:text-primary-300"
                  @mousedown.prevent="selectTimezone(tz)"
                >
                  {{ tz }}
                </button>
              </div>
            </div>

            <!-- Label -->
            <div>
              <label class="block text-xs font-medium text-gray-600 dark:text-gray-400 mb-1">Label (nama tampilan)</label>
              <input
                v-model="newLabel"
                type="text"
                class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm focus:border-primary-500 focus:outline-none focus:ring-1 focus:ring-primary-500 dark:border-gray-600 dark:bg-gray-700 dark:text-white"
                placeholder="misal: Server Istanbul"
              />
            </div>

            <BaseButton
              variant="primary"
              size="sm"
              :icon="Plus"
              :disabled="!newTimezone || !newLabel"
              @click="addTimezone"
            >
              Tambah
            </BaseButton>
          </div>
        </div>
      </section>
    </div>
  </div>
</template>
