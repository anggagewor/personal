<script setup lang="ts">
import { ref, computed, onMounted } from 'vue'
import { useToast } from '@purdia/toast'
import BaseInput from '@purdia/ui/src/components/BaseInput.vue'
import BaseButton from '@purdia/ui/src/components/BaseButton.vue'
import BaseBadge from '@purdia/ui/src/components/BaseBadge.vue'
import BaseModal from '@purdia/ui/src/components/BaseModal.vue'
import {
  Search, Package, Download, Upload, ArrowRight, Check, AlertTriangle,
  Shield, FileText, CheckCircle, Activity, Zap,
} from '@lucide/vue'
import type {
  ModuleInfo, ModuleDetail, InspectData, ImpactData, ExtractPreviewData, ModuleHealth,
} from '@/types/module-manager'
import * as api from '@/api/module-manager'

const toast = useToast()

const modules = ref<ModuleInfo[]>([])
const loading = ref(true)
const search = ref('')
const selectedTag = ref('')

// Detail state
const showDetail = ref(false)
const detailModule = ref<ModuleDetail | null>(null)
const detailInspect = ref<InspectData | null>(null)
const detailImpact = ref<ImpactData | null>(null)
const detailHealth = ref<ModuleHealth | null>(null)
const detailPreview = ref<ExtractPreviewData | null>(null)
const detailLoading = ref(false)
const extracting = ref(false)

// Import state
const showImport = ref(false)
const importing = ref(false)

const tags = computed(() => {
  const all = modules.value.flatMap(m => m.tags)
  return [...new Set(all)].sort()
})

