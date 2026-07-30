#!/bin/bash

# ============================================================================
# scaffold-module.sh — Scaffold baru module full-stack (Backend DDD + Frontend)
#
# Usage:
#   ./scripts/scaffold-module.sh ModuleName
#   ./scripts/scaffold-module.sh ModuleName --entity "field1:type field2:type"
#
# Examples:
#   ./scripts/scaffold-module.sh Expense
#   ./scripts/scaffold-module.sh Expense --entity "title:string amount:int category:string date:string description:?string"
#
# Type syntax:
#   field:type       → required field
#   field:?type      → nullable field
#   Supported types: string, int, float, bool, array
# ============================================================================

set -e

# --- Colors ---
RED='\033[0;31m'
GREEN='\033[0;32m'
YELLOW='\033[1;33m'
CYAN='\033[0;36m'
NC='\033[0m'

# --- Helpers ---
info() { echo -e "${CYAN}[INFO]${NC} $1"; }
success() { echo -e "${GREEN}[OK]${NC} $1"; }
warn() { echo -e "${YELLOW}[WARN]${NC} $1"; }
error() { echo -e "${RED}[ERROR]${NC} $1"; exit 1; }

# --- Parse Args ---
MODULE_NAME="$1"
ENTITY_FIELDS=""

if [ -z "$MODULE_NAME" ]; then
  error "Usage: ./scripts/scaffold-module.sh ModuleName [--entity \"field:type ...\"]"
fi

shift
while [[ $# -gt 0 ]]; do
  case $1 in
    --entity)
      ENTITY_FIELDS="$2"
      shift 2
      ;;
    *)
      error "Unknown option: $1"
      ;;
  esac
done

# --- Derive names ---
# PascalCase module name (as given)
MODULE="$MODULE_NAME"
# camelCase
MODULE_CAMEL="$(echo "${MODULE:0:1}" | tr '[:upper:]' '[:lower:]')${MODULE:1}"
# kebab-case for frontend
MODULE_KEBAB=$(echo "$MODULE" | sed 's/\([A-Z]\)/-\1/g' | sed 's/^-//' | tr '[:upper:]' '[:lower:]')
# snake_case for DB table
MODULE_SNAKE=$(echo "$MODULE" | sed 's/\([A-Z]\)/_\1/g' | sed 's/^_//' | tr '[:upper:]' '[:lower:]')
TABLE_NAME="${MODULE_SNAKE}s"

# Paths
BE_BASE="src/Modules/$MODULE"
FE_TYPES="resources/js/types"
FE_API="resources/js/api"
FE_PAGES="resources/js/pages/$MODULE_KEBAB"

info "Scaffolding module: $MODULE"
info "  Backend:  $BE_BASE/"
info "  Frontend: $FE_PAGES/, $FE_TYPES/${MODULE_KEBAB}.ts, $FE_API/${MODULE_KEBAB}.ts"
echo ""

# ============================================================================
# BACKEND
# ============================================================================

# --- Create directory structure ---
mkdir -p "$BE_BASE/Domain/Entities"
mkdir -p "$BE_BASE/Domain/Contracts"
mkdir -p "$BE_BASE/Application/Actions"
mkdir -p "$BE_BASE/Application/DTO"
mkdir -p "$BE_BASE/Infrastructure/Controllers"
mkdir -p "$BE_BASE/Infrastructure/Requests"
mkdir -p "$BE_BASE/Infrastructure/Models"
mkdir -p "$BE_BASE/Infrastructure/Repositories"
mkdir -p "$BE_BASE/Infrastructure/Resources"
mkdir -p "$BE_BASE/Infrastructure/Providers"
mkdir -p "$BE_BASE/Infrastructure/Routes"

success "Backend directories created"

# --- Domain/Entities ---
cat > "$BE_BASE/Domain/Entities/$MODULE.php" << 'ENTITY_EOF'
<?php

namespace Modules\__MODULE__\Domain\Entities;

use DateTimeImmutable;

class __MODULE__
{
    public function __construct(
        public ?int $id,
        public int $userId,
        // TODO: Add entity properties
        public ?DateTimeImmutable $createdAt = null,
        public ?DateTimeImmutable $updatedAt = null,
    ) {}
}
ENTITY_EOF
sed -i '' "s/__MODULE__/$MODULE/g" "$BE_BASE/Domain/Entities/$MODULE.php"

