# Frontend — Vue 3 + TypeScript

## Reuse dari @purdia/*

Sebelum bikin komponen atau utility baru, cek dulu:

| Kebutuhan | Pakai |
|-----------|-------|
| HTTP calls | `@purdia/http` (get, post, put, patch, del, upload, download) |
| API error handling | `@purdia/http` → `onError` callback (otomatis show toast) |
| Toast notifications | `@purdia/toast` → `useToast()` |
| API state (loading/error/data) | `@purdia/composables` → `useApi` |
| Paginated list | `@purdia/composables` → `usePagination` |
| Auth (login, logout, guard) | `@purdia/auth` |
| Encrypted storage | `@purdia/crypto` |
| Dark/light mode, color | `@purdia/theme` |
| UI components | `@purdia/ui` — import per-component, JANGAN dari barrel |
| Charts | `@purdia/charts` |
| Tailwind tokens | `@purdia/tailwind` |
| Icons | `@lucide/vue` — import spesifik, register di `utils/icons.ts` |

## Import @purdia/ui components

**BENAR:**
```ts
import BaseButton from '@purdia/ui/src/components/BaseButton.vue'
import BaseModal from '@purdia/ui/src/components/BaseModal.vue'
```

**SALAH (bundle semua termasuk Tiptap 400KB+):**
```ts
import { BaseButton, BaseModal } from '@purdia/ui'
```

## Komponen @purdia/ui yang tersedia

BaseAccordion, BaseAlert, BaseAvatar, BaseAvatarGroup, BaseBadge, BaseBreadcrumb, BaseButton, BaseCalendar, BaseCard, BaseCheckbox, BaseColorPicker, BaseCommandPalette, BaseContextMenu, BaseDatePicker, BaseDivider, BaseDrawer, BaseEditor, BaseEmptyState, BaseFileUpload, BaseInput, BaseKbd, BaseModal, BaseNotificationList, BaseNumberInput, BasePagination, BasePopover, BaseProgress, BaseRadio, BaseRating, BaseSelect, BaseSkeleton, BaseSlider, BaseSpinner, BaseSteps, BaseTable, BaseTabs, BaseTag, BaseTextarea, BaseTimeline, BaseToggle, BaseTooltip, BaseTreeView, ButtonGroup, DropdownButton, StatCard, TabPanel

## Struktur Frontend

```
resources/js/
├── app.ts               → Entry point (initHttp, configureAuth, dll)
├── App.vue              → Root component + ToastContainer
├── router/index.ts      → Route definitions (lazy loaded)
├── layouts/             → DashboardLayout, AuthLayout
├── pages/               → Route-level components
│   └── {domain}/        → Grouped by domain (notes/, tasks/, dll)
├── components/
│   ├── layout/          → TheSidebar, TheTopbar, SidebarItem
│   └── {domain}/        → Domain-specific components
├── composables/         → App-level composables (useSidebar, usePreferences)
├── config/              → navigation.ts, constants
├── utils/               → icons.ts, helpers
└── types/               → Shared TypeScript interfaces
```

## Rules

1. Pages selalu lazy-loaded via `() => import(...)` di router.
2. Pakai `<script setup lang="ts">` — no Options API.
3. Icon import harus explicit — register di `utils/icons.ts`, jangan import `*`.
4. Styling pakai Tailwind classes — no custom CSS kecuali animasi/transition.
5. State management: Pinia stores per-domain kalau perlu.
6. API calls di page langsung atau composable — jangan di store kecuali shared state.
7. TypeScript strict — no `any`, define interfaces untuk API response.
8. Component naming: PascalCase, prefix `Base` untuk generic, prefix `The` untuk singleton.
9. Error handling: `catch` block wajib ada di setiap async function. Error toast otomatis dari `@purdia/http` onError. Success toast manual via `useToast().success()`.
10. Modal: pakai `BaseModal` component. JANGAN bungkus slot content dengan `<div class="p-6">` (BaseModal sudah kasih padding).

## Per-User Settings Flow

```
Login → GET /api/me (include preferences) → cache di localStorage → apply ke UI
User ubah → POST /api/preferences → update localStorage + UI (optimistic)
```
