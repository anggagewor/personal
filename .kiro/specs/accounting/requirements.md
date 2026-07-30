# Requirements Document

## Introduction

Modul Accounting adalah modul pembelajaran akuntansi interaktif pada personal dashboard. Modul ini terpisah dari modul Finance (pencatatan pemasukan/pengeluaran sederhana) karena berfokus pada konsep akuntansi formal: double-entry bookkeeping, Chart of Accounts (COA), jurnal umum, buku besar (ledger), dan laporan keuangan. Tujuan utama modul ini adalah menyediakan environment bagi pengguna untuk bereksperimen dan mempraktikkan konsep akuntansi secara hands-on.

## Glossary

- **Accounting_Module**: Modul akuntansi dalam personal dashboard yang mengelola seluruh alur akuntansi dari COA sampai laporan keuangan
- **COA (Chart_of_Accounts)**: Daftar hierarkis semua akun yang digunakan dalam pencatatan akuntansi, dikategorikan berdasarkan tipe akun
- **Account**: Satu entri dalam Chart of Accounts yang merepresentasikan kategori keuangan tertentu (contoh: Kas, Piutang, Modal)
- **Account_Type**: Klasifikasi akun berdasarkan sifatnya — Asset, Liability, Equity, Revenue, Expense
- **Normal_Balance**: Sisi default saldo akun (Debit untuk Asset & Expense, Kredit untuk Liability, Equity & Revenue)
- **Journal_Entry**: Satu transaksi akuntansi yang terdiri dari minimal dua baris (debit dan kredit) yang harus seimbang
- **Journal_Line**: Satu baris dalam Journal Entry yang mencatat debit atau kredit pada satu Account
- **Ledger**: Kumpulan semua transaksi yang tercatat pada satu Account tertentu, menampilkan saldo berjalan
- **Trial_Balance**: Laporan yang menampilkan saldo semua akun untuk memverifikasi keseimbangan total debit dan kredit
- **Income_Statement**: Laporan laba rugi yang menampilkan pendapatan dikurangi beban dalam periode tertentu
- **Balance_Sheet**: Laporan posisi keuangan yang menampilkan Asset = Liability + Equity pada titik waktu tertentu
- **Fiscal_Period**: Periode waktu yang digunakan untuk pelaporan keuangan (bulanan atau tahunan)
- **Double_Entry**: Prinsip akuntansi di mana setiap transaksi harus memiliki total debit sama dengan total kredit

## Requirements

### Requirement 1: Pengelolaan Chart of Accounts (COA)

**User Story:** As a learner, I want to manage a Chart of Accounts, so that I can organize and categorize all accounts used in my bookkeeping practice.

#### Acceptance Criteria

1. THE Accounting_Module SHALL display all Accounts in a hierarchical tree grouped by Account_Type, showing account code and account name for each entry
2. WHEN a user creates a new Account, THE Accounting_Module SHALL require an account code (alphanumeric, 1 to 10 characters), an account name (1 to 100 characters), an Account_Type, and an optional parent account
3. WHEN a user creates a new Account, THE Accounting_Module SHALL assign the Normal_Balance based on the selected Account_Type (Debit for Asset and Expense, Credit for Liability, Equity, and Revenue)
4. WHEN a user edits an Account, THE Accounting_Module SHALL allow modification of account name and parent account, but SHALL NOT allow modification of the account code or Account_Type
5. WHEN a user attempts to delete an Account that has Journal_Lines referencing it, THE Accounting_Module SHALL prevent deletion and display an error message indicating the Account is in use
6. IF a user submits an account code that already exists, THEN THE Accounting_Module SHALL reject the creation and display an error message indicating the code is already in use
7. WHEN a user accesses the Accounting_Module for the first time and no Accounts exist, THE Accounting_Module SHALL automatically provision a set of default template Accounts covering all five Account_Types (Asset, Liability, Equity, Revenue, Expense)
8. WHEN a user selects a parent account for a new or edited Account, THE Accounting_Module SHALL only allow selection of Accounts with the same Account_Type and limit nesting to a maximum depth of 3 levels

### Requirement 2: Pencatatan Journal Entry

**User Story:** As a learner, I want to create journal entries with double-entry bookkeeping, so that I can practice recording financial transactions correctly.

#### Acceptance Criteria

