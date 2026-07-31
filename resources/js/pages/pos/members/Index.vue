<script setup lang="ts">
import { ref, computed, watch } from 'vue'
import { useRoute } from 'vue-router'
import { useToast } from '@purdia/toast'
import { formatDate } from '@purdia/utils'
import BaseButton from '@purdia/ui/src/components/BaseButton.vue'
import BaseInput from '@purdia/ui/src/components/BaseInput.vue'
import { Plus, Pencil, Trash2, Users, Search, ChevronLeft, ChevronRight } from '@lucide/vue'
import type { Member } from '@/types/pos'
import * as posApi from '@/api/pos'
import MemberForm from './MemberForm.vue'
import { usePosOutlet } from '@/composables/usePosOutlet'

const route = useRoute()
const toast = useToast()
const { outletId } = usePosOutlet()

const members = ref<Member[]>([])
const loading = ref(true)
const currentPage = ref(1)
const totalPages = ref(1)
const perPage = 20

// Search
const searchQuery = ref('')
const searchTimeout = ref<ReturnType<typeof setTimeout> | null>(null)

// Form modal
const showForm = ref(false)
const editingMember = ref<Member | null>(null)

async function fetchMembers() {
  if (!outletId.value) return
  loading.value = true
  try {
    if (searchQuery.value.trim()) {
      const res = await posApi.searchMembers(outletId.value, { q: searchQuery.value.trim() })
      members.value = res.data
      totalPages.value = 1
    } else {
      const res = await posApi.fetchMembers(outletId.value, { page: currentPage.value, per_page: perPage })
      if (Array.isArray(res.data)) {
        members.value = res.data
      } else {
        const paginated = res.data as unknown as { data: Member[]; last_page: number }
        members.value = paginated.data ?? res.data
        totalPages.value = paginated.last_page ?? 1
      }
    }
  } catch {
    // Error handled globally
  } finally {
    loading.value = false
  }
}

function onSearchInput() {
  if (searchTimeout.value) clearTimeout(searchTimeout.value)
  searchTimeout.value = setTimeout(() => {
    currentPage.value = 1
    fetchMembers()
  }, 400)
}

function openCreate() {
  editingMember.value = null
  showForm.value = true
}

function openEdit(member: Member) {
  editingMember.value = member
  showForm.value = true
}

async function deleteMember(member: Member) {
  if (!confirm(`Hapus member "${member.name}"? Data transaksi tetap tersimpan.`)) return
  try {
    await posApi.deleteMember(member.id)
    toast.success('Member berhasil dihapus.')
    fetchMembers()
  } catch {
    // Error handled globally
  }
}

function goToPage(page: number) {
  if (page < 1 || page > totalPages.value) return
  currentPage.value = page
}

function onSaved() {
  fetchMembers()
}

watch(currentPage, () => fetchMembers())

watch(outletId, (val) => { if (val) fetchMembers() })

// Initial load
if (outletId.value) fetchMembers()
</script>

