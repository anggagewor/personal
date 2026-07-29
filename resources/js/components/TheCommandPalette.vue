<script setup lang="ts">
import { ref, computed, onMounted, onBeforeUnmount, watch } from 'vue'
import { useRouter } from 'vue-router'
import { get } from '@purdia/http'
import { debounce } from '@purdia/utils'
import { Search, Loader2 } from '@lucide/vue'
import { resolveIcon } from '@/utils/icons'
import { useCommandPalette } from '@/composables/useCommandPalette'

interface CommandItem {
  id: string
  label: string
  icon: string
  to: string
  group: string
  subtitle?: string
}

const router = useRouter()
const { isOpen, close } = useCommandPalette()
const query = ref('')
const selectedIndex = ref(0)
const searchResults = ref<CommandItem[]>([])
const searching = ref(false)

const commands: CommandItem[] = [
  { id: 'dashboard', label: 'Dashboard', icon: 'LayoutDashboard', to: '/', group: 'Navigasi' },
  { id: 'notes', label: 'Catatan', icon: 'FileText', to: '/notes', group: 'Navigasi' },
  { id: 'tasks', label: 'Tugas', icon: 'ListTodo', to: '/tasks', group: 'Navigasi' },
  { id: 'tasks-kanban', label: 'Kanban Board', icon: 'Columns3', to: '/tasks/kanban', group: 'Navigasi' },
  { id: 'bookmarks', label: 'Bookmark', icon: 'Bookmark', to: '/bookmarks', group: 'Navigasi' },
  { id: 'calendar', label: 'Kalender', icon: 'Calendar', to: '/calendar', group: 'Navigasi' },
  { id: 'pomodoro', label: 'Pomodoro', icon: 'Clock', to: '/pomodoro', group: 'Navigasi' },
  { id: 'habits', label: 'Habits', icon: 'CheckCircle', to: '/habits', group: 'Navigasi' },
  { id: 'finance', label: 'Keuangan', icon: 'Wallet', to: '/finance', group: 'Navigasi' },
  { id: 'budget', label: 'Budget', icon: 'PiggyBank', to: '/budget', group: 'Navigasi' },
  { id: 'vault', label: 'Password Vault', icon: 'Lock', to: '/vault', group: 'Navigasi' },
  { id: 'drive', label: 'Google Drive', icon: 'HardDrive', to: '/drive', group: 'Navigasi' },
  { id: 'reading-list', label: 'Reading List', icon: 'Library', to: '/reading-list', group: 'Navigasi' },
  { id: 'journal', label: 'Jurnal', icon: 'BookOpen', to: '/journal', group: 'Navigasi' },
  { id: 'goals', label: 'Goals', icon: 'Target', to: '/goals', group: 'Navigasi' },
  { id: 'scratchpads', label: 'Scratchpad', icon: 'StickyNote', to: '/scratchpads', group: 'Navigasi' },
  { id: 'wishlist', label: 'Wishlist', icon: 'Heart', to: '/wishlist', group: 'Navigasi' },
  { id: 'streaks', label: 'Streaks', icon: 'Flame', to: '/streaks', group: 'Navigasi' },
  { id: 'activity', label: 'Aktivitas', icon: 'Activity', to: '/activity', group: 'Navigasi' },
  { id: 'settings-general', label: 'Pengaturan Umum', icon: 'SlidersHorizontal', to: '/settings/general', group: 'Pengaturan' },
  { id: 'settings-appearance', label: 'Tampilan', icon: 'Palette', to: '/settings/appearance', group: 'Pengaturan' },
  { id: 'settings-account', label: 'Akun', icon: 'UserCircle', to: '/settings/account', group: 'Pengaturan' },
  { id: 'settings-export', label: 'Export & Backup', icon: 'Download', to: '/settings/export', group: 'Pengaturan' },
]

// Dynamic search across notes, tasks, bookmarks
const debouncedSearch = debounce(async (q: string) => {
  if (!q || q.length < 2) {
    searchResults.value = []
    searching.value = false
    return
  }

  searching.value = true
  try {
    const [notesRes, tasksRes, bookmarksRes] = await Promise.allSettled([
      get<Array<{ id: number; title: string }>>('/notes', { params: { search: q, per_page: 5 } }),
      get<Array<{ id: number; title: string }>>('/tasks', { params: { search: q, per_page: 5 } }),
      get<Array<{ id: number; title: string; url: string }>>('/bookmarks', { params: { search: q, per_page: 5 } }),
    ])

    const results: CommandItem[] = []

    if (notesRes.status === 'fulfilled' && notesRes.value.data) {
      const notes = Array.isArray(notesRes.value.data) ? notesRes.value.data : []
      for (const note of notes.slice(0, 5)) {
        results.push({ id: `note-${note.id}`, label: note.title, icon: 'FileText', to: '/notes', group: 'Catatan', subtitle: 'Note' })
      }
    }

    if (tasksRes.status === 'fulfilled' && tasksRes.value.data) {
      const tasks = Array.isArray(tasksRes.value.data) ? tasksRes.value.data : []
      for (const task of tasks.slice(0, 5)) {
        results.push({ id: `task-${task.id}`, label: task.title, icon: 'ListTodo', to: '/tasks', group: 'Tugas', subtitle: 'Task' })
      }
    }

    if (bookmarksRes.status === 'fulfilled' && bookmarksRes.value.data) {
      const bookmarks = Array.isArray(bookmarksRes.value.data) ? bookmarksRes.value.data : []
      for (const bm of bookmarks.slice(0, 5)) {
        results.push({ id: `bm-${bm.id}`, label: bm.title, icon: 'Bookmark', to: '/bookmarks', group: 'Bookmark', subtitle: bm.url })
      }
    }

    searchResults.value = results
  } catch {
    searchResults.value = []
  } finally {
    searching.value = false
  }
}, 300)

