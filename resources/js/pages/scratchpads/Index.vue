<script setup lang="ts">
import { ref } from 'vue'
import BaseButton from '@purdia/ui/src/components/BaseButton.vue'
import { Plus, Trash2 } from '@lucide/vue'
import type { Scratchpad } from '@/types/scratchpad'
import * as scratchpadsApi from '@/api/scratchpads'

const pads = ref<Scratchpad[]>([])
const loading = ref(false)

const colors = ['bg-yellow-100 dark:bg-yellow-900/30', 'bg-blue-100 dark:bg-blue-900/30', 'bg-green-100 dark:bg-green-900/30', 'bg-pink-100 dark:bg-pink-900/30', 'bg-purple-100 dark:bg-purple-900/30']

function getColorClass(color: string | null, index: number): string {
  if (color) return color
  return colors[index % colors.length]
}

async function fetchPads() {
  loading.value = true
  try {
    const res = await scratchpadsApi.fetchScratchpads()
    pads.value = res.data
  } catch { /* */ }
  loading.value = false
}

async function addPad() {
  try {
    const res = await scratchpadsApi.createScratchpad({ content: '' })
    pads.value.push(res.data)
  } catch { /* */ }
}

let saveTimer: ReturnType<typeof setTimeout> | null = null
function onInput(pad: Scratchpad) {
  if (saveTimer) clearTimeout(saveTimer)
  saveTimer = setTimeout(() => {
    scratchpadsApi.updateScratchpad(pad.id, { content: pad.content })
  }, 500)
}

async function deletePad(pad: Scratchpad) {
  await scratchpadsApi.deleteScratchpad(pad.id)
  pads.value = pads.value.filter((p) => p.id !== pad.id)
}

fetchPads()
</script>

<template>
  <div>
    <div class="flex items-center justify-between">
      <div>
        <h1 class="text-2xl font-semibold text-gray-900 dark:text-white">Scratchpad</h1>
        <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">Catatan cepat, sticky notes digital.</p>
      </div>
      <BaseButton variant="primary" size="sm" :icon="Plus" @click="addPad">Tambah</BaseButton>
    </div>

    <div class="mt-6 grid grid-cols-1 gap-4 sm:grid-cols-2 lg:grid-cols-3">
      <div
        v-for="(pad, idx) in pads"
        :key="pad.id"
        class="group relative rounded-xl border p-4 transition-shadow hover:shadow-md"
        :class="getColorClass(pad.color, idx)"
      >
        <textarea
          v-model="pad.content"
          class="w-full resize-none border-none bg-transparent text-sm text-gray-800 placeholder-gray-400 focus:outline-none dark:text-gray-200"
          rows="5"
          placeholder="Tulis sesuatu..."
          @input="onInput(pad)"
        />
        <button
          class="absolute right-2 top-2 rounded p-1 text-gray-400 opacity-0 transition-opacity group-hover:opacity-100 hover:text-red-500"
          @click="deletePad(pad)"
        >
          <Trash2 :size="14" />
        </button>
      </div>
    </div>

    <div v-if="!loading && !pads.length" class="mt-12 text-center">
      <p class="text-gray-400">Belum ada scratchpad. Klik tombol Tambah.</p>
    </div>
  </div>
</template>
