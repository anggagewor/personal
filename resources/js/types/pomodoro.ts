export interface PomodoroSession {
  id: number
  task_id: number | null
  duration: number
  status: string
  started_at: string
  finished_at: string | null
}

export interface PomodoroStats {
  today: number
  week: number
  total_minutes_week: number
}
