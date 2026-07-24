<script setup lang="ts">
import { ref } from 'vue'
import { useRouter } from 'vue-router'
import { useAuthStore } from '@purdia/auth'
import { usePreferences } from '@/composables/usePreferences'

const auth = useAuthStore()
const router = useRouter()
const preferences = usePreferences()

const name = ref('')
const email = ref('')
const password = ref('')
const passwordConfirmation = ref('')
const loading = ref(false)
const error = ref('')
const errors = ref<Record<string, string[]>>({})

async function handleRegister() {
  loading.value = true
  error.value = ''
  errors.value = {}

  if (password.value !== passwordConfirmation.value) {
    errors.value = { password: ['Konfirmasi password tidak cocok.'] }
    loading.value = false
    return
  }

  try {
    await auth.register(name.value, email.value, password.value)
    await preferences.load()
    router.push({ name: 'dashboard' })
  } catch (err: unknown) {
    const apiErr = err as { message?: string; errors?: Record<string, string[]> }
    error.value = apiErr?.message ?? 'Registrasi gagal. Coba lagi.'
    errors.value = apiErr?.errors ?? {}
  } finally {
    loading.value = false
  }
}
</script>

<template>
  <div class="flex min-h-screen items-center justify-center bg-gray-50 px-4 dark:bg-gray-900">
    <div class="w-full max-w-sm">
      <!-- Brand -->
      <div class="mb-8 text-center">
        <h1 class="text-2xl font-bold text-primary-600">Purdia</h1>
        <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">Buat akun baru</p>
      </div>

      <!-- Form -->
      <form
        class="rounded-xl border border-gray-200 bg-white p-6 shadow-sm dark:border-gray-700 dark:bg-gray-800"
        @submit.prevent="handleRegister"
      >
        <!-- Global error -->
        <div
          v-if="error && !Object.keys(errors).length"
          class="mb-4 rounded-lg bg-red-50 px-4 py-3 text-sm text-red-700 dark:bg-red-900/20 dark:text-red-400"
        >
          {{ error }}
        </div>

        <!-- Name -->
        <div class="mb-4">
          <label class="mb-1 block text-sm font-medium text-gray-700 dark:text-gray-300">
            Nama
          </label>
          <input
            v-model="name"
            type="text"
            required
            autocomplete="name"
            class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm focus:border-primary-500 focus:outline-none focus:ring-1 focus:ring-primary-500 dark:border-gray-600 dark:bg-gray-700 dark:text-white dark:focus:border-primary-400"
            :class="errors.name ? 'border-red-500 dark:border-red-500' : ''"
            placeholder="Nama lengkap"
          />
          <p v-if="errors.name" class="mt-1 text-xs text-red-600 dark:text-red-400">
            {{ errors.name[0] }}
          </p>
        </div>

        <!-- Email -->
        <div class="mb-4">
          <label class="mb-1 block text-sm font-medium text-gray-700 dark:text-gray-300">
            Email
          </label>
          <input
            v-model="email"
            type="email"
            required
            autocomplete="email"
            class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm focus:border-primary-500 focus:outline-none focus:ring-1 focus:ring-primary-500 dark:border-gray-600 dark:bg-gray-700 dark:text-white dark:focus:border-primary-400"
            :class="errors.email ? 'border-red-500 dark:border-red-500' : ''"
            placeholder="email@contoh.com"
          />
          <p v-if="errors.email" class="mt-1 text-xs text-red-600 dark:text-red-400">
            {{ errors.email[0] }}
          </p>
        </div>

        <!-- Password -->
        <div class="mb-4">
          <label class="mb-1 block text-sm font-medium text-gray-700 dark:text-gray-300">
            Password
          </label>
          <input
            v-model="password"
            type="password"
            required
            autocomplete="new-password"
            class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm focus:border-primary-500 focus:outline-none focus:ring-1 focus:ring-primary-500 dark:border-gray-600 dark:bg-gray-700 dark:text-white dark:focus:border-primary-400"
            :class="errors.password ? 'border-red-500 dark:border-red-500' : ''"
            placeholder="Minimal 8 karakter"
          />
          <p v-if="errors.password" class="mt-1 text-xs text-red-600 dark:text-red-400">
            {{ errors.password[0] }}
          </p>
        </div>

        <!-- Confirm Password -->
        <div class="mb-6">
          <label class="mb-1 block text-sm font-medium text-gray-700 dark:text-gray-300">
            Konfirmasi Password
          </label>
          <input
            v-model="passwordConfirmation"
            type="password"
            required
            autocomplete="new-password"
            class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm focus:border-primary-500 focus:outline-none focus:ring-1 focus:ring-primary-500 dark:border-gray-600 dark:bg-gray-700 dark:text-white dark:focus:border-primary-400"
            placeholder="Ulangi password"
          />
        </div>

        <!-- Submit -->
        <button
          type="submit"
          :disabled="loading"
          class="w-full rounded-lg bg-primary-600 px-4 py-2.5 text-sm font-medium text-white transition-colors hover:bg-primary-700 disabled:cursor-not-allowed disabled:opacity-50"
        >
          {{ loading ? 'Memproses...' : 'Daftar' }}
        </button>

        <!-- Link to login -->
        <p class="mt-4 text-center text-sm text-gray-500 dark:text-gray-400">
          Sudah punya akun?
          <router-link
            :to="{ name: 'login' }"
            class="font-medium text-primary-600 hover:text-primary-700 dark:text-primary-400"
          >
            Masuk
          </router-link>
        </p>
      </form>
    </div>
  </div>
</template>
