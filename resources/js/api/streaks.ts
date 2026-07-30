import { get } from '@purdia/http'

export function fetchHabitsData() {
  return get('/habits')
}

export function fetchPomodorosStats() {
  return get('/pomodoros/stats')
}
