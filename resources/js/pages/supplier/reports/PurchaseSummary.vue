<script setup lang="ts">
/**
 * PurchaseSummary.vue — Alias for the reports Index page.
 * All purchase summary functionality (summary cards, per-supplier table,
 * per-product table, CSV export) is consolidated in Index.vue.
 * This component exists for route compatibility.
 */
import { onMounted } from 'vue'
import { useRouter, useRoute } from 'vue-router'

const router = useRouter()
const route = useRoute()

onMounted(() => {
  router.replace({ name: 'supplier.reports', query: route.query })
})
</script>

<template>
  <div class="py-8 text-center text-sm text-gray-400">Mengalihkan...</div>
</template>
