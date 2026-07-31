<script setup lang="ts">
import { ref, computed, onMounted } from 'vue'
import { useRouter } from 'vue-router'
import BaseBadge from '@purdia/ui/src/components/BaseBadge.vue'
import { Package, Globe, Activity, Shield, FileText, CheckCircle, Zap } from '@lucide/vue'
import type { HealthData } from '@/types/module-manager'
import * as api from '@/api/module-manager'

const router = useRouter()
const health = ref<HealthData | null>(null)
const loading = ref(true)

const scoreColor = computed(() => {
  if (!health.value) return 'text-gray-400'
  const s = health.value.overall_score
  if (s >= 80) return 'text-green-500'
  if (s >= 60) return 'text-amber-500'
  return 'text-red-500'
})

const categoryCards = computed(() => {
  if (!health.value) return []
  const c = health.value.categories
  return [
    { label: 'Architecture', value: c.architecture, max: 40, icon: Shield, color: 'text-blue-500' },
    { label: 'Documentation', value: c.documentation, max: 20, icon: FileText, color: 'text-purple-500' },
    { label: 'Extractability', value: c.extractability, max: 20, icon: Package, color: 'text-green-500' },
    { label: 'Testing', value: c.testing, max: 20, icon: CheckCircle, color: 'text-amber-500' },
  ]
})

// Heatmap: modules sorted by used_by_count (from graph data)
const heatmap = ref<{ name: string; count: number }[]>([])

const topModules = computed(() => {
  if (!health.value) return []
  return [...health.value.modules]
    .sort((a, b) => b.overall_score - a.overall_score)
    .slice(0, 10)
})

const bottomModules = computed(() => {
  if (!health.value) return []
  return [...health.value.modules]
    .sort((a, b) => a.overall_score - b.overall_score)
    .slice(0, 5)
})

async function fetchData() {
  loading.value = true
  try {
    const [healthRes, graphRes] = await Promise.all([
      api.fetchHealth(),
      api.fetchGraph(),
    ])
    health.value = healthRes.data

    // Build heatmap from graph
    heatmap.value = graphRes.data.nodes
      .map(n => ({ name: n.id, count: n.used_by_count }))
      .sort((a, b) => b.count - a.count)
      .slice(0, 15)
  } catch {
    // handled
  } finally {
    loading.value = false
  }
}

function scoreBarWidth(score: number, max: number): string {
  return `${Math.round((score / max) * 100)}%`
}

onMounted(fetchData)
</script>

