import { get, post } from '@purdia/http'
import type { PomodoroSession, PomodoroStats } from '@/types/pomodoro'

export function fetchStats() {
  return get<PomodoroStats>('/pomodoros/stats')
}

export function startSession(duration: number) {
  return post<PomodoroSession>('/pomodoros', { duration })
}

export function completeSession(id: number) {
  return post(`/pomodoros/${id}/complete`)
}

export function cancelSession(id: number) {
  return post(`/pomodoros/${id}/cancel`)
}
