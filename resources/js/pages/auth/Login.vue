<script setup lang="ts">
import { ref } from 'vue'
import { useRouter, useRoute } from 'vue-router'
import { useAuthStore } from '@purdia/auth'
import { usePreferences } from '@/composables/usePreferences'

const auth = useAuthStore()
const router = useRouter()
const route = useRoute()
const preferences = usePreferences()

const email = ref('')
const password = ref('')
const loading = ref(false)
const error = ref('')

async function handleLogin() {
  loading.value = true
  error.value = ''

  try {
    await auth.login(email.value, password.value)
    await preferences.load()
    const redirect = (route.query.redirect as string) ?? '/'
    router.push(redirect)
  } catch (err: unknown) {
    error.value = (err as { message?: string })?.message ?? 'Login gagal. Coba lagi.'
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
        <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">Masuk ke dashboard</p>
      </div>

      <!-- Form -->
      <form
        class="rounded-xl border border-gray-200 bg-white p-6 shadow-sm dark:border-gray-700 dark:bg-gray-800"
        @submit.prevent="handleLogin"
      >
        <!-- Error -->
        <div
          v-if="error"
          class="mb-4 rounded-lg bg-red-50 px-4 py-3 text-sm text-red-700 dark:bg-red-900/20 dark:text-red-400"
        >
          {{ error }}
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
            placeholder="email@contoh.com"
          />
        </div>

        <!-- Password -->
        <div class="mb-6">
          <label class="mb-1 block text-sm font-medium text-gray-700 dark:text-gray-300">
            Password
          </label>
          <input
            v-model="password"
            type="password"
            required
            autocomplete="current-password"
            class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm focus:border-primary-500 focus:outline-none focus:ring-1 focus:ring-primary-500 dark:border-gray-600 dark:bg-gray-700 dark:text-white dark:focus:border-primary-400"
            placeholder="••••••••"
          />
        </div>

        <!-- Submit -->
        <button
          type="submit"
          :disabled="loading"
          class="w-full rounded-lg bg-primary-600 px-4 py-2.5 text-sm font-medium text-white transition-colors hover:bg-primary-700 disabled:cursor-not-allowed disabled:opacity-50"
        >
          {{ loading ? 'Memproses...' : 'Masuk' }}
        </button>

        <!-- Link to register -->
        <p class="mt-4 text-center text-sm text-gray-500 dark:text-gray-400">
          Belum punya akun?
          <router-link
            :to="{ name: 'register' }"
            class="font-medium text-primary-600 hover:text-primary-700 dark:text-primary-400"
          >
            Daftar
          </router-link>
        </p>
      </form>
    </div>
  </div>
</template>
