export interface Account {
  id: number
  code: string
  name: string
  type: string
  normal_balance: string
  parent_id: number | null
  depth: number
}

export type GroupedAccounts = Record<string, Account[]>

export interface JournalLine {
  id?: number
  account_id: number | ''
  debit: number | ''
  credit: number | ''
}

export interface JournalEntry {
  id: number
  entry_number: number
  date: string
  description: string
  total_debit: number
  lines: JournalLine[]
  created_at: string
}

export interface JournalEntryPayload {
  date: string
  description: string
  lines: Array<{ account_id: number; debit: number; credit: number }>
}

export interface LedgerLine {
  date: string
  entry_number: number
  description: string
  debit: number
  credit: number
  balance: number
}

export interface LedgerResponse {
  account: Account
  opening_balance: number
  lines: LedgerLine[]
}

export interface ReportAccount {
  account_id: number
  code: string
  name: string
  balance: number
}

export interface BalanceSheetData {
  assets: ReportAccount[]
  liabilities: ReportAccount[]
  equity: ReportAccount[]
  net_income: number
  total_assets: number
  total_liabilities: number
  total_equity: number
  is_balanced: boolean
}

export interface IncomeStatementData {
  revenue: { accounts: ReportAccount[]; total: number }
  expense: { accounts: ReportAccount[]; total: number }
  net_income: number
  label: string
}

export interface TrialBalanceAccount {
  account_id: number
  code: string
  name: string
  debit: number
  credit: number
}

export interface TrialBalanceData {
  accounts: TrialBalanceAccount[]
  total_debit: number
  total_credit: number
  is_balanced: boolean
}
