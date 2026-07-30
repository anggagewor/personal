import { get, post, del } from '@purdia/http'
import type { FinanceItem, FinanceSummary, FinancePayload } from '@/types/finance'

export function fetchTransactions(params: { month: string; per_page?: number }) {
  return get<FinanceItem[]>('/finances', { params })
}

export function fetchSummary(params: { month: string }) {
  return get<FinanceSummary>('/finances/summary', { params })
}

export function createTransaction(payload: FinancePayload) {
  return post<FinanceItem>('/finances', payload)
}

export function deleteTransaction(id: number) {
  return del(`/finances/${id}`)
}
