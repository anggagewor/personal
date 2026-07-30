# Implementation Plan: Accounting Module

## Overview

Modul Accounting dengan double-entry bookkeeping, Chart of Accounts, Journal Entries, Ledger, dan Financial Reports. Dibangun menggunakan DDD Layered Modular architecture (Backend) dan Vue 3 + TypeScript (Frontend).

## Tasks

- [x] 1. Create database migration `2026_07_30_200000_create_accounting_tables.php` with tables: `accounting_accounts` (id, user_id, code varchar(10), name varchar(100), type varchar(20), normal_balance varchar(10), parent_id nullable bigint unsigned, depth tinyint default 1, timestamps; unique(user_id,code), index(user_id,type), index(parent_id)), `accounting_journal_entries` (id, user_id, entry_number int unsigned, date, description varchar(255), total_debit decimal(15,2), timestamps; unique(user_id,entry_number), index(user_id,date)), `accounting_journal_lines` (id, journal_entry_id bigint unsigned, account_id bigint unsigned, debit decimal(15,2) default 0, credit decimal(15,2) default 0; index(journal_entry_id), index(account_id)). Create `AccountingServiceProvider` that binds repository interfaces and registers routes. Register in `bootstrap/providers.php`. Create `AccountModel` and `JournalEntryModel` with lines HasMany relationship.
  - Requirements: 1, 2, 3
  - Design References: Data Models, Architecture/Module Structure

- [x] 2. Create Domain layer: `AccountType` enum (Asset, Liability, Equity, Revenue, Expense), `NormalBalance` enum (Debit, Credit), `Account` entity (id, userId, code, name, type, normalBalance, parentId, depth, createdAt), `JournalEntry` entity (id, userId, entryNumber, date, description, lines[], createdAt) with methods isBalanced(), totalDebit(), totalCredit(), imbalanceAmount(). Create `JournalLine` value object (id, accountId, debit, credit) with isDebit(), isCredit(), amount() methods. Create `AccountRepositoryInterface` and `JournalEntryRepositoryInterface` contracts. Create domain exceptions: `AccountInUseException`, `UnbalancedEntryException`, `MaxDepthExceededException`.
  - Requirements: 1, 2, 3
  - Design References: Components and Interfaces/Domain Layer

- [x] 3. Create Application DTOs: `AccountData` (code, name, type, parentId), `JournalEntryData` (date, description, lines[]), `JournalLineData` (accountId, debit, credit). Create Account actions: `CreateAccountAction` (validate unique code, assign normal_balance from type, validate parent same-type and max depth 3), `UpdateAccountAction` (keep code/type immutable, validate parent constraints), `DeleteAccountAction` (check hasJournalLines, throw AccountInUseException), `ProvisionDefaultAccountsAction` (create 16 default accounts if countByUser is 0).
  - Requirements: 1.2, 1.3, 1.4, 1.5, 1.6, 1.7, 1.8
  - Design References: Components and Interfaces/Application Layer, Data Models/Default Template Accounts

- [x] 4. Create Journal Entry actions: `CreateJournalEntryAction` (get next entry number, validate isBalanced, min 2 / max 20 lines, all account_ids exist, save), `UpdateJournalEntryAction` (rebuild entry, re-validate balance, replace lines), `DeleteJournalEntryAction` (delete entry cascading lines).
  - Requirements: 2.1, 2.2, 2.3, 2.4, 2.5, 2.7, 2.8, 2.9, 2.10, 2.11
  - Design References: Components and Interfaces/Application Layer, Correctness Properties P6, P7, P8

- [x] 5. Create report/ledger actions: `GetLedgerAction` (compute running balance per line based on normal_balance, compute opening balance for date-filtered), `GetTrialBalanceAction` (pivot balances into debit/credit columns by normal_balance, compute totals and balanced status), `GetIncomeStatementAction` (filter Revenue/Expense, compute net income, determine label), `GetBalanceSheetAction` (cumulative Asset/Liability/Equity balances, inject net income into Equity, verify equation).
  - Requirements: 3.1-3.5, 4.1-4.6, 5.1-5.7, 6.1-6.7
  - Design References: Components and Interfaces/Application Layer, Correctness Properties P9, P10, P11, P12

