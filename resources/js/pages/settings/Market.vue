<script setup lang="ts">
import { ref, computed, onMounted } from 'vue'
import { get, post, del } from '@purdia/http'
import { useToast } from '@purdia/toast'
import BaseButton from '@purdia/ui/src/components/BaseButton.vue'
import BaseInput from '@purdia/ui/src/components/BaseInput.vue'
import BaseSelect from '@purdia/ui/src/components/BaseSelect.vue'
import { Plus, Trash2, TrendingUp, Info } from '@lucide/vue'

const toast = useToast()

// --- Types ---
interface WatchlistItem {
  id: number
  symbol: string
  type: string
  label: string | null
  position: number
}

interface MarketConfig {
  refresh_interval: number
  max_symbols: number
  api_configured: boolean
}

// --- State ---
const items = ref<WatchlistItem[]>([])
const config = ref<MarketConfig>({ refresh_interval: 15, max_symbols: 15, api_configured: false })
const loading = ref(true)

// --- Form ---
const form = ref({ symbol: '', type: 'forex', label: '' })

const typeOptions = [
  { label: 'Forex (mata uang)', value: 'forex' },
  { label: 'Crypto', value: 'crypto' },
  { label: 'Saham', value: 'stock' },
  { label: 'Komoditas', value: 'commodity' },
]

// --- Popular symbols for quick add ---
const popularSymbols = [
  { symbol: 'USD/IDR', type: 'forex', label: 'USD/IDR' },
  { symbol: 'EUR/USD', type: 'forex', label: 'EUR/USD' },
  { symbol: 'GBP/USD', type: 'forex', label: 'GBP/USD' },
  { symbol: 'XAU/USD', type: 'commodity', label: 'Emas (XAU)' },
  { symbol: 'BTC/USD', type: 'crypto', label: 'Bitcoin' },
  { symbol: 'ETH/USD', type: 'crypto', label: 'Ethereum' },
  { symbol: 'AAPL', type: 'stock', label: 'Apple' },
  { symbol: 'GOOGL', type: 'stock', label: 'Google' },
  { symbol: 'TSLA', type: 'stock', label: 'Tesla' },
  { symbol: 'MSFT', type: 'stock', label: 'Microsoft' },
  { symbol: 'USD/JPY', type: 'forex', label: 'USD/JPY' },
  { symbol: 'EUR/IDR', type: 'forex', label: 'EUR/IDR' },
]

const availablePopular = computed(() =>
  popularSymbols.filter(p => !items.value.some(i => i.symbol === p.symbol))
)

const canAdd = computed(() => items.value.length < config.value.max_symbols)

// --- Rate limit info ---
const estimatedDailyCallsPerRefresh = computed(() => {
  const symbolCount = items.value.length
  const chunks = Math.ceil(symbolCount / 8)
  const refreshesPerHour = 60 / config.value.refresh_interval
  const hoursActive = 12 // assume 12 hours active usage
  return Math.round(chunks * refreshesPerHour * hoursActive)
})

// --- Actions ---
async function fetchData() {
  loading.value = true
  try {
    const [itemsRes, configRes] = await Promise.all([
      get<WatchlistItem[]>('/market/watchlist'),
      get<MarketConfig>('/market/config'),
    ])
    items.value = itemsRes.data
    config.value = configRes.data
  } catch {
    // handled by global error
  } finally {
    loading.value = false
  }
}

async function addItem() {
  if (!form.value.symbol.trim()) return
  try {
    const res = await post<WatchlistItem>('/market/watchlist', {
      symbol: form.value.symbol.trim().toUpperCase(),
      type: form.value.type,
      label: form.value.label.trim() || null,
    })
    items.value.push(res.data)
    form.value = { symbol: '', type: 'forex', label: '' }
    toast.success('Simbol berhasil ditambahkan.')
  } catch {
    // handled by global error
  }
}

async function quickAdd(s: { symbol: string; type: string; label: string }) {
  try {
    const res = await post<WatchlistItem>('/market/watchlist', {
      symbol: s.symbol,
      type: s.type,
      label: s.label,
    })
    items.value.push(res.data)
    toast.success(`${s.symbol} ditambahkan.`)
  } catch {
    // handled by global error
  }
}

async function removeItem(item: WatchlistItem) {
  try {
    await del(`/market/watchlist/${item.id}`)
    items.value = items.value.filter(i => i.id !== item.id)
    toast.success(`${item.symbol} dihapus dari watchlist.`)
  } catch {
    // handled by global error
  }
}

onMounted(fetchData)
</script>

