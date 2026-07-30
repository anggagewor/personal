import { get, post, put, del } from '@purdia/http'
import type {
  GroupedAccounts,
  JournalEntry,
  JournalEntryPayload,
  LedgerResponse,
  BalanceSheetData,
  IncomeStatementData,
  TrialBalanceData,
} from '@/types/accounting'

// --- Accounts ---

export function fetchAccounts() {
  return get<GroupedAccounts>('/accounting/accounts')
}

export function createAccount(payload: { code: string; name: string; type: string; parent_id: number | null }) {
  return post('/accounting/accounts', payload)
}

export function updateAccount(id: number, payload: { name: string; parent_id: number | null }) {
  return put(`/accounting/accounts/${id}`, payload)
}

export function deleteAccount(id: number) {
  return del(`/accounting/accounts/${id}`)
}

// --- Journal Entries ---

export function fetchJournalEntries(params: { page?: number; per_page?: number }) {
  return get<JournalEntry[]>('/accounting/journal-entries', { params })
}

export function fetchJournalEntry(id: number) {
  return get<JournalEntry>(`/accounting/journal-entries/${id}`)
}

export function createJournalEntry(payload: JournalEntryPayload) {
  return post<JournalEntry>('/accounting/journal-entries', payload)
}

export function updateJournalEntry(id: number, payload: JournalEntryPayload) {
  return put<JournalEntry>(`/accounting/journal-entries/${id}`, payload)
}

export function deleteJournalEntry(id: number) {
  return del(`/accounting/journal-entries/${id}`)
}

// --- Ledger ---

export function fetchLedger(accountId: number, params?: { start_date?: string; end_date?: string }) {
  return get<LedgerResponse>(`/accounting/ledger/${accountId}`, { params })
}

// --- Reports ---

export function fetchBalanceSheet(params: { date: string }) {
  return get<BalanceSheetData>('/accounting/reports/balance-sheet', { params })
}

export function fetchIncomeStatement(params: { start_date: string; end_date: string }) {
  return get<IncomeStatementData>('/accounting/reports/income-statement', { params })
}

export function fetchTrialBalance(params: { start_date: string; end_date: string }) {
  return get<TrialBalanceData>('/accounting/reports/trial-balance', { params })
}

// --- Reset / Sample Data ---

export function loadSampleData() {
  return post<{ message: string; count: number }>('/accounting/sample-data')
}

export function resetJournal() {
  return post<{ message: string; count: number }>('/accounting/reset/journal', { confirm: true })
}

export function resetAll() {
  return post<{ message: string }>('/accounting/reset/all', { confirm: true })
}
