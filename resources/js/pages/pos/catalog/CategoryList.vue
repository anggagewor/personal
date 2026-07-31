<script setup lang="ts">
import { ref } from 'vue'
import { useToast } from '@purdia/toast'
import BaseButton from '@purdia/ui/src/components/BaseButton.vue'
import BaseInput from '@purdia/ui/src/components/BaseInput.vue'
import BaseModal from '@purdia/ui/src/components/BaseModal.vue'
import { Plus, GripVertical, Pencil, Trash2, Folder } from '@lucide/vue'
import type { Category } from '@/types/pos'
import * as posApi from '@/api/pos'

const props = defineProps<{
  outletId: number
  categories: Category[]
  selectedCategoryId: number | null
}>()

const emit = defineEmits<{
  select: [id: number | null]
  updated: []
}>()

const toast = useToast()

const showForm = ref(false)
const editingCategory = ref<Category | null>(null)
const form = ref({ name: '' })
const draggingId = ref<number | null>(null)
const dragOverId = ref<number | null>(null)

function openCreate() {
  editingCategory.value = null
  form.value = { name: '' }
  showForm.value = true
}

function openEdit(category: Category) {
  editingCategory.value = category
  form.value = { name: category.name }
  showForm.value = true
}

async function saveCategory() {
  if (!form.value.name.trim()) return

  try {
    if (editingCategory.value) {
      await posApi.updateCategory(editingCategory.value.id, { name: form.value.name })
      toast.success('Kategori berhasil diperbarui.')
    } else {
      await posApi.createCategory(props.outletId, { name: form.value.name })
      toast.success('Kategori berhasil ditambahkan.')
    }
    showForm.value = false
    emit('updated')
  } catch {
    // Error handled globally
  }
}

async function deleteCategory(category: Category) {
  if (!confirm(`Hapus kategori "${category.name}"? Produk akan dipindahkan ke Tanpa Kategori.`)) return

  try {
    await posApi.deleteCategory(category.id)
    toast.success('Kategori berhasil dihapus.')
    if (props.selectedCategoryId === category.id) {
      emit('select', null)
    }
    emit('updated')
  } catch {
    // Error handled globally
  }
}

// Drag & Drop for reordering
function onDragStart(e: DragEvent, categoryId: number) {
  draggingId.value = categoryId
  if (e.dataTransfer) {
    e.dataTransfer.effectAllowed = 'move'
    e.dataTransfer.setData('text/plain', String(categoryId))
  }
}

function onDragEnd() {
  draggingId.value = null
  dragOverId.value = null
}

function onDragOver(e: DragEvent, categoryId: number) {
  e.preventDefault()
  if (e.dataTransfer) e.dataTransfer.dropEffect = 'move'
  dragOverId.value = categoryId
}

function onDragLeave() {
  dragOverId.value = null
}

async function onDrop(e: DragEvent, targetId: number) {
  e.preventDefault()
  dragOverId.value = null

  if (!draggingId.value || draggingId.value === targetId) return

  // Reorder: move draggingId before targetId
  const ids = props.categories.map((c) => c.id)
  const fromIdx = ids.indexOf(draggingId.value)
  const toIdx = ids.indexOf(targetId)
  if (fromIdx < 0 || toIdx < 0) return

  ids.splice(fromIdx, 1)
  ids.splice(toIdx, 0, draggingId.value)

  draggingId.value = null

  try {
    await posApi.reorderCategories({ ids })
    emit('updated')
  } catch {
    // Error handled globally
  }
}
</script>

<template>
  <div class="flex flex-col h-full">
    <!-- Header -->
    <div class="flex items-center justify-between px-4 py-3 border-b border-gray-200 dark:border-gray-700">
      <h2 class="text-sm font-semibold text-gray-700 dark:text-gray-200">Kategori</h2>
      <button
        class="rounded p-1 text-gray-400 hover:text-primary-600 hover:bg-gray-100 dark:hover:bg-gray-700"
        title="Tambah Kategori"
        @click="openCreate"
      >
        <Plus :size="16" />
      </button>
    </div>

    <!-- All products option -->
    <div class="px-2 pt-2">
      <button
        class="flex w-full items-center gap-2 rounded-lg px-3 py-2 text-sm transition-colors"
        :class="selectedCategoryId === null ? 'bg-primary-50 text-primary-700 dark:bg-primary-900/20 dark:text-primary-300' : 'text-gray-600 hover:bg-gray-100 dark:text-gray-400 dark:hover:bg-gray-700'"
        @click="emit('select', null)"
      >
        <Folder :size="16" />
        Semua Produk
      </button>
    </div>

    <!-- Category list -->
    <div class="flex-1 overflow-y-auto px-2 py-2 space-y-0.5">
      <div
        v-for="category in categories"
        :key="category.id"
        draggable="true"
        class="group flex items-center gap-1 rounded-lg transition-colors"
        :class="[
          selectedCategoryId === category.id ? 'bg-primary-50 dark:bg-primary-900/20' : 'hover:bg-gray-100 dark:hover:bg-gray-700',
          dragOverId === category.id ? 'ring-2 ring-primary-400' : '',
          draggingId === category.id ? 'opacity-50' : '',
        ]"
        @dragstart="onDragStart($event, category.id)"
        @dragend="onDragEnd"
        @dragover="onDragOver($event, category.id)"
        @dragleave="onDragLeave"
        @drop="onDrop($event, category.id)"
      >
        <div class="cursor-grab p-1 text-gray-300 dark:text-gray-600">
          <GripVertical :size="14" />
        </div>
        <button
          class="flex-1 truncate text-left text-sm py-2"
          :class="selectedCategoryId === category.id ? 'text-primary-700 font-medium dark:text-primary-300' : 'text-gray-700 dark:text-gray-300'"
          @click="emit('select', category.id)"
        >
          {{ category.name }}
        </button>
        <div class="flex items-center gap-0.5 pr-1 opacity-0 group-hover:opacity-100 transition-opacity">
          <button
            class="rounded p-1 text-gray-400 hover:text-primary-600"
            title="Edit"
            @click.stop="openEdit(category)"
          >
            <Pencil :size="12" />
          </button>
          <button
            class="rounded p-1 text-gray-400 hover:text-red-600"
            title="Hapus"
            @click.stop="deleteCategory(category)"
          >
            <Trash2 :size="12" />
          </button>
        </div>
      </div>

      <div v-if="!categories.length" class="py-6 text-center">
        <p class="text-xs text-gray-400">Belum ada kategori.</p>
      </div>
    </div>

    <!-- Category Form Modal -->
    <BaseModal v-model="showForm" size="sm" persistent>
      <template #default>
        <h2 class="text-lg font-semibold text-gray-900 dark:text-white">
          {{ editingCategory ? 'Edit Kategori' : 'Kategori Baru' }}
        </h2>
        <form class="mt-4 space-y-4" @submit.prevent="saveCategory">
          <BaseInput v-model="form.name" label="Nama Kategori" placeholder="contoh: Makanan" required />
          <div class="flex justify-end gap-2 pt-2">
            <BaseButton variant="secondary" size="sm" type="button" @click="showForm = false">Batal</BaseButton>
            <BaseButton variant="primary" size="sm" type="submit">Simpan</BaseButton>
          </div>
        </form>
      </template>
    </BaseModal>
  </div>
</template>
