/**
 * Composable for POS/Supplier pages that need the active outlet ID.
 *
 * Returns a computed outletId that reactively reads from the centralized store.
 * Pages just call `const { outletId } = usePosOutlet()` and they're good.
 */

import { computed } from 'vue'
import { usePosOutletStore } from '@/stores/pos-outlet'

export function usePosOutlet() {
  const store = usePosOutletStore()

  const outletId = computed(() => store.activeOutletId)
  const outlet = computed(() => store.activeOutlet)
  const ready = computed(() => store.loaded && store.activeOutletId > 0)

  return {
    outletId,
    outlet,
    ready,
    store,
  }
}
