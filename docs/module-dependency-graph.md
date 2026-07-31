# Module Dependency Graph

Dokumen ini memetakan dependensi antar module di `src/Modules/`.  
Berguna untuk menentukan module mana yang bisa dicabut sendiri (standalone) dan mana yang harus dibawa sebundel.

> Last updated: 2026-07-31

---

## Legend

- 🟢 **Standalone** — bisa dibawa sendiri tanpa modul lain
- 🟡 **Light deps** — cuma butuh `Shared` (foundation)
- 🔴 **Bundle** — harus dibawa bareng modul lain

---

## Dependency Map

| Module | Dependencies | Keterangan |
|--------|-------------|------------|
| Accounting | Shared | BelongsToUser, AuthorizesOwnership |
| Activity | Shared | BelongsToUser |
| Bookmark | Shared | BelongsToUser, AuthorizesOwnership |
| Budget | **Finance**, Shared | Uses FinanceModel |
| Calendar | Shared | BelongsToUser, AuthorizesOwnership |
| Converter | Shared | BelongsToUser, AuthorizesOwnership |
| Dashboard | — | Aggregation via raw DB queries (no model imports) |
| DatabaseManager | — | Fully standalone |
| Finance | Shared | BelongsToUser, AuthorizesOwnership |
| Goal | Shared | BelongsToUser, AuthorizesOwnership |
| Gold | — | Fully standalone |
| GoogleDrive | **Note, Task, Bookmark, Finance, Habit, Journal**, Shared | Backup/export module |
| Habit | Shared | BelongsToUser, AuthorizesOwnership |
| Journal | Shared | BelongsToUser, AuthorizesOwnership |
| LogReader | — | Fully standalone |
| Market | Shared | AuthorizesOwnership |
| Note | Shared | BelongsToUser, AuthorizesOwnership |
| Pomodoro | Shared | BelongsToUser, AuthorizesOwnership |
| Pos | Shared | BaseController, BelongsToUser, AuthorizesOwnership |
| Quote | — | Fully standalone |
| ReadingList | Shared | BelongsToUser, AuthorizesOwnership |
| Scratchpad | Shared | BelongsToUser, AuthorizesOwnership |
| Shared | — | Foundation module (traits, base classes, foundry commands) |
| Supplier | **Pos**, Shared | AdjustStockAction, StockAdjustmentData, OutletRepository |
| Tag | **Note, Task**, Shared | Polymorphic tagging |
| Task | Shared | BelongsToUser, AuthorizesOwnership |
| Trash | **Note, Task** | Soft-delete/restore |
| User | — | Auth/profile, standalone |
| Vault | Shared | BelongsToUser, AuthorizesOwnership |
| Weather | — | Fully standalone (OpenWeatherMap API) |
| Wishlist | Shared | BelongsToUser, AuthorizesOwnership |

---

## Dependency Diagram

```
                        ┌──────────┐
                        │  Shared  │  ← Foundation (traits, base classes)
                        └────┬─────┘
                             │
          ┌──────────────────┼──────────────────────────┐
          │                  │                          │
     ┌────┴────┐       ┌────┴────┐              ┌─────┴─────┐
     │  Note   │       │  Task   │              │  Finance  │
     └────┬────┘       └────┬────┘              └─────┬─────┘
          │                  │                         │
     ┌────┴──────────────────┴────┐              ┌────┴────┐
     │     Tag    │    Trash      │              │ Budget  │
     └────────────┴───────────────┘              └─────────┘

     ┌─────────┐
     │   Pos   │───────→ Supplier
     └─────────┘

     ┌─────────────┐
     │ GoogleDrive │───→ Note, Task, Bookmark, Finance, Habit, Journal
     └─────────────┘
```

---

## Klasifikasi untuk Foundry

### 🟢 Fully Standalone (0 dependency)

Bisa dicabut langsung tanpa bawa apa-apa:

- `Dashboard`
- `DatabaseManager`
- `Gold`
- `LogReader`
- `Quote`
- `User`
- `Weather`

### 🟡 Standalone + Shared

Cuma butuh `Shared` sebagai foundation. Formula: **Shared + Module X** = jalan sendiri.

- `Accounting`, `Activity`, `Bookmark`, `Calendar`, `Converter`
- `Finance`, `Goal`, `Habit`, `Journal`, `Market`
- `Note`, `Pomodoro`, `Pos`, `ReadingList`, `Scratchpad`
- `Task`, `Vault`, `Wishlist`

### 🔴 Bundle (harus dibawa bareng)

| Module | Minimum Bundle |
|--------|---------------|
| Budget | `Shared` + `Finance` + `Budget` |
| Supplier | `Shared` + `Pos` + `Supplier` |
| Tag | `Shared` + `Note` + `Task` + `Tag` |
| Trash | `Shared` + `Note` + `Task` + `Trash` |
| GoogleDrive | Practically full app (backup semua data) |

---

## Rekomendasi Bundle untuk Aplikasi Baru

### Core Kit (selalu bawa)

```
Shared + User
```

### Productivity Bundle

```
Shared + User + Note + Task + Tag + Trash + Calendar + Pomodoro
```

### Finance Bundle

```
Shared + User + Finance + Budget + Accounting
```

### POS Bundle

```
Shared + User + Pos + Supplier
```

### Pick & Mix (independent, pilih sesuai kebutuhan)

```
Bookmark, Habit, Journal, Goal, ReadingList,
Scratchpad, Wishlist, Quote, Vault, Market,
Gold, Converter, Activity, Weather, Dashboard
```

### Admin/Dev Tools (optional)

```
DatabaseManager, LogReader
```

### Skip kalau bukan full app

```
GoogleDrive — terlalu banyak coupling, only makes sense di full app
```

---

## Tooling

### Artisan Commands

```bash
# Scan dependencies from source code
php artisan foundry:scan
php artisan foundry:scan Note          # scan specific module
php artisan foundry:scan --json        # output as JSON

# Generate dependency graph
php artisan foundry:graph                    # mermaid format
php artisan foundry:graph --format=table     # table format
php artisan foundry:graph --output=docs/graph.md  # write to file

# Verify module integrity
php artisan foundry:verify               # verify all modules
php artisan foundry:verify Note          # verify specific module
php artisan foundry:verify --strict      # fail on warnings too

# Overall health check
php artisan foundry:doctor
```

### Module Manifest (`module.json`)

Setiap module sebaiknya punya `module.json` di root-nya:

```json
{
    "name": "Note",
    "display_name": "Notes",
    "description": "Personal note management with rich text editor",
    "depends": ["Shared"],
    "extractable": true,
    "tags": ["productivity"]
}
```

Dependencies di manifest diverifikasi terhadap hasil scan. Kalau ada mismatch, `foundry:verify` akan report error.

---

## Catatan

- Semua module menggunakan namespace `Modules\*`, tidak ada `use App\...` cross-import.
- `Shared` menyediakan: `BelongsToUser` trait, `AuthorizesOwnership` trait, `BaseController`, foundry commands.
- `Dashboard` menggunakan raw DB queries untuk aggregation — tidak import model dari module lain.
- `Weather` adalah API integration standalone (OpenWeatherMap), tidak perlu `Shared`.
- Dependency graph di-generate otomatis dari source code via `foundry:scan`.
