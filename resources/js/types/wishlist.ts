export interface WishlistItem {
  id: number
  title: string
  description: string | null
  category: string | null
  is_completed: boolean
  completed_at: string | null
  created_at: string
}

export interface WishlistPayload {
  title: string
  description: string | null
  category: string | null
}
