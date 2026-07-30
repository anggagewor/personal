<script setup lang="ts">
import ConverterLayout from './ConverterLayout.vue'
import { useConverter, type UnitDef } from './useConverter'

const units: UnitDef[] = [
  { id: 'm3', label: 'Meter³ (m³)', toBase: 1000 },
  { id: 'l', label: 'Liter (L)', toBase: 1 },
  { id: 'ml', label: 'Mililiter (mL)', toBase: 0.001 },
  { id: 'cm3', label: 'Sentimeter³ (cm³)', toBase: 0.001 },
  { id: 'gal_us', label: 'Galon US (gal)', toBase: 3.78541 },
  { id: 'gal_uk', label: 'Galon UK (gal)', toBase: 4.54609 },
  { id: 'qt', label: 'Quart (qt)', toBase: 0.946353 },
  { id: 'pt', label: 'Pint (pt)', toBase: 0.473176 },
  { id: 'cup', label: 'Cup (cup)', toBase: 0.236588 },
  { id: 'floz', label: 'Fluid Ounce (fl oz)', toBase: 0.0295735 },
  { id: 'tbsp', label: 'Sendok Makan (tbsp)', toBase: 0.0147868 },
  { id: 'tsp', label: 'Sendok Teh (tsp)', toBase: 0.00492892 },
]

const { fromUnit, toUnit, fromValue, result, swap } = useConverter(units)
</script>

<template>
  <ConverterLayout
    title="Volume"
    v-model:from-value="fromValue"
    v-model:from-unit="fromUnit"
    v-model:to-unit="toUnit"
    :result="result"
    :units="units"
    @swap="swap"
  />
</template>
