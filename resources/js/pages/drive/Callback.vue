<script setup lang="ts">
import { onMounted } from 'vue'

onMounted(() => {
  const params = new URLSearchParams(window.location.search)
  const code = params.get('code')

  if (code && window.opener) {
    window.opener.postMessage({ type: 'google-oauth-callback', code }, window.location.origin)
    window.close()
  }
})
</script>

<template>
  <div class="flex min-h-screen items-center justify-center">
    <div class="text-center">
      <p class="text-sm text-gray-500">Menghubungkan ke Google Drive...</p>
      <p class="mt-2 text-xs text-gray-400">Jendela ini akan tertutup otomatis.</p>
    </div>
  </div>
</template>
