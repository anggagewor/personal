<script setup lang="ts">
import BaseSkeleton from '@purdia/ui/src/components/BaseSkeleton.vue'

interface Props {
  rows?: number
  cols?: number
}

withDefaults(defineProps<Props>(), {
  rows: 5,
  cols: 4,
})
</script>

<template>
  <div class="rounded-xl border border-gray-200 bg-white dark:border-gray-700 dark:bg-gray-800 overflow-hidden">
    <!-- Header -->
    <div class="flex gap-4 border-b border-gray-100 bg-gray-50 px-5 py-3 dark:border-gray-700 dark:bg-gray-800/50">
      <BaseSkeleton v-for="c in cols" :key="c" variant="text" height="12px" :width="c === 1 ? '30%' : '18%'" />
    </div>
    <!-- Rows -->
    <div class="divide-y divide-gray-100 dark:divide-gray-700">
      <div v-for="r in rows" :key="r" class="flex items-center gap-4 px-5 py-3">
        <BaseSkeleton v-for="c in cols" :key="c" variant="text" :width="c === 1 ? '30%' : '18%'" />
      </div>
    </div>
  </div>
</template>
