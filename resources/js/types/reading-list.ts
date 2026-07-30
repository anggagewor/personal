export interface ReadingItem {
  id: number
  title: string
  url: string
  description: string | null
  domain: string | null
  is_read: boolean
  is_favorite: boolean
  created_at: string
}

export interface ReadingItemPayload {
  url: string
  title: string
  description: string
}