<template>
  <div>
    <h1 class="text-2xl font-semibold text-gray-900 dark:text-white">Market Watchlist</h1>
    <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">Kelola simbol yang ingin dipantau harganya di dashboard.</p>

    <div class="mt-8 max-w-2xl space-y-8">
      <!-- API Status -->
      <section class="rounded-xl border border-gray-200 bg-white p-6 dark:border-gray-700 dark:bg-gray-800">
        <div class="flex items-center gap-2">
          <Info :size="16" class="text-gray-500" />
          <h2 class="text-base font-semibold text-gray-900 dark:text-white">Status & Limit</h2>
        </div>
        <div class="mt-4 space-y-2">
          <div class="flex items-center justify-between text-sm">
            <span class="text-gray-600 dark:text-gray-400">API Key</span>
            <span :class="config.api_configured ? 'text-emerald-600 dark:text-emerald-400' : 'text-red-600 dark:text-red-400'">
              {{ config.api_configured ? 'Aktif' : 'Belum diset' }}
            </span>
          </div>
          <div class="flex items-center justify-between text-sm">
            <span class="text-gray-600 dark:text-gray-400">Refresh interval</span>
            <span class="text-gray-900 dark:text-white font-medium">{{ config.refresh_interval }} menit</span>
          </div>
          <div class="flex items-center justify-between text-sm">
            <span class="text-gray-600 dark:text-gray-400">Simbol terdaftar</span>
            <span class="text-gray-900 dark:text-white font-medium">{{ items.length }} / {{ config.max_symbols }}</span>
          </div>
          <div class="flex items-center justify-between text-sm">
            <span class="text-gray-600 dark:text-gray-400">Estimasi API call/hari</span>
            <span class="font-medium" :class="estimatedDailyCallsPerRefresh > 700 ? 'text-red-600 dark:text-red-400' : 'text-gray-900 dark:text-white'">
              ~{{ estimatedDailyCallsPerRefresh }} / 800
            </span>
          </div>
        </div>
        <p v-if="!config.api_configured" class="mt-3 rounded-lg bg-amber-50 px-3 py-2 text-xs text-amber-700 dark:bg-amber-900/20 dark:text-amber-300">
          Tambahkan <code class="font-mono">TWELVEDATA_API_KEY</code> di file .env untuk mengaktifkan fitur ini.
        </p>
      </section>

      <!-- Watchlist -->
      <section class="rounded-xl border border-gray-200 bg-white p-6 dark:border-gray-700 dark:bg-gray-800">
        <div class="flex items-center gap-2">
          <TrendingUp :size="16" class="text-gray-500" />
          <h2 class="text-base font-semibold text-gray-900 dark:text-white">Watchlist</h2>
        </div>

        <!-- Current items -->
        <div v-if="items.length" class="mt-4 space-y-2">
          <div
            v-for="item in items"
            :key="item.id"
            class="flex items-center justify-between rounded-lg border border-gray-100 bg-gray-50 px-4 py-2.5 dark:border-gray-700 dark:bg-gray-700/50"
          >
            <div class="flex items-center gap-3">
              <span class="inline-flex items-center rounded px-1.5 py-0.5 text-[10px] font-bold uppercase" :class="{
                'bg-blue-100 text-blue-700 dark:bg-blue-900/30 dark:text-blue-300': item.type === 'forex',
                'bg-amber-100 text-amber-700 dark:bg-amber-900/30 dark:text-amber-300': item.type === 'crypto',
                'bg-violet-100 text-violet-700 dark:bg-violet-900/30 dark:text-violet-300': item.type === 'stock',
                'bg-emerald-100 text-emerald-700 dark:bg-emerald-900/30 dark:text-emerald-300': item.type === 'commodity',
              }">{{ item.type }}</span>
              <div>
                <p class="text-sm font-medium text-gray-900 dark:text-white">{{ item.symbol }}</p>
                <p v-if="item.label" class="text-xs text-gray-500 dark:text-gray-400">{{ item.label }}</p>
              </div>
            </div>
            <button
              class="rounded p-1 text-gray-400 hover:text-red-500 transition-colors"
              @click="removeItem(item)"
              title="Hapus"
            >
              <Trash2 :size="14" />
            </button>
          </div>
        </div>
        <p v-else-if="!loading" class="mt-4 text-sm text-gray-400 dark:text-gray-500">Belum ada simbol. Tambahkan dari form di bawah atau klik simbol populer.</p>

        <!-- Quick add popular -->
        <div v-if="availablePopular.length && canAdd" class="mt-5">
          <p class="text-xs font-medium text-gray-500 dark:text-gray-400 mb-2">Populer:</p>
          <div class="flex flex-wrap gap-2">
            <button
              v-for="s in availablePopular"
              :key="s.symbol"
              class="rounded-md border border-gray-200 px-2.5 py-1 text-xs text-gray-600 hover:border-primary-300 hover:bg-primary-50 hover:text-primary-700 transition-colors dark:border-gray-600 dark:text-gray-400 dark:hover:border-primary-500 dark:hover:bg-primary-900/20 dark:hover:text-primary-300"
              @click="quickAdd(s)"
            >
              {{ s.symbol }}
            </button>
          </div>
        </div>

        <!-- Add form -->
        <div v-if="canAdd" class="mt-5 rounded-lg border border-gray-200 bg-gray-50 p-4 dark:border-gray-700 dark:bg-gray-700/30">
          <p class="text-sm font-medium text-gray-700 dark:text-gray-300 mb-3">Tambah Simbol</p>
          <form class="space-y-3" @submit.prevent="addItem">
            <div class="grid grid-cols-1 gap-3 sm:grid-cols-2">
              <BaseInput
                v-model="form.symbol"
                label="Simbol"
                placeholder="misal: USD/IDR, BTC/USD, AAPL"
                required
              />
              <BaseSelect
                v-model="form.type"
                label="Tipe"
                :options="typeOptions"
              />
            </div>
            <BaseInput
              v-model="form.label"
              label="Label (opsional)"
              placeholder="misal: Dollar, Bitcoin, Apple"
            />
            <BaseButton
              variant="primary"
              size="sm"
              :icon="Plus"
              type="submit"
              :disabled="!form.symbol.trim()"
            >
              Tambah
            </BaseButton>
          </form>
        </div>
        <p v-else class="mt-4 rounded-lg bg-amber-50 px-3 py-2 text-xs text-amber-700 dark:bg-amber-900/20 dark:text-amber-300">
          Watchlist penuh (maks {{ config.max_symbols }} simbol). Hapus yang tidak diperlukan untuk menambah yang baru.
        </p>
      </section>
    </div>
  </div>
</template>
