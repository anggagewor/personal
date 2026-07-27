<script setup lang="ts">
import BaseSkeleton from '@purdia/ui/src/components/BaseSkeleton.vue'

interface Props {
  count?: number
}

withDefaults(defineProps<Props>(), {
  count: 5,
})
</script>

<template>
  <div class="rounded-xl border border-gray-200 bg-white dark:border-gray-700 dark:bg-gray-800">
    <div class="divide-y divide-gray-100 dark:divide-gray-700">
      <div
        v-for="i in count"
        :key="i"
        class="flex items-center gap-4 px-5 py-3"
      >
        <BaseSkeleton variant="circle" width="10px" height="10px" />
        <div class="flex-1 space-y-1.5">
          <BaseSkeleton variant="text" width="40%" />
          <BaseSkeleton variant="text" width="25%" height="10px" />
        </div>
        <BaseSkeleton variant="text" width="80px" />
        <BaseSkeleton variant="text" width="50px" />
      </div>
    </div>
  </div>
</template>