# --- Domain/Contracts ---
cat > "$BE_BASE/Domain/Contracts/${MODULE}RepositoryInterface.php" << 'IFACE_EOF'
<?php

namespace Modules\__MODULE__\Domain\Contracts;

use Modules\__MODULE__\Domain\Entities\__MODULE__;

interface __MODULE__RepositoryInterface
{
    public function findById(int $id): ?__MODULE__;

    public function findByUserPaginated(int $userId, int $perPage = 15): array;

    public function save(__MODULE__ $entity): __MODULE__;

    public function delete(int $id): void;
}
IFACE_EOF
sed -i '' "s/__MODULE__/$MODULE/g" "$BE_BASE/Domain/Contracts/${MODULE}RepositoryInterface.php"

# --- Application/DTO ---
cat > "$BE_BASE/Application/DTO/${MODULE}Data.php" << 'DTO_EOF'
<?php

namespace Modules\__MODULE__\Application\DTO;

readonly class __MODULE__Data
{
    public function __construct(
        // TODO: Add DTO properties
    ) {}

    public static function fromArray(array $data): self
    {
        return new self(
            // TODO: Map from array
        );
    }
}
DTO_EOF
sed -i '' "s/__MODULE__/$MODULE/g" "$BE_BASE/Application/DTO/${MODULE}Data.php"

# --- Application/Actions ---
cat > "$BE_BASE/Application/Actions/Create${MODULE}Action.php" << 'ACTION_CREATE_EOF'
<?php

namespace Modules\__MODULE__\Application\Actions;

use Modules\__MODULE__\Application\DTO\__MODULE__Data;
use Modules\__MODULE__\Domain\Contracts\__MODULE__RepositoryInterface;
use Modules\__MODULE__\Domain\Entities\__MODULE__;

class Create__MODULE__Action
{
    public function __construct(
        private __MODULE__RepositoryInterface $repository,
    ) {}

    public function execute(int $userId, __MODULE__Data $data): __MODULE__
    {
        $entity = new __MODULE__(
            id: null,
            userId: $userId,
            // TODO: Map DTO fields to entity
        );

        return $this->repository->save($entity);
    }
}
ACTION_CREATE_EOF
sed -i '' "s/__MODULE__/$MODULE/g" "$BE_BASE/Application/Actions/Create${MODULE}Action.php"

cat > "$BE_BASE/Application/Actions/Update${MODULE}Action.php" << 'ACTION_UPDATE_EOF'
<?php

namespace Modules\__MODULE__\Application\Actions;

use Modules\__MODULE__\Application\DTO\__MODULE__Data;
use Modules\__MODULE__\Domain\Contracts\__MODULE__RepositoryInterface;
use Modules\__MODULE__\Domain\Entities\__MODULE__;

class Update__MODULE__Action
{
    public function __construct(
        private __MODULE__RepositoryInterface $repository,
    ) {}

    public function execute(int $id, __MODULE__Data $data): __MODULE__
    {
        $entity = $this->repository->findById($id);

        // TODO: Update entity fields from $data

        return $this->repository->save($entity);
    }
}
ACTION_UPDATE_EOF
sed -i '' "s/__MODULE__/$MODULE/g" "$BE_BASE/Application/Actions/Update${MODULE}Action.php"

cat > "$BE_BASE/Application/Actions/Delete${MODULE}Action.php" << 'ACTION_DELETE_EOF'
<?php

namespace Modules\__MODULE__\Application\Actions;

use Modules\__MODULE__\Domain\Contracts\__MODULE__RepositoryInterface;

class Delete__MODULE__Action
{
    public function __construct(
        private __MODULE__RepositoryInterface $repository,
    ) {}

    public function execute(int $id): void
    {
        $this->repository->delete($id);
    }
}
ACTION_DELETE_EOF
sed -i '' "s/__MODULE__/$MODULE/g" "$BE_BASE/Application/Actions/Delete${MODULE}Action.php"

