<script setup lang="ts">
import { ArrowLeftRight, ArrowLeft } from '@lucide/vue'
import BaseSelect from '@purdia/ui/src/components/BaseSelect.vue'
import BaseInput from '@purdia/ui/src/components/BaseInput.vue'
import BaseButton from '@purdia/ui/src/components/BaseButton.vue'
import type { UnitDef } from './useConverter'

interface Props {
  title: string
  fromValue: string
  fromUnit: string
  toUnit: string
  result: string
  units: UnitDef[]
}

const props = defineProps<Props>()

const emit = defineEmits<{
  'update:fromValue': [value: string]
  'update:fromUnit': [value: string]
  'update:toUnit': [value: string]
  swap: []
}>()

const unitOptions = props.units.map(u => ({ label: u.label, value: u.id }))
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
        <h1 class="text-2xl font-semibold text-gray-900 dark:text-white">{{ title }}</h1>
        <p class="mt-0.5 text-sm text-gray-500 dark:text-gray-400">Konversi satuan {{ title.toLowerCase() }}</p>
      </div>
    </div>

    <div class="mx-auto max-w-xl">
      <div class="rounded-xl border border-gray-200 bg-white p-6 dark:border-gray-700 dark:bg-gray-800">
        <!-- From -->
        <div class="space-y-3">
          <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">Dari</label>
          <BaseInput
            type="number"
            :model-value="fromValue"
            placeholder="Masukkan angka"
            @update:model-value="emit('update:fromValue', $event)"
          />
          <BaseSelect
            :model-value="fromUnit"
            :options="unitOptions"
            @update:model-value="emit('update:fromUnit', $event)"
          />
        </div>

        <!-- Swap button -->
        <div class="my-4 flex justify-center">
          <button
            class="flex h-10 w-10 items-center justify-center rounded-full border border-gray-200 text-gray-500 transition-colors hover:bg-gray-100 hover:text-primary-600 dark:border-gray-600 dark:hover:bg-gray-700 dark:hover:text-primary-400"
            @click="emit('swap')"
          >
            <ArrowLeftRight class="h-4 w-4 rotate-90" />
          </button>
        </div>

        <!-- To -->
        <div class="space-y-3">
          <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">Ke</label>
          <div class="rounded-lg border border-gray-200 bg-gray-50 px-4 py-3 text-lg font-semibold text-gray-900 dark:border-gray-600 dark:bg-gray-700 dark:text-white">
            {{ result || '—' }}
          </div>
          <BaseSelect
            :model-value="toUnit"
            :options="unitOptions"
            @update:model-value="emit('update:toUnit', $event)"
          />
        </div>
      </div>
    </div>
  </div>
</template>
