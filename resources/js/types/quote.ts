export interface QuoteItem {
  id: number
  content: string
  author: string | null
}

export interface QuoteMeta {
  current_page: number
  last_page: number
  per_page: number
  total: number
}

export interface QuotePayload {
  content: string
  author: string | null
}
