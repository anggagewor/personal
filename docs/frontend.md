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
| Auth (login, logout, guard, configure) | `@purdia/auth` |
| Encrypted storage | `@purdia/crypto` |
| Dark/light mode, color | `@purdia/theme` |
| UI components | `@purdia/ui` — import per-component, JANGAN dari barrel |
| Charts | `@purdia/charts` (LineChart, BarChart, DoughnutChart) |
| Utilities (format, timing, misc) | `@purdia/utils` |
| Tailwind tokens | `@purdia/tailwind` |
| Icons | `@lucide/vue` — import spesifik |

## Package Exports

### @purdia/http

```ts
import { initHttp, get, post, put, patch, del, upload, download, useHttp } from '@purdia/http'
import type { ApiResponse, PaginationMeta, ApiError, HttpClientConfig } from '@purdia/http'
```

### @purdia/auth

```ts
import { useAuthStore, configureAuth, createAuthGuard } from '@purdia/auth'
import type { User, AuthConfig, AuthGuardOptions } from '@purdia/auth'
```

### @purdia/toast

```ts
import { useToast, useToastStore, ToastContainer } from '@purdia/toast'
import type { Toast, ToastOptions, ToastVariant, ToastPosition } from '@purdia/toast'
```

### @purdia/theme

```ts
import { useThemeStore, configureTheme, colorOptions } from '@purdia/theme'
import type { Theme, PrimaryColor, ColorOption, ThemeConfig } from '@purdia/theme'
```

### @purdia/crypto

```ts
import { configureSecureStorage, secureSet, secureGet, secureRemove, secureClearAll } from '@purdia/crypto'
import type { SecureStorageConfig } from '@purdia/crypto'
```

### @purdia/composables

```ts
import { useApi, usePagination } from '@purdia/composables'
import type { UseApiReturn, UseApiOptions, UsePaginationReturn, UsePaginationOptions, PaginationParams } from '@purdia/composables'
```

### @purdia/utils

```ts
import { formatCurrency, formatNumber, formatDate, formatRelativeTime } from '@purdia/utils'
import { debounce, throttle, sleep } from '@purdia/utils'
import { clamp, randomInt, uid } from '@purdia/utils'
```

### @purdia/charts

```ts
import { LineChart, BarChart, DoughnutChart } from '@purdia/charts'
```

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
├── app.ts               → Entry point (initHttp, configureAuth, configureTheme, dll)
├── App.vue              → Root component + ToastContainer
├── env.d.ts             → Vite env type declarations
├── router/index.ts      → Route definitions (lazy loaded)
├── layouts/
│   └── DashboardLayout.vue  → Layout utama (sidebar + topbar + content)
├── pages/               → Route-level components
│   ├── Dashboard.vue
│   ├── auth/            → Login.vue, Register.vue (standalone, tanpa layout)
│   ├── notes/
│   ├── tasks/
│   ├── bookmarks/
│   ├── calendar/
│   ├── pomodoro/
│   ├── scratchpads/
│   ├── habits/
│   ├── finance/
│   ├── reading-list/
│   ├── journal/
│   ├── goals/
│   ├── quotes/
│   ├── wishlist/
│   ├── streaks/
│   ├── trash/
│   ├── activity/
│   └── settings/        → General.vue, Appearance.vue, Account.vue, Export.vue
├── components/
│   ├── layout/          → TheSidebar, TheTopbar, SidebarItem, CommandPalette
│   ├── calendar/        → CalendarDayView, CalendarWeekView, CalendarYearView
│   └── TheCommandPalette.vue
├── composables/         → useCommandPalette, usePreferences, useProfile, useSidebar
├── config/
│   └── navigation.ts   → Sidebar navigation config
└── utils/
    └── icons.ts         → Centralized icon imports
```

## Rules

1. Pages selalu lazy-loaded via `() => import(...)` di router.
2. Pakai `<script setup lang="ts">` — no Options API.
3. Icon import dari `@lucide/vue` — import spesifik per-icon yang dipakai.
4. Styling pakai Tailwind classes — no custom CSS kecuali animasi/transition.
5. State management: Pinia stores per-domain kalau perlu.
6. API calls di page langsung atau composable — jangan di store kecuali shared state.
7. TypeScript strict — no `any`, define interfaces untuk API response.
8. Component naming: PascalCase, prefix `Base` untuk generic, prefix `The` untuk singleton.
9. Error handling: `catch` block wajib ada di setiap async function. Error toast otomatis dari `@purdia/http` onError. Success toast manual via `useToast().success()`.
10. Modal: pakai `BaseModal` component. JANGAN bungkus slot content dengan `<div class="p-6">` (BaseModal sudah kasih padding).
11. Auth pages (Login, Register) pakai `meta: { guest: true }` — standalone tanpa DashboardLayout.
12. Protected routes otomatis dibungkus DashboardLayout — guard dari `createAuthGuard`.

## Per-User Settings Flow

```
Login → GET /api/me (include preferences) → cache di localStorage → apply ke UI
User ubah → POST /api/preferences → update localStorage + UI (optimistic)
```

## Packages Tooling (internal)

| Package | Fungsi |
|---------|--------|
| `@purdia/eslint-config` | Shared ESLint config |
| `@purdia/tsconfig` | Shared TypeScript config |