1. WHEN a user creates a Journal_Entry, THE Accounting_Module SHALL require a date, a description (maximum 255 characters), and at least two Journal_Lines (maximum 20 Journal_Lines per entry)
2. WHEN a user submits a Journal_Entry, THE Accounting_Module SHALL validate that total debit equals total credit across all Journal_Lines
3. IF total debit does not equal total credit, THEN THE Accounting_Module SHALL reject the Journal_Entry and display the imbalance amount
4. WHEN a Journal_Entry is saved successfully, THE Accounting_Module SHALL update the Ledger for each Account referenced in the Journal_Lines
5. WHEN a user creates a Journal_Line, THE Accounting_Module SHALL require selecting an Account and entering either a debit amount or a credit amount (not both, and not zero for both), where the amount must be between 0.01 and 9,999,999,999,999.99
6. THE Accounting_Module SHALL display all Journal_Entries in reverse chronological order with date, description, entry number, and total debit amount
7. WHEN a user edits an existing Journal_Entry, THE Accounting_Module SHALL re-validate the double-entry balance before saving and update the Ledger to reflect the changes
8. WHEN a user deletes a Journal_Entry, THE Accounting_Module SHALL reverse the Ledger effects for all associated Journal_Lines
9. THE Accounting_Module SHALL auto-generate a sequential entry number per user for each Journal_Entry, starting from 1 and incrementing by 1 regardless of deletions
10. IF a user submits a Journal_Entry with a Journal_Line referencing an Account that does not exist in the Chart_of_Accounts, THEN THE Accounting_Module SHALL reject the Journal_Entry and display an error message indicating the invalid Account
11. IF a user submits a Journal_Entry with two or more Journal_Lines referencing the same Account on the same side (both debit or both credit), THEN THE Accounting_Module SHALL accept the entry provided the total debit still equals total credit

### Requirement 3: Buku Besar (Ledger)

**User Story:** As a learner, I want to view the ledger for each account, so that I can see all transactions and running balance for a specific account.

#### Acceptance Criteria

1. WHEN a user selects an Account, THE Accounting_Module SHALL display all Journal_Lines for that Account in chronological order, showing for each line: the Journal_Entry date, Journal_Entry number, description, debit amount, credit amount, and running balance
2. THE Accounting_Module SHALL calculate the running balance after each Journal_Line by adding debit amounts and subtracting credit amounts for Accounts with a Debit Normal_Balance, and by adding credit amounts and subtracting debit amounts for Accounts with a Credit Normal_Balance
3. WHEN a user filters the Ledger by date range, THE Accounting_Module SHALL display only Journal_Lines within the specified period and show the opening balance calculated as the net sum of all Journal_Lines dated before the start of the period
4. THE Accounting_Module SHALL display the current total balance for each Account in the Ledger overview, calculated as the net sum of all Journal_Lines recorded for that Account
5. IF a user selects an Account that has no Journal_Lines, THEN THE Accounting_Module SHALL display the Ledger view with a zero balance and a message indicating no transactions have been recorded

### Requirement 4: Trial Balance

**User Story:** As a learner, I want to generate a trial balance report, so that I can verify that all my journal entries are balanced correctly.

#### Acceptance Criteria

1. WHEN a user generates a Trial_Balance, THE Accounting_Module SHALL list all Accounts that have at least one Journal_Line recorded, displaying each Account's net balance in the debit column if the Account has a debit Normal_Balance, or in the credit column if the Account has a credit Normal_Balance
2. THE Accounting_Module SHALL display total debit and total credit columns at the bottom of the Trial_Balance
3. WHEN total debit equals total credit in the Trial_Balance, THE Accounting_Module SHALL display a "Balanced" indicator
4. WHEN total debit does not equal total credit in the Trial_Balance, THE Accounting_Module SHALL display an "Unbalanced" warning with the difference amount
5. WHEN a user selects a Fiscal_Period, THE Accounting_Module SHALL generate the Trial_Balance using cumulative Account balances from all Journal_Entries dated from the start of the Fiscal_Period up to and including the end of the Fiscal_Period
6. IF no Journal_Entries exist for the selected Fiscal_Period, THEN THE Accounting_Module SHALL display an empty Trial_Balance with zero totals for both debit and credit columns

### Requirement 5: Laporan Laba Rugi (Income Statement)

**User Story:** As a learner, I want to generate an income statement, so that I can see the profit or loss for a given period.

#### Acceptance Criteria