<template>
  <div>
    <!-- Header -->
    <div class="flex items-center justify-between">
      <div>
        <h1 class="text-2xl font-semibold text-gray-900 dark:text-white">Foundry</h1>
        <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">Architecture health & module observatory</p>
      </div>
      <div class="flex gap-2">
        <button
          class="flex items-center gap-2 rounded-lg border border-gray-200 bg-white px-3 py-2 text-sm font-medium text-gray-700 transition hover:bg-gray-50 dark:border-gray-700 dark:bg-gray-800 dark:text-gray-300 dark:hover:bg-gray-700"
          @click="router.push('/foundry/graph')"
        >
          <Globe :size="16" />
          Graph
        </button>
        <button
          class="flex items-center gap-2 rounded-lg border border-gray-200 bg-white px-3 py-2 text-sm font-medium text-gray-700 transition hover:bg-gray-50 dark:border-gray-700 dark:bg-gray-800 dark:text-gray-300 dark:hover:bg-gray-700"
          @click="router.push('/foundry/modules')"
        >
          <Package :size="16" />
          Modules
        </button>
      </div>
    </div>

    <!-- Loading -->
    <div v-if="loading" class="mt-8 py-12 text-center text-sm text-gray-400">Memuat data kesehatan arsitektur...</div>

    <template v-else-if="health">
      <!-- DX Score Hero -->
      <div class="mt-8 flex flex-col items-center rounded-2xl border border-gray-200 bg-white p-8 dark:border-gray-700 dark:bg-gray-800">
        <div class="flex items-center gap-3">
          <Zap :size="24" :class="scoreColor" />
          <span class="text-5xl font-bold" :class="scoreColor">{{ health.overall_score }}</span>
          <span class="text-lg text-gray-400">/100</span>
        </div>
        <p class="mt-2 text-sm text-gray-500 dark:text-gray-400">Foundry DX Score &middot; {{ health.module_count }} modules</p>
      </div>

      <!-- Category Scores -->
      <div class="mt-6 grid grid-cols-2 gap-4 lg:grid-cols-4">
        <div
          v-for="cat in categoryCards"
          :key="cat.label"
          class="rounded-xl border border-gray-200 bg-white p-4 dark:border-gray-700 dark:bg-gray-800"
        >
          <div class="flex items-center gap-2">
            <component :is="cat.icon" :size="16" :class="cat.color" />
            <span class="text-xs font-medium text-gray-500 dark:text-gray-400">{{ cat.label }}</span>
          </div>
          <div class="mt-2 flex items-end gap-1">
            <span class="text-2xl font-bold text-gray-900 dark:text-white">{{ cat.value }}</span>
            <span class="mb-0.5 text-xs text-gray-400">/{{ cat.max }}</span>
          </div>
          <div class="mt-2 h-1.5 w-full overflow-hidden rounded-full bg-gray-100 dark:bg-gray-700">
            <div
              class="h-full rounded-full bg-gradient-to-r from-primary-400 to-primary-600"
              :style="{ width: scoreBarWidth(cat.value, cat.max) }"
            />
          </div>
        </div>
      </div>

      <!-- Dependency Heatmap -->
      <div class="mt-8">
        <h2 class="text-sm font-semibold text-gray-900 dark:text-white">Dependency Heatmap</h2>
        <p class="mt-1 text-xs text-gray-500 dark:text-gray-400">Module yang paling banyak di-depend oleh module lain</p>
        <div class="mt-4 space-y-2">
          <div v-for="item in heatmap" :key="item.name" class="flex items-center gap-3">
            <span class="w-28 truncate text-xs font-medium text-gray-700 dark:text-gray-300">{{ item.name }}</span>
            <div class="flex-1">
              <div class="h-4 overflow-hidden rounded bg-gray-100 dark:bg-gray-700">
                <div
                  class="h-full rounded bg-gradient-to-r from-primary-400 to-primary-600 transition-all"
                  :style="{ width: heatmap.length ? `${Math.max(4, (item.count / heatmap[0].count) * 100)}%` : '0%' }"
                />
              </div>
            </div>
            <span class="w-8 text-right text-xs font-mono text-gray-500">{{ item.count }}</span>
          </div>
        </div>
      </div>

      <!-- Module Scores -->
      <div class="mt-8 grid grid-cols-1 gap-6 lg:grid-cols-2">
        <!-- Top Performers -->
        <div>
          <h2 class="text-sm font-semibold text-gray-900 dark:text-white">Top Modules</h2>
          <div class="mt-3 space-y-2">
            <div
              v-for="mod in topModules"
              :key="mod.name"
              class="flex items-center justify-between rounded-lg border border-gray-100 bg-white px-4 py-2.5 dark:border-gray-700 dark:bg-gray-800"
            >
              <span class="text-sm text-gray-700 dark:text-gray-300">{{ mod.name }}</span>
              <BaseBadge :variant="mod.overall_score >= 80 ? 'success' : mod.overall_score >= 60 ? 'warning' : 'danger'" size="xs">
                {{ mod.overall_score }}/100
              </BaseBadge>
            </div>
          </div>
        </div>

        <!-- Needs Attention -->
        <div>
          <h2 class="text-sm font-semibold text-gray-900 dark:text-white">Perlu Perhatian</h2>
          <div class="mt-3 space-y-2">
            <div
              v-for="mod in bottomModules"
              :key="mod.name"
              class="flex items-center justify-between rounded-lg border border-gray-100 bg-white px-4 py-2.5 dark:border-gray-700 dark:bg-gray-800"
            >
              <span class="text-sm text-gray-700 dark:text-gray-300">{{ mod.name }}</span>
              <BaseBadge :variant="mod.overall_score >= 80 ? 'success' : mod.overall_score >= 60 ? 'warning' : 'danger'" size="xs">
                {{ mod.overall_score }}/100
              </BaseBadge>
            </div>
          </div>
        </div>
      </div>
    </template>
  </div>
</template>
