export interface GoldHistory {
  date: string
  price: number
  change: number
  change_percent: number
}

export interface GoldDashboard {
  latest: { date: string; price: number; change: number; change_percent: number } | null
  sparkline: number[]
  stats: { high_30d: number; low_30d: number; change_30d: number; change_percent_30d: number } | null
}
