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
