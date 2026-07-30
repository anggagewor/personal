import { get, post, del } from '@purdia/http'
import type { Habit, HabitToggleResult, HabitPayload } from '@/types/habit'

export function fetchHabits() {
  return get<Habit[]>('/habits')
}

export function createHabit(payload: HabitPayload) {
  return post<Habit>('/habits', payload)
}

export function toggleHabit(id: number) {
  return post<HabitToggleResult>(`/habits/${id}/toggle`)
}

export function deleteHabit(id: number) {
  return del(`/habits/${id}`)
}
