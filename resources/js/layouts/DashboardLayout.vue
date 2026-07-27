<script setup lang="ts">
import { watch } from 'vue'
import { RouterView, useRoute } from 'vue-router'
import TheSidebar from '@/components/layout/TheSidebar.vue'
import TheTopbar from '@/components/layout/TheTopbar.vue'
import TheCommandPalette from '@/components/TheCommandPalette.vue'
import { useSidebar } from '@/composables/useSidebar'

const { collapsed, syncWithRoute } = useSidebar()
const route = useRoute()

// Auto-open accordion for active route on load and navigation
syncWithRoute(route.path)
watch(() => route.path, (path) => syncWithRoute(path))
</script>

<template>
  <div class="flex min-h-screen">
    <!-- Sidebar — not fixed, part of the flex flow -->
    <TheSidebar />

    <!-- Main area -->
    <div class="flex flex-1 flex-col min-w-0">
      <!-- Topbar — not fixed, scrolls with content -->
      <TheTopbar />

      <!-- Page content -->
      <main class="flex-1 p-6">
        <RouterView />
      </main>

      <!-- Footer -->
      <footer class="px-6 py-4 text-center">
        <p class="text-xs text-gray-400 dark:text-gray-600">Powered by Purdia</p>
      </footer>
    </div>

    <!-- Command Palette (Cmd+K) -->
    <TheCommandPalette />
  </div>
</template>
