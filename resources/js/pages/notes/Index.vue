<script setup lang="ts">
import { ref, computed } from 'vue'
import { useToast } from '@purdia/toast'
import { formatDate } from '@purdia/utils'
import BaseEditor from '@purdia/ui/src/components/BaseEditor.vue'
import { Plus, Pin, Search, Trash2, FileText } from '@lucide/vue'
import type { Note } from '@/types/note'
import * as notesApi from '@/api/notes'

const toast = useToast()

const notes = ref<Note[]>([])
const search = ref('')
const loading = ref(false)
const showEditor = ref(false)
const editingNote = ref<Note | null>(null)

const form = ref({ title: '', content: '' })

async function fetchNotes() {
  loading.value = true
  try {
    const params = search.value ? { search: search.value } : undefined
    const response = await notesApi.fetchNotes(params)
    notes.value = response.data
  } catch {
    // Error toast handled globally by @purdia/http onError
  }
  loading.value = false
}

async function saveNote() {
  if (!form.value.title.trim()) return

  try {
    if (editingNote.value) {
      const response = await notesApi.updateNote(editingNote.value.id, form.value)
      const idx = notes.value.findIndex((n) => n.id === editingNote.value!.id)
      if (idx >= 0) notes.value[idx] = response.data
      toast.success('Catatan berhasil diperbarui.')
    } else {
      const response = await notesApi.createNote(form.value)
      notes.value.unshift(response.data)
      toast.success('Catatan berhasil dibuat.')
    }
    closeEditor()
  } catch {
    // Error toast handled globally by @purdia/http onError
  }
}

async function togglePin(note: Note) {
  try {
    await notesApi.toggleNotePin(note.id)
    note.is_pinned = !note.is_pinned
  } catch {
    // Error toast handled globally by @purdia/http onError
  }
}

async function deleteNote(note: Note) {
  try {
    await notesApi.deleteNote(note.id)
    notes.value = notes.value.filter((n) => n.id !== note.id)
    toast.success('Catatan berhasil dihapus.')
  } catch {
    // Error toast handled globally by @purdia/http onError
  }
}

function openNew() {
  editingNote.value = null
  form.value = { title: '', content: '' }
  showEditor.value = true
}

function openEdit(note: Note) {
  editingNote.value = note
  form.value = { title: note.title, content: note.content }
  showEditor.value = true
}

function closeEditor() {
  showEditor.value = false
  editingNote.value = null
  form.value = { title: '', content: '' }
}

const sortedNotes = computed(() =>
  [...notes.value].sort((a, b) => {
    if (a.is_pinned !== b.is_pinned) return b.is_pinned ? 1 : -1
    return new Date(b.created_at).getTime() - new Date(a.created_at).getTime()
  })
)

let searchTimer: ReturnType<typeof setTimeout> | null = null
function onSearch() {
  if (searchTimer) clearTimeout(searchTimer)
  searchTimer = setTimeout(fetchNotes, 300)
}

fetchNotes()
</script>

<template>
  <div>
    <div class="flex items-center justify-between">
      <div>
        <h1 class="text-2xl font-semibold text-gray-900 dark:text-white">Catatan</h1>
        <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">Tulis dan simpan catatan kamu.</p>
      </div>
      <button
        class="flex items-center gap-1.5 rounded-lg bg-primary-600 px-4 py-2 text-sm font-medium text-white hover:bg-primary-700"
        @click="openNew"
      >
        <Plus :size="16" />
        Catatan Baru
      </button>
    </div>

    <!-- Search -->
    <div class="relative mt-5">
      <Search :size="16" class="absolute left-3 top-1/2 -translate-y-1/2 text-gray-400" />
      <input
        v-model="search"
        type="text"
        placeholder="Cari catatan..."
        class="w-full rounded-lg border border-gray-300 py-2 pl-9 pr-4 text-sm focus:border-primary-500 focus:outline-none focus:ring-1 focus:ring-primary-500 dark:border-gray-600 dark:bg-gray-700 dark:text-white"
        @input="onSearch"
      />
    </div>

    <!-- Notes grid -->
    <div v-if="sortedNotes.length" class="mt-5 grid grid-cols-1 gap-4 sm:grid-cols-2 lg:grid-cols-3">
      <div
        v-for="note in sortedNotes"
        :key="note.id"
        class="group relative cursor-pointer rounded-xl border border-gray-200 bg-white p-4 transition-shadow hover:shadow-md dark:border-gray-700 dark:bg-gray-800"
        @click="openEdit(note)"
      >
        <div class="flex items-start justify-between">
          <h3 class="text-sm font-semibold text-gray-900 dark:text-white line-clamp-1">
            {{ note.title }}
          </h3>
          <Pin
            v-if="note.is_pinned"
            :size="14"
            class="shrink-0 text-primary-500"
          />
        </div>
        <p class="mt-2 text-xs text-gray-500 line-clamp-3 dark:text-gray-400" v-html="note.content"></p>
        <div class="mt-3 flex items-center justify-between">
          <span class="text-xs text-gray-400">{{ formatDate(note.updated_at) }}</span>
          <div class="flex gap-1 opacity-0 group-hover:opacity-100 transition-opacity">
            <button
              class="rounded p-1 text-gray-400 hover:text-primary-600"
              @click.stop="togglePin(note)"
              :title="note.is_pinned ? 'Unpin' : 'Pin'"
            >
              <Pin :size="14" />
            </button>
            <button
              class="rounded p-1 text-gray-400 hover:text-red-600"
              @click.stop="deleteNote(note)"
              title="Hapus"
            >
              <Trash2 :size="14" />
            </button>
          </div>
        </div>
      </div>
    </div>

    <!-- Empty state -->
    <div v-else-if="!loading" class="mt-12 flex flex-col items-center text-center">
      <FileText :size="48" class="text-gray-300 dark:text-gray-600" />
      <p class="mt-3 text-sm text-gray-500 dark:text-gray-400">Belum ada catatan. Buat yang pertama!</p>
    </div>

    <!-- Editor modal -->
    <Teleport to="body">
      <Transition name="modal">
        <div v-if="showEditor" class="fixed inset-0 z-[200] flex items-center justify-center bg-black/50 p-4" @click.self="closeEditor">
          <div class="w-full max-w-lg rounded-xl bg-white px-5 py-4 shadow-xl dark:bg-gray-800">
            <h2 class="text-lg font-semibold text-gray-900 dark:text-white">
              {{ editingNote ? 'Edit Catatan' : 'Catatan Baru' }}
            </h2>
            <form class="mt-4 space-y-4" @submit.prevent="saveNote">
              <input
                v-model="form.title"
                type="text"
                placeholder="Judul"
                required
                class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm focus:border-primary-500 focus:outline-none focus:ring-1 focus:ring-primary-500 dark:border-gray-600 dark:bg-gray-700 dark:text-white"
              />
              <BaseEditor
                v-model="form.content"
                placeholder="Tulis catatan..."
                variant="default"
                size="md"
              />
              <div class="flex justify-end gap-2">
                <button type="button" class="rounded-lg border border-gray-300 px-4 py-2 text-sm font-medium text-gray-700 hover:bg-gray-50 dark:border-gray-600 dark:text-gray-300 dark:hover:bg-gray-700" @click="closeEditor">Batal</button>
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
