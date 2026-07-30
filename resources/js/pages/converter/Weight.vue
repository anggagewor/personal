<script setup lang="ts">
import ConverterLayout from './ConverterLayout.vue'
import { useConverter, type UnitDef } from './useConverter'

const units: UnitDef[] = [
  { id: 'ton', label: 'Ton (t)', toBase: 1000000 },
  { id: 'kg', label: 'Kilogram (kg)', toBase: 1000 },
  { id: 'g', label: 'Gram (g)', toBase: 1 },
  { id: 'mg', label: 'Miligram (mg)', toBase: 0.001 },
  { id: 'ug', label: 'Mikrogram (μg)', toBase: 0.000001 },
  { id: 'quintal', label: 'Kuintal (q)', toBase: 100000 },
  { id: 'lb', label: 'Pound (lb)', toBase: 453.592 },
  { id: 'oz', label: 'Ounce (oz)', toBase: 28.3495 },
  { id: 'stone', label: 'Stone (st)', toBase: 6350.29 },
  { id: 'carat', label: 'Karat (ct)', toBase: 0.2 },
]

const { fromUnit, toUnit, fromValue, result, swap } = useConverter(units)
</script>

<template>
  <ConverterLayout
    title="Berat"
    v-model:from-value="fromValue"
    v-model:from-unit="fromUnit"
    v-model:to-unit="toUnit"
    :result="result"
    :units="units"
    @swap="swap"
  />
</template>
