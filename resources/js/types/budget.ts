export interface BudgetItem {
  id: number
  category: string
  amount: number
  spent: number
  remaining: number
  percent_used: number
  is_exceeded: boolean
  is_near_limit: boolean
}

export interface BudgetSummary {
  budgets: BudgetItem[]
  total_budget: number
  total_spent: number
  total_remaining: number
}

export interface BudgetPayload {
  category: string
  amount: number
  month: string
}
