# Coding Rules — Purdia Dashboard

## Prinsip Utama

- **DDD Layered Modular** — semua logic ada di `src/Modules/`. Setiap module punya 3 layer: Domain, Application, Infrastructure.
- Ikuti **Laravel way** untuk hal-hal yang memang framework-concern (config, middleware, console). Tapi business logic harus di module.
- **Konsisten full 3-layer** — bahkan untuk module sederhana. Tujuannya: membangun muscle memory, bukan karena complexity.
- Frontend **reuse semua dari `@purdia/*` packages** dulu. Tambah baru hanya kalau belum ada atau nggak cocok.
- Bahasa UI default: **Bahasa Indonesia**.

---

## Backend (Laravel + DDD Layered Modular)

### Struktur Module

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
│       ├── Migrations/          → Database migrations untuk module ini
│       ├── Factories/           → Model factories untuk module ini
│       └── Routes/
│           └── api.php          → Module routes
│
└── Shared/
    ├── Domain/
    │   ├── Contracts/           → BaseRepositoryInterface, shared interfaces
    │   ├── ValueObjects/        → UserId, shared value objects
    │   └── Exceptions/          → Shared domain exceptions
    └── Infrastructure/
        ├── Traits/              → Auditable, HasUuid, dll
        └── Providers/           → SharedServiceProvider

app/
├── Http/
│   └── Middleware/              → Global middleware only
├── Providers/
│   └── AppServiceProvider.php   → Register module providers
└── Console/                     → Artisan commands (kalau ada)
```

### Dependency Rule (WAJIB)

```
Domain ← Application ← Infrastructure
```

- **Domain** TIDAK BOLEH import dari Application atau Infrastructure. Murni PHP, no Laravel.
- **Application** boleh import dari Domain. TIDAK BOLEH import dari Infrastructure.
- **Infrastructure** boleh import dari semua layer (dia adapter ke framework).

### Layer Rules

#### Domain Layer
1. **Entity = pure PHP class** — tidak extend apapun dari Laravel. Berisi business rules dan state.
2. **Entity TIDAK tau database** — tidak ada `save()`, `find()`, atau query apapun.
3. **Contracts (interfaces)** — wajib ada untuk setiap repository. Meski cuma satu implementasi, ini melatih dependency inversion.
4. **Enums** — pakai PHP native enum. Taruh di Domain karena ini business concept.
5. **Events** — domain events, bukan Laravel events. Kalau perlu dispatch via Laravel, lakukan di Infrastructure.
6. **ValueObjects** — opsional, bikin kalau ada value yang butuh validation/immutability (Email, Money, dll).

#### Application Layer
1. **Actions = use case** — satu action, satu tanggung jawab. Terima DTO/primitives, return Entity/DTO.
2. **Actions depend on Contracts** — inject interface, bukan concrete repository.
3. **DTO** — bikin untuk data yang complex. Untuk simple CRUD, bisa pakai array tapi tetap typed.
4. **Queries** — opsional, untuk complex read yang butuh join/aggregate. Simple read bisa langsung di Action.

#### Infrastructure Layer
1. **Controllers thin** — validate via FormRequest, panggil Action, transform via Resource.
2. **Controllers extend `Illuminate\Routing\Controller`** — bukan `App\Http\Controllers\Controller`.
3. **Models = Eloquent** — hanya untuk persistence. Relationships, scopes, casts boleh di sini.
4. **Repository implement interface** — mapping antara Entity ↔ Model di sini.
5. **Resources** — transform Entity/Model ke API response format.
6. **Routes per-module** — setiap module punya `Routes/api.php` sendiri.
7. **Migrations per-module** — taruh di `Infrastructure/Migrations/`.
8. **Factories per-module** — taruh di `Infrastructure/Factories/`.
9. **ServiceProvider per-module** — bind interface ke concrete, register routes, load migrations.

### Entity vs Model — Contoh

```php
// src/Modules/Note/Domain/Entities/Note.php — PURE PHP
namespace Modules\Note\Domain\Entities;

use Modules\Note\Domain\Enums\NoteStatus;
use DateTimeImmutable;

class Note
{
    public function __construct(
        public ?int $id,
        public int $userId,
        public string $title,
        public string $content,
        public NoteStatus $status = NoteStatus::Active,
        public ?DateTimeImmutable $createdAt = null,
        public ?DateTimeImmutable $updatedAt = null,
    ) {}

    public function archive(): void
    {
        $this->status = NoteStatus::Archived;
    }

    public function restore(): void
    {
        $this->status = NoteStatus::Active;
    }
}