- [x] 6. Create reset/sample actions: `ResetJournalDataAction` (delete all journal entries for user in DB transaction, return count), `ResetAllDataAction` (delete journals + accounts in transaction, re-provision defaults), `LoadSampleEntriesAction` (create ≥5 sample entries demonstrating revenue, expense, asset purchase, liability payment, equity investment using existing accounts).
  - Requirements: 7.1, 7.2, 7.3, 7.4, 7.5, 7.6
  - Design References: Components and Interfaces/Application Layer, Correctness Properties P13, P14, P15

- [x] 7. Create Eloquent repositories: `EloquentAccountRepository` (findById, findByCode, findByUser, findByUserGroupedByType, getDepth, hasJournalLines, save, delete, countByUser with entity mapping), `EloquentJournalEntryRepository` (findById with lines eager load, findByUserPaginated, getNextEntryNumber as MAX+1, save in DB transaction with line replacement, delete, getLedgerForAccount JOIN query, getAccountBalances SUM grouped, deleteAllByUser).
  - Requirements: 1, 2, 3, 4, 5, 6
  - Design References: Data Models/Ledger Computation, Data Models/Report Computation

- [x] 8. Create Infrastructure controllers and requests for Accounts: `StoreAccountRequest` (code required alpha_num max:10, name required max:100, type required in enum values, parent_id nullable integer), `UpdateAccountRequest` (name sometimes required max:100, parent_id nullable integer), `AccountController` (index: provision if empty then return grouped; store: create; update: findOwnedOrFail then update; destroy: findOwnedOrFail then delete catching AccountInUseException as 409), `AccountResource` (toArray mapping).
  - Requirements: 1.1, 1.2, 1.3, 1.4, 1.5, 1.6, 1.7, 1.8
  - Design References: Infrastructure/API Endpoints, Error Handling

- [x] 9. Create Infrastructure controllers and requests for Journal Entries: `StoreJournalEntryRequest` (date required date, description required max:255, lines required array min:2 max:20, lines.*.account_id required integer, lines.*.debit required numeric min:0, lines.*.credit required numeric min:0), `UpdateJournalEntryRequest` (same rules), `JournalEntryController` (index: paginated; show: single with lines; store: create catching UnbalancedEntryException; update: findOwnedOrFail then update; destroy: findOwnedOrFail then delete), `JournalEntryResource` (toArray with lines).
  - Requirements: 2.1-2.11
  - Design References: Infrastructure/API Endpoints, Error Handling/Validation Errors

- [x] 10. Create `LedgerController` (show: findOwnedOrFail account, call GetLedgerAction with optional date params), `ReportController` (trialBalance, incomeStatement, balanceSheet with period/date params defaulting to current month/today), `ResetController` (resetJournal, resetAll requiring confirm param, loadSample), and response resources (`LedgerResource`, `ReportResource`).
  - Requirements: 3.1-3.5, 4.1-4.6, 5.1-5.7, 6.1-6.7, 7.1-7.6
  - Design References: Infrastructure/API Endpoints

- [x] 11. Create `Routes/api.php` with all 16 endpoints under auth:sanctum middleware: apiResource for accounts (except show), apiResource for journal-entries, GET ledger/{accountId}, GET reports/trial-balance, GET reports/income-statement, GET reports/balance-sheet, POST reset/journal, POST reset/all, POST sample-data. Verify ServiceProvider registers routes and binds both interfaces correctly.
  - Requirements: 1-8
  - Design References: Infrastructure/API Endpoints

- [x] 12. Add icons to `resources/js/utils/icons.ts` (BookOpenCheck or Calculator for Akuntansi). Add "Akuntansi" navigation item to `resources/js/config/navigation.ts` in Referensi group with icon and children: COA (/accounting), Jurnal (/accounting/journal), Buku Besar (/accounting/ledger), Trial Balance (/accounting/reports/trial-balance), Laba Rugi (/accounting/reports/income-statement), Neraca (/accounting/reports/balance-sheet). Add all 6 routes to `resources/js/router/index.ts` as lazy-loaded pages.
  - Requirements: 8.1, 8.5
  - Design References: Frontend Structure