const filtered = computed(() => {
  const q = query.value.toLowerCase()
  const navResults = q ? commands.filter((c) => c.label.toLowerCase().includes(q)) : commands
  return [...navResults, ...searchResults.value]
})

const groupedFiltered = computed(() => {
  const groups: Record<string, CommandItem[]> = {}
  for (const item of filtered.value) {
    if (!groups[item.group]) groups[item.group] = []
    groups[item.group].push(item)
  }
  return groups
})

watch(query, (val) => {
  selectedIndex.value = 0
  debouncedSearch(val)
})

watch(isOpen, (val) => {
  if (val) {
    query.value = ''
    selectedIndex.value = 0
    searchResults.value = []
  }
})

function execute(item: CommandItem) {
  router.push(item.to)
  close()
}

function onKeydown(e: KeyboardEvent) {
  if ((e.metaKey || e.ctrlKey) && e.key === 'k') {
    e.preventDefault()
    isOpen.value = !isOpen.value
    return
  }

  if (!isOpen.value) return

  if (e.key === 'Escape') { close(); return }
  if (e.key === 'ArrowDown') {
    e.preventDefault()
    selectedIndex.value = Math.min(selectedIndex.value + 1, filtered.value.length - 1)
  }
  if (e.key === 'ArrowUp') {
    e.preventDefault()
    selectedIndex.value = Math.max(selectedIndex.value - 1, 0)
  }
  if (e.key === 'Enter') {
    e.preventDefault()
    const item = filtered.value[selectedIndex.value]
    if (item) execute(item)
  }
}

onMounted(() => document.addEventListener('keydown', onKeydown))
onBeforeUnmount(() => document.removeEventListener('keydown', onKeydown))
</script>

<template>
  <Teleport to="body">
    <Transition name="palette">
      <div
        v-if="isOpen"
        class="fixed inset-0 z-[300] flex items-start justify-center bg-black/50 backdrop-blur-sm pt-[15vh]"
        @click.self="close"
      >
        <div class="w-full max-w-lg rounded-xl border border-gray-200 bg-white shadow-2xl dark:border-gray-700 dark:bg-gray-800">
          <!-- Search input -->
          <div class="flex items-center gap-3 border-b border-gray-200 px-4 dark:border-gray-700">
            <Search v-if="!searching" :size="18" class="text-gray-400" />
            <Loader2 v-else :size="18" class="animate-spin text-primary-500" />
            <input
              v-model="query"
              type="text"
              placeholder="Cari halaman, catatan, tugas, bookmark..."
              class="flex-1 border-0 bg-transparent py-3.5 text-sm text-gray-900 placeholder-gray-400 focus:outline-none dark:text-white"
              autofocus
            />
            <kbd class="hidden rounded border border-gray-300 px-1.5 py-0.5 text-xs text-gray-400 sm:inline dark:border-gray-600">Esc</kbd>
          </div>

          <!-- Results -->
          <div class="max-h-[20rem] overflow-y-auto p-2">
            <template v-if="filtered.length">
              <div v-for="(items, group) in groupedFiltered" :key="group" class="mb-2">
                <p class="px-2 py-1 text-xs font-medium text-gray-400 dark:text-gray-500">{{ group }}</p>
                <button
                  v-for="item in items"
                  :key="item.id"
                  class="flex w-full items-center gap-3 rounded-lg px-3 py-2 text-sm text-left transition-colors"
                  :class="filtered.indexOf(item) === selectedIndex
                    ? 'bg-primary-50 text-primary-700 dark:bg-primary-900/30 dark:text-primary-300'
                    : 'text-gray-700 hover:bg-gray-100 dark:text-gray-300 dark:hover:bg-gray-700'"
                  @click="execute(item)"
                  @mouseenter="selectedIndex = filtered.indexOf(item)"
                >
                  <component :is="resolveIcon(item.icon)" :size="16" class="shrink-0" />
                  <div class="min-w-0 flex-1">
                    <span class="block truncate">{{ item.label }}</span>
                    <span v-if="item.subtitle" class="block truncate text-xs text-gray-400">{{ item.subtitle }}</span>
                  </div>
                </button>
              </div>
            </template>
            <p v-else class="px-3 py-4 text-center text-sm text-gray-400">Tidak ditemukan.</p>
          </div>

          <!-- Footer -->
          <div class="flex items-center gap-4 border-t border-gray-200 px-4 py-2 text-xs text-gray-400 dark:border-gray-700">
            <span>↑↓ navigasi</span>
            <span>↵ pilih</span>
            <span>esc tutup</span>
          </div>
        </div>
      </div>
    </Transition>
  </Teleport>
</template>

<style scoped>
.palette-enter-active, .palette-leave-active { transition: opacity 150ms ease; }
.palette-enter-from, .palette-leave-to { opacity: 0; }
</style>
