<script setup lang="ts">
import { ref, onMounted } from 'vue'
import { get, post, del } from '@purdia/http'
import { formatDate } from '@purdia/utils'
import BaseButton from '@purdia/ui/src/components/BaseButton.vue'
import BaseBadge from '@purdia/ui/src/components/BaseBadge.vue'
import BaseEmptyState from '@purdia/ui/src/components/BaseEmptyState.vue'
import { Trash2, RotateCcw } from '@lucide/vue'

interface TrashedItem {
  id: number
  type: 'note' | 'task'
  title: string
  deleted_at: string
}

const items = ref<TrashedItem[]>([])
const loading = ref(true)

async function fetchItems() {
  try {
    const res = await get('/trash')
    items.value = res.data?.data || []
  } catch {
    // handle error
  } finally {
    loading.value = false
  }
}

async function restoreItem(item: TrashedItem) {
  try {
    await post(`/trash/${item.type}/${item.id}/restore`)
    items.value = items.value.filter((i) => !(i.id === item.id && i.type === item.type))
  } catch {
    // handle error
  }
}

async function permanentDelete(item: TrashedItem) {
  try {
    await del(`/trash/${item.type}/${item.id}`)
    items.value = items.value.filter((i) => !(i.id === item.id && i.type === item.type))
  } catch {
    // handle error
  }
}

function typeLabel(type: string): string {
  return type === 'note' ? 'Catatan' : 'Tugas'
}

onMounted(fetchItems)
</script>

<template>
  <div class="space-y-6">
    <!-- Header -->
    <div class="flex items-center gap-3">
      <Trash2 :size="24" class="text-gray-500" />
      <h1 class="text-2xl font-bold text-gray-900 dark:text-white">Sampah</h1>
    </div>

    <p class="text-sm text-gray-500 dark:text-gray-400">
      Item yang dihapus akan tersimpan di sini. Kamu bisa memulihkan atau menghapus secara permanen.
    </p>

    <!-- Loading -->
    <div v-if="loading" class="flex items-center justify-center py-20">
      <div class="h-8 w-8 animate-spin rounded-full border-4 border-primary-500 border-t-transparent"></div>
    </div>

    <!-- Empty state -->
    <BaseEmptyState
      v-else-if="!items.length"
      title="Sampah kosong"
      description="Tidak ada item yang dihapus saat ini."
    />

    <!-- List -->
    <div v-else class="space-y-2">
      <div
        v-for="item in items"
        :key="`${item.type}-${item.id}`"
        class="flex items-center justify-between rounded-lg border border-gray-200 bg-white px-4 py-3 dark:border-gray-700 dark:bg-gray-800"
      >
        <div class="flex items-center gap-3">
          <BaseBadge
            :variant="item.type === 'note' ? 'primary' : 'warning'"
            size="sm"
          >
            {{ typeLabel(item.type) }}
          </BaseBadge>
          <div>
            <p class="text-sm font-medium text-gray-900 dark:text-white">{{ item.title }}</p>
            <p class="text-xs text-gray-400 dark:text-gray-500">
              Dihapus {{ formatDate(item.deleted_at) }}
            </p>
          </div>
        </div>

        <div class="flex items-center gap-2">
          <BaseButton variant="secondary" size="sm" :icon="RotateCcw" @click="restoreItem(item)">
            Pulihkan
          </BaseButton>
          <BaseButton variant="danger" size="sm" :icon="Trash2" @click="permanentDelete(item)">
            Hapus
          </BaseButton>
        </div>
      </div>
    </div>
  </div>
</template>
