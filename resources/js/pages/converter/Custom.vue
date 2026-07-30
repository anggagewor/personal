<script setup lang="ts">
import { ref, computed } from 'vue'
import { useToast } from '@purdia/toast'
import BaseButton from '@purdia/ui/src/components/BaseButton.vue'
import BaseInput from '@purdia/ui/src/components/BaseInput.vue'
import BaseModal from '@purdia/ui/src/components/BaseModal.vue'
import BaseSelect from '@purdia/ui/src/components/BaseSelect.vue'
import BaseEmptyState from '@purdia/ui/src/components/BaseEmptyState.vue'
import { Plus, Trash2, Pencil, ArrowLeftRight, ArrowLeft } from '@lucide/vue'
import type { CustomUnit, CustomCategory } from '@/types/converter'
import * as converterApi from '@/api/converter'

const toast = useToast()
const categories = ref<CustomCategory[]>([])
const loading = ref(false)

// Category form
const showCategoryModal = ref(false)
const editingCategory = ref<CustomCategory | null>(null)
const categoryForm = ref({ name: '', description: '', icon: '' })

// Unit form
const showUnitModal = ref(false)
const editingUnit = ref<CustomUnit | null>(null)
const unitForm = ref({ category_id: 0, name: '', symbol: '', to_base: '1', is_base: false })

// Converter state
const activeCategory = ref<CustomCategory | null>(null)
const fromUnit = ref('')
const toUnit = ref('')
const fromValue = ref('1')

const result = computed(() => {
  if (!activeCategory.value) return ''
  const val = parseFloat(fromValue.value)
  if (isNaN(val)) return ''

  const from = activeCategory.value.units.find(u => u.id.toString() === fromUnit.value)
  const to = activeCategory.value.units.find(u => u.id.toString() === toUnit.value)
  if (!from || !to) return ''

  const baseValue = val * from.to_base
  const converted = baseValue / to.to_base
  if (Number.isInteger(converted)) return converted.toString()
  return parseFloat(converted.toPrecision(10)).toString()
})

const unitOptions = computed(() => {
  if (!activeCategory.value) return []
  return activeCategory.value.units.map(u => ({ label: `${u.name} (${u.symbol})`, value: u.id.toString() }))
})

async function fetchCategories() {
  loading.value = true
  try {
    const res = await converterApi.fetchCategories()
    categories.value = res.data
  } catch { /* */ }
  loading.value = false
}

function openCategoryModal(cat?: CustomCategory) {
  editingCategory.value = cat ?? null
  categoryForm.value = {
    name: cat?.name ?? '',
    description: cat?.description ?? '',
    icon: cat?.icon ?? '',
  }
  showCategoryModal.value = true
}

async function saveCategory() {
  try {
    if (editingCategory.value) {
      await converterApi.updateCategory(editingCategory.value.id, categoryForm.value)
      toast.success('Kategori berhasil diperbarui.')
    } else {
      await converterApi.createCategory(categoryForm.value)
      toast.success('Kategori berhasil dibuat.')
    }
    showCategoryModal.value = false
    fetchCategories()
  } catch { /* */ }
}

async function deleteCategory(cat: CustomCategory) {
  if (!confirm(`Hapus kategori "${cat.name}" beserta semua satuan di dalamnya?`)) return
  try {
    await converterApi.deleteCategory(cat.id)
    toast.success('Kategori berhasil dihapus.')
    if (activeCategory.value?.id === cat.id) activeCategory.value = null
    fetchCategories()
  } catch { /* */ }
}

function openUnitModal(categoryId: number, unit?: CustomUnit) {
  editingUnit.value = unit ?? null
  unitForm.value = {
    category_id: categoryId,
    name: unit?.name ?? '',
    symbol: unit?.symbol ?? '',
    to_base: unit?.to_base?.toString() ?? '1',
    is_base: unit?.is_base ?? false,
  }
  showUnitModal.value = true
}

async function saveUnit() {
  try {
    const payload = {
      ...unitForm.value,
      to_base: parseFloat(unitForm.value.to_base),
    }
    if (editingUnit.value) {
      await converterApi.updateUnit(editingUnit.value.id, payload)
      toast.success('Satuan berhasil diperbarui.')
    } else {
      await converterApi.createUnit(payload)
      toast.success('Satuan berhasil ditambahkan.')
    }
    showUnitModal.value = false
    fetchCategories()
  } catch { /* */ }
}

