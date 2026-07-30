---
inclusion: always
---

# Coding Rules — Purdia Dashboard

## Philosophy

- **DDD Layered Modular** — semua logic ada di `src/Modules/`. Setiap module punya 3 layer: Domain, Application, Infrastructure.
- Ikuti **Laravel way** untuk framework-concern (config, middleware, console). Business logic harus di module.
- **Konsisten full 3-layer** — bahkan untuk module sederhana.
- Frontend **reuse dari `@purdia/*` packages** dulu. Tambah baru hanya kalau belum ada.
- Bahasa UI default: **Bahasa Indonesia**.
- **Commit messages:** Bahasa Inggris, conventional commits (`feat:`, `fix:`, `refactor:`, `chore:`).
- **Setelah selesai coding, selalu `npm run build`** — pastikan build pass tanpa error.

## Backend

Lihat dokumentasi lengkap di [docs/backend.md](docs/backend.md).

## Frontend

Lihat dokumentasi lengkap di [docs/frontend.md](docs/frontend.md).

### Modal Rules

- `BaseModal` selalu pakai `v-model` (bukan `:show` atau prop lain).
- Untuk form modal (create/edit), selalu pakai `persistent` prop agar tidak tertutup saat klik di luar modal. Ini mencegah kehilangan data yang sudah diisi.
- Contoh: `<BaseModal v-model="showForm" size="md" persistent>`

## Testing

Lihat dokumentasi lengkap di [docs/testing.md](docs/testing.md).
