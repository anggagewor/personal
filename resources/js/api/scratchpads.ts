import { get, post, put, del } from '@purdia/http'
import type { Scratchpad } from '@/types/scratchpad'

export function fetchScratchpads() {
  return get<Scratchpad[]>('/scratchpads')
}

export function createScratchpad(payload: { content: string }) {
  return post<Scratchpad>('/scratchpads', payload)
}

export function updateScratchpad(id: number, payload: { content: string }) {
  return put<Scratchpad>(`/scratchpads/${id}`, payload)
}

export function deleteScratchpad(id: number) {
  return del(`/scratchpads/${id}`)
}
