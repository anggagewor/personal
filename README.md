# Purdia Dashboard

Personal productivity dashboard yang dibangun dengan Laravel 13 (DDD Modular) dan Vue 3 + TypeScript.

## Tech Stack

**Backend:**
- PHP 8.3 + Laravel 13
- DDD Modular Architecture (`src/Domain/`)
- Laravel Sanctum (token-based auth)
- SQLite / MySQL

**Frontend:**
- Vue 3 + TypeScript (Composition API)
- Pinia (state management)
- Vue Router
- Tailwind CSS 4
- Vite 8

**Internal Packages (monorepo `packages/`):**
- `@purdia/auth` — Authentication store & route guard
- `@purdia/http` — HTTP client wrapper
- `@purdia/composables` — Reusable composables (useApi, usePagination, dll)
- `@purdia/ui` — Component library (40+ komponen)
- `@purdia/charts` — Chart components (Bar, Line, Doughnut)
- `@purdia/theme` — Dark/light mode & color scheme
- `@purdia/crypto` — Encrypted localStorage
- `@purdia/tailwind` — Shared Tailwind config & tokens

## Fitur

Lihat [FEATURES.md](./FEATURES.md) untuk daftar lengkap fitur.

**Ringkasan:**
- 🔐 Authentication (login, register, token refresh)
- 📝 Notes dengan rich text editor & pin
- 🔖 Bookmarks dengan kategori
- ✅ Task management (status, priority, drag reorder, recurring)
- 📅 Calendar events & hari libur nasional
- 🍅 Pomodoro timer
- 📋 Scratchpads (quick notes)
- 🎯 Habit tracker (daily/weekly)
- 💰 Finance tracker (income/expense)
- 📚 Reading list
- 📓 Daily journal & mood tracker
- 🏆 Goals & milestones
- 🎁 Wishlist
- 🏷️ Tags (polymorphic, bisa attach ke item apapun)
- 💬 Daily motivational quotes
- 🗑️ Unified trash (restore / permanent delete)
- ⚙️ Settings (profile, appearance, export)

## Arsitektur

```
src/Domain/          → Domain layer (controllers, models, actions, enums)
app/                 → Laravel boilerplate (providers, middleware)
resources/js/        → Vue 3 SPA
packages/            → @purdia/* internal packages
routes/api.php       → API routes
database/migrations/ → Database schema
```

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

## Build

```bash
npm run build
```

## API

Semua API endpoint ada di `routes/api.php`. Format response konsisten:

```json
{
  "data": { ... },
  "message": "optional",
  "meta": { "current_page": 1, "last_page": 5, "per_page": 10, "total": 50 }
}
```

Auth menggunakan Bearer token (Sanctum). Prefix: `/api/`.
