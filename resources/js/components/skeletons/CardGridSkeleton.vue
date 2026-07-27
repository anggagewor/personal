<script setup lang="ts">
import BaseSkeleton from '@purdia/ui/src/components/BaseSkeleton.vue'

interface Props {
  count?: number
  columns?: 1 | 2 | 3 | 4
}

withDefaults(defineProps<Props>(), {
  count: 6,
  columns: 3,
})
</script>

<template>
  <div
    class="grid grid-cols-1 gap-4"
    :class="{
      'sm:grid-cols-2': columns >= 2,
      'lg:grid-cols-3': columns >= 3,
      'xl:grid-cols-4': columns >= 4,
    }"
  >
    <div
      v-for="i in count"
      :key="i"
      class="rounded-xl border border-gray-200 bg-white p-4 dark:border-gray-700 dark:bg-gray-800"
    >
      <div class="flex items-start justify-between">
        <BaseSkeleton variant="text" width="60%" />
        <BaseSkeleton variant="badge" />
      </div>
      <div class="mt-3 space-y-2">
        <BaseSkeleton variant="text" />
        <BaseSkeleton variant="text" width="80%" />
      </div>
      <div class="mt-4 flex items-center justify-between">
        <BaseSkeleton variant="text" width="30%" />
        <BaseSkeleton variant="circle" width="24px" height="24px" />
      </div>
    </div>
  </div>
</template>
