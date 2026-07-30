import { ref } from 'vue'

// ---------------------------------------------------------------------------
// Helpers
// ---------------------------------------------------------------------------

function getFirstDayOfMonth(): string {
  const now = new Date()
  return `${now.getFullYear()}-${String(now.getMonth() + 1).padStart(2, '0')}-01`
}

function getLastDayOfMonth(): string {
  const now = new Date()
  const lastDay = new Date(now.getFullYear(), now.getMonth() + 1, 0)
  return `${lastDay.getFullYear()}-${String(lastDay.getMonth() + 1).padStart(2, '0')}-${String(lastDay.getDate()).padStart(2, '0')}`
}

// ---------------------------------------------------------------------------
// Module-level state — persists across navigation but resets on page reload
// ---------------------------------------------------------------------------

const startDate = ref(getFirstDayOfMonth())
const endDate = ref(getLastDayOfMonth())

// ---------------------------------------------------------------------------
// Composable
// ---------------------------------------------------------------------------

export function useFiscalPeriod() {
  function setRange(start: string, end: string) {
    startDate.value = start
    endDate.value = end
  }

  function reset() {
    startDate.value = getFirstDayOfMonth()
    endDate.value = getLastDayOfMonth()
  }

  return {
    startDate,
    endDate,
    setRange,
    reset,
  }
}
