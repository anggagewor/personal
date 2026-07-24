<script setup lang="ts">
import { RouterView } from 'vue-router'
import { onMounted } from 'vue'
import { useAuthStore } from '@purdia/auth'
import { usePreferences } from '@/composables/usePreferences'
import { useProfile } from '@/composables/useProfile'
import { get } from '@purdia/http'

const auth = useAuthStore()
const preferences = usePreferences()
const { setAvatar } = useProfile()

// Load user preferences and profile after auth is ready
onMounted(async () => {
  if (!auth.ready) {
    await auth.init()
  }
  if (auth.isAuthenticated) {
    await preferences.load()
    // Load avatar
    try {
      const response = await get<{ avatar: string | null }>('/profile')
      setAvatar(response.data.avatar)
    } catch {
      // Not critical
    }
  }
})
</script>

<template>
  <RouterView />
</template>
