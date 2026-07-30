<script setup lang="ts">
import { ref } from 'vue'
import { useToast } from '@purdia/toast'
import { Plus, Trash2, ExternalLink, Bookmark } from '@lucide/vue'
import type { BookmarkItem } from '@/types/bookmark'
import * as bookmarksApi from '@/api/bookmarks'

const toast = useToast()

const grouped = ref<Record<string, BookmarkItem[]>>({})
const loading = ref(false)
const showForm = ref(false)
const editingBookmark = ref<BookmarkItem | null>(null)

const form = ref({ title: '', url: '', description: '', category: '' })

async function fetchBookmarks() {
  loading.value = true
  try {
    const response = await bookmarksApi.fetchBookmarks()
    grouped.value = response.data
  } catch {
    // Error toast handled globally by @purdia/http onError
  }
  loading.value = false
}

async function saveBookmark() {
  if (!form.value.title.trim() || !form.value.url.trim()) return
  const payload = { ...form.value, category: form.value.category || null, description: form.value.description || null }

  try {
    if (editingBookmark.value) {
      await bookmarksApi.updateBookmark(editingBookmark.value.id, payload)
      toast.success('Bookmark berhasil diperbarui.')
    } else {
      await bookmarksApi.createBookmark(payload)
      toast.success('Bookmark berhasil ditambahkan.')
    }
    closeForm()
    fetchBookmarks()
  } catch {
    // Error toast handled globally by @purdia/http onError
  }
}

async function deleteBookmark(bookmark: BookmarkItem) {
  try {
    await bookmarksApi.deleteBookmark(bookmark.id)
    toast.success('Bookmark berhasil dihapus.')
    fetchBookmarks()
  } catch {
    // Error toast handled globally by @purdia/http onError
  }
}

function openNew() {
  editingBookmark.value = null
  form.value = { title: '', url: '', description: '', category: '' }
  showForm.value = true
}

function openEdit(bookmark: BookmarkItem) {
  editingBookmark.value = bookmark
  form.value = { title: bookmark.title, url: bookmark.url, description: bookmark.description ?? '', category: bookmark.category ?? '' }
  showForm.value = true
}

function closeForm() {
  showForm.value = false
  editingBookmark.value = null
}

const isEmpty = () => Object.keys(grouped.value).length === 0

fetchBookmarks()
</script>

<template>
  <div>
    <div class="flex items-center justify-between">
      <div>
        <h1 class="text-2xl font-semibold text-gray-900 dark:text-white">Bookmark</h1>
        <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">Simpan link penting kamu.</p>
      </div>
      <button class="flex items-center gap-1.5 rounded-lg bg-primary-600 px-4 py-2 text-sm font-medium text-white hover:bg-primary-700" @click="openNew">
        <Plus :size="16" /> Tambah
      </button>
    </div>

    <!-- Grouped bookmarks -->
    <div v-if="!isEmpty()" class="mt-6 space-y-6">
      <section v-for="(items, category) in grouped" :key="category">
        <h2 class="mb-3 text-xs font-semibold uppercase tracking-wider text-gray-400 dark:text-gray-500">{{ category }}</h2>
        <div class="space-y-2">
          <div
            v-for="bookmark in items"
            :key="bookmark.id"
            class="group flex items-center gap-3 rounded-lg border border-gray-200 bg-white px-4 py-3 dark:border-gray-700 dark:bg-gray-800"
          >
            <div class="flex h-8 w-8 shrink-0 items-center justify-center rounded-lg bg-gray-100 dark:bg-gray-700">
              <Bookmark :size="16" class="text-gray-500 dark:text-gray-400" />
            </div>
            <div class="flex-1 min-w-0 cursor-pointer" @click="openEdit(bookmark)">
              <p class="text-sm font-medium text-gray-900 truncate dark:text-white">{{ bookmark.title }}</p>
              <p class="text-xs text-gray-400 truncate">{{ bookmark.url }}</p>
            </div>
            <a :href="bookmark.url" target="_blank" rel="noopener" class="rounded p-1 text-gray-400 hover:text-primary-600" @click.stop>
              <ExternalLink :size="16" />
            </a>
            <button class="rounded p-1 text-gray-400 opacity-0 group-hover:opacity-100 hover:text-red-600 transition-opacity" @click="deleteBookmark(bookmark)">
              <Trash2 :size="16" />
            </button>
          </div>
        </div>
      </section>
    </div>

    <!-- Empty -->
    <div v-else-if="!loading" class="mt-12 flex flex-col items-center text-center">
      <Bookmark :size="48" class="text-gray-300 dark:text-gray-600" />
      <p class="mt-3 text-sm text-gray-500 dark:text-gray-400">Belum ada bookmark.</p>
    </div>

    <!-- Form modal -->
    <Teleport to="body">
      <Transition name="modal">
        <div v-if="showForm" class="fixed inset-0 z-[200] flex items-center justify-center bg-black/50 p-4" @click.self="closeForm">
          <div class="w-full max-w-md rounded-xl bg-white px-5 py-4 shadow-xl dark:bg-gray-800">
            <h2 class="text-lg font-semibold text-gray-900 dark:text-white">{{ editingBookmark ? 'Edit Bookmark' : 'Bookmark Baru' }}</h2>
            <form class="mt-4 space-y-4" @submit.prevent="saveBookmark">
              <input v-model="form.title" type="text" placeholder="Judul" required class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm focus:border-primary-500 focus:outline-none focus:ring-1 focus:ring-primary-500 dark:border-gray-600 dark:bg-gray-700 dark:text-white" />
              <input v-model="form.url" type="url" placeholder="https://..." required class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm focus:border-primary-500 focus:outline-none focus:ring-1 focus:ring-primary-500 dark:border-gray-600 dark:bg-gray-700 dark:text-white" />
              <input v-model="form.category" type="text" placeholder="Kategori (opsional)" class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm focus:border-primary-500 focus:outline-none focus:ring-1 focus:ring-primary-500 dark:border-gray-600 dark:bg-gray-700 dark:text-white" />
              <textarea v-model="form.description" rows="2" placeholder="Deskripsi (opsional)" class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm focus:border-primary-500 focus:outline-none focus:ring-1 focus:ring-primary-500 dark:border-gray-600 dark:bg-gray-700 dark:text-white" />
              <div class="flex justify-end gap-2">
                <button type="button" class="rounded-lg border border-gray-300 px-4 py-2 text-sm font-medium text-gray-700 hover:bg-gray-50 dark:border-gray-600 dark:text-gray-300 dark:hover:bg-gray-700" @click="closeForm">Batal</button>
                <button type="submit" class="rounded-lg bg-primary-600 px-4 py-2 text-sm font-medium text-white hover:bg-primary-700">Simpan</button>
              </div>
            </form>
          </div>
        </div>
      </Transition>
    </Teleport>
  </div>
</template>

<style scoped>
.modal-enter-active, .modal-leave-active { transition: opacity 200ms ease; }
.modal-enter-from, .modal-leave-to { opacity: 0; }
</style>
