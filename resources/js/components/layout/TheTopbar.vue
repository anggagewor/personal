<script setup lang="ts">
import { computed, onBeforeUnmount, onMounted } from 'vue'
import { useAuthStore } from '@purdia/auth'
import { useThemeStore } from '@purdia/theme'
import { useRouter } from 'vue-router'
import { usePreferences } from '@/composables/usePreferences'
import { useSidebar } from '@/composables/useSidebar'
import { useProfile } from '@/composables/useProfile'
import { Sun, Moon, Monitor, LogOut, User, PanelLeftClose, PanelLeftOpen } from '@lucide/vue'
import { ref } from 'vue'

const auth = useAuthStore()
const theme = useThemeStore()
const router = useRouter()
const preferences = usePreferences()
const { collapsed, toggle } = useSidebar()
const { avatarUrl } = useProfile()

const showUserMenu = ref(false)
const userMenuRef = ref<HTMLElement | null>(null)

const themeIcon = {
  light: Sun,
  dark: Moon,
  system: Monitor,
}

const initials = computed(() => {
  const n = auth.user?.name ?? ''
  return n.split(' ').map((w: string) => w[0]).slice(0, 2).join('').toUpperCase()
})

function handleToggleSidebar() {
  toggle()
  preferences.save({ sidebar_collapsed: collapsed.value })
}

function cycleTheme() {
  theme.toggle()
  preferences.save({ theme: theme.theme })
}

async function logout() {
  showUserMenu.value = false
  preferences.clear()
  await auth.logout()
  router.push({ name: 'login' })
}

function onClickOutside(e: MouseEvent) {
  if (userMenuRef.value && !userMenuRef.value.contains(e.target as Node)) {
    showUserMenu.value = false
  }
}

onMounted(() => {
  document.addEventListener('click', onClickOutside)
})

onBeforeUnmount(() => {
  document.removeEventListener('click', onClickOutside)
})
</script>

<template>
  <header class="flex h-14 items-center justify-between border-b border-gray-200 bg-white px-6 dark:border-gray-700 dark:bg-gray-800">
    <!-- Left: sidebar toggle -->
    <div class="flex items-center">
      <button
        class="rounded-lg p-2 text-gray-500 hover:bg-gray-100 hover:text-gray-700 dark:text-gray-400 dark:hover:bg-gray-700 dark:hover:text-gray-200"
        @click="handleToggleSidebar"
        :title="collapsed ? 'Expand sidebar' : 'Collapse sidebar'"
      >
        <PanelLeftOpen v-if="collapsed" :size="20" />
        <PanelLeftClose v-else :size="20" />
      </button>
    </div>

    <!-- Right: actions -->
    <div class="flex items-center gap-2">
      <!-- Theme toggle -->
      <button
        class="rounded-lg p-2 text-gray-500 hover:bg-gray-100 hover:text-gray-700 dark:text-gray-400 dark:hover:bg-gray-700 dark:hover:text-gray-200"
        @click="cycleTheme()"
        :title="`Theme: ${theme.theme}`"
      >
        <component :is="themeIcon[theme.theme]" :size="18" />
      </button>

      <!-- User menu -->
      <div ref="userMenuRef" class="relative">
        <button
          class="flex items-center gap-2 rounded-lg px-3 py-1.5 text-sm text-gray-700 hover:bg-gray-100 dark:text-gray-300 dark:hover:bg-gray-700 cursor-pointer"
          @click="showUserMenu = !showUserMenu"
        >
          <div class="flex h-7 w-7 items-center justify-center overflow-hidden rounded-full bg-primary-100 text-primary-700 dark:bg-primary-900/40 dark:text-primary-300">
            <img v-if="avatarUrl" :src="avatarUrl" alt="Avatar" class="h-full w-full object-cover" />
            <span v-else-if="initials" class="text-xs font-semibold">{{ initials }}</span>
            <User v-else :size="14" />
          </div>
          <span class="hidden sm:inline">{{ auth.user?.name ?? 'User' }}</span>
        </button>

        <Transition name="dropdown">
          <div
            v-if="showUserMenu"
            class="absolute right-0 top-full z-50 mt-2 w-48 rounded-lg border border-gray-200 bg-white py-1 shadow-lg dark:border-gray-700 dark:bg-gray-800"
          >
            <button
              class="flex w-full items-center gap-2 px-4 py-2 text-sm text-gray-700 hover:bg-gray-100 dark:text-gray-300 dark:hover:bg-gray-700 cursor-pointer"
              @click="logout"
            >
              <LogOut :size="16" />
              Keluar
            </button>
          </div>
        </Transition>
      </div>
    </div>
  </header>
</template>

<style scoped>
.dropdown-enter-active,
.dropdown-leave-active {
  transition: opacity 150ms ease, transform 150ms ease;
}
.dropdown-enter-from,
.dropdown-leave-to {
  opacity: 0;
  transform: translateY(-4px);
}
</style>
