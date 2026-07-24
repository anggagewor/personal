<script setup lang="ts">
import { useSidebar } from '@/composables/useSidebar'
import { navigation } from '@/config/navigation'
import SidebarItem from './SidebarItem.vue'

const { collapsed } = useSidebar()
</script>

<template>
  <aside
    class="flex flex-col border-r border-gray-200 bg-white transition-all duration-300 dark:border-gray-700 dark:bg-gray-800"
    :class="[collapsed ? 'w-16 overflow-visible' : 'w-64']"
  >
    <!-- Logo / Brand -->
    <div class="flex h-14 items-center border-b border-gray-200 px-4 dark:border-gray-700">
      <template v-if="!collapsed">
        <span class="text-lg font-semibold text-primary-600">Purdia</span>
      </template>
      <template v-else>
        <span class="mx-auto text-lg font-bold text-primary-600">P</span>
      </template>
    </div>

    <!-- Navigation -->
    <nav
      class="flex-1 px-2 py-4 space-y-4"
      :class="collapsed ? 'overflow-visible' : 'overflow-y-auto'"
    >
      <div v-for="(group, gi) in navigation" :key="gi">
        <!-- Group title -->
        <p
          v-if="group.title && !collapsed"
          class="mb-2 px-3 text-xs font-medium uppercase tracking-wider text-gray-400 dark:text-gray-500"
        >
          {{ group.title }}
        </p>
        <div v-else-if="group.title && collapsed" class="mb-2 border-t border-gray-200 dark:border-gray-700" />

        <ul class="space-y-1">
          <SidebarItem
            v-for="item in group.items"
            :key="item.id"
            :item="item"
          />
        </ul>
      </div>
    </nav>
  </aside>
</template>
