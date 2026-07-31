# Purdia Dashboard — AI Coding Rules

## Project Overview

Purdia is a modular foundry — every feature is built as an independent module that can eventually be extracted into a standalone package. Read `MANIFESTO.md` for the full philosophy.

## Architecture

- **DDD Layered Modular** — all business logic lives in `src/Modules/`. Each module has 3 layers: Domain, Application, Infrastructure.
- Follow **Laravel conventions** for framework-level concerns (config, middleware, console). Business logic belongs in modules.
- Every module must have all 3 layers, even simple ones.
- Module namespace: `Modules\{ModuleName}\...` (not `App\`).

## Structure

```
src/Modules/{ModuleName}/
├── Domain/           → Entities, Contracts, ValueObjects, Enums, Events
├── Application/      → Actions (use cases), DTOs, Queries
└── Infrastructure/   → Controllers, Models, Repositories, Requests, Resources, Routes, Providers
```

## Dependency Rules (CRITICAL)

- `Domain ← Application ← Infrastructure` (never reverse)
- Modules must NOT import from `App\` namespace
- Shared module must have ZERO dependencies on other modules
- User module must NOT reference feature modules (Note, Task, etc.)
- No circular dependencies between modules
- Run `php artisan foundry:verify` to check

## Backend Rules

- Thin controllers — orchestrate only, delegate to Actions
- One Action = one use case = one responsibility
- Repository pattern: interface in Domain/Contracts, implementation in Infrastructure/Repositories
- Bind interfaces in module's ServiceProvider
- Use DTOs for data transfer between layers
- Form validation in FormRequest classes
- API responses via Resource classes

## Frontend Rules

- Stack: Vue 3 + TypeScript + Inertia.js
- Reuse from `@purdia/*` packages first. Only add new if nothing exists.
- UI language: **Bahasa Indonesia** (default)
- Types/interfaces → `resources/js/types/<module>.ts`
- API calls → `resources/js/api/<module>.ts`
- Do NOT write inline interfaces or raw axios/fetch calls in page components
- `BaseModal` always uses `v-model` (not `:show`)
- Form modals must use `persistent` prop to prevent accidental close

## Conventions

- Commit messages: English, conventional commits (`feat:`, `fix:`, `refactor:`, `chore:`)
- After coding, always run `npm run build` — ensure build passes
- Model naming: `{Name}Model` (e.g., `NoteModel`, `TaskModel`)
- Entity naming: `{Name}` (e.g., `Note`, `Task`) — pure PHP, no Laravel deps

## Testing

- PHPUnit for backend
- Tests in `tests/Feature/{ModuleName}/` and `tests/Unit/{ModuleName}/`
- Each test class uses `RefreshDatabase` trait
- Run: `composer test`

## Key Docs

- `MANIFESTO.md` — foundry philosophy and principles
- `docs/backend.md` — detailed backend architecture
- `docs/frontend.md` — detailed frontend architecture
- `docs/foundry.md` — foundry tooling commands
- `docs/module-dependency-graph.md` — module dependency map
- `docs/testing.md` — testing guide
