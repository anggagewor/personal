<script setup lang="ts">
import { computed, ref } from 'vue'
import { RouterLink, useRoute } from 'vue-router'
import { useSidebar } from '@/composables/useSidebar'
import { ChevronDown } from '@lucide/vue'
import type { NavItem } from '@/config/navigation'
import { resolveIcon } from '@/utils/icons'

const props = defineProps<{ item: NavItem }>()

const route = useRoute()
const { collapsed, toggleMenu, isMenuOpen } = useSidebar()

// Resolve icon component from name
const IconComponent = computed(() => resolveIcon(props.item.icon))

const hasChildren = computed(() => !!props.item.children?.length)

const isActive = computed(() => {
  if (props.item.to) {
    return route.path === props.item.to
  }
  // Active if any child route matches
  return props.item.children?.some((child) => route.path === child.to) ?? false
})

const isOpen = computed(() => isMenuOpen(props.item.id))

// Popover state for collapsed mode (both with and without children)
const showPopover = ref(false)
let hideTimer: ReturnType<typeof setTimeout> | null = null

function onMouseEnter() {
  if (collapsed.value) {
    if (hideTimer) clearTimeout(hideTimer)
    showPopover.value = true
  }
}

function onMouseLeave() {
  if (collapsed.value) {
    hideTimer = setTimeout(() => {
      showPopover.value = false
    }, 150)
  }
}

function handleClick() {
  if (hasChildren.value && !collapsed.value) {
    toggleMenu(props.item.id)
  }
}
</script>

<template>
  <li class="relative" @mouseenter="onMouseEnter" @mouseleave="onMouseLeave">
    <!-- Main item -->
    <component
      :is="item.to && !hasChildren ? RouterLink : 'button'"
      :to="item.to && !hasChildren ? item.to : undefined"
      class="flex w-full items-center gap-3 rounded-lg px-3 py-2 text-sm font-medium transition-colors cursor-pointer"
      :class="[
        isActive
          ? 'bg-primary-50 text-primary-700 dark:bg-primary-900/30 dark:text-primary-300'
          : 'text-gray-700 hover:bg-gray-100 hover:text-gray-900 dark:text-gray-300 dark:hover:bg-gray-700 dark:hover:text-white',
        collapsed ? 'justify-center px-0' : '',
      ]"
      @click="handleClick"
      :title="collapsed ? item.label : undefined"
    >
      <!-- Icon -->
      <component
        v-if="IconComponent"
        :is="IconComponent"
        :size="20"
        class="shrink-0"
      />

      <!-- Label (hidden when collapsed) -->
      <span v-if="!collapsed" class="flex-1 truncate text-left">
        {{ item.label }}
      </span>

      <!-- Chevron for accordion (expanded mode only) -->
      <ChevronDown
        v-if="hasChildren && !collapsed"
        :size="16"
        class="shrink-0 transition-transform duration-200"
        :class="isOpen ? 'rotate-180' : ''"
      />
    </component>

    <!-- Accordion children (expanded sidebar) -->
    <Transition name="accordion">
      <ul
        v-if="hasChildren && !collapsed && isOpen"
        class="mt-1 space-y-1 overflow-hidden pl-4"
      >
        <li v-for="child in item.children" :key="child.to">
          <RouterLink
            :to="child.to"
            class="flex items-center gap-2.5 rounded-lg px-3 py-1.5 text-sm transition-colors"
            :class="
              route.path === child.to
                ? 'text-primary-700 font-medium dark:text-primary-300'
                : 'text-gray-600 hover:text-gray-900 dark:text-gray-400 dark:hover:text-white'
            "
          >
            <component
              :is="resolveIcon(child.icon)"
              v-if="resolveIcon(child.icon)"
              :size="16"
              class="shrink-0"
            />
            {{ child.label }}
          </RouterLink>
        </li>
      </ul>
    </Transition>

    <!-- Popover (collapsed sidebar) -->
    <Transition name="popover">
      <div
        v-if="collapsed && showPopover"
        class="absolute left-full top-0 z-[100] ml-2 min-w-[11rem] rounded-lg border border-gray-200 bg-white py-1 shadow-lg dark:border-gray-700 dark:bg-gray-800"
      >
        <!-- Single item (no children) — clickable link -->
        <template v-if="!hasChildren">
          <RouterLink
            :to="item.to!"
            class="flex items-center gap-2 px-3 py-2 text-sm font-medium text-gray-700 hover:text-primary-600 dark:text-gray-200 dark:hover:text-primary-400"
          >
            {{ item.label }}
          </RouterLink>
        </template>

        <!-- Submenu (has children) -->
        <template v-else>
          <p class="border-b border-gray-100 px-3 py-2 text-xs font-semibold text-gray-500 dark:border-gray-700 dark:text-gray-400">
            {{ item.label }}
          </p>
          <ul class="py-1">
            <li v-for="child in item.children" :key="child.to">
              <RouterLink
                :to="child.to"
                class="flex items-center gap-2.5 px-3 py-1.5 text-sm transition-colors"
                :class="
                  route.path === child.to
                    ? 'text-primary-700 font-medium dark:text-primary-300'
                    : 'text-gray-600 hover:text-gray-900 dark:text-gray-400 dark:hover:text-white'
                "
              >
                <component
                  :is="resolveIcon(child.icon)"
                  v-if="resolveIcon(child.icon)"
                  :size="16"
                  class="shrink-0"
                />
                {{ child.label }}
              </RouterLink>
            </li>
          </ul>
        </template>
      </div>
    </Transition>
  </li>
</template>

<style scoped>
/* Accordion animation */
.accordion-enter-active,
.accordion-leave-active {
  transition: all 200ms ease;
  max-height: 500px;
}
.accordion-enter-from,
.accordion-leave-to {
  opacity: 0;
  max-height: 0;
}

/* Popover animation */
.popover-enter-active,
.popover-leave-active {
  transition: opacity 150ms ease, transform 150ms ease;
}
.popover-enter-from,
.popover-leave-to {
  opacity: 0;
  transform: translateX(-4px);
}
</style>
