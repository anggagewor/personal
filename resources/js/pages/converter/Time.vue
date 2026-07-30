<script setup lang="ts">
import ConverterLayout from './ConverterLayout.vue'
import { useConverter, type UnitDef } from './useConverter'

const units: UnitDef[] = [
  { id: 'ns', label: 'Nanodetik (ns)', toBase: 0.000000001 },
  { id: 'us', label: 'Mikrodetik (μs)', toBase: 0.000001 },
  { id: 'ms', label: 'Milidetik (ms)', toBase: 0.001 },
  { id: 's', label: 'Detik (s)', toBase: 1 },
  { id: 'min', label: 'Menit (min)', toBase: 60 },
  { id: 'h', label: 'Jam (h)', toBase: 3600 },
  { id: 'day', label: 'Hari', toBase: 86400 },
  { id: 'week', label: 'Minggu', toBase: 604800 },
  { id: 'month', label: 'Bulan (30 hari)', toBase: 2592000 },
  { id: 'year', label: 'Tahun (365 hari)', toBase: 31536000 },
]

const { fromUnit, toUnit, fromValue, result, swap } = useConverter(units)
</script>

<template>
  <ConverterLayout
    title="Waktu"
    v-model:from-value="fromValue"
    v-model:from-unit="fromUnit"
    v-model:to-unit="toUnit"
    :result="result"
    :units="units"
    @swap="swap"
  />
</template>
