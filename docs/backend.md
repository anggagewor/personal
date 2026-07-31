# Backend — Laravel + DDD Layered Modular

## Struktur Module

```
src/Modules/
├── {ModuleName}/
│   ├── Domain/
│   │   ├── Entities/            → Pure PHP class, business rules, NO Laravel dependency
│   │   ├── ValueObjects/        → Immutable value types (opsional)
│   │   ├── Enums/               → PHP native enum untuk domain state
│   │   ├── Events/              → Domain events
│   │   ├── Exceptions/          → Domain-specific exceptions
│   │   └── Contracts/           → Interfaces (repository, service)
│   │
│   ├── Application/
│   │   ├── Actions/             → Use cases (satu action, satu tanggung jawab)
│   │   ├── DTO/                 → Data Transfer Objects (input/output antar layer)
│   │   └── Queries/             → Complex read operations (opsional)
│   │
│   └── Infrastructure/
│       ├── Controllers/         → HTTP controllers (thin, orchestrate only)
│       ├── Requests/            → FormRequest validation
│       ├── Models/              → Eloquent models (persistence concern)
│       ├── Repositories/        → Implementasi interface dari Domain/Contracts
│       ├── Resources/           → API Resource (response transform)
│       ├── Providers/           → Module ServiceProvider (bind interfaces)
│       ├── Migrations/          → (opsional) Database migrations per-module
│       ├── Factories/           → (opsional) Model factories per-module
│       └── Routes/
│           └── api.php          → Module routes
│
└── Shared/
    ├── Domain/
    │   ├── Contracts/           → BaseRepositoryInterface, shared interfaces
    │   └── Exceptions/          → Shared domain exceptions
    ├── Application/
    │   └── Actions/             → Cross-module actions (dashboard, weather, dll)
    └── Infrastructure/
        ├── Controllers/         → Thin controllers untuk shared endpoints
        ├── Traits/              → AuthorizesOwnership, BelongsToUser, dll
        ├── Routes/              → Shared routes (dashboard, weather, dll)
        └── Providers/           → SharedServiceProvider
```

## Dependency Rule (WAJIB)

```
Domain ← Application ← Infrastructure
```

- **Domain** TIDAK BOLEH import dari Application atau Infrastructure. Murni PHP, no Laravel.
- **Application** boleh import dari Domain. TIDAK BOLEH import dari Infrastructure.
- **Infrastructure** boleh import dari semua layer (dia adapter ke framework).

## Layer Rules

### Domain Layer
1. Entity = pure PHP class — tidak extend apapun dari Laravel. Berisi business rules dan state.
2. Entity TIDAK tau database — tidak ada `save()`, `find()`, atau query apapun.
3. Contracts (interfaces) WAJIB untuk setiap repository.
4. Enums — pakai PHP native enum. Taruh di Domain karena ini business concept.

### Application Layer
1. Actions = use case — satu action, satu tanggung jawab. Terima DTO/primitives, return Entity/DTO.
2. Actions depend on Contracts — inject interface, bukan concrete repository.
3. DTO — bikin untuk data yang complex. Untuk simple CRUD, bisa pakai array tapi tetap typed.

### Infrastructure Layer
1. Controllers thin — validate via FormRequest, panggil Action, transform via Resource.
2. Controllers extend `Illuminate\Routing\Controller` — bukan `App\Http\Controllers\Controller`.
3. Controllers pakai `AuthorizesOwnership` trait untuk ownership check via `findOwnedOrFail()`.
4. Models = Eloquent — hanya untuk persistence. Relationships, scopes, casts boleh di sini.
5. Repository implement interface — mapping antara Entity ↔ Model di sini.
6. Routes per-module — setiap module punya `Routes/api.php` sendiri.
7. Migrations — taruh di `database/migrations/` (centralized). Per-module `Infrastructure/Migrations/` opsional.
8. ServiceProvider per-module — bind interface ke concrete, register routes, load migrations.

## Naming Convention

