import { get, post, del } from '@purdia/http'
import type { BudgetSummary, BudgetPayload } from '@/types/budget'

export function fetchBudgetSummary(params: { month: string }) {
  return get<BudgetSummary>('/budgets/summary', { params })
}

export function createBudget(payload: BudgetPayload) {
  return post('/budgets', payload)
}

export function deleteBudget(id: number) {
  return del(`/budgets/${id}`)
}
