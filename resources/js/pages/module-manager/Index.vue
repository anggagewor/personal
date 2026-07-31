<script setup lang="ts">
import { ref, computed, onMounted } from 'vue'
import { useToast } from '@purdia/toast'
import BaseInput from '@purdia/ui/src/components/BaseInput.vue'
import BaseButton from '@purdia/ui/src/components/BaseButton.vue'
import BaseBadge from '@purdia/ui/src/components/BaseBadge.vue'
import BaseModal from '@purdia/ui/src/components/BaseModal.vue'
import { Search, Package, Download, Upload, Blocks, ArrowRight, Check, AlertTriangle } from '@lucide/vue'
import type { ModuleInfo, ModuleDetail } from '@/types/module-manager'
import * as moduleApi from '@/api/module-manager'

const toast = useToast()

const modules = ref<ModuleInfo[]>([])
const loading = ref(true)
const search = ref('')
const selectedTag = ref('')
const selectedModule = ref<ModuleDetail | null>(null)
const showDetail = ref(false)
const showImport = ref(false)
const extracting = ref(false)
const importing = ref(false)

const tags = computed(() => {
  const all = modules.value.flatMap(m => m.tags)
  return [...new Set(all)].sort()
})

const filteredModules = computed(() => {
  let result = modules.value

  if (selectedTag.value) {
    result = result.filter(m => m.tags.includes(selectedTag.value))
  }

  if (search.value.trim()) {
    const q = search.value.toLowerCase()
    result = result.filter(m =>
      m.name.toLowerCase().includes(q) ||
      m.display_name.toLowerCase().includes(q) ||
      m.description.toLowerCase().includes(q)
    )
  }

  return result
})

const stats = computed(() => ({
  total: modules.value.length,
  extractable: modules.value.filter(m => m.extractable).length,
  standalone: modules.value.filter(m => m.depends.length === 0).length,
}))

async function fetchModules() {
  loading.value = true
  try {
    const res = await moduleApi.fetchModules()
    modules.value = res.data
  } catch {
    // handled by @purdia/http
  } finally {
    loading.value = false
  }
}

async function openDetail(mod: ModuleInfo) {
  try {
    const res = await moduleApi.fetchModule(mod.name)
    selectedModule.value = res.data
    showDetail.value = true
  } catch {
    // handled by @purdia/http
  }
}

async function handleExtract(mod: ModuleDetail) {
  extracting.value = true
  try {
    const res = await moduleApi.extractModule(mod.name, true)
    toast.success(`Module ${mod.name} berhasil di-extract.`)
    showDetail.value = false
  } catch {
    // handled by @purdia/http
  } finally {
    extracting.value = false
  }
}

async function handleImport(event: Event) {
  const input = event.target as HTMLInputElement
  const file = input.files?.[0]
  if (!file) return

  importing.value = true
  try {
    const res = await moduleApi.importModule(file)
    const imported = res.data.imported
    const skipped = res.data.skipped

    if (imported.length) {
      toast.success(`Berhasil import: ${imported.join(', ')}`)
    }
    if (skipped.length) {
      toast.warning(`Dilewati (sudah ada): ${skipped.join(', ')}`)
    }

    showImport.value = false
    fetchModules()
  } catch {
    // handled by @purdia/http
  } finally {
    importing.value = false
    input.value = ''
  }
}

function getTagColor(tag: string): string {
  const map: Record<string, string> = {
    productivity: 'primary',
    finance: 'success',
    utility: 'default',
    'dev-tools': 'warning',
    pos: 'info',
    integration: 'danger',
    core: 'primary',
    auth: 'primary',
    aggregation: 'default',
    foundation: 'primary',
    business: 'info',
  }
  return map[tag] || 'default'
}

onMounted(fetchModules)
</script>

