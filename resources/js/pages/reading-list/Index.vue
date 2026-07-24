<script setup lang="ts">
import { ref } from 'vue'
import { get, post, del } from '@purdia/http'
import BaseButton from '@purdia/ui/src/components/BaseButton.vue'
import BaseInput from '@purdia/ui/src/components/BaseInput.vue'
import BaseModal from '@purdia/ui/src/components/BaseModal.vue'
import { Plus, Trash2, ExternalLink, Check, Star, BookOpen } from '@lucide/vue'

interface ReadingItem {
  id: number
  title: string
  url: string
  description: string | null
  domain: string | null
  is_read: boolean
  is_favorite: boolean
  created_at: string
}

const items = ref<ReadingItem[]>([])
const showForm = ref(false)
const form = ref({ url: '', title: '', description: '' })
const filter = ref<'all' | 'unread' | 'favorite'>('all')

async function fetchItems() {
  const params: Record<string, string> = {}
  if (filter.value === 'unread') params.unread = '1'
  if (filter.value === 'favorite') params.favorite = '1'

  try {
    const res = await get<ReadingItem[]>('/reading-list', { params })
    items.value = res.data
  } catch { /* */ }
}

async function addItem() {
  if (!form.value.url.trim()) return
  await post('/reading-list', form.value)
  form.value = { url: '', title: '', description: '' }
  showForm.value = false
  fetchItems()
}

async function toggleRead(item: ReadingItem) {
  await post(`/reading-list/${item.id}/toggle-read`)
  item.is_read = !item.is_read
}

async function toggleFavorite(item: ReadingItem) {
  await post(`/reading-list/${item.id}/toggle-favorite`)
  item.is_favorite = !item.is_favorite
}

async function deleteItem(item: ReadingItem) {
  await del(`/reading-list/${item.id}`)
  items.value = items.value.filter((i) => i.id !== item.id)
}

fetchItems()
</script>

<template>
  <div>
    <div class="flex items-center justify-between">
      <div>
        <h1 class="text-2xl font-semibold text-gray-900 dark:text-white">Reading List</h1>
        <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">Simpan artikel untuk dibaca nanti.</p>
      </div>
      <BaseButton variant="primary" size="sm" :icon="Plus" @click="showForm = true">Tambah</BaseButton>
    </div>

    <!-- Filters -->
    <div class="mt-4 flex gap-1 rounded-lg border border-gray-200 bg-gray-50 p-0.5 w-fit dark:border-gray-700 dark:bg-gray-800">
      <button v-for="f in (['all', 'unread', 'favorite'] as const)" :key="f"
        class="rounded-md px-3 py-1.5 text-xs font-medium transition-colors"
        :class="filter === f ? 'bg-white text-gray-900 shadow-sm dark:bg-gray-700 dark:text-white' : 'text-gray-500 hover:text-gray-700 dark:text-gray-400'"
        @click="filter = f; fetchItems()"
      >{{ f === 'all' ? 'Semua' : f === 'unread' ? 'Belum Dibaca' : 'Favorit' }}</button>
    </div>

    <!-- List -->
    <div class="mt-6 space-y-3">
      <div v-for="item in items" :key="item.id"
        class="group flex items-start gap-4 rounded-xl border border-gray-200 bg-white px-5 py-4 dark:border-gray-700 dark:bg-gray-800"
      >
        <button
          class="mt-0.5 flex h-6 w-6 shrink-0 items-center justify-center rounded-full border-2 transition-colors"
          :class="item.is_read ? 'border-emerald-500 bg-emerald-500 text-white' : 'border-gray-300 text-transparent hover:border-emerald-400 dark:border-gray-600'"
          @click="toggleRead(item)"
        ><Check :size="12" /></button>

        <div class="flex-1 min-w-0">
          <a :href="item.url" target="_blank" class="text-sm font-medium text-gray-900 hover:text-primary-600 dark:text-white" :class="item.is_read ? 'line-through opacity-60' : ''">
            {{ item.title }}
            <ExternalLink :size="12" class="ml-1 inline" />
          </a>
          <p v-if="item.domain" class="text-xs text-gray-400 mt-0.5">{{ item.domain }}</p>
        </div>

        <button
          class="shrink-0 p-1 transition-colors"
          :class="item.is_favorite ? 'text-amber-500' : 'text-gray-300 hover:text-amber-500 dark:text-gray-600'"
          @click="toggleFavorite(item)"
        ><Star :size="16" :fill="item.is_favorite ? 'currentColor' : 'none'" /></button>

        <button class="shrink-0 rounded p-1 text-gray-400 opacity-0 group-hover:opacity-100 hover:text-red-500" @click="deleteItem(item)"><Trash2 :size="14" /></button>
      </div>
    </div>

    <div v-if="!items.length" class="mt-12 text-center"><p class="text-gray-400">Belum ada artikel disimpan.</p></div>

    <BaseModal v-model="showForm" size="sm">
      <template #default>
        <h2 class="text-lg font-semibold text-gray-900 dark:text-white">Tambah Artikel</h2>
        <form class="mt-4 space-y-4" @submit.prevent="addItem">
          <BaseInput v-model="form.url" label="URL" placeholder="https://..." required />
          <BaseInput v-model="form.title" label="Judul (opsional)" placeholder="Auto-detect dari URL" />
          <div class="flex justify-end gap-2 pt-2">
            <BaseButton variant="secondary" size="sm" type="button" @click="showForm = false">Batal</BaseButton>
            <BaseButton variant="primary" size="sm" type="submit">Simpan</BaseButton>
          </div>
        </form>
      </template>
    </BaseModal>
  </div>
</template>
