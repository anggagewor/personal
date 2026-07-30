export interface CalendarEvent {
  id: number
  title: string
  description: string | null
  start_at: string
  end_at: string | null
  all_day: boolean
  color: string | null
}

export interface CalendarEventPayload {
  title: string
  description: string | null
  start_at: string
  end_at: string | null
  all_day: boolean
  color: string | null
}

export interface HolidayItem {
  id: number
  date: string
  summary: string
  description: string | null
  is_national_holiday: boolean
}