1. WHEN a user generates an Income_Statement for a Fiscal_Period, THE Accounting_Module SHALL display all Revenue accounts that have transaction activity within that period, along with each account's balance for that period, and a total Revenue subtotal
2. WHEN a user generates an Income_Statement for a Fiscal_Period, THE Accounting_Module SHALL display all Expense accounts that have transaction activity within that period, along with each account's balance for that period, and a total Expense subtotal
3. WHEN a user generates an Income_Statement for a Fiscal_Period, THE Accounting_Module SHALL calculate and display Net Income as total Revenue minus total Expense
4. WHEN Net Income is positive, THE Accounting_Module SHALL display it as "Laba Bersih" (profit)
5. WHEN Net Income is negative, THE Accounting_Module SHALL display it as "Rugi Bersih" (loss)
6. WHEN Net Income is zero, THE Accounting_Module SHALL display it as "Impas" (break-even) with a value of 0
7. IF no Journal_Entries exist within the selected Fiscal_Period, THEN THE Accounting_Module SHALL display the Income_Statement with zero values for Revenue, Expense, and Net Income

### Requirement 6: Neraca (Balance Sheet)

**User Story:** As a learner, I want to generate a balance sheet, so that I can see the financial position at a specific point in time.

#### Acceptance Criteria

1. WHEN a user generates a Balance_Sheet for a specific date, THE Accounting_Module SHALL display all Asset accounts and their balances calculated as the net sum of all Journal_Lines from inception up to and including the selected date
2. WHEN a user generates a Balance_Sheet for a specific date, THE Accounting_Module SHALL display all Liability accounts and their balances calculated as the net sum of all Journal_Lines from inception up to and including the selected date
3. WHEN a user generates a Balance_Sheet for a specific date, THE Accounting_Module SHALL display all Equity accounts and their balances, including Net Income calculated as total Revenue minus total Expense from the start of the Fiscal_Period containing the selected date up to and including the selected date
4. WHEN a user generates a Balance_Sheet, THE Accounting_Module SHALL display the accounting equation: Total Asset = Total Liability + Total Equity
5. WHEN Total Asset equals Total Liability plus Total Equity, THE Accounting_Module SHALL display a "Balanced" indicator
6. WHEN Total Asset does not equal Total Liability plus Total Equity, THE Accounting_Module SHALL display an "Unbalanced" warning with the difference amount
7. IF no Journal_Entries exist up to the selected date, THEN THE Accounting_Module SHALL display the Balance_Sheet with all balances as zero

### Requirement 7: Reset dan Template Data

**User Story:** As a learner, I want to reset my accounting data and reload templates, so that I can start fresh whenever I want to practice from the beginning.

#### Acceptance Criteria

1. WHEN a user triggers a data reset, THE Accounting_Module SHALL delete all Journal_Entries and Ledger data while preserving the Chart_of_Accounts
2. WHEN a user triggers a full reset, THE Accounting_Module SHALL delete all data including the Chart_of_Accounts and reload the default template Accounts
3. WHEN a user triggers any reset action, THE Accounting_Module SHALL display a confirmation dialog requiring explicit user approval before proceeding with the deletion
4. IF a reset operation fails before completing all deletions, THEN THE Accounting_Module SHALL roll back any partial changes and display an error message indicating the reset was not completed
5. WHEN a user loads sample Journal_Entries, THE Accounting_Module SHALL add a minimum of 5 sample Journal_Entries demonstrating different transaction types (revenue, expense, asset purchase) to the existing data without removing current Journal_Entries
6. WHEN a reset or sample data load operation completes successfully, THE Accounting_Module SHALL display a success notification indicating the operation performed and the number of records affected

### Requirement 8: Navigasi dan Antarmuka

**User Story:** As a learner, I want a clear and intuitive navigation structure, so that I can easily access all parts of the accounting module.

#### Acceptance Criteria

1. THE Accounting_Module SHALL be accessible from the sidebar navigation under a dedicated "Akuntansi" menu item with sub-navigation children for COA, Jurnal, Buku Besar, and Laporan (containing Trial Balance, Laba Rugi, and Neraca)
2. THE Accounting_Module SHALL display all labels, headers, and messages in Bahasa Indonesia
3. WHEN a user navigates between sub-pages, THE Accounting_Module SHALL preserve the selected Fiscal_Period filter across pages until the user logs out or explicitly changes it
4. WHEN a user accesses the Accounting_Module for the first time in a session without a previously selected Fiscal_Period, THE Accounting_Module SHALL default the Fiscal_Period filter to the current month
5. WHEN a user is on an Accounting_Module sub-page, THE Accounting_Module SHALL visually indicate the active sub-page in the sidebar navigation
