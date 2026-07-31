<p align="center">
  <img src="./docs/banner.png" alt="Purdia">
</p>

# Purdia Dashboard

Personal productivity dashboard + business tools, dibangun dengan Laravel 13 (DDD Modular) dan Vue 3 + TypeScript.

> **Disclaimer:** Ini adalah proyek personal sekaligus playground untuk meracik module-module yang bisa dipakai di proyek lain. Jangan heran kalau banyak module yang terasa "over-engineered" untuk kelas personal dashboard — memang sengaja dibikin proper agar bisa di-reuse dan dipelajari pola-polanya (DDD, property testing, full 3-layer, dsb). Think of it as a module incubator.

## Tech Stack

**Backend:**
- PHP 8.3 + Laravel 13
- DDD Layered Modular Architecture (`src/Modules/`)
- Laravel Sanctum (token-based auth)
- MariaDB (production) / SQLite (testing)

**Frontend:**
- Vue 3 + TypeScript (Composition API)
- Pinia (state management)
- Vue Router
- Tailwind CSS 4
- Vite 8

**Internal Packages (monorepo `packages/`):**
- `@purdia/auth` — Authentication store & route guard
- `@purdia/http` — HTTP client with silent token refresh & global error handling
- `@purdia/toast` — Toast notification system (Pinia store + component)
- `@purdia/composables` — Reusable composables (useApi, usePagination)
- `@purdia/ui` — Component library (40+ komponen)
- `@purdia/charts` — Chart components (Bar, Line, Doughnut)
- `@purdia/utils` — Utility functions (format, timing, misc)
- `@purdia/theme` — Dark/light mode & color scheme
- `@purdia/crypto` — Encrypted localStorage (AES-GCM via Web Crypto API)
- `@purdia/tailwind` — Shared Tailwind config & tokens

## Modules (28 total)

Lihat [FEATURES.md](./FEATURES.md) untuk daftar lengkap fitur per module.

**Productivity:**
- 📝 Notes (rich text, pin, search)
- ✅ Tasks (status, priority, recurrence, kanban)
- 📅 Calendar (events, hari libur nasional)
- 🍅 Pomodoro Timer
- 📋 Scratchpads (quick notes)
- 🎯 Habits (daily/weekly + streak)
- 📓 Journal & Mood Tracker
- 🏆 Goals & Milestones
- 📚 Reading List
- 🔖 Bookmarks
- 🎁 Wishlist
- 💬 Daily Quotes
- 🏷️ Tags (polymorphic)

**Finance & Market:**
- 💰 Finance Tracker (income/expense)
- 💳 Budget Planning
- 📈 Market Watchlist (forex, crypto, stock — Twelve Data API)
- 🪙 Emas Antam (harga harian + chart 15 tahun)
- 📊 Accounting (double-entry bookkeeping, laporan keuangan)

**Business:**
- 🛒 Point of Sale (multi-outlet, kasir, katalog, diskon, voucher, member, meja, open bill, QR order, laporan)
- 📦 Supplier Management (PO lifecycle, goods receiving, payment tracking, laporan pembelian)

**Utilities & Developer Tools:**
- 🔐 Password Vault (client-side encrypted)
- 🌐 Google Drive Integration (OAuth, backup, sync notes)
- 📐 Unit Converter (8 kategori + custom)
- 🛠️ SQL Generator (multi-dialect, frontend-only)
- 🗄️ Database Manager (browse tables, filter, edit rows, alter table)
- 📜 Log Viewer (tail log files, filter by level)
- 🗑️ Unified Trash (restore / permanent delete)

**System:**
- 🔐 Authentication (login, register, token refresh)
- ⚙️ Settings (profile, appearance, market config, export)
- 📊 Dashboard (weekly summary, weather, world clock, market & gold widgets)
- ⌨️ Quick Command (Cmd+K palette)

## Arsitektur

```
src/Modules/
├── {Module}/
│   ├── Domain/           → Entities, Enums, Contracts (pure PHP)
│   ├── Application/      → Actions (use cases), DTOs, Queries
│   └── Infrastructure/   → Controllers, Models, Repositories, Routes, Requests, Resources
└── Shared/               → Cross-module traits, contracts

app/                      → Laravel boilerplate (providers, middleware)
resources/js/             → Vue 3 SPA
packages/                 → @purdia/* internal packages
```

Dependency rule: `Domain ← Application ← Infrastructure`

## Setup

```bash
# Install dependencies & setup database
composer setup

# Atau manual:
composer install
cp .env.example .env
php artisan key:generate
php artisan migrate
npm install
npm run build

# Seed dummy data (POS + Supplier)
php artisan db:seed

# Install Chromium untuk headless browser (dipakai modul Gold)
npx puppeteer browsers install chrome
```

## Development

```bash
composer dev
```

Ini akan jalankan secara bersamaan:
- Laravel dev server (`php artisan serve`)
- Queue worker
- Log viewer (Pail)
- Vite HMR

## Scaffold Module Baru

Generate full-stack module (backend DDD + frontend) dalam satu command:

```bash
./scripts/scaffold-module.sh NamaModule
```

Menghasilkan: Entity, Contracts, Actions, DTO, Controller, Requests, Model, Repository, Resource, ServiceProvider, Routes (backend) + types, api, page (frontend).

Lihat [docs/backend.md](./docs/backend.md#scaffold-module-baru) untuk detail & next steps.

## Testing

```bash
# Feature tests
php artisan test --testsuite=Feature

# Property-based tests
php artisan test --testsuite=Property

# Unit tests
php artisan test --testsuite=Unit

# Module-specific
vendor/bin/phpunit tests/Feature/Supplier/
vendor/bin/phpunit tests/Property/Supplier/
```

Tests menggunakan SQLite in-memory.

## Build

```bash
npm run build
```

## Scheduler & Cron

```bash
# Tambahkan ke crontab (crontab -e)
* * * * * cd /path/to/project && php artisan schedule:run >> /dev/null 2>&1
```

**Scheduled commands:**

| Command | Jadwal | Keterangan |
|---------|--------|------------|
| `market:fetch-prices` | Setiap 15 menit (configurable) | Fetch harga market watchlist via Twelve Data API |
| `gold:fetch-daily` | Setiap hari jam 12:00 | Fetch harga emas Antam via headless browser |

### Prasyarat Gold Module

```bash
npx puppeteer browsers install chrome
```

Konfigurasi `.env`:
```env
ANTAM_CAPTCHA_URL=<captcha_url>
ANTAM_API_URL=<api_url>
```

> **Disclaimer:** Data harga emas diambil dari sumber publik semata-mata untuk keperluan personal
> dashboard. Penggunaan dibatasi 1x per hari.

## API

Setiap module punya route sendiri di `src/Modules/{Module}/Infrastructure/Routes/api.php`. Format response konsisten:

```json
{
  "data": { ... },
  "message": "optional",
  "meta": { "current_page": 1, "last_page": 5, "per_page": 10, "total": 50 }
}
```

Auth menggunakan Bearer token (Sanctum). Prefix: `/api/`.