# --- Infrastructure/Controllers ---
cat > "$BE_BASE/Infrastructure/Controllers/${MODULE}Controller.php" << 'CTRL_EOF'
<?php

namespace Modules\__MODULE__\Infrastructure\Controllers;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Modules\__MODULE__\Application\Actions\Create__MODULE__Action;
use Modules\__MODULE__\Application\Actions\Delete__MODULE__Action;
use Modules\__MODULE__\Application\Actions\Update__MODULE__Action;
use Modules\__MODULE__\Application\DTO\__MODULE__Data;
use Modules\__MODULE__\Domain\Contracts\__MODULE__RepositoryInterface;
use Modules\__MODULE__\Infrastructure\Requests\Store__MODULE__Request;
use Modules\__MODULE__\Infrastructure\Requests\Update__MODULE__Request;
use Modules\__MODULE__\Infrastructure\Resources\__MODULE__Resource;
use Modules\Shared\Infrastructure\Traits\AuthorizesOwnership;

class __MODULE__Controller extends Controller
{
    use AuthorizesOwnership;

    public function __construct(
        private __MODULE__RepositoryInterface $repository,
    ) {}

    public function index(Request $request): JsonResponse
    {
        $result = $this->repository->findByUserPaginated(
            userId: $request->user()->id,
            perPage: (int) $request->query('per_page', 15),
        );

        return response()->json([
            'data' => __MODULE__Resource::collection($result['data']),
            'meta' => $result['meta'],
        ]);
    }

    public function store(Store__MODULE__Request $request, Create__MODULE__Action $action): JsonResponse
    {
        $entity = $action->execute(
            userId: $request->user()->id,
            data: __MODULE__Data::fromArray($request->validated()),
        );

        return response()->json([
            'data' => __MODULE__Resource::toArray($entity),
        ], 201);
    }

    public function update(Update__MODULE__Request $request, int $id, Update__MODULE__Action $action): JsonResponse
    {
        $this->findOwnedOrFail($this->repository, $id, $request);

        $entity = $action->execute($id, __MODULE__Data::fromArray($request->validated()));

        return response()->json([
            'data' => __MODULE__Resource::toArray($entity),
        ]);
    }

    public function destroy(Request $request, int $id, Delete__MODULE__Action $action): JsonResponse
    {
        $this->findOwnedOrFail($this->repository, $id, $request);

        $action->execute($id);

        return response()->json(['message' => 'Berhasil dihapus.']);
    }
}
CTRL_EOF
sed -i '' "s/__MODULE__/$MODULE/g" "$BE_BASE/Infrastructure/Controllers/${MODULE}Controller.php"

# --- Infrastructure/Requests ---
cat > "$BE_BASE/Infrastructure/Requests/Store${MODULE}Request.php" << 'REQ_STORE_EOF'
<?php

namespace Modules\__MODULE__\Infrastructure\Requests;

use Illuminate\Foundation\Http\FormRequest;

class Store__MODULE__Request extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            // TODO: Define validation rules
        ];
    }
}
REQ_STORE_EOF
sed -i '' "s/__MODULE__/$MODULE/g" "$BE_BASE/Infrastructure/Requests/Store${MODULE}Request.php"

cat > "$BE_BASE/Infrastructure/Requests/Update${MODULE}Request.php" << 'REQ_UPDATE_EOF'
<?php

namespace Modules\__MODULE__\Infrastructure\Requests;

use Illuminate\Foundation\Http\FormRequest;

class Update__MODULE__Request extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            // TODO: Define validation rules
        ];
    }
}
REQ_UPDATE_EOF
sed -i '' "s/__MODULE__/$MODULE/g" "$BE_BASE/Infrastructure/Requests/Update${MODULE}Request.php"

# --- Infrastructure/Models ---
cat > "$BE_BASE/Infrastructure/Models/${MODULE}Model.php" << 'MODEL_EOF'
<?php

namespace Modules\__MODULE__\Infrastructure\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class __MODULE__Model extends Model
{
    protected $table = '__TABLE_NAME__';

    protected $fillable = [
        'user_id',
        // TODO: Add fillable fields
    ];

