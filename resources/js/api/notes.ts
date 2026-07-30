import { get, post, put, del } from '@purdia/http'
import type { Note, NotePayload } from '@/types/note'

export function fetchNotes(params?: { search?: string }) {
  return get<Note[]>('/notes', { params })
}

export function createNote(payload: NotePayload) {
  return post<Note>('/notes', payload)
}

export function updateNote(id: number, payload: NotePayload) {
  return put<Note>(`/notes/${id}`, payload)
}

export function deleteNote(id: number) {
  return del(`/notes/${id}`)
}

export function toggleNotePin(id: number) {
  return post<Note>(`/notes/${id}/toggle-pin`)
}
