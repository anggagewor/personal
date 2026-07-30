import { get, post, del } from '@purdia/http'
import type { QuoteItem, QuotePayload } from '@/types/quote'

export function fetchTodayQuote() {
  return get<QuoteItem | null>('/quotes/today')
}

export function fetchQuotes(params: { page?: number; per_page?: number; search?: string }) {
  return get<QuoteItem[]>('/quotes', { params })
}

export function createQuote(payload: QuotePayload) {
  return post<QuoteItem>('/quotes', payload)
}

export function deleteQuote(id: number) {
  return del(`/quotes/${id}`)
}
