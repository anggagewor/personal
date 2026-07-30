import { get, post, del } from '@purdia/http'
import type { TrashedItem } from '@/types/trash'

export function fetchTrashedItems() {
  return get('/trash')
}

export function restoreItem(type: string, id: number) {
  return post(`/trash/${type}/${id}/restore`)
}

export function permanentDelete(type: string, id: number) {
  return del(`/trash/${type}/${id}`)
}
