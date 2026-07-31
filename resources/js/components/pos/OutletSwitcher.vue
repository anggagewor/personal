<script setup lang="ts">
import { computed } from 'vue'
import { usePosOutletStore } from '@/stores/pos-outlet'
import { Store, ChevronDown } from '@lucide/vue'

const outletStore = usePosOutletStore()

const label = computed(() => outletStore.activeOutlet?.name ?? 'Pilih Outlet')
</script>

<template>
  <div v-if="outletStore.hasOutlets" class="relative inline-flex items-center">
    <Store :size="14" class="mr-1.5 text-gray-400" />
    <select
      :value="outletStore.activeOutletId"
      class="appearance-none rounded-md border border-gray-200 bg-gray-50 py-1 pl-1 pr-7 text-xs font-medium text-gray-700 outline-none transition-colors hover:border-gray-300 focus:border-primary-300 focus:ring-1 focus:ring-primary-200 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-200 dark:hover:border-gray-500 dark:focus:border-primary-500"
      @change="outletStore.setOutlet(Number(($event.target as HTMLSelectElement).value))"
    >
      <option
        v-for="outlet in outletStore.outlets"
        :key="outlet.id"
        :value="outlet.id"
      >
        {{ outlet.name }}
      </option>
    </select>
    <ChevronDown :size="12" class="pointer-events-none absolute right-1.5 text-gray-400" />
  </div>
</template>
