# Foundry — Module Tooling

Tooling untuk menjaga dan memverifikasi modularitas arsitektur Purdia.

## Commands

### `foundry:scan`

Scan dependency antar module dari source code. Membaca semua `use` statements dan inline FQCN references di PHP files.

```bash
# Scan semua module
php artisan foundry:scan

# Scan specific module
php artisan foundry:scan Note

# Output JSON (untuk pipeline / CI)
php artisan foundry:scan --json
```

Output:

```
┌─────────────────────────────────────────────┐
│         Foundry Dependency Scanner          │
└─────────────────────────────────────────────┘

  Accounting → Shared
  Budget → Finance, Shared
  GoogleDrive → Bookmark, Finance, Habit, Journal, Note, Shared, Task
  ...

  Standalone (no dependencies):
    ✓ DatabaseManager
    ✓ Gold
    ✓ Shared

  Total: 29 modules | 5 standalone | 24 with deps
```

---

### `foundry:graph`

Generate dependency graph dalam format Mermaid atau tabel Markdown.

```bash
# Mermaid format (default)
php artisan foundry:graph

# Table format
php artisan foundry:graph --format=table

# Write to file
php artisan foundry:graph --output=docs/graph.md
```

Output table:

```
| Module     | Dependencies     | Classification  |
|------------|-----------------|-----------------|
| Note       | Shared, User    | 🔴 Bundle       |
| Task       | Shared          | 🟡 Shared only  |
| Gold       | —               | 🟢 Standalone   |
```

Klasifikasi:
- 🟢 **Standalone** — zero dependency, bisa dicabut langsung
- 🟡 **Shared only** — hanya butuh Shared foundation
- 🔴 **Bundle** — depend ke module lain, harus dibawa bareng

---

### `foundry:verify`

Verifikasi integritas module dan aturan arsitektur.

```bash
# Verify semua module
php artisan foundry:verify

# Verify specific module
php artisan foundry:verify Note

# Strict mode (fail on warnings juga)
php artisan foundry:verify --strict
```

Yang dicek:
1. **DDD 3-layer** — Domain/, Application/, Infrastructure/ harus ada
2. **ServiceProvider** — setiap module wajib punya
3. **Routes** — warning kalau tidak ada (mungkin module internal)
4. **Manifest** — warning kalau `module.json` belum ada
5. **Manifest vs actual** — declared dependencies harus cocok dengan scan result
6. **Circular dependency** — tidak boleh ada cycle di graph
7. **Illegal imports** — module tidak boleh import dari `App\` namespace

Exit code:
- `0` — semua pass
- `1` — ada error (atau warning di strict mode)

Cocok untuk CI:

```yaml
# GitHub Actions / GitLab CI
- run: php artisan foundry:verify --strict
```

---

### `foundry:doctor`

Health check keseluruhan foundry. Memberikan score 0–100.

```bash
php artisan foundry:doctor
```

Output:

```
┌─────────────────────────────────────────────┐
│            Foundry Doctor                   │
└─────────────────────────────────────────────┘

  Foundry Health Report
  Modules: 29

    ✓ No circular dependencies
    ✓ All providers registered
    ✓ DDD 3-layer consistency (100%)
    ✓ Module manifests (100%)
    ✓ Extractable modules (24/29 = 83%)
    ✓ No App\ namespace imports in modules
    ✓ Modules with routes (29/29 = 100%)

  Overall Health: 95/100
```

Scoring:
- Circular dependency: **-20**
- Missing provider: **-5** per module
- Missing DDD layer: **-2** per layer
- Low manifest coverage: **-5 to -10**
- `App\` namespace violation: **-3** per file

---

## Module Manifest

Setiap module punya `module.json` di root directory-nya. Manifest berisi metadata yang **tidak bisa diinfer dari source code**.

```
src/Modules/Note/module.json
```

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

| Field | Type | Keterangan |
|-------|------|-----------|
| `name` | string | Nama module (harus match folder name) |
| `display_name` | string | Nama tampilan untuk UI/docs |
| `description` | string | Deskripsi singkat module |
| `depends` | string[] | Dependencies yang di-declare (diverifikasi vs scan) |
| `extractable` | boolean | Apakah module ini bisa di-extract ke project lain |
| `tags` | string[] | Kategori untuk grouping (productivity, finance, business, dll) |

### Kenapa `depends` ada di manifest kalau sudah ada scanner?

Scanner menghasilkan **actual dependencies** dari kode. Manifest berisi **declared dependencies** — intent developer.

`foundry:verify` membandingkan keduanya:
- **Undeclared dependency** (ada di code, tidak di manifest) → ERROR
- **Stale dependency** (ada di manifest, tidak di code) → WARNING

Ini mencegah dependency "diam-diam masuk" tanpa developer sadar.

---

## Architectural Rules

Rules yang di-enforce oleh `foundry:verify`:

1. **No circular dependencies** — graph harus DAG
2. **Shared must be standalone** — Shared tidak boleh depend ke module manapun
3. **No `App\` imports** — module hanya boleh import dari `Modules\` namespace
4. **DDD consistency** — setiap module wajib punya Domain/, Application/, Infrastructure/
5. **Provider exists** — setiap module wajib punya ServiceProvider

---

## File Locations

```
src/Modules/
├── Shared/
│   └── Infrastructure/
│       └── Commands/
│           └── Foundry/
│               ├── ScanCommand.php
│               ├── GraphCommand.php
│               ├── VerifyCommand.php
│               └── DoctorCommand.php
├── Note/
│   └── module.json
├── Task/
│   └── module.json
├── ...
```

Commands diregistrasi di `SharedServiceProvider`.