<template>
  <div>
    <!-- Header -->
    <div class="flex items-center justify-between">
      <div>
        <h1 class="text-2xl font-semibold text-gray-900 dark:text-white">Member</h1>
        <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">Kelola data member outlet.</p>
      </div>
      <BaseButton variant="primary" size="sm" :icon="Plus" @click="openCreate">
        Member Baru
      </BaseButton>
    </div>

    <!-- Search -->
    <div class="mt-6 max-w-sm">
      <BaseInput
        v-model="searchQuery"
        placeholder="Cari nama atau no. telepon..."
        :icon="Search"
        @input="onSearchInput"
      />
    </div>

    <!-- Loading -->
    <div v-if="loading" class="mt-6 py-8 text-center text-sm text-gray-400">Memuat data...</div>

    <!-- Empty state -->
    <div v-else-if="!members.length" class="mt-12 flex flex-col items-center text-center">
      <div class="flex h-16 w-16 items-center justify-center rounded-2xl bg-gray-100 dark:bg-gray-800">
        <Users :size="28" class="text-gray-400" />
      </div>
      <h3 class="mt-4 text-base font-medium text-gray-900 dark:text-white">
        {{ searchQuery ? 'Member tidak ditemukan' : 'Belum ada member' }}
      </h3>
      <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">
        {{ searchQuery ? 'Coba kata kunci lain.' : 'Tambahkan member pertama untuk outlet ini.' }}
      </p>
      <BaseButton v-if="!searchQuery" variant="primary" size="sm" :icon="Plus" class="mt-4" @click="openCreate">
        Tambah Member
      </BaseButton>
    </div>

    <!-- Member table -->
    <div v-else class="mt-6 overflow-x-auto rounded-xl border border-gray-200 bg-white dark:border-gray-700 dark:bg-gray-800">
      <table class="w-full text-left text-sm">
        <thead>
          <tr class="border-b border-gray-200 dark:border-gray-700">
            <th class="px-4 py-3 font-medium text-gray-500 dark:text-gray-400">Nama</th>
            <th class="px-4 py-3 font-medium text-gray-500 dark:text-gray-400">No. Telepon</th>
            <th class="px-4 py-3 font-medium text-gray-500 dark:text-gray-400">Email</th>
            <th class="px-4 py-3 font-medium text-gray-500 dark:text-gray-400">Terdaftar</th>
            <th class="px-4 py-3 font-medium text-gray-500 dark:text-gray-400"></th>
          </tr>
        </thead>
        <tbody>
          <tr
            v-for="member in members"
            :key="member.id"
            class="border-b border-gray-100 last:border-0 hover:bg-gray-50 dark:border-gray-700/50 dark:hover:bg-gray-700/30"
          >
            <td class="px-4 py-3 font-medium text-gray-900 dark:text-white">
              {{ member.name }}
            </td>
            <td class="px-4 py-3 text-gray-600 dark:text-gray-300">
              {{ member.phone }}
            </td>
            <td class="px-4 py-3 text-gray-600 dark:text-gray-300">
              {{ member.email ?? '—' }}
            </td>
            <td class="px-4 py-3 text-gray-600 dark:text-gray-300">
              {{ formatDate(member.created_at, { day: 'numeric', month: 'short', year: 'numeric' }) }}
            </td>
            <td class="px-4 py-3">
              <div class="flex items-center gap-1">
                <button
                  class="rounded p-1.5 text-gray-400 hover:text-primary-600 transition-colors"
                  @click="openEdit(member)"
                  title="Edit"
                >
                  <Pencil :size="14" />
                </button>
                <button
                  class="rounded p-1.5 text-gray-400 hover:text-red-500 transition-colors"
                  @click="deleteMember(member)"
                  title="Hapus"
                >
                  <Trash2 :size="14" />
                </button>
              </div>
            </td>
          </tr>
        </tbody>
      </table>

      <!-- Pagination -->
      <div
        v-if="totalPages > 1"
        class="flex items-center justify-between border-t border-gray-200 px-4 py-3 dark:border-gray-700"
      >
        <span class="text-xs text-gray-500 dark:text-gray-400">
          Halaman {{ currentPage }} dari {{ totalPages }}
        </span>
        <div class="flex gap-1">
          <button
            class="rounded p-1.5 text-gray-400 hover:text-gray-600 disabled:opacity-30 dark:hover:text-gray-300"
            :disabled="currentPage <= 1"
            @click="goToPage(currentPage - 1)"
          >
            <ChevronLeft :size="16" />
          </button>
          <button
            class="rounded p-1.5 text-gray-400 hover:text-gray-600 disabled:opacity-30 dark:hover:text-gray-300"
            :disabled="currentPage >= totalPages"
            @click="goToPage(currentPage + 1)"
          >
            <ChevronRight :size="16" />
          </button>
        </div>
      </div>
    </div>

    <!-- Member Form Modal -->
    <MemberForm
      v-model="showForm"
      :outlet-id="outletId"
      :editing-member="editingMember"
      @saved="onSaved"
    />
  </div>
</template>