    protected function casts(): array
    {
        return [
            // TODO: Add casts
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(\Modules\User\Infrastructure\Models\UserModel::class, 'user_id');
    }
}
MODEL_EOF
sed -i '' "s/__MODULE__/$MODULE/g" "$BE_BASE/Infrastructure/Models/${MODULE}Model.php"
sed -i '' "s/__TABLE_NAME__/$TABLE_NAME/g" "$BE_BASE/Infrastructure/Models/${MODULE}Model.php"

# --- Infrastructure/Repositories ---
cat > "$BE_BASE/Infrastructure/Repositories/Eloquent${MODULE}Repository.php" << 'REPO_EOF'
<?php

namespace Modules\__MODULE__\Infrastructure\Repositories;

use DateTimeImmutable;
use Modules\__MODULE__\Domain\Contracts\__MODULE__RepositoryInterface;
use Modules\__MODULE__\Domain\Entities\__MODULE__;
use Modules\__MODULE__\Infrastructure\Models\__MODULE__Model;

class Eloquent__MODULE__Repository implements __MODULE__RepositoryInterface
{
    public function findById(int $id): ?__MODULE__
    {
        $model = __MODULE__Model::find($id);
        return $model ? $this->toEntity($model) : null;
    }

    public function findByUserPaginated(int $userId, int $perPage = 15): array
    {
        $paginator = __MODULE__Model::where('user_id', $userId)
            ->orderByDesc('created_at')
            ->paginate($perPage);

        return [
            'data' => array_map(fn ($m) => $this->toEntity($m), $paginator->items()),
            'meta' => [
                'current_page' => $paginator->currentPage(),
                'last_page' => $paginator->lastPage(),
                'per_page' => $paginator->perPage(),
                'total' => $paginator->total(),
            ],
        ];
    }

    public function save(__MODULE__ $entity): __MODULE__
    {
        $model = __MODULE__Model::updateOrCreate(
            ['id' => $entity->id],
            [
                'user_id' => $entity->userId,
                // TODO: Map entity fields to model
            ]
        );

        return $this->toEntity($model->fresh());
    }

    public function delete(int $id): void
    {
        __MODULE__Model::where('id', $id)->delete();
    }

    private function toEntity(__MODULE__Model $model): __MODULE__
    {
        return new __MODULE__(
            id: $model->id,
            userId: $model->user_id,
            // TODO: Map model fields to entity
            createdAt: $model->created_at ? new DateTimeImmutable($model->created_at->toDateTimeString()) : null,
            updatedAt: $model->updated_at ? new DateTimeImmutable($model->updated_at->toDateTimeString()) : null,
        );
    }
}
REPO_EOF
sed -i '' "s/__MODULE__/$MODULE/g" "$BE_BASE/Infrastructure/Repositories/Eloquent${MODULE}Repository.php"

# --- Infrastructure/Resources ---
cat > "$BE_BASE/Infrastructure/Resources/${MODULE}Resource.php" << 'RES_EOF'
<?php

namespace Modules\__MODULE__\Infrastructure\Resources;

use Modules\__MODULE__\Domain\Entities\__MODULE__;

class __MODULE__Resource
{
    public static function toArray(__MODULE__ $entity): array
    {
        return [
            'id' => $entity->id,
            // TODO: Map entity to response array
            'created_at' => $entity->createdAt?->format('Y-m-d\TH:i:s.000000\Z'),
            'updated_at' => $entity->updatedAt?->format('Y-m-d\TH:i:s.000000\Z'),
        ];
    }

    public static function collection(array $entities): array
    {
        return array_map(fn (__MODULE__ $e) => self::toArray($e), $entities);
    }
}
RES_EOF
sed -i '' "s/__MODULE__/$MODULE/g" "$BE_BASE/Infrastructure/Resources/${MODULE}Resource.php"

# --- Infrastructure/Providers ---
cat > "$BE_BASE/Infrastructure/Providers/${MODULE}ServiceProvider.php" << 'PROV_EOF'
<?php

namespace Modules\__MODULE__\Infrastructure\Providers;

use Illuminate\Support\Facades\Route;
use Illuminate\Support\ServiceProvider;
use Modules\__MODULE__\Domain\Contracts\__MODULE__RepositoryInterface;
use Modules\__MODULE__\Infrastructure\Repositories\Eloquent__MODULE__Repository;

class __MODULE__ServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->app->bind(__MODULE__RepositoryInterface::class, Eloquent__MODULE__Repository::class);
    }

    public function boot(): void
    {
        Route::prefix('api')->group(__DIR__ . '/../Routes/api.php');
    }
}
PROV_EOF
sed -i '' "s/__MODULE__/$MODULE/g" "$BE_BASE/Infrastructure/Providers/${MODULE}ServiceProvider.php"

