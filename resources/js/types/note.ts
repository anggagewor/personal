export interface Note {
  id: number
  title: string
  content: string
  is_pinned: boolean
  created_at: string
  updated_at: string
}

export interface NotePayload {
  title: string
  content: string
}
