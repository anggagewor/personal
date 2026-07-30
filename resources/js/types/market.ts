export interface WatchlistItem {
  id: number
  symbol: string
  type: string
  label: string | null
  position: number
}

export interface PriceData {
  symbol: string
  price: number
  change: number
  change_percent: number
  previous_close: number | null
}

export interface HistoryPoint {
  price: number
  change: number
  change_percent: number
  fetched_at: string
}

export interface OhlcPoint {
  time: string
  open: number
  high: number
  low: number
  close: number
}