const filteredModules = computed(() => {
  let result = modules.value
  if (selectedTag.value) result = result.filter(m => m.tags.includes(selectedTag.value))
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

async function fetchModules() {
  loading.value = true
  try {
    const res = await api.fetchModules()
    modules.value = res.data
  } catch { /* handled */ }
  finally { loading.value = false }
}

async function openDetail(mod: ModuleInfo) {
  detailLoading.value = true
  showDetail.value = true
  detailModule.value = null
  detailInspect.value = null
  detailImpact.value = null
  detailHealth.value = null
  detailPreview.value = null

  try {
    const [modRes, inspectRes, impactRes, healthRes] = await Promise.all([
      api.fetchModule(mod.name),
      api.fetchInspect(mod.name),
      api.fetchImpact(mod.name),
      api.fetchHealth(mod.name),
    ])
    detailModule.value = modRes.data
    detailInspect.value = inspectRes.data
    detailImpact.value = impactRes.data
    detailHealth.value = healthRes.data as unknown as ModuleHealth

    // Fetch extract preview if extractable
    if (mod.extractable) {
      const previewRes = await api.fetchExtractPreview(mod.name)
      detailPreview.value = previewRes.data
    }
  } catch { /* handled */ }
  finally { detailLoading.value = false }
}

async function handleExtract() {
  if (!detailModule.value) return
  extracting.value = true
  try {
    await api.extractModule(detailModule.value.name, true)
    toast.success(`Module ${detailModule.value.name} berhasil di-extract.`)
  } catch { /* handled */ }
  finally { extracting.value = false }
}

async function handleImport(event: Event) {
  const input = event.target as HTMLInputElement
  const file = input.files?.[0]
  if (!file) return

  importing.value = true
  try {
    const res = await api.importModule(file)
    if (res.data.imported.length) toast.success(`Berhasil import: ${res.data.imported.join(', ')}`)
    if (res.data.skipped.length) toast.warning(`Dilewati: ${res.data.skipped.join(', ')}`)
    showImport.value = false
    fetchModules()
  } catch { /* handled */ }
  finally { importing.value = false; input.value = '' }
}

function formatBytes(bytes: number): string {
  if (bytes < 1024) return `${bytes} B`
  if (bytes < 1024 * 1024) return `${(bytes / 1024).toFixed(1)} KB`
  return `${(bytes / (1024 * 1024)).toFixed(1)} MB`
}

function getTagColor(tag: string): string {
  const map: Record<string, string> = {
    productivity: 'primary', finance: 'success', utility: 'default',
    'dev-tools': 'warning', pos: 'info', integration: 'danger',
    core: 'primary', auth: 'primary', aggregation: 'default',
    foundation: 'primary', business: 'info',
  }
  return map[tag] || 'default'
}

function scoreColor(score: number): string {
  if (score >= 80) return 'success'
  if (score >= 60) return 'warning'
  return 'danger'
}

onMounted(fetchModules)
</script>

<template>
  <div>
    <!-- Header -->
    <div class="flex items-center justify-between">
      <div>
        <h1 class="text-2xl font-semibold text-gray-900 dark:text-white">Modules</h1>
        <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">{{ modules.length }} modules terdaftar</p>
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
          :class="!selectedTag ? 'bg-primary-100 text-primary-700 dark:bg-primary-900/40 dark:text-primary-300' : 'bg-gray-100 text-gray-600 hover:bg-gray-200 dark:bg-gray-800 dark:text-gray-400'"
          @click="selectedTag = ''"
        >Semua</button>
        <button
          v-for="tag in tags" :key="tag"
          class="rounded-full px-3 py-1 text-xs font-medium transition-colors"
          :class="selectedTag === tag ? 'bg-primary-100 text-primary-700 dark:bg-primary-900/40 dark:text-primary-300' : 'bg-gray-100 text-gray-600 hover:bg-gray-200 dark:bg-gray-800 dark:text-gray-400'"
          @click="selectedTag = tag"
        >{{ tag }}</button>
      </div>
    </div>

    <!-- Loading -->
    <div v-if="loading" class="mt-6 py-8 text-center text-sm text-gray-400">Memuat module...</div>

    <!-- Module Grid -->
    <div v-else class="mt-6 grid grid-cols-1 gap-3 sm:grid-cols-2 lg:grid-cols-3">
      <button
        v-for="mod in filteredModules" :key="mod.name"
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
    <BaseModal v-model="showDetail" size="xl">
      <template #default>
        <div v-if="detailLoading" class="py-12 text-center text-sm text-gray-400">Memuat detail...</div>
        <div v-else-if="detailModule" class="space-y-5 max-h-[70vh] overflow-y-auto">
          <!-- Header -->
          <div class="flex items-start justify-between">
            <div class="flex items-start gap-3">
              <div class="flex h-11 w-11 shrink-0 items-center justify-center rounded-xl bg-primary-50 text-primary-600 dark:bg-primary-900/30 dark:text-primary-400">
                <Package :size="22" />
              </div>
              <div>
                <h2 class="text-lg font-semibold text-gray-900 dark:text-white">{{ detailModule.display_name }}</h2>
                <p class="text-sm text-gray-500">{{ detailModule.description }}</p>
              </div>
            </div>
            <BaseBadge v-if="detailHealth" :variant="scoreColor(detailHealth.overall_score)">
              <Zap :size="12" class="mr-1" /> {{ detailHealth.overall_score }}/100
            </BaseBadge>
          </div>

          <!-- Health Checks -->
          <div v-if="detailHealth" class="rounded-lg border border-gray-200 p-4 dark:border-gray-700">
            <h3 class="text-sm font-medium text-gray-900 dark:text-white">Health Checks</h3>
            <div class="mt-3 grid grid-cols-2 gap-2">
              <div
                v-for="check in detailHealth.checks" :key="check.name"
                class="flex items-center gap-2 text-xs"
              >
                <Check v-if="check.pass" :size="14" class="text-green-500" />
                <AlertTriangle v-else :size="14" class="text-amber-500" />
                <span :class="check.pass ? 'text-gray-600 dark:text-gray-400' : 'text-amber-600 dark:text-amber-400'">{{ check.name }}</span>
              </div>
            </div>
          </div>

          <!-- Inspector -->
          <div v-if="detailInspect" class="rounded-lg border border-gray-200 p-4 dark:border-gray-700">
            <h3 class="text-sm font-medium text-gray-900 dark:text-white">Inspector</h3>
            <div class="mt-3 grid grid-cols-3 gap-3 sm:grid-cols-4">
              <div v-if="detailInspect.entities" class="text-center">
                <p class="text-lg font-bold text-gray-900 dark:text-white">{{ detailInspect.entities }}</p>
                <p class="text-xs text-gray-500">Entities</p>
              </div>
              <div v-if="detailInspect.actions" class="text-center">
                <p class="text-lg font-bold text-gray-900 dark:text-white">{{ detailInspect.actions }}</p>
                <p class="text-xs text-gray-500">Actions</p>
              </div>
              <div v-if="detailInspect.controllers" class="text-center">
                <p class="text-lg font-bold text-gray-900 dark:text-white">{{ detailInspect.controllers }}</p>
                <p class="text-xs text-gray-500">Controllers</p>
              </div>
              <div v-if="detailInspect.models" class="text-center">
                <p class="text-lg font-bold text-gray-900 dark:text-white">{{ detailInspect.models }}</p>
                <p class="text-xs text-gray-500">Models</p>
              </div>
              <div v-if="detailInspect.repositories" class="text-center">
                <p class="text-lg font-bold text-gray-900 dark:text-white">{{ detailInspect.repositories }}</p>
                <p class="text-xs text-gray-500">Repositories</p>
              </div>
              <div v-if="detailInspect.commands" class="text-center">
                <p class="text-lg font-bold text-gray-900 dark:text-white">{{ detailInspect.commands }}</p>
                <p class="text-xs text-gray-500">Commands</p>
              </div>
              <div v-if="detailInspect.migrations" class="text-center">
                <p class="text-lg font-bold text-gray-900 dark:text-white">{{ detailInspect.migrations }}</p>
                <p class="text-xs text-gray-500">Migrations</p>
              </div>
              <div v-if="detailInspect.tests" class="text-center">
                <p class="text-lg font-bold text-gray-900 dark:text-white">{{ detailInspect.tests }}</p>
                <p class="text-xs text-gray-500">Tests</p>
              </div>
              <div class="text-center">
                <p class="text-lg font-bold text-gray-900 dark:text-white">{{ detailInspect.total_files }}</p>
                <p class="text-xs text-gray-500">Total Files</p>
              </div>
              <div class="text-center">
                <p class="text-lg font-bold text-gray-900 dark:text-white">{{ formatBytes(detailInspect.size_bytes) }}</p>
                <p class="text-xs text-gray-500">Size</p>
              </div>
            </div>
          </div>

          <!-- Dependencies & Used By -->
          <div class="grid grid-cols-1 gap-4 sm:grid-cols-2">
            <div class="rounded-lg border border-gray-200 p-4 dark:border-gray-700">
              <h3 class="text-sm font-medium text-gray-900 dark:text-white">Depends On</h3>
              <div v-if="detailModule.depends.length" class="mt-2 flex flex-wrap gap-1.5">
                <BaseBadge v-for="dep in detailModule.depends" :key="dep" variant="default">{{ dep }}</BaseBadge>
              </div>
              <p v-else class="mt-2 flex items-center gap-1.5 text-xs text-green-600 dark:text-green-400">
                <Check :size="14" /> Standalone
              </p>
            </div>
            <div class="rounded-lg border border-gray-200 p-4 dark:border-gray-700">
              <h3 class="text-sm font-medium text-gray-900 dark:text-white">Used By</h3>
              <div v-if="detailModule.used_by.length" class="mt-2 flex flex-wrap gap-1.5">
                <BaseBadge v-for="name in detailModule.used_by" :key="name" variant="info">{{ name }}</BaseBadge>
              </div>
              <p v-else class="mt-2 text-xs text-gray-400">Tidak digunakan module lain.</p>
            </div>
          </div>

          <!-- Impact Analysis -->
          <div v-if="detailImpact && detailImpact.affected_count > 0" class="rounded-lg border border-amber-200 bg-amber-50 p-4 dark:border-amber-800 dark:bg-amber-900/20">
            <h3 class="flex items-center gap-2 text-sm font-medium text-amber-800 dark:text-amber-300">
              <Activity :size="14" />
              Impact Analysis
            </h3>
            <p class="mt-1 text-xs text-amber-600 dark:text-amber-400">
              Perubahan di module ini akan mempengaruhi {{ detailImpact.affected_count }} module:
            </p>
            <div class="mt-2 flex flex-wrap gap-1.5">
              <BaseBadge v-for="item in detailImpact.affected" :key="item.name" variant="warning" size="xs">
                {{ item.name }} <span class="ml-1 opacity-60">({{ item.reason }})</span>
              </BaseBadge>
            </div>
          </div>

          <!-- Extract Preview -->
          <div v-if="detailPreview && detailModule.extractable" class="rounded-lg border border-gray-200 p-4 dark:border-gray-700">
            <h3 class="text-sm font-medium text-gray-900 dark:text-white">Extract Preview</h3>
            <div class="mt-3 grid grid-cols-2 gap-3 sm:grid-cols-5">
              <div class="text-center">
                <p class="text-lg font-bold text-gray-900 dark:text-white">{{ detailPreview.totals.modules }}</p>
                <p class="text-xs text-gray-500">Modules</p>
              </div>
              <div class="text-center">
                <p class="text-lg font-bold text-gray-900 dark:text-white">{{ detailPreview.totals.files }}</p>
                <p class="text-xs text-gray-500">Files</p>
              </div>
              <div class="text-center">
                <p class="text-lg font-bold text-gray-900 dark:text-white">{{ detailPreview.totals.migrations }}</p>
                <p class="text-xs text-gray-500">Migrations</p>
              </div>
              <div class="text-center">
                <p class="text-lg font-bold text-gray-900 dark:text-white">{{ detailPreview.totals.tests }}</p>
                <p class="text-xs text-gray-500">Tests</p>
              </div>
              <div class="text-center">
                <p class="text-lg font-bold text-gray-900 dark:text-white">{{ formatBytes(detailPreview.totals.size_bytes) }}</p>
                <p class="text-xs text-gray-500">Archive</p>
              </div>
            </div>
            <div class="mt-3">
              <p class="text-xs text-gray-500">Included: {{ detailPreview.included_modules.map(m => m.name).join(', ') }}</p>
            </div>
          </div>

          <!-- Actions -->
          <div class="flex items-center justify-end gap-2 border-t border-gray-200 pt-4 dark:border-gray-700">
            <BaseButton variant="secondary" size="sm" @click="showDetail = false">Tutup</BaseButton>
            <BaseButton
              v-if="detailModule.extractable"
              variant="primary" size="sm" :icon="Download" :loading="extracting"
              @click="handleExtract"
            >Extract</BaseButton>
            <span v-else class="flex items-center gap-1.5 text-xs text-amber-600 dark:text-amber-400">
              <AlertTriangle :size="14" /> Tidak bisa di-extract
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
          <label class="flex cursor-pointer flex-col items-center gap-2 rounded-xl border-2 border-dashed border-gray-300 bg-gray-50 p-8 transition-colors hover:border-primary-400 dark:border-gray-600 dark:bg-gray-800 dark:hover:border-primary-500">
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
