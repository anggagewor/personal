<script setup lang="ts">
import { ref, computed, onMounted } from 'vue'
import { formatDate } from '@purdia/utils'
import BaseButton from '@purdia/ui/src/components/BaseButton.vue'
import BaseModal from '@purdia/ui/src/components/BaseModal.vue'
import BaseInput from '@purdia/ui/src/components/BaseInput.vue'
import BaseTextarea from '@purdia/ui/src/components/BaseTextarea.vue'
import BaseBadge from '@purdia/ui/src/components/BaseBadge.vue'
import BaseEmptyState from '@purdia/ui/src/components/BaseEmptyState.vue'
import { Heart, Plus, Trash2, Check } from '@lucide/vue'
import type { WishlistItem } from '@/types/wishlist'
import * as wishlistsApi from '@/api/wishlists'

type FilterType = 'all' | 'active' | 'completed'

const items = ref<WishlistItem[]>([])
const loading = ref(true)
const filter = ref<FilterType>('all')
const showModal = ref(false)

const form = ref({
  title: '',
  description: '',
  category: '',
})

const filtered = computed(() => {
  if (filter.value === 'active') return items.value.filter((i) => !i.is_completed)
  if (filter.value === 'completed') return items.value.filter((i) => i.is_completed)
  return items.value
})

async function fetchItems() {
  try {
    const res = await wishlistsApi.fetchWishlists()
    items.value = res.data?.data || []
  } catch {
    // handle error
  } finally {
    loading.value = false
  }
}

async function addItem() {
  if (!form.value.title.trim()) return
  try {
    const res = await wishlistsApi.createWishlist({
      title: form.value.title,
      description: form.value.description || null,
      category: form.value.category || null,
    })
    items.value.unshift(res.data?.data)
    showModal.value = false
    form.value = { title: '', description: '', category: '' }
  } catch {
    // handle error
  }
}

async function toggleComplete(item: WishlistItem) {
  try {
    const res = await wishlistsApi.updateWishlist(item.id, {
      is_completed: !item.is_completed,
    })
    const idx = items.value.findIndex((i) => i.id === item.id)
    if (idx !== -1) items.value[idx] = res.data?.data
  } catch {
    // handle error
  }
}

async function deleteItem(item: WishlistItem) {
  try {
    await wishlistsApi.deleteWishlist(item.id)
    items.value = items.value.filter((i) => i.id !== item.id)
  } catch {
    // handle error
  }
}

onMounted(fetchItems)
</script>

<template>
  <div class="space-y-6">
    <!-- Header -->
    <div class="flex items-center justify-between">
      <div class="flex items-center gap-3">
        <Heart :size="24" class="text-pink-500" />
        <h1 class="text-2xl font-bold text-gray-900 dark:text-white">Wishlist</h1>
      </div>
      <BaseButton :icon="Plus" @click="showModal = true">
        Tambah
      </BaseButton>
    </div>

    <!-- Filters -->
    <div class="flex gap-2">
      <button
        v-for="f in (['all', 'active', 'completed'] as FilterType[])"
        :key="f"
        class="rounded-lg px-3 py-1.5 text-sm font-medium transition-colors"
        :class="filter === f
          ? 'bg-primary-100 text-primary-700 dark:bg-primary-900/30 dark:text-primary-300'
          : 'text-gray-600 hover:bg-gray-100 dark:text-gray-400 dark:hover:bg-gray-700'"
        @click="filter = f"
      >
        {{ f === 'all' ? 'Semua' : f === 'active' ? 'Aktif' : 'Selesai' }}
      </button>
    </div>

    <!-- Loading -->
    <div v-if="loading" class="flex items-center justify-center py-20">
      <div class="h-8 w-8 animate-spin rounded-full border-4 border-primary-500 border-t-transparent"></div>
    </div>

    <!-- Empty state -->
    <BaseEmptyState
      v-else-if="!filtered.length"
      title="Belum ada wishlist"
      description="Tambahkan hal yang kamu inginkan ke daftar wishlist."
    />

    <!-- Grid -->
    <div v-else class="grid gap-4 sm:grid-cols-2 lg:grid-cols-3">
      <div
        v-for="item in filtered"
        :key="item.id"
        class="rounded-xl border border-gray-200 bg-white p-4 transition-shadow hover:shadow-md dark:border-gray-700 dark:bg-gray-800"
        :class="{ 'opacity-60': item.is_completed }"
      >
        <div class="flex items-start justify-between gap-2">
          <div class="flex items-start gap-3">
            <button
              class="mt-0.5 flex h-5 w-5 shrink-0 items-center justify-center rounded border transition-colors"
              :class="item.is_completed
                ? 'border-green-500 bg-green-500 text-white'
                : 'border-gray-300 dark:border-gray-600'"
              @click="toggleComplete(item)"
            >
              <Check v-if="item.is_completed" :size="12" />
            </button>
            <div>
              <h3
                class="text-sm font-medium text-gray-900 dark:text-white"
                :class="{ 'line-through': item.is_completed }"
              >
                {{ item.title }}
              </h3>
              <p v-if="item.description" class="mt-1 text-xs text-gray-500 dark:text-gray-400">
                {{ item.description }}
              </p>
            </div>
          </div>
          <button
            class="shrink-0 rounded p-1 text-gray-400 transition-colors hover:bg-red-50 hover:text-red-500 dark:hover:bg-red-900/20"
            @click="deleteItem(item)"
          >
            <Trash2 :size="14" />
          </button>
        </div>

        <div class="mt-3 flex items-center gap-2">
          <BaseBadge v-if="item.category" variant="secondary" size="sm">
            {{ item.category }}
          </BaseBadge>
          <span v-if="item.completed_at" class="text-[10px] text-gray-400 dark:text-gray-500">
            Selesai {{ formatDate(item.completed_at) }}
          </span>
        </div>
      </div>
    </div>

    <!-- Add Modal -->
    <BaseModal :open="showModal" title="Tambah Wishlist" @close="showModal = false">
      <form class="space-y-4" @submit.prevent="addItem">
        <BaseInput
          v-model="form.title"
          label="Judul"
          placeholder="Apa yang kamu inginkan?"
          required
        />
        <BaseTextarea
          v-model="form.description"
          label="Deskripsi"
          placeholder="Detail tambahan (opsional)"
          :rows="3"
        />
        <BaseInput
          v-model="form.category"
          label="Kategori"
          placeholder="Misalnya: Gadget, Buku, Travel"
        />
        <div class="flex justify-end gap-2">
          <BaseButton variant="secondary" type="button" @click="showModal = false">Batal</BaseButton>
          <BaseButton type="submit">Simpan</BaseButton>
        </div>
      </form>
    </BaseModal>
  </div>
</template>
