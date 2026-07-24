<script setup lang="ts">
import { ref, onMounted } from 'vue'
import { usePreferences } from '@/composables/usePreferences'
import { useAuthStore } from '@purdia/auth'

const auth = useAuthStore()
const preferences = usePreferences()

const locale = ref('id')

const localeOptions = [
  { value: 'id', label: 'Bahasa Indonesia' },
  { value: 'en', label: 'English' },
]

onMounted(() => {
  locale.value = localStorage.getItem('app_locale') ?? 'id'
})

function updateLocale(value: string) {
  locale.value = value
  preferences.save({ locale: value })
}
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
    </div>
  </div>
</template>
