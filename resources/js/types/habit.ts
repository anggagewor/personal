export interface Habit {
  id: number
  name: string
  icon: string | null
  color: string | null
  frequency: string
  completed_today: boolean
  streak: number
}

export interface HabitToggleResult {
  completed: boolean
  streak: number
}

export interface HabitPayload {
  name: string
  color: string | null
}
