---
inclusion: always
---

# Coding Rules — Purdia Dashboard

Refer to `AGENTS.md` at project root for complete rules. Below are Kiro-specific additions.

## After Coding

- Selalu `npm run build` — pastikan build pass tanpa error.
- Run `php artisan foundry:verify` kalau ada perubahan di module structure atau cross-module imports.

## Key Docs

- `MANIFESTO.md` — foundry philosophy
- `AGENTS.md` — full coding rules (shared across all AI agents)
- `docs/backend.md` — backend architecture
- `docs/frontend.md` — frontend architecture
- `docs/foundry.md` — foundry tooling
- `docs/testing.md` — testing guide