<template>
  <div>
    <!-- Header -->
    <div class="flex items-center justify-between">
      <div>
        <h1 class="text-2xl font-semibold text-gray-900 dark:text-white">Module Manager</h1>
        <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">
          {{ stats.total }} module &middot; {{ stats.extractable }} extractable &middot; {{ stats.standalone }} standalone
        </p>
      </div>
      <BaseButton variant="secondary" size="sm" :icon="Upload" @click="showImport = true">Import</BaseButton>
    </div>

    <!-- Filters -->
    <div class="mt-6 flex flex-col gap-3 sm:flex-row sm:items-center">
      <div class="max-w-xs flex-1">
        <BaseInput v-model="search" placeholder="Cari module..." :icon="Search" />
      </div>
      <div class="flex flex-wrap gap-2">
        <button
          class="rounded-full px-3 py-1 text-xs font-medium transition-colors"
          :class="!selectedTag
            ? 'bg-primary-100 text-primary-700 dark:bg-primary-900/40 dark:text-primary-300'
            : 'bg-gray-100 text-gray-600 hover:bg-gray-200 dark:bg-gray-800 dark:text-gray-400 dark:hover:bg-gray-700'"
          @click="selectedTag = ''"
        >
          Semua
        </button>
        <button
          v-for="tag in tags"
          :key="tag"
          class="rounded-full px-3 py-1 text-xs font-medium transition-colors"
          :class="selectedTag === tag
            ? 'bg-primary-100 text-primary-700 dark:bg-primary-900/40 dark:text-primary-300'
            : 'bg-gray-100 text-gray-600 hover:bg-gray-200 dark:bg-gray-800 dark:text-gray-400 dark:hover:bg-gray-700'"
          @click="selectedTag = tag"
        >
          {{ tag }}
        </button>
      </div>
    </div>

    <!-- Loading -->
    <div v-if="loading" class="mt-6 py-8 text-center text-sm text-gray-400">Memuat module...</div>

    <!-- Empty -->
    <div v-else-if="!filteredModules.length" class="mt-12 flex flex-col items-center text-center">
      <div class="flex h-16 w-16 items-center justify-center rounded-2xl bg-gray-100 dark:bg-gray-800">
        <Blocks :size="28" class="text-gray-400" />
      </div>
      <h3 class="mt-4 text-base font-medium text-gray-900 dark:text-white">
        {{ search ? 'Module tidak ditemukan' : 'Tidak ada module' }}
      </h3>
    </div>

    <!-- Module Grid -->
    <div v-else class="mt-6 grid grid-cols-1 gap-3 sm:grid-cols-2 lg:grid-cols-3">
      <button
        v-for="mod in filteredModules"
        :key="mod.name"
        class="flex flex-col gap-2 rounded-xl border border-gray-200 bg-white p-4 text-left transition-all hover:border-primary-300 hover:shadow-sm dark:border-gray-700 dark:bg-gray-800 dark:hover:border-primary-600"
        @click="openDetail(mod)"
      >
        <div class="flex items-center gap-3">
          <div class="flex h-9 w-9 shrink-0 items-center justify-center rounded-lg bg-primary-50 text-primary-600 dark:bg-primary-900/30 dark:text-primary-400">
            <Package :size="18" />
          </div>
          <div class="min-w-0 flex-1">
            <p class="truncate text-sm font-medium text-gray-900 dark:text-white">{{ mod.display_name }}</p>
            <p class="text-xs text-gray-500 dark:text-gray-400">{{ mod.name }}</p>
          </div>
          <ArrowRight :size="14" class="shrink-0 text-gray-300 dark:text-gray-600" />
        </div>

        <p v-if="mod.description" class="line-clamp-2 text-xs text-gray-500 dark:text-gray-400">{{ mod.description }}</p>

        <div class="flex flex-wrap gap-1">
          <BaseBadge v-for="tag in mod.tags" :key="tag" :variant="getTagColor(tag)" size="xs">{{ tag }}</BaseBadge>
          <BaseBadge v-if="mod.extractable" variant="success" size="xs">extractable</BaseBadge>
          <BaseBadge v-if="!mod.depends.length" variant="info" size="xs">standalone</BaseBadge>
        </div>
      </button>
    </div>

    <!-- Detail Modal -->
    <BaseModal v-model="showDetail" size="lg">
      <template #default>
        <div v-if="selectedModule" class="space-y-5">
          <div class="flex items-start gap-3">
            <div class="flex h-11 w-11 shrink-0 items-center justify-center rounded-xl bg-primary-50 text-primary-600 dark:bg-primary-900/30 dark:text-primary-400">
              <Package :size="22" />
            </div>
            <div>
              <h2 class="text-lg font-semibold text-gray-900 dark:text-white">{{ selectedModule.display_name }}</h2>
              <p class="text-sm text-gray-500 dark:text-gray-400">{{ selectedModule.name }}</p>
            </div>
          </div>

          <p v-if="selectedModule.description" class="text-sm text-gray-600 dark:text-gray-300">{{ selectedModule.description }}</p>

          <div class="flex flex-wrap gap-1.5">
            <BaseBadge v-for="tag in selectedModule.tags" :key="tag" :variant="getTagColor(tag)">{{ tag }}</BaseBadge>
          </div>

          <!-- Dependencies -->
          <div v-if="selectedModule.depends.length" class="rounded-lg border border-gray-200 p-4 dark:border-gray-700">
            <h3 class="text-sm font-medium text-gray-900 dark:text-white">Dependencies (direct)</h3>
            <div class="mt-2 flex flex-wrap gap-1.5">
              <BaseBadge v-for="dep in selectedModule.depends" :key="dep" variant="default">{{ dep }}</BaseBadge>
            </div>
          </div>

          <!-- Dependency Tree -->
          <div v-if="selectedModule.dependency_tree.length" class="rounded-lg border border-gray-200 p-4 dark:border-gray-700">
            <h3 class="text-sm font-medium text-gray-900 dark:text-white">Full Dependency Tree (akan ikut di-extract)</h3>
            <div class="mt-2 flex flex-wrap gap-1.5">
              <BaseBadge v-for="dep in selectedModule.dependency_tree" :key="dep" variant="info">{{ dep }}</BaseBadge>
            </div>
          </div>

          <div v-if="!selectedModule.depends.length" class="flex items-center gap-2 rounded-lg border border-green-200 bg-green-50 p-3 dark:border-green-800 dark:bg-green-900/20">
            <Check :size="16" class="text-green-600 dark:text-green-400" />
            <span class="text-sm text-green-700 dark:text-green-300">Standalone — tidak butuh module lain.</span>
          </div>

          <!-- Actions -->
          <div class="flex items-center justify-end gap-2 border-t border-gray-200 pt-4 dark:border-gray-700">
            <BaseButton variant="secondary" size="sm" @click="showDetail = false">Tutup</BaseButton>
            <BaseButton
              v-if="selectedModule.extractable"
              variant="primary"
              size="sm"
              :icon="Download"
              :loading="extracting"
              @click="handleExtract(selectedModule)"
            >
              Extract Module
            </BaseButton>
            <span v-else class="flex items-center gap-1.5 text-xs text-amber-600 dark:text-amber-400">
              <AlertTriangle :size="14" />
              Tidak bisa di-extract
            </span>
          </div>
        </div>
      </template>
    </BaseModal>

    <!-- Import Modal -->
    <BaseModal v-model="showImport" size="md" persistent>
      <template #default>
        <h2 class="text-lg font-semibold text-gray-900 dark:text-white">Import Module</h2>
        <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">Upload file .zip hasil extract dari project lain.</p>

        <div class="mt-5">
          <label
            class="flex cursor-pointer flex-col items-center gap-2 rounded-xl border-2 border-dashed border-gray-300 bg-gray-50 p-8 transition-colors hover:border-primary-400 hover:bg-primary-50/50 dark:border-gray-600 dark:bg-gray-800 dark:hover:border-primary-500 dark:hover:bg-primary-900/10"
          >
            <Upload :size="32" class="text-gray-400" />
            <span class="text-sm font-medium text-gray-600 dark:text-gray-300">Pilih file atau drag & drop</span>
            <span class="text-xs text-gray-400">.zip (hasil foundry:extract)</span>
            <input type="file" accept=".zip" class="hidden" :disabled="importing" @change="handleImport" />
          </label>
        </div>

        <div v-if="importing" class="mt-4 text-center text-sm text-gray-500">Mengimport module...</div>

        <div class="mt-5 flex justify-end">
          <BaseButton variant="secondary" size="sm" @click="showImport = false">Batal</BaseButton>
        </div>
      </template>
    </BaseModal>
  </div>
</template>
