import { get, post } from '@purdia/http'
import type { JournalEntry, JournalPayload } from '@/types/journal'

export function fetchJournalEntry(date: string) {
  return get<JournalEntry | null>(`/journals/${date}`)
}

export function saveJournalEntry(payload: JournalPayload) {
  return post<JournalEntry>('/journals', payload)
}
