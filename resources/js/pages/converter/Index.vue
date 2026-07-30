<script setup lang="ts">
import { computed } from 'vue'
import { useRouter } from 'vue-router'
import {
  Ruler,
  Weight,
  Thermometer,
  Square,
  Cuboid,
  Gauge,
  Timer,
  HardDrive,
  Wrench,
} from '@lucide/vue'

const router = useRouter()

const categories = [
  { label: 'Panjang', description: 'km, m, cm, mm, mil, yard, kaki, inci', icon: Ruler, to: '/converter/length' },
  { label: 'Berat', description: 'kg, g, mg, ton, lb, oz', icon: Weight, to: '/converter/weight' },
  { label: 'Suhu', description: 'Celsius, Fahrenheit, Kelvin', icon: Thermometer, to: '/converter/temperature' },
  { label: 'Luas', description: 'km², m², hektar, are, kaki²', icon: Square, to: '/converter/area' },
  { label: 'Volume', description: 'liter, ml, m³, galon, cup', icon: Cuboid, to: '/converter/volume' },
  { label: 'Kecepatan', description: 'km/h, m/s, mph, knot', icon: Gauge, to: '/converter/speed' },
  { label: 'Waktu', description: 'detik, menit, jam, hari, minggu, bulan', icon: Timer, to: '/converter/time' },
  { label: 'Data Digital', description: 'bit, byte, KB, MB, GB, TB', icon: HardDrive, to: '/converter/data' },
  { label: 'Custom', description: 'Buat satuan konversi sendiri', icon: Wrench, to: '/converter/custom' },
]
</script>

<template>
  <div>
    <h1 class="text-2xl font-semibold text-gray-900 dark:text-white">Unit Converter</h1>
    <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">Konversi satuan dengan cepat dan mudah.</p>

    <div class="mt-6 grid grid-cols-1 gap-4 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4">
      <button
        v-for="cat in categories"
        :key="cat.to"
        class="flex items-start gap-4 rounded-xl border border-gray-200 bg-white p-5 text-left transition-all hover:border-primary-300 hover:shadow-md dark:border-gray-700 dark:bg-gray-800 dark:hover:border-primary-600"
        @click="router.push(cat.to)"
      >
        <div class="flex h-10 w-10 shrink-0 items-center justify-center rounded-lg bg-primary-50 text-primary-600 dark:bg-primary-900/30 dark:text-primary-400">
          <component :is="cat.icon" class="h-5 w-5" />
        </div>
        <div>
          <p class="font-medium text-gray-900 dark:text-white">{{ cat.label }}</p>
          <p class="mt-0.5 text-xs text-gray-500 dark:text-gray-400">{{ cat.description }}</p>
        </div>
      </button>
    </div>
  </div>
</template>