- [x] 13. Create `resources/js/pages/accounting/Index.vue` (COA page): fetch accounts grouped by type, display hierarchical tree showing code + name indented by depth, grouped under 5 Account Type sections. Add "Tambah Akun" button opening BaseModal with form (code, name, type BaseSelect, parent BaseSelect filtered same type). Edit modal (name + parent editable, code + type disabled). Delete with confirm. All labels in Bahasa Indonesia.
  - Requirements: 1.1, 1.2, 1.3, 1.4, 1.5, 1.6, 1.7, 1.8
  - Design References: Frontend Structure, Components and Interfaces

- [x] 14. Create `resources/js/pages/accounting/Journal.vue`: fetch paginated journal entries, display table with entry_number, date, description, total debit. "Buat Jurnal" button opens modal with date picker, description input, dynamic lines table (account select, debit input, credit input, add/remove row). Show real-time total debit & credit with balance indicator. Submit/edit/delete with appropriate toasts and error handling for unbalanced entries. Pagination at bottom.
  - Requirements: 2.1, 2.2, 2.3, 2.5, 2.6, 2.7, 2.8, 2.9, 2.10, 2.11
  - Design References: Frontend Structure

- [x] 15. Create `resources/js/pages/accounting/Ledger.vue`: account selector dropdown showing all accounts with current balance, date range filter (start/end), table with columns Tanggal, No. Jurnal, Keterangan, Debit, Kredit, Saldo. Show opening balance row when filtered. Running balance per row. Empty state for accounts with no transactions. Display current total balance prominently at top.
  - Requirements: 3.1, 3.2, 3.3, 3.4, 3.5
  - Design References: Frontend Structure

- [x] 16. Create report pages: `reports/TrialBalance.vue` (period selector defaulting current month, table with Kode/Nama/Debit/Kredit, total row, balanced badge), `reports/IncomeStatement.vue` (period selector, Pendapatan section with subtotal, Beban section with subtotal, Net Income with Laba Bersih/Rugi Bersih/Impas label), `reports/BalanceSheet.vue` (date picker default today, Aset/Kewajiban/Ekuitas sections each with subtotal, accounting equation display, balanced indicator).
  - Requirements: 4.1-4.6, 5.1-5.7, 6.1-6.7
  - Design References: Frontend Structure

- [x] 17. Add reset/sample controls to accounting UI: "Reset Jurnal" button with confirmation modal, "Reset Semua" button with prominent warning modal, "Muat Data Contoh" button. Each calls respective POST endpoint with confirm param. Show success toast with affected count. Refresh data after completion.
  - Requirements: 7.1, 7.2, 7.3, 7.4, 7.5, 7.6
  - Design References: Components and Interfaces/Application Layer

- [x] 18. Create `resources/js/composables/useFiscalPeriod.ts`: reactive store for selected period (startDate, endDate), default current month, persist across navigation within session (not localStorage), reset on logout. Wire period selector in Ledger, Trial Balance, Income Statement pages. Run `npm run build` to verify no errors.
  - Requirements: 8.3, 8.4
  - Design References: Frontend Structure

## Task Dependency Graph

```json
{
  "waves": [
    { "id": 0, "tasks": ["1"] },
    { "id": 1, "tasks": ["2"] },
    { "id": 2, "tasks": ["3", "7"] },
    { "id": 3, "tasks": ["4", "5", "6"] },
    { "id": 4, "tasks": ["8", "9", "10"] },
    { "id": 5, "tasks": ["11"] },
    { "id": 6, "tasks": ["12", "18"] },
    { "id": 7, "tasks": ["13", "14", "15", "16", "17"] }
  ]
}
```

## Notes

- Semua UI dalam Bahasa Indonesia
- Backend mengikuti DDD Layered Modular pattern yang sama dengan module lain
- Ledger dan Report dihitung on-read, bukan disimpan terpisah
- Migration pakai `unsignedBigInteger` tanpa foreign key constraint (sesuai aturan project)
- Column type disimpan sebagai string (bukan enum column)
