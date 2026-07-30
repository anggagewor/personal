import { get, upload } from '@purdia/http'
import type { WatchlistItem, PriceData, HistoryPoint, OhlcPoint } from '@/types/market'

export function fetchWatchlist() {
  return get<WatchlistItem[]>('/market/watchlist')
}

export function fetchPrices() {
  return get<Record<string, PriceData>>('/market/prices')
}

export function fetchHistory(symbol: string, params: { from: string; to: string }) {
  return get<HistoryPoint[]>(`/market/history/${symbol}`, { params })
}

export function fetchOhlc(symbol: string, params: { from: string; to: string; interval?: string }) {
  return get<OhlcPoint[]>(`/market/ohlc/${symbol}`, { params })
}

export function exportData(params: { format: 'csv' | 'json' }) {
  return get<Blob>('/market/export', { params, responseType: 'blob' } as any)
}

export function downloadTemplate() {
  return get<Blob>('/market/import/template', { responseType: 'blob' } as any)
}

export function importData(formData: FormData) {
  return upload<{ imported: number }>('/market/import', formData)
}