# --- Infrastructure/Routes ---
cat > "$BE_BASE/Infrastructure/Routes/api.php" << ROUTES_EOF
<?php

use Illuminate\Support\Facades\Route;
use Modules\\${MODULE}\Infrastructure\Controllers\\${MODULE}Controller;

Route::middleware('auth:sanctum')->group(function () {
    Route::apiResource('${MODULE_KEBAB}s', ${MODULE}Controller::class);
});
ROUTES_EOF

success "Backend module files created"

# ============================================================================
# FRONTEND
# ============================================================================

mkdir -p "$FE_PAGES"

# --- types ---
cat > "$FE_TYPES/$MODULE_KEBAB.ts" << 'FE_TYPES_EOF'
export interface __MODULE__ {
  id: number
  // TODO: Add interface properties
  created_at: string
  updated_at: string
}

export interface __MODULE__Payload {
  // TODO: Add payload properties
}
FE_TYPES_EOF
sed -i '' "s/__MODULE__/$MODULE/g" "$FE_TYPES/$MODULE_KEBAB.ts"

# --- api ---
cat > "$FE_API/$MODULE_KEBAB.ts" << FE_API_EOF
import { get, post, put, del } from '@purdia/http'
import type { ${MODULE}, ${MODULE}Payload } from '@/types/${MODULE_KEBAB}'

export function fetchAll(params?: Record<string, unknown>) {
  return get<${MODULE}[]>('/${MODULE_KEBAB}s', { params })
}

export function create(payload: ${MODULE}Payload) {
  return post<${MODULE}>('/${MODULE_KEBAB}s', payload)
}

