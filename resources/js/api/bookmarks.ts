import { get, post, put, del } from '@purdia/http'
import type { BookmarkItem, BookmarkPayload } from '@/types/bookmark'

export function fetchBookmarks() {
  return get<Record<string, BookmarkItem[]>>('/bookmarks')
}

export function createBookmark(payload: BookmarkPayload) {
  return post<BookmarkItem>('/bookmarks', payload)
}

export function updateBookmark(id: number, payload: BookmarkPayload) {
  return put<BookmarkItem>(`/bookmarks/${id}`, payload)
}

export function deleteBookmark(id: number) {
  return del(`/bookmarks/${id}`)
}
