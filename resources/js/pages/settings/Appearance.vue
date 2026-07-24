<script setup lang="ts">
import { computed } from 'vue'
import { useThemeStore, colorOptions } from '@purdia/theme'
import { usePreferences } from '@/composables/usePreferences'
import { Sun, Moon, Monitor } from '@lucide/vue'
import type { Theme, PrimaryColor } from '@purdia/theme'

const theme = useThemeStore()
const preferences = usePreferences()

const themeOptions = [
  { value: 'light' as Theme, label: 'Terang', icon: Sun },
  { value: 'dark' as Theme, label: 'Gelap', icon: Moon },
  { value: 'system' as Theme, label: 'Sistem', icon: Monitor },
]

const currentTheme = computed(() => theme.theme)
const currentColor = computed(() => theme.primaryColor)

function selectTheme(value: Theme) {
  theme.setTheme(value)
  preferences.save({ theme: value })
}

function selectColor(value: PrimaryColor) {
  theme.setColor(value)
  preferences.save({ primary_color: value })
}
</script>

<template>
  <div>
    <h1 class="text-2xl font-semibold text-gray-900 dark:text-white">Tampilan</h1>
    <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">Sesuaikan tampilan dashboard kamu.</p>

    <div class="mt-8 max-w-2xl space-y-8">
      <!-- Theme mode -->
      <section class="rounded-xl border border-gray-200 bg-white p-6 dark:border-gray-700 dark:bg-gray-800">
        <h2 class="text-base font-semibold text-gray-900 dark:text-white">Mode Tema</h2>
        <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">Pilih antara terang, gelap, atau ikuti sistem.</p>

        <div class="mt-5 flex flex-wrap gap-3">
          <button
            v-for="opt in themeOptions"
            :key="opt.value"
            class="flex items-center gap-2 rounded-lg border px-4 py-2.5 text-sm font-medium transition-colors"
            :class="
              currentTheme === opt.value
                ? 'border-primary-500 bg-primary-50 text-primary-700 dark:border-primary-400 dark:bg-primary-900/30 dark:text-primary-300'
                : 'border-gray-200 text-gray-700 hover:border-gray-300 hover:bg-gray-50 dark:border-gray-600 dark:text-gray-300 dark:hover:border-gray-500 dark:hover:bg-gray-700'
            "
            @click="selectTheme(opt.value)"
          >
            <component :is="opt.icon" :size="18" />
            {{ opt.label }}
          </button>
        </div>
      </section>

      <!-- Primary color -->
      <section class="rounded-xl border border-gray-200 bg-white p-6 dark:border-gray-700 dark:bg-gray-800">
        <h2 class="text-base font-semibold text-gray-900 dark:text-white">Warna Utama</h2>
        <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">Pilih warna aksen untuk tampilan dashboard.</p>

        <div class="mt-5 grid grid-cols-4 gap-3 sm:grid-cols-8">
          <button
            v-for="color in colorOptions"
            :key="color.name"
            class="group flex flex-col items-center gap-1.5"
            @click="selectColor(color.name)"
            :title="color.label"
          >
            <div
              class="flex h-10 w-10 items-center justify-center rounded-full border-2 transition-all"
              :class="
                currentColor === color.name
                  ? 'border-gray-900 scale-110 dark:border-white'
                  : 'border-transparent group-hover:border-gray-300 dark:group-hover:border-gray-500'
              "
            >
              <div
                class="h-7 w-7 rounded-full"
                :style="{ backgroundColor: color.swatch }"
              />
            </div>
            <span
              class="text-xs"
              :class="
                currentColor === color.name
                  ? 'font-medium text-gray-900 dark:text-white'
                  : 'text-gray-500 dark:text-gray-400'
              "
            >
              {{ color.label }}
            </span>
          </button>
        </div>
      </section>

      <!-- Preview -->
      <section class="rounded-xl border border-gray-200 bg-white p-6 dark:border-gray-700 dark:bg-gray-800">
        <h2 class="text-base font-semibold text-gray-900 dark:text-white">Preview</h2>
        <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">Contoh tampilan dengan konfigurasi saat ini.</p>

        <div class="mt-5 space-y-3">
          <div class="flex gap-2">
            <button class="rounded-lg bg-primary-600 px-4 py-2 text-sm font-medium text-white hover:bg-primary-700">
              Tombol Utama
            </button>
            <button class="rounded-lg border border-primary-600 px-4 py-2 text-sm font-medium text-primary-600 hover:bg-primary-50 dark:border-primary-400 dark:text-primary-400 dark:hover:bg-primary-900/20">
              Tombol Outline
            </button>
          </div>
          <div class="rounded-lg bg-primary-50 p-4 dark:bg-primary-900/20">
            <p class="text-sm font-medium text-primary-800 dark:text-primary-200">
              Ini adalah contoh alert dengan warna utama yang dipilih.
            </p>
          </div>
          <div class="flex items-center gap-3">
            <div class="h-3 w-3 rounded-full bg-primary-500" />
            <span class="text-sm text-gray-700 dark:text-gray-300">Status aktif dengan warna primary</span>
          </div>
        </div>
      </section>
    </div>
  </div>
</template>
