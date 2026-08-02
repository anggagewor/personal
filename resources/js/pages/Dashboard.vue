<script setup lang="ts">
import { useAuthStore } from '@purdia/auth'

const auth = useAuthStore()

function getGreeting(): string {
  const hour = new Date().getHours()
  if (hour < 12) return 'Selamat pagi'
  if (hour < 17) return 'Selamat siang'
  if (hour < 20) return 'Selamat sore'
  return 'Selamat malam'
}
</script>

<template>
  <div class="flex min-h-[60vh] flex-col items-center justify-center px-4 text-center">
    <h1 class="text-3xl font-semibold text-gray-900 dark:text-white">
      {{ getGreeting() }}, {{ auth.user?.name ?? 'User' }} 👋
    </h1>
    <p class="mt-3 max-w-md text-gray-500 dark:text-gray-400">
      Selamat datang di Purdia Dashboard.
    </p>
  </div>
</template>