async function deleteUnit(unit: CustomUnit) {
  if (!confirm(`Hapus satuan "${unit.name}"?`)) return
  try {
    await converterApi.deleteUnit(unit.id)
    toast.success('Satuan berhasil dihapus.')
    fetchCategories()
  } catch { /* */ }
}

function selectCategory(cat: CustomCategory) {
  activeCategory.value = cat
  if (cat.units.length >= 2) {
    fromUnit.value = cat.units[0].id.toString()
    toUnit.value = cat.units[1].id.toString()
  } else if (cat.units.length === 1) {
    fromUnit.value = cat.units[0].id.toString()
    toUnit.value = cat.units[0].id.toString()
  }
  fromValue.value = '1'
}

function swapUnits() {
  const temp = fromUnit.value
  fromUnit.value = toUnit.value
  toUnit.value = temp
}

fetchCategories()
</script>

<template>
  <div>
    <div class="mb-6 flex items-center gap-3">
      <router-link
        to="/converter"
        class="flex h-8 w-8 items-center justify-center rounded-lg text-gray-500 transition-colors hover:bg-gray-100 hover:text-gray-700 dark:hover:bg-gray-700 dark:hover:text-gray-200"
      >
        <ArrowLeft class="h-4 w-4" />
      </router-link>
      <div>
        <h1 class="text-2xl font-semibold text-gray-900 dark:text-white">Satuan Custom</h1>
        <p class="mt-0.5 text-sm text-gray-500 dark:text-gray-400">Buat kategori dan satuan konversi sendiri</p>
      </div>
    </div>

    <div class="grid grid-cols-1 gap-6 lg:grid-cols-2">
      <!-- Left: Categories & Units Management -->
      <div>
        <div class="flex items-center justify-between">
          <h2 class="text-lg font-medium text-gray-900 dark:text-white">Kategori</h2>
          <BaseButton size="sm" variant="primary" :icon="Plus" @click="openCategoryModal()">
            Baru
          </BaseButton>
        </div>

        <div v-if="categories.length === 0 && !loading" class="mt-4">
          <BaseEmptyState
            title="Belum ada kategori"
            description="Buat kategori custom pertamamu, contoh: Rokok, Panjang Kabel, dll."
          />
        </div>

        <div class="mt-4 space-y-3">
          <div
            v-for="cat in categories"
            :key="cat.id"
            class="rounded-xl border border-gray-200 bg-white p-4 dark:border-gray-700 dark:bg-gray-800"
            :class="activeCategory?.id === cat.id ? 'ring-2 ring-primary-500' : ''"
          >
            <div class="flex items-center justify-between">
              <button class="text-left" @click="selectCategory(cat)">
                <p class="font-medium text-gray-900 dark:text-white">{{ cat.name }}</p>
                <p v-if="cat.description" class="text-xs text-gray-500">{{ cat.description }}</p>
              </button>
              <div class="flex items-center gap-1">
                <button class="rounded p-1 text-gray-400 hover:text-primary-500" @click="openCategoryModal(cat)">
                  <Pencil class="h-3.5 w-3.5" />
                </button>
                <button class="rounded p-1 text-gray-400 hover:text-red-500" @click="deleteCategory(cat)">
                  <Trash2 class="h-3.5 w-3.5" />
                </button>
              </div>
            </div>

            <!-- Units list -->
            <div class="mt-3 space-y-1">
              <div
                v-for="unit in cat.units"
                :key="unit.id"
                class="flex items-center justify-between rounded-lg bg-gray-50 px-3 py-1.5 text-sm dark:bg-gray-700/50"
              >
                <span class="text-gray-700 dark:text-gray-300">
                  {{ unit.name }}
                  <span class="text-gray-400">({{ unit.symbol }})</span>
                  <span v-if="unit.is_base" class="ml-1 rounded bg-primary-100 px-1.5 py-0.5 text-xs font-medium text-primary-700 dark:bg-primary-900/30 dark:text-primary-400">
                    base
                  </span>
                  <span v-else class="text-gray-400"> = {{ unit.to_base }} base</span>
                </span>
                <div class="flex items-center gap-1">
                  <button class="rounded p-1 text-gray-400 hover:text-primary-500" @click="openUnitModal(cat.id, unit)">
                    <Pencil class="h-3 w-3" />
                  </button>
                  <button class="rounded p-1 text-gray-400 hover:text-red-500" @click="deleteUnit(unit)">
                    <Trash2 class="h-3 w-3" />
                  </button>
                </div>
              </div>
            </div>

            <button
              class="mt-2 flex items-center gap-1 text-xs text-primary-600 hover:text-primary-700 dark:text-primary-400"
              @click="openUnitModal(cat.id)"
            >
              <Plus class="h-3 w-3" />
              Tambah Satuan
            </button>
          </div>
        </div>
      </div>

      <!-- Right: Converter -->
      <div>
        <h2 class="text-lg font-medium text-gray-900 dark:text-white">Konversi</h2>

        <div v-if="!activeCategory" class="mt-4">
          <BaseEmptyState
            title="Pilih kategori"
            description="Klik salah satu kategori di sebelah kiri untuk mulai konversi."
          />
        </div>

        <div v-else class="mt-4 rounded-xl border border-gray-200 bg-white p-6 dark:border-gray-700 dark:bg-gray-800">
          <p class="mb-4 text-sm font-medium text-gray-500 dark:text-gray-400">{{ activeCategory.name }}</p>

          <div v-if="activeCategory.units.length < 2">
            <p class="text-sm text-gray-500">Tambahkan minimal 2 satuan untuk mulai konversi.</p>
          </div>

          <template v-else>
            <div class="space-y-3">
              <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">Dari</label>
              <BaseInput v-model="fromValue" type="number" placeholder="Masukkan angka" />
              <BaseSelect v-model="fromUnit" :options="unitOptions" />
            </div>

            <div class="my-4 flex justify-center">
              <button
                class="flex h-10 w-10 items-center justify-center rounded-full border border-gray-200 text-gray-500 transition-colors hover:bg-gray-100 hover:text-primary-600 dark:border-gray-600 dark:hover:bg-gray-700 dark:hover:text-primary-400"
                @click="swapUnits"
              >
                <ArrowLeftRight class="h-4 w-4 rotate-90" />
              </button>
            </div>

            <div class="space-y-3">
              <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">Ke</label>
              <div class="rounded-lg border border-gray-200 bg-gray-50 px-4 py-3 text-lg font-semibold text-gray-900 dark:border-gray-600 dark:bg-gray-700 dark:text-white">
                {{ result || '—' }}
              </div>
              <BaseSelect v-model="toUnit" :options="unitOptions" />
            </div>
          </template>
        </div>
      </div>
    </div>

    <!-- Category Modal -->
    <BaseModal v-model="showCategoryModal" :title="editingCategory ? 'Edit Kategori' : 'Kategori Baru'">
      <div class="space-y-4">
        <BaseInput v-model="categoryForm.name" label="Nama Kategori" placeholder="contoh: Rokok" />
        <BaseInput v-model="categoryForm.description" label="Deskripsi (opsional)" placeholder="contoh: Konversi satuan rokok" />
        <BaseInput v-model="categoryForm.icon" label="Icon (opsional)" placeholder="contoh: Cigarette" />
      </div>
      <template #footer>
        <BaseButton variant="secondary" @click="showCategoryModal = false">Batal</BaseButton>
        <BaseButton variant="primary" @click="saveCategory">Simpan</BaseButton>
      </template>
    </BaseModal>

    <!-- Unit Modal -->
    <BaseModal v-model="showUnitModal" :title="editingUnit ? 'Edit Satuan' : 'Tambah Satuan'">
      <div class="space-y-4">
        <BaseInput v-model="unitForm.name" label="Nama Satuan" placeholder="contoh: Bungkus" />
        <BaseInput v-model="unitForm.symbol" label="Simbol" placeholder="contoh: bks" />
        <BaseInput v-model="unitForm.to_base" label="Nilai ke Base" type="number" placeholder="contoh: 20" />
        <p class="text-xs text-gray-500 dark:text-gray-400">
          Berapa unit base yang setara dengan 1 satuan ini. Contoh: 1 Bungkus = 20 Batang → isi 20.
          Untuk satuan base sendiri, isi 1.
        </p>
        <label class="flex items-center gap-2 text-sm text-gray-700 dark:text-gray-300">
          <input v-model="unitForm.is_base" type="checkbox" class="rounded border-gray-300 text-primary-600" />
          Jadikan sebagai satuan base
        </label>
      </div>
      <template #footer>
        <BaseButton variant="secondary" @click="showUnitModal = false">Batal</BaseButton>
        <BaseButton variant="primary" @click="saveUnit">Simpan</BaseButton>
      </template>
    </BaseModal>
  </div>
</template>
