import { get, post, del } from '@purdia/http'
import type { ReadingItem, ReadingItemPayload } from '@/types/reading-list'

export function fetchReadingList(params?: { unread?: string; favorite?: string }) {
  return get<ReadingItem[]>('/reading-list', { params })
}

export function createReadingItem(payload: ReadingItemPayload) {
  return post<ReadingItem>('/reading-list', payload)
}

export function toggleRead(id: number) {
  return post(`/reading-list/${id}/toggle-read`)
}

export function toggleFavorite(id: number) {
  return post(`/reading-list/${id}/toggle-favorite`)
}

export function deleteReadingItem(id: number) {
  return del(`/reading-list/${id}`)
}
