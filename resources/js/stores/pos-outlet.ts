/**
 * POS Outlet Store
 *
 * Centralized store for managing the active outlet across all POS & Supplier pages.
 * Persists the selected outlet ID in localStorage and syncs to user preferences.
 */

import { ref, computed } from 'vue'
import { defineStore } from 'pinia'
import type { Outlet } from '@/types/pos'
import * as posApi from '@/api/pos'

const STORAGE_KEY = 'pos_active_outlet'

export const usePosOutletStore = defineStore('pos-outlet', () => {
  // ---------------------------------------------------------------------------
  // State
  // ---------------------------------------------------------------------------

  const outlets = ref<Outlet[]>([])
  const activeOutletId = ref<number>(0)
  const loaded = ref(false)
  const loading = ref(false)

  // ---------------------------------------------------------------------------
  // Getters
  // ---------------------------------------------------------------------------

  const activeOutlet = computed(() =>
    outlets.value.find((o) => o.id === activeOutletId.value) ?? null,
  )

  const hasOutlets = computed(() => outlets.value.length > 0)

  // ---------------------------------------------------------------------------
  // Actions
  // ---------------------------------------------------------------------------

  /**
   * Initialize the store: fetch outlets and resolve which one is active.
   * Safe to call multiple times — will only fetch once unless forced.
   */
  async function init(force = false): Promise<void> {
    if (loaded.value && !force) return
    loading.value = true
    try {
      const res = await posApi.fetchOutlets()
      outlets.value = res.data

      // Resolve active outlet: persisted > first available
      const persisted = getPersistedId()
      if (persisted && outlets.value.some((o) => o.id === persisted)) {
        activeOutletId.value = persisted
      } else if (outlets.value.length > 0) {
        activeOutletId.value = outlets.value[0].id
        persistId(activeOutletId.value)
      }

      loaded.value = true
    } catch {
      // Error handled by @purdia/http
    } finally {
      loading.value = false
    }
  }

  /**
   * Switch to a different outlet.
   */
  function setOutlet(id: number): void {
    if (!outlets.value.some((o) => o.id === id)) return
    activeOutletId.value = id
    persistId(id)
  }

  /**
   * Refresh outlets list from server (e.g. after creating/deleting outlet).
   */
  async function refresh(): Promise<void> {
    await init(true)
  }

  // ---------------------------------------------------------------------------
  // Persistence helpers
  // ---------------------------------------------------------------------------

  function getPersistedId(): number | null {
    try {
      const raw = localStorage.getItem(STORAGE_KEY)
      return raw ? Number(raw) : null
    } catch {
      return null
    }
  }

  function persistId(id: number): void {
    localStorage.setItem(STORAGE_KEY, String(id))
  }

  return {
    // State
    outlets,
    activeOutletId,
    loaded,
    loading,
    // Getters
    activeOutlet,
    hasOutlets,
    // Actions
    init,
    setOutlet,
    refresh,
  }
})
