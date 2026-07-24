<script setup lang="ts">
import { ref, computed, onMounted, onBeforeUnmount, watch } from 'vue'
import { useRouter } from 'vue-router'
import { Search } from '@lucide/vue'
import { resolveIcon } from '@/utils/icons'

interface CommandItem {
  id: string
  label: string
  icon: string
  to?: string
  action?: () => void
  group: string
}

const router = useRouter()
const visible = ref(false)
const query = ref('')
const selectedIndex = ref(0)

const commands: CommandItem[] = [
  { id: 'dashboard', label: 'Dashboard', icon: 'LayoutDashboard', to: '/', group: 'Navigasi' },
  { id: 'notes', label: 'Catatan', icon: 'FileText', to: '/notes', group: 'Navigasi' },
  { id: 'tasks', label: 'Tugas', icon: 'ListTodo', to: '/tasks', group: 'Navigasi' },
  { id: 'bookmarks', label: 'Bookmark', icon: 'Bookmark', to: '/bookmarks', group: 'Navigasi' },
  { id: 'calendar', label: 'Kalender', icon: 'Calendar', to: '/calendar', group: 'Navigasi' },
  { id: 'activity', label: 'Aktivitas', icon: 'Activity', to: '/activity', group: 'Navigasi' },
  { id: 'settings-general', label: 'Pengaturan Umum', icon: 'SlidersHorizontal', to: '/settings/general', group: 'Pengaturan' },
  { id: 'settings-appearance', label: 'Tampilan', icon: 'Palette', to: '/settings/appearance', group: 'Pengaturan' },
  { id: 'settings-account', label: 'Akun', icon: 'UserCircle', to: '/settings/account', group: 'Pengaturan' },
]

const filtered = computed(() => {
  if (!query.value) return commands
  const q = query.value.toLowerCase()
  return commands.filter((c) => c.label.toLowerCase().includes(q))
})

const groupedFiltered = computed(() => {
  const groups: Record<string, CommandItem[]> = {}
  for (const item of filtered.value) {
    if (!groups[item.group]) groups[item.group] = []
    groups[item.group].push(item)
  }
  return groups
})

watch(query, () => { selectedIndex.value = 0 })

function open() {
  visible.value = true
  query.value = ''
  selectedIndex.value = 0
}

function close() {
  visible.value = false
}

function execute(item: CommandItem) {
  if (item.to) router.push(item.to)
  if (item.action) item.action()
  close()
}

function onKeydown(e: KeyboardEvent) {
  if ((e.metaKey || e.ctrlKey) && e.key === 'k') {
    e.preventDefault()
    visible.value ? close() : open()
  }

  if (!visible.value) return

  if (e.key === 'Escape') { close(); return }
  if (e.key === 'ArrowDown') { e.preventDefault(); selectedIndex.value = Math.min(selectedIndex.value + 1, filtered.value.length - 1) }
  if (e.key === 'ArrowUp') { e.preventDefault(); selectedIndex.value = Math.max(selectedIndex.value - 1, 0) }
  if (e.key === 'Enter') {
    e.preventDefault()
    const item = filtered.value[selectedIndex.value]
    if (item) execute(item)
  }
}

onMounted(() => document.addEventListener('keydown', onKeydown))
onBeforeUnmount(() => document.removeEventListener('keydown', onKeydown))

defineExpose({ open })
</script>

<template>
  <Teleport to="body">
    <Transition name="palette">
      <div v-if="visible" class="fixed inset-0 z-[300] flex items-start justify-center bg-black/50 pt-[15vh]" @click.self="close">
        <div class="w-full max-w-lg rounded-xl border border-gray-200 bg-white shadow-2xl dark:border-gray-700 dark:bg-gray-800">
          <!-- Search input -->
          <div class="flex items-center gap-3 border-b border-gray-200 px-4 dark:border-gray-700">
            <Search :size="18" class="text-gray-400" />
            <input
              v-model="query"
              type="text"
              placeholder="Cari halaman atau perintah..."
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
                  v-for="(item, idx) in items"
                  :key="item.id"
                  class="flex w-full items-center gap-3 rounded-lg px-3 py-2 text-sm text-left transition-colors"
                  :class="filtered.indexOf(item) === selectedIndex ? 'bg-primary-50 text-primary-700 dark:bg-primary-900/30 dark:text-primary-300' : 'text-gray-700 hover:bg-gray-100 dark:text-gray-300 dark:hover:bg-gray-700'"
                  @click="execute(item)"
                  @mouseenter="selectedIndex = filtered.indexOf(item)"
                >
                  <component :is="resolveIcon(item.icon)" :size="16" class="shrink-0" />
                  <span>{{ item.label }}</span>
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