| Jenis | Format | Contoh |
|-------|--------|--------|
| Entity | singular PascalCase | `Note`, `Task` |
| Model | `{Noun}Model` | `NoteModel`, `TaskModel` |
| Action | `{Verb}{Noun}Action` | `CreateNoteAction` |
| DTO | `{Noun}Data` | `NoteData` |
| Controller | `{Noun}Controller` | `NoteController` |
| FormRequest | `{Verb}{Noun}Request` | `StoreNoteRequest` |
| Interface | `{Noun}RepositoryInterface` | `NoteRepositoryInterface` |
| Repository | `Eloquent{Noun}Repository` | `EloquentNoteRepository` |
| Enum | `{Noun}{Type}` | `NoteStatus`, `TaskPriority` |
| Resource | `{Noun}Resource` | `NoteResource` |
| ServiceProvider | `{Module}ServiceProvider` | `NoteServiceProvider` |

## API Response Format

```json
{
  "data": { ... },
  "message": "optional",
  "meta": { "current_page": 1, "last_page": 5, "per_page": 10, "total": 50 }
}
```

## Rules Singkat

1. Semua module WAJIB 3-layer (Domain, Application, Infrastructure).
2. Controllers harus thin — logic di Action, validasi di FormRequest.
3. Entity = pure PHP, Model = Eloquent only.
4. Interface (Contracts) WAJIB untuk setiap repository.
5. DTO wajib untuk input ke Action — typed, immutable (readonly class).
6. Sanctum untuk auth token.
7. Enums pakai PHP native enum, validate pakai `Rule::enum()`.
8. Migration: JANGAN pakai enum column — simpan sebagai `string`.
9. Migration: JANGAN pakai foreign key constraint — pakai `unsignedBigInteger`.
10. ServiceProvider per-module — register di `bootstrap/providers.php`.
11. Ownership check pakai `AuthorizesOwnership` trait + `findOwnedOrFail()`.

## Autoload (composer.json)

```json
{
  "psr-4": {
    "App\\": "app/",
    "Modules\\": "src/Modules/"
  }
}
```

## Module yang Ada

| Module | Tanggung Jawab |
|--------|---------------|
| User | Auth, profile, preferences |
| Note | Catatan / notes CRUD |
| Bookmark | Bookmark URLs |
| Task | Task management + reorder + recurrence |
| Calendar | Calendar events + holidays |
| Activity | Activity logging |
| Pomodoro | Pomodoro timer sessions |
| Scratchpad | Quick scratch notes |
| Habit | Habit tracking + streak |
| Finance | Personal finance tracking |
| Market | Market watchlist (Twelve Data API + price history) |
| Gold | Emas Antam (harga harian + histori 15 tahun) |
| ReadingList | Reading list management |
| Journal | Daily journal entries + mood |
| Goal | Goal setting & milestones |
| Tag | Tagging system (notes, tasks) |
| Quote | Motivational quotes (CRUD + daily) |
| Wishlist | Wishlist items |
| Trash | Soft-delete / restore |
| Shared | Cross-module traits, contracts, weather, dashboard |
| LogReader | Memory-efficient Laravel log reader (reverse file reading) |

## Scaffold Module Baru

Gunakan script scaffold untuk generate full-stack module (backend DDD + frontend) dalam satu command:

```bash
./scripts/scaffold-module.sh NamaModule
```

### Contoh

```bash
./scripts/scaffold-module.sh Expense
```

### Yang Di-Generate

**Backend (`src/Modules/Expense/`):**

| Layer | File |
|-------|------|
| Domain | `Entities/Expense.php`, `Contracts/ExpenseRepositoryInterface.php` |
| Application | `Actions/CreateExpenseAction.php`, `UpdateExpenseAction.php`, `DeleteExpenseAction.php`, `DTO/ExpenseData.php` |
| Infrastructure | `Controllers/ExpenseController.php`, `Requests/StoreExpenseRequest.php`, `UpdateExpenseRequest.php`, `Models/ExpenseModel.php`, `Repositories/EloquentExpenseRepository.php`, `Resources/ExpenseResource.php`, `Providers/ExpenseServiceProvider.php`, `Routes/api.php` |

**Frontend:**

| File | Keterangan |
|------|------------|
| `resources/js/types/expense.ts` | Interface & payload types |
| `resources/js/api/expense.ts` | API call functions (fetchAll, create, update, remove) |
| `resources/js/pages/expense/Index.vue` | Page component dengan CRUD skeleton |

