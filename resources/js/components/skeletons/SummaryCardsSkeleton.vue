<script setup lang="ts">
import BaseSkeleton from '@purdia/ui/src/components/BaseSkeleton.vue'

interface Props {
  count?: number
}

withDefaults(defineProps<Props>(), {
  count: 3,
})
</script>

<template>
  <div class="grid grid-cols-1 gap-4 sm:grid-cols-3">
    <div
      v-for="i in count"
      :key="i"
      class="rounded-xl border border-gray-200 bg-white p-5 dark:border-gray-700 dark:bg-gray-800"
    >
      <div class="flex items-center gap-3">
        <BaseSkeleton variant="rect" width="40px" height="40px" />
        <div class="flex-1 space-y-1.5">
          <BaseSkeleton variant="text" width="60%" height="20px" />
          <BaseSkeleton variant="text" width="40%" height="12px" />
        </div>
      </div>
    </div>
  </div>
</template>
