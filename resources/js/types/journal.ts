export interface JournalEntry {
  id: number
  date: string
  content: string
  mood: string | null
}

export interface JournalPayload {
  date: string
  content: string
  mood: string | null
}