// src/Modules/Note/Infrastructure/Models/NoteModel.php — ELOQUENT
namespace Modules\Note\Infrastructure\Models;

use Illuminate\Database\Eloquent\Model;

class NoteModel extends Model
{
    protected $table = 'notes';
    protected $fillable = ['user_id', 'title', 'content', 'status'];
}

// src/Modules/Note/Infrastructure/Repositories/EloquentNoteRepository.php
namespace Modules\Note\Infrastructure\Repositories;

use Modules\Note\Domain\Contracts\NoteRepositoryInterface;
use Modules\Note\Domain\Entities\Note;
use Modules\Note\Infrastructure\Models\NoteModel;

class EloquentNoteRepository implements NoteRepositoryInterface
{
    public function save(Note $note): Note
    {
        $model = NoteModel::updateOrCreate(
            ['id' => $note->id],
            [
                'user_id' => $note->userId,
                'title' => $note->title,
                'content' => $note->content,
                'status' => $note->status->value,
            ]
        );

        return $this->toEntity($model);
    }

    private function toEntity(NoteModel $model): Note
    {
        return new Note(
            id: $model->id,
            userId: $model->user_id,
            title: $model->title,
            content: $model->content,
            status: NoteStatus::from($model->status),
            createdAt: new DateTimeImmutable($model->created_at),
            updatedAt: new DateTimeImmutable($model->updated_at),
        );
    }
}
```

### Contoh Flow: Create Note

```
HTTP Request
    ↓
[Infrastructure] NoteController → validates via StoreNoteRequest
    ↓
[Application] CreateNoteAction → receives DTO, calls repository
    ↓
[Domain] Note entity → constructs with business rules
    ↓
[Domain] NoteRepositoryInterface → contract
    ↓
[Infrastructure] EloquentNoteRepository → maps to NoteModel, persists
    ↓
Database
```

### Routes Per-Module

```php
// src/Modules/Note/Infrastructure/Routes/api.php
use Illuminate\Support\Facades\Route;
use Modules\Note\Infrastructure\Controllers\NoteController;

Route::middleware('auth:sanctum')->group(function () {
    Route::apiResource('notes', NoteController::class);
});
```

```php
// src/Modules/Note/Infrastructure/Providers/NoteServiceProvider.php
namespace Modules\Note\Infrastructure\Providers;

use Illuminate\Support\ServiceProvider;
use Modules\Note\Domain\Contracts\NoteRepositoryInterface;
use Modules\Note\Infrastructure\Repositories\EloquentNoteRepository;

class NoteServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->app->bind(NoteRepositoryInterface::class, EloquentNoteRepository::class);
    }

    public function boot(): void
    {
        $this->loadRoutesFrom(__DIR__ . '/../Routes/api.php');
        $this->loadMigrationsFrom(__DIR__ . '/../Migrations');
    }
}
```

### Rules

1. **Semua module WAJIB 3-layer** — Domain, Application, Infrastructure. No exception.
2. **Controllers harus thin** — validasi di FormRequest, logic di Action class, transform di Resource.
3. **Controllers extend `Illuminate\Routing\Controller`** — bukan `App\Http\Controllers\Controller`.
4. **Entity = pure PHP** — no Laravel dependencies. Business rules di sini.
5. **Model = Eloquent only** — persistence concern. Relationships, scopes, casts.
6. **Actions = use case** — satu action, satu tanggung jawab. Depend on interfaces, bukan concrete.
7. **Interface (Contracts) WAJIB** — setiap repository harus punya interface di Domain/Contracts.
8. **DTO wajib untuk input ke Action** — typed, immutable. Bisa pakai readonly class.
9. **API selalu return format konsisten:**
   ```json
   {
     "data": { ... },
     "message": "optional",
     "meta": { "current_page": 1, "last_page": 5, "per_page": 10, "total": 50 }
   }
   ```
10. **Sanctum** untuk auth token.
11. **Migrations di module** — taruh di `Infrastructure/Migrations/`, load via ServiceProvider.
12. **Factories di module** — taruh di `Infrastructure/Factories/`.
13. **Config, global middleware** — tetap di tempat standard Laravel (`config/`, `app/Http/Middleware/`).
14. **Enums** — pakai PHP native enum, taruh di Domain/Enums. Validate pakai `Rule::enum()` di FormRequest.
15. **Migration: JANGAN pakai enum column di DB** — simpan sebagai `string` di database.
16. **Migration: JANGAN pakai foreign key constraint** — pakai `unsignedBigInteger` tanpa `->constrained()`.
17. **ServiceProvider per-module** — register di `app/Providers/AppServiceProvider.php` atau `bootstrap/providers.php`.

### Naming Convention

- Entity: singular PascalCase (`Note`, `Task`, `User`)
- Model: `{Noun}Model` (`NoteModel`, `TaskModel`, `UserModel`)
- Action: `{Verb}{Noun}Action` (`CreateNoteAction`, `ArchiveNoteAction`)
- DTO: `{Noun}Data` (`NoteData`, `TaskData`)
- Controller: `{Noun}Controller` (`NoteController`, `TaskController`)
- FormRequest: `{Verb}{Noun}Request` (`StoreNoteRequest`, `UpdateNoteRequest`)
- Interface: `{Noun}RepositoryInterface` (`NoteRepositoryInterface`)
- Repository: `Eloquent{Noun}Repository` (`EloquentNoteRepository`)
- Enum: `{Noun}{Type}` (`NoteStatus`, `TaskPriority`)
- Event: past tense (`NoteCreated`, `TaskCompleted`)
- Resource: `{Noun}Resource` (`NoteResource`, `TaskResource`)
- ServiceProvider: `{Module}ServiceProvider` (`NoteServiceProvider`)

### Autoload (composer.json)

```json
{
  "psr-4": {
    "App\\": "app/",
    "Modules\\": "src/Modules/"
  }
}
```

### Module yang Ada

| Module | Tanggung Jawab |
|--------|---------------|
| `User` | Auth, profile, preferences |
| `Note` | Catatan / notes CRUD |
| `Bookmark` | Bookmark URLs |
| `Task` | Task management + reorder + recurrence |
| `Calendar` | Calendar events + holidays |
| `Activity` | Activity logging |
| `Pomodoro` | Pomodoro timer sessions |
| `Scratchpad` | Quick scratch notes |
| `Habit` | Habit tracking |
| `Finance` | Personal finance tracking |
| `ReadingList` | Reading list management |
| `Journal` | Daily journal entries |
| `Goal` | Goal setting & tracking |
| `Tag` | Tagging system (cross-module) |
| `Quote` | Motivational quotes |
| `Wishlist` | Wishlist items |
| `Trash` | Soft-delete / restore |
| `Shared` | Cross-module contracts, value objects, traits |

---

## Frontend (Vue 3 + TypeScript)

### Reuse dari @purdia/*

Sebelum bikin komponen atau utility baru, cek dulu:

| Kebutuhan | Pakai |
|-----------|-------|
| HTTP calls | `@purdia/http` (get, post, put, patch, del, upload, download) |
| API state (loading/error/data) | `@purdia/composables` → `useApi` |
| Paginated list | `@purdia/composables` → `usePagination` |
| Auth (login, logout, guard) | `@purdia/auth` |
| Encrypted storage | `@purdia/crypto` |
| Dark/light mode, color | `@purdia/theme` |
| UI components | `@purdia/ui` — import per-component, JANGAN dari barrel |
| Charts | `@purdia/charts` |
| Tailwind tokens | `@purdia/tailwind` |
| Icons | `@lucide/vue` — import spesifik, JANGAN import `*` |

### Import @purdia/ui components

**JANGAN** import dari barrel (`@purdia/ui`), karena akan bundle semua component termasuk Tiptap editor (400KB+).

**BENAR:**
```ts
import BaseButton from '@purdia/ui/src/components/BaseButton.vue'
import BaseModal from '@purdia/ui/src/components/BaseModal.vue'
import BaseInput from '@purdia/ui/src/components/BaseInput.vue'
```

**SALAH:**
```ts
import { BaseButton, BaseModal, BaseInput } from '@purdia/ui'
```

### Komponen @purdia/ui yang tersedia:

BaseAccordion, BaseAlert, BaseAvatar, BaseAvatarGroup, BaseBadge, BaseBreadcrumb, BaseButton, BaseCalendar, BaseCard, BaseCheckbox, BaseColorPicker, BaseCommandPalette, BaseContextMenu, BaseDatePicker, BaseDivider, BaseDrawer, BaseEditor, BaseEmptyState, BaseFileUpload, BaseInput, BaseKbd, BaseModal, BaseNotificationList, BaseNumberInput, BasePagination, BasePopover, BaseProgress, BaseRadio, BaseRating, BaseSelect, BaseSkeleton, BaseSlider, BaseSpinner, BaseSteps, BaseTable, BaseTabs, BaseTag, BaseTextarea, BaseTimeline, BaseToggle, BaseTooltip, BaseTreeView, ButtonGroup, DropdownButton, StatCard, TabPanel

**Selalu cek komponen yang tersedia di atas sebelum bikin komponen baru.**

### Struktur Frontend

```
resources/js/
├── app.ts               → Entry point
├── App.vue              → Root component
├── router/index.ts      → Route definitions
├── layouts/             → Page layouts (DashboardLayout, AuthLayout, dll)
├── pages/               → Route-level components (lazy loaded)
│   ├── auth/
│   └── {domain}/        → Grouped by domain
├── components/
│   ├── layout/          → Sidebar, Topbar, dll
│   └── {domain}/        → Domain-specific components
├── composables/         → App-level composables (useSidebar, dll)
├── config/              → Static config (navigation, constants)
├── utils/               → Helper functions
└── types/               → Shared TypeScript interfaces
```

### Rules

1. **Pages selalu lazy-loaded** via `() => import(...)` di router.
2. **Pakai `<script setup lang="ts">`** — no Options API.
3. **Icon import harus explicit** — tambah di `utils/icons.ts` registry, jangan import `*`.
4. **Styling pakai Tailwind classes** — no custom CSS kecuali animasi/transition.
5. **State management:** Pinia stores. Per-domain kalau perlu (`useUserStore`, `useOrderStore`).
6. **API calls di composable atau di page langsung** — jangan taruh di store kecuali memang shared state.
7. **TypeScript strict** — no `any`, define interfaces untuk API response.
8. **Component naming:** PascalCase, prefix `Base` untuk generic (`BaseCard`), prefix `The` untuk singleton (`TheSidebar`).

---

## Per-User Settings (Theme, Color Schema)

### Flow

```
[Database] → GET /api/me (include preferences) → [Frontend] → cache ke localStorage
                                                            → apply ke UI

