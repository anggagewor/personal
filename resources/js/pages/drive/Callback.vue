<script setup lang="ts">
import { ref, onMounted } from 'vue'

const status = ref<'sending' | 'success' | 'no-opener' | 'no-code'>('sending')
const code = ref('')

onMounted(() => {
  const params = new URLSearchParams(window.location.search)
  const authCode = params.get('code')

  if (!authCode) {
    status.value = 'no-code'
    return
  }

  code.value = authCode

  if (window.opener) {
    // Send code to parent window
    window.opener.postMessage({ type: 'google-oauth-callback', code: authCode }, '*')
    status.value = 'success'
    // Delay close slightly to ensure message is sent
    setTimeout(() => window.close(), 500)
  } else {
    // No opener — user might have navigated directly
    status.value = 'no-opener'
  }
})
</script>

<template>
  <div class="flex min-h-screen items-center justify-center bg-gray-50 dark:bg-gray-900">
    <div class="max-w-sm text-center px-6">
      <template v-if="status === 'sending' || status === 'success'">
        <div class="mx-auto h-10 w-10 animate-spin rounded-full border-4 border-primary-200 border-t-primary-600"></div>
        <p class="mt-4 text-sm text-gray-600 dark:text-gray-400">Menghubungkan ke Google Drive...</p>
        <p class="mt-1 text-xs text-gray-400">Jendela ini akan tertutup otomatis.</p>
      </template>

      <template v-else-if="status === 'no-opener'">
        <p class="text-sm text-gray-600 dark:text-gray-400">Koneksi berhasil. Kamu bisa menutup halaman ini dan kembali ke dashboard.</p>
      </template>

      <template v-else-if="status === 'no-code'">
        <p class="text-sm text-red-600">Gagal mendapatkan kode otorisasi dari Google.</p>
        <p class="mt-2 text-xs text-gray-400">Tutup halaman ini dan coba lagi.</p>
      </template>
    </div>
  </div>
</template>
