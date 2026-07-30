import { get, post, put, del } from '@purdia/http'
import type { CalendarEvent, CalendarEventPayload, HolidayItem } from '@/types/calendar'

export function fetchCalendarEvents(params: { start: string; end: string }) {
  return get<CalendarEvent[]>('/calendar-events', { params })
}

export function createCalendarEvent(payload: CalendarEventPayload) {
  return post<CalendarEvent>('/calendar-events', payload)
}

export function updateCalendarEvent(id: number, payload: CalendarEventPayload) {
  return put<CalendarEvent>(`/calendar-events/${id}`, payload)
}

export function deleteCalendarEvent(id: number) {
  return del(`/calendar-events/${id}`)
}

export function fetchHolidays(params: { start: string; end: string }) {
  return get<HolidayItem[]>('/holidays', { params })
}