[Frontend] → user ubah setting → POST /api/preferences → [Database]
                                                       → update localStorage cache
```

### Backend

- Simpan di tabel `user_preferences` atau JSON column di `users` table.
- API `/api/preferences` untuk GET dan PUT.
- Format:
  ```json
  {
    "theme": "dark",
    "primary_color": "indigo",
    "locale": "id",
    "sidebar_collapsed": false
  }
  ```

### Frontend

- Saat login berhasil atau app init: fetch preferences dari API, cache di localStorage.
- `@purdia/theme` store baca dari cache dulu (instant), lalu sync kalau ada update dari server.
- Kalau user ubah theme/color: update UI langsung + fire API call (optimistic update).
- Cache key format: `preferences:{user_id}` di localStorage (plain JSON, bukan encrypted — ini bukan sensitive data).

---

## General

- **Commit messages:** Bahasa Inggris, conventional commits (`feat:`, `fix:`, `refactor:`, `chore:`).
- **No premature abstraction** — tapi tetap konsisten 3-layer. Layer boleh "tipis" (misal Entity cuma constructor), tapi harus ada.
- **Error handling:** Backend return proper HTTP status + message. Frontend catch dan tampilkan via toast/alert.
- **Validation:** Backend SELALU validate (single source of truth). Frontend validate untuk UX aja.
- **Setelah selesai coding, selalu `npm run build`** — pastikan build pass tanpa error sebelum dianggap selesai.

---

## Testing

### Struktur

```
tests/
├── Unit/
│   └── {Module}/
│       └── Domain/              → Test entities, value objects, business rules
├── Feature/
│   └── {Module}/
│       └── {ActionTest}.php     → Test full flow (HTTP → Action → DB)
└── TestCase.php
```

### Rules

1. **Setiap module HARUS punya test** — minimal feature test untuk CRUD + authorization.
2. **Unit test untuk Domain layer** — test entity business rules tanpa database.
3. **Feature test untuk full flow** — HTTP request → response, pakai database.
4. **Kalau ada perubahan di module, test-nya juga HARUS di-update**.
5. **Test harus cover:** CRUD operations, validation, authorization (ownership check), edge cases.
6. **Run tests sebelum dianggap selesai:** `php artisan test` — semua harus pass.
7. **Factory per module** — taruh di `Infrastructure/Factories/`.
8. **Gunakan `RefreshDatabase` trait** — setiap test class reset database.
9. **Naming convention:** `test_{action}_{context}` (e.g., `test_user_can_create_note`).
10. **Auth di test:** Pakai `$this->actingAs($user)` untuk authenticated requests.
11. **Setiap endpoint yang butuh auth harus ada test unauthenticated returns 401.**
12. **Setiap endpoint yang punya ownership check harus ada test returns 403 untuk user lain.**
