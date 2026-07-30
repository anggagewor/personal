export interface FinanceItem {
  id: number
  type: string
  category: string
  amount: number
  description: string | null
  date: string
}

export interface FinanceSummary {
  income: number
  expense: number
  balance: number
  by_category: Array<{ category: string; total: number }>
}

export interface FinancePayload {
  type: string
  category: string
  amount: number
  description: string | null
  date: string
}
