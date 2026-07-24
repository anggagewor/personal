import { ref } from 'vue'
import { useRouter } from 'vue-router'
import { navigation } from '@/config/navigation'

// Read initial collapsed state from localStorage cache
function getInitialCollapsed(): boolean {
  try {
    const raw = localStorage.getItem('sidebar_collapsed')
    if (raw !== null) return raw === 'true'

    // Also check preferences cache for any user
    for (let i = 0; i < localStorage.length; i++) {
      const key = localStorage.key(i)
      if (key?.startsWith('preferences:')) {
        const prefs = JSON.parse(localStorage.getItem(key) ?? '{}')
        if (prefs.sidebar_collapsed !== undefined) return prefs.sidebar_collapsed
      }
    }
  } catch {
    // ignore
  }
  return false
}

const collapsed = ref(getInitialCollapsed())
const openMenus = ref<Set<string>>(new Set())

export function useSidebar() {
  function toggle() {
    collapsed.value = !collapsed.value
    localStorage.setItem('sidebar_collapsed', String(collapsed.value))

    if (collapsed.value) {
      openMenus.value.clear()
    } else {
      autoOpenActiveMenu()
    }
  }

  function collapse() {
    collapsed.value = true
    localStorage.setItem('sidebar_collapsed', 'true')
    openMenus.value.clear()
  }

  function expand() {
    collapsed.value = false
    localStorage.setItem('sidebar_collapsed', 'false')
    autoOpenActiveMenu()
  }

  function toggleMenu(id: string) {
    if (openMenus.value.has(id)) {
      openMenus.value.delete(id)
    } else {
      openMenus.value.add(id)
    }
  }

  function isMenuOpen(id: string): boolean {
    return openMenus.value.has(id)
  }

  /**
   * Auto-open accordion for the menu that contains the current route path.
   */
  function autoOpenActiveMenu(path?: string) {
    const currentPath = path ?? window.location.pathname
    for (const group of navigation) {
      for (const item of group.items) {
        if (item.children?.some((child) => currentPath === child.to)) {
          openMenus.value.add(item.id)
        }
      }
    }
  }

  /**
   * Call on layout mount and route changes to keep accordion in sync.
   */
  function syncWithRoute(path?: string) {
    if (!collapsed.value) {
      autoOpenActiveMenu(path)
    }
  }

  return {
    collapsed,
    openMenus,
    toggle,
    collapse,
    expand,
    toggleMenu,
    isMenuOpen,
    syncWithRoute,
  }
}
