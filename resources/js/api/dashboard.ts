import { get, post } from '@purdia/http'

export function fetchNotes(params?: { per_page?: number }) {
  return get<unknown[]>('/notes', { params })
}

export function fetchTasks(params?: { per_page?: number; status?: string }) {
  return get<unknown[]>('/tasks', { params })
}

export function fetchBookmarks() {
  return get<Record<string, unknown[]>>('/bookmarks')
}

export function fetchCalendarEvents(params?: { start?: string; end?: string }) {
  return get('/calendar-events', { params })
}

export function fetchTodayQuote() {
  return get('/quotes/today')
}

export function fetchWeather() {
  return get('/weather')
}

export function fetchWeeklySummary() {
  return get('/dashboard/weekly-summary')
}

export function fetchPreferences() {
  return get('/preferences')
}

export function createQuickScratchpad(payload: { content: string }) {
  return post('/scratchpads', payload)
}
