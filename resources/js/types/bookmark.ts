export interface BookmarkItem {
  id: number
  title: string
  url: string
  description: string | null
  category: string | null
  created_at: string
}

export interface BookmarkPayload {
  title: string
  url: string
  description: string | null
  category: string | null
}
