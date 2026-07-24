import { ref, watch } from 'vue'
import { get, put } from '@purdia/http'
import { useThemeStore } from '@purdia/theme'
import { useAuthStore } from '@purdia/auth'
import { useSidebar } from './useSidebar'
import type { Theme, PrimaryColor } from '@purdia/theme'

// ---------------------------------------------------------------------------
// Types
// ---------------------------------------------------------------------------

export interface UserPreferences {
  theme: Theme
  primary_color: PrimaryColor
  locale: string
  sidebar_collapsed: boolean
}

// ---------------------------------------------------------------------------
// Cache helpers
// ---------------------------------------------------------------------------

function getCacheKey(userId: number | string): string {
  return `preferences:${userId}`
}

function readCache(userId: number | string): UserPreferences | null {
  try {
    const raw = localStorage.getItem(getCacheKey(userId))
    return raw ? JSON.parse(raw) : null
  } catch {
    return null
  }
}

function writeCache(userId: number | string, prefs: UserPreferences): void {
  localStorage.setItem(getCacheKey(userId), JSON.stringify(prefs))
}

// ---------------------------------------------------------------------------
// Composable
// ---------------------------------------------------------------------------

const syncing = ref(false)

export function usePreferences() {
  const theme = useThemeStore()
  const auth = useAuthStore()
  const sidebar = useSidebar()

  /**
   * Apply preferences to the UI (theme store + sidebar state).
   */
  function apply(prefs: UserPreferences): void {
    theme.setTheme(prefs.theme)
    theme.setColor(prefs.primary_color)
    sidebar.collapsed.value = prefs.sidebar_collapsed

    if (prefs.locale) {
      localStorage.setItem('app_locale', prefs.locale)
    }
  }

  /**
   * Load preferences: read from cache first (instant), then fetch from API.
   * Call this after login or on app init when user is authenticated.
   */
  async function load(): Promise<void> {
    const userId = auth.user?.id
    if (!userId) return

    // 1. Apply cached prefs immediately (no flash)
    const cached = readCache(userId)
    if (cached) {
      apply(cached)
    }

    // 2. Fetch fresh from server and update cache
    try {
      const response = await get<UserPreferences>('/preferences')
      const fresh = response.data
      writeCache(userId, fresh)
      apply(fresh)
    } catch {
      // Offline or error — cached version is fine
    }
  }

  /**
   * Save a partial preference update. Optimistic: applies immediately,
   * then syncs to server in background.
   */
  async function save(partial: Partial<UserPreferences>): Promise<void> {
    const userId = auth.user?.id
    if (!userId) return

    // Optimistic: update cache and UI immediately
    const cached = readCache(userId)
    const merged = { ...(cached ?? getDefaults()), ...partial }
    writeCache(userId, merged)
    apply(merged)

    // Sync to server
    syncing.value = true
    try {
      const response = await put<UserPreferences>('/preferences', partial)
      // Server returns the full merged preferences — update cache with truth
      writeCache(userId, response.data)
    } catch {
      // Rollback not needed — server will catch up next load
    } finally {
      syncing.value = false
    }
  }

  /**
   * Clear cached preferences (call on logout).
   */
  function clear(): void {
    const userId = auth.user?.id
    if (userId) {
      localStorage.removeItem(getCacheKey(userId))
    }
  }

  return {
    syncing,
    load,
    save,
    clear,
    apply,
  }
}

function getDefaults(): UserPreferences {
  return {
    theme: 'system',
    primary_color: 'indigo',
    locale: 'id',
    sidebar_collapsed: false,
  }
}
