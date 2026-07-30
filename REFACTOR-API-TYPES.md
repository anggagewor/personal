# Refactor: Pisahkan API Calls & Interfaces ke File Terpisah

## Tujuan

Pindahkan semua inline `interface` dan API call functions dari page components ke file terpisah:

- `resources/js/types/<module>.ts` — semua interface/type per module
- `resources/js/api/<module>.ts` — semua API call functions per module

## Konvensi

### File Types (`resources/js/types/<module>.ts`)

```ts
// Contoh: resources/js/types/note.ts
export interface Note {
  id: number
  title: string
  content: string
  is_pinned: boolean
  created_at: string
  updated_at: string
}

// Form payload types (optional, kalau form complex)
export interface NotePayload {
  title: string
  content: string
}
```

### File API (`resources/js/api/<module>.ts`)

```ts
// Contoh: resources/js/api/notes.ts
import { get, post, put, del } from '@purdia/http'
import type { Note, NotePayload } from '@/types/note'

export function fetchNotes(params?: { search?: string }) {
  return get<Note[]>('/notes', { params })
}

export function createNote(payload: NotePayload) {
  return post<Note>('/notes', payload)
}

export function updateNote(id: number, payload: Partial<NotePayload>) {
  return put<Note>(`/notes/${id}`, payload)
}

export function deleteNote(id: number) {
  return del(`/notes/${id}`)
}

export function toggleNotePin(id: number) {
  return post<Note>(`/notes/${id}/toggle-pin`)
}
```

### Di Page Component

```ts
// Before
interface Note { ... }
async function fetchNotes() { const response = await get<Note[]>('/notes') ... }

// After
import type { Note } from '@/types/note'
import * as notesApi from '@/api/notes'
// atau individual: import { fetchNotes, createNote } from '@/api/notes'
```

## Aturan

1. Nama file types & api pakai **kebab-case** (sesuai folder module)
2. Export **named exports** (bukan default)
3. API function names deskriptif: `fetchX`, `createX`, `updateX`, `deleteX`, `toggleX`
4. Kalau module punya sub-page (misal accounting punya Journal, Ledger, Reports), tetap 1 file api & 1 file types per module
5. Setelah selesai refactor tiap module, pastikan `npm run build` pass

---

## Task List

### Setup

- [x] **Task 0**: Buat folder `resources/js/types/` dan `resources/js/api/`

### Module Refactoring (per module)

- [x] **Task 1**: `notes` — 1 page (Index.vue)
- [x] **Task 2**: `bookmarks` — 1 page (Index.vue)
- [x] **Task 3**: `tasks` — 2 pages (Index.vue, Kanban.vue)
- [x] **Task 4**: `calendar` — 1 page (Index.vue)
- [x] **Task 5**: `finance` — 1 page (Index.vue)
- [x] **Task 6**: `budget` — 1 page (Index.vue)
- [x] **Task 7**: `habits` — 1 page (Index.vue)
- [x] **Task 8**: `goals` — 1 page (Index.vue)
- [x] **Task 9**: `journal` — 1 page (Index.vue)
- [x] **Task 10**: `pomodoro` — 1 page (Index.vue)
- [x] **Task 11**: `scratchpads` — 1 page (Index.vue)
- [x] **Task 12**: `reading-list` — 1 page (Index.vue)
- [x] **Task 13**: `quotes` — 1 page (Index.vue)
- [x] **Task 14**: `wishlist` — 1 page (Index.vue)
- [x] **Task 15**: `vault` — 1 page (Index.vue)
- [x] **Task 16**: `market` — 1 page (Index.vue)
- [x] **Task 17**: `gold` — 1 page (Index.vue)
- [ ] **Task 18**: `accounting` — 7 pages (Index, Journal, Ledger, ResetControls, reports/BalanceSheet, reports/IncomeStatement, reports/TrialBalance)
- [ ] **Task 19**: `converter` — 11 pages + useConverter composable (Index, Custom, Area, Data, Length, Speed, Temperature, Time, Volume, Weight, ConverterLayout)
- [ ] **Task 20**: `drive` — 2 pages (Index.vue, Callback.vue)
- [ ] **Task 21**: `activity` — 1 page (Index.vue)
- [ ] **Task 22**: `trash` — 1 page (Index.vue)
- [ ] **Task 23**: `streaks` — 1 page (Index.vue)
- [ ] **Task 24**: `settings` — 5 pages (General, Appearance, Account, Export, Market)
- [ ] **Task 25**: `auth` — 2 pages (Login.vue, Register.vue)
- [ ] **Task 26**: `Dashboard.vue` — main dashboard page

### Finalisasi

- [ ] **Task 27**: Final build check (`npm run build`) & cleanup

---

## Cara Kerja per Task

1. Baca page component(s) di module tersebut
2. Extract semua `interface` → buat `resources/js/types/<module>.ts`
3. Extract semua API call functions → buat `resources/js/api/<module>.ts`
4. Update page component: import types & api dari file baru
5. Jalankan `npm run build` — pastikan pass tanpa error

## Progress

Started: 2026-07-30
Completed: —

### Done:
- Task 0–4 ✅ (build pass)
- Task 5–10 ✅ (build pass)
- Task 11–17 ✅ (build pass)