export function update(id: number, payload: Partial<${MODULE}Payload>) {
  return put<${MODULE}>(\`/${MODULE_KEBAB}s/\${id}\`, payload)
}

export function remove(id: number) {
  return del(\`/${MODULE_KEBAB}s/\${id}\`)
}
FE_API_EOF

# --- page ---
cat > "$FE_PAGES/Index.vue" << FE_PAGE_EOF
<script setup lang="ts">
import { ref } from 'vue'
import { useToast } from '@purdia/toast'
import BaseButton from '@purdia/ui/src/components/BaseButton.vue'
import BaseModal from '@purdia/ui/src/components/BaseModal.vue'
import BaseInput from '@purdia/ui/src/components/BaseInput.vue'
import { Plus, Trash2 } from '@lucide/vue'
import type { ${MODULE} } from '@/types/${MODULE_KEBAB}'
import * as ${MODULE_CAMEL}Api from '@/api/${MODULE_KEBAB}'

const toast = useToast()

const items = ref<${MODULE}[]>([])
const loading = ref(false)
const showForm = ref(false)

const form = ref({
  // TODO: Define form fields
})

async function fetchItems() {
  loading.value = true
  try {
    const res = await ${MODULE_CAMEL}Api.fetchAll()
    items.value = res.data
  } catch {
    // Error toast handled globally by @purdia/http onError
  }
  loading.value = false
}

async function saveItem() {
  try {
    await ${MODULE_CAMEL}Api.create(form.value as any)
    toast.success('Berhasil disimpan.')
    showForm.value = false
    fetchItems()
  } catch {
    // Error toast handled globally by @purdia/http onError
  }
}

async function deleteItem(item: ${MODULE}) {
  try {
    await ${MODULE_CAMEL}Api.remove(item.id)
    items.value = items.value.filter((i) => i.id !== item.id)
    toast.success('Berhasil dihapus.')
  } catch {
    // Error toast handled globally by @purdia/http onError
  }
}

fetchItems()
</script>

<template>
  <div>
    <div class="flex items-center justify-between">
      <div>
        <h1 class="text-2xl font-semibold text-gray-900 dark:text-white">${MODULE}</h1>
        <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">TODO: Deskripsi module.</p>
      </div>
      <BaseButton variant="primary" size="sm" :icon="Plus" @click="showForm = true">Tambah</BaseButton>
    </div>

    <!-- List -->
    <div v-if="items.length" class="mt-6 space-y-2">
      <div
        v-for="item in items"
        :key="item.id"
        class="group flex items-center gap-4 rounded-lg border border-gray-200 bg-white px-5 py-3 dark:border-gray-700 dark:bg-gray-800"
      >
        <div class="flex-1 min-w-0">
          <p class="text-sm font-medium text-gray-900 dark:text-white">{{ item.id }}</p>
        </div>
        <button class="rounded p-1 text-gray-400 opacity-0 group-hover:opacity-100 hover:text-red-500" @click="deleteItem(item)">
          <Trash2 :size="14" />
        </button>
      </div>
    </div>

    <!-- Empty -->
    <div v-else-if="!loading" class="mt-12 text-center">
      <p class="text-gray-400">Belum ada data.</p>
    </div>

    <!-- Form -->
    <BaseModal v-model="showForm" size="md" persistent>
      <template #default>
        <h2 class="text-lg font-semibold text-gray-900 dark:text-white">Tambah ${MODULE}</h2>
        <form class="mt-4 space-y-4" @submit.prevent="saveItem">
          <!-- TODO: Form fields -->
          <div class="flex justify-end gap-2 pt-2">
            <BaseButton variant="secondary" size="sm" type="button" @click="showForm = false">Batal</BaseButton>
            <BaseButton variant="primary" size="sm" type="submit">Simpan</BaseButton>
          </div>
        </form>
      </template>
    </BaseModal>
  </div>
</template>
FE_PAGE_EOF

success "Frontend files created"

# ============================================================================
# SUMMARY
# ============================================================================

echo ""
echo -e "${GREEN}════════════════════════════════════════════════════${NC}"
echo -e "${GREEN}  Module '$MODULE' berhasil di-scaffold! 🎉${NC}"
echo -e "${GREEN}════════════════════════════════════════════════════${NC}"
echo ""
echo "📁 Backend:"
echo "   $BE_BASE/"
echo "   ├── Domain/Entities/$MODULE.php"
echo "   ├── Domain/Contracts/${MODULE}RepositoryInterface.php"
echo "   ├── Application/Actions/Create${MODULE}Action.php"
echo "   ├── Application/Actions/Update${MODULE}Action.php"
echo "   ├── Application/Actions/Delete${MODULE}Action.php"
echo "   ├── Application/DTO/${MODULE}Data.php"
echo "   ├── Infrastructure/Controllers/${MODULE}Controller.php"
echo "   ├── Infrastructure/Requests/Store${MODULE}Request.php"
echo "   ├── Infrastructure/Requests/Update${MODULE}Request.php"
echo "   ├── Infrastructure/Models/${MODULE}Model.php"
echo "   ├── Infrastructure/Repositories/Eloquent${MODULE}Repository.php"
echo "   ├── Infrastructure/Resources/${MODULE}Resource.php"
echo "   ├── Infrastructure/Providers/${MODULE}ServiceProvider.php"
echo "   └── Infrastructure/Routes/api.php"
echo ""
echo "📁 Frontend:"
echo "   $FE_TYPES/$MODULE_KEBAB.ts"
echo "   $FE_API/$MODULE_KEBAB.ts"
echo "   $FE_PAGES/Index.vue"
echo ""
echo -e "${YELLOW}⚠️  Next steps:${NC}"
echo "   1. Register provider di bootstrap/providers.php:"
echo "      Modules\\${MODULE}\\Infrastructure\\Providers\\${MODULE}ServiceProvider::class"
echo ""
echo "   2. Tambah route di router/index.ts:"
echo "      { path: '/${MODULE_KEBAB}s', component: () => import('@/pages/${MODULE_KEBAB}/Index.vue') }"
echo ""
echo "   3. Buat migration:"
echo "      php artisan make:migration create_${TABLE_NAME}_table"
echo ""
echo "   4. Isi TODO di semua file yang sudah di-generate."
echo ""