### Setelah Scaffold

1. **Register provider** di `bootstrap/providers.php`:
   ```php
   Modules\Expense\Infrastructure\Providers\ExpenseServiceProvider::class,
   ```

2. **Tambah route** di `resources/js/router/index.ts`:
   ```ts
   { path: '/expenses', component: () => import('@/pages/expense/Index.vue') }
   ```

3. **Buat migration**:
   ```bash
   php artisan make:migration create_expenses_table
   ```

4. **Isi TODO** di semua file — entity fields, DTO mapping, validation rules, model fillable, dll.

5. **Build & test**:
   ```bash
   npm run build
   php artisan test
   ```

## LogReader — Strategi Baca Log Hemat Memory

Module LogReader menggunakan **Reverse File Reading** via `fseek` untuk membaca file log Laravel berukuran besar (bisa bergiga-giga) tanpa membebani memory.

### Prinsip

1. **Baca dari ujung file** — `fseek` langsung ke akhir file, lalu mundur per chunk.
2. **Chunk 8KB** — setiap request hanya alokasi ~8-16KB buffer di RAM, regardless file size.
3. **Parse on-the-fly** — begitu chunk terbaca, langsung di-parse cari pattern `[YYYY-MM-DD HH:MM:SS]`.
4. **Stop begitu cukup** — minta 30 entries? Begitu dapat 30, langsung return.
5. **Safety limit** — max scan 2MB per request. Jika filter terlalu ketat dan 2MB tidak cukup, return yang ada.

### Flow Per Request

```
Request: GET /api/logs/entries?file=laravel.log&offset=0&per_page=30

1. filesize() → 3GB, set position = 3GB (end of file)
2. position -= 8192, fseek(position), fread(8192)
3. Parse entries dari chunk → dapet 12 entries (belum 30)
4. position -= 8192, fseek(position), fread(8192)
5. Parse lagi, total = 30 → STOP
6. Return entries + meta.next_offset = position saat ini

Request berikutnya: ?offset={next_offset}
→ langsung fseek ke posisi itu, lanjut mundur
```

### Pagination: Cursor-Based (Byte Offset)

Tidak menggunakan page number. Frontend menyimpan `next_offset` dari response `meta`, lalu kirim ulang sebagai parameter `offset` untuk "load more". Ini memungkinkan jump langsung ke posisi file tanpa scan ulang.

### Kenapa Tidak Pakai Pendekatan Lain?

| Pendekatan | Masalah |
|-----------|---------|
| `file_get_contents()` | Load seluruh file ke string — OOM untuk file besar |
| `file()` (array per line) | Load seluruh file ke array — sama OOM |
| `SplFileObject` + seek line | Harus count lines dari awal — lambat untuk file besar |
| Reverse fseek (✓) | Langsung loncat ke posisi byte, memory konstan ~16KB |

### Struktur File

```
src/Modules/LogReader/
├── Domain/
│   ├── Entities/LogEntry.php
│   ├── Enums/LogLevel.php
│   └── Contracts/LogReaderInterface.php
├── Application/
│   └── Actions/
│       ├── ReadLogEntriesAction.php
│       └── ListLogFilesAction.php
└── Infrastructure/
    ├── Controllers/LogReaderController.php
    ├── Services/ReverseFileLogReader.php   ← core logic
    ├── Resources/LogEntryResource.php
    ├── Providers/LogReaderServiceProvider.php
    └── Routes/api.php
```

### API Endpoints

| Method | Endpoint | Keterangan |
|--------|----------|-----------|
| GET | `/api/logs/files` | List file log + ukuran + modified date |
| GET | `/api/logs/entries` | Baca entries (params: file, per_page, offset, level, search) |

### Response Meta

```json
{
  "meta": {
    "file_size": 3221225472,
    "next_offset": 3221209088,
    "count": 30,
    "has_more": true
  }
}
```

### Keamanan

- **Prevent directory traversal** — filename di-`basename()` sebelum resolve path.
- **Regex validation** — controller validate filename harus match `/^[\w\-\.]+\.log$/`.
- **Auth required** — semua endpoint pakai `auth:sanctum` middleware.
