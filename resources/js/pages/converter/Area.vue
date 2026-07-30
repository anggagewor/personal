<script setup lang="ts">
import ConverterLayout from './ConverterLayout.vue'
import { useConverter, type UnitDef } from './useConverter'

const units: UnitDef[] = [
  { id: 'km2', label: 'Kilometer² (km²)', toBase: 1000000 },
  { id: 'm2', label: 'Meter² (m²)', toBase: 1 },
  { id: 'cm2', label: 'Sentimeter² (cm²)', toBase: 0.0001 },
  { id: 'mm2', label: 'Milimeter² (mm²)', toBase: 0.000001 },
  { id: 'hectare', label: 'Hektar (ha)', toBase: 10000 },
  { id: 'are', label: 'Are (a)', toBase: 100 },
  { id: 'acre', label: 'Acre (ac)', toBase: 4046.86 },
  { id: 'sqmi', label: 'Mil² (mi²)', toBase: 2590000 },
  { id: 'sqyd', label: 'Yard² (yd²)', toBase: 0.836127 },
  { id: 'sqft', label: 'Kaki² (ft²)', toBase: 0.092903 },
  { id: 'sqin', label: 'Inci² (in²)', toBase: 0.00064516 },
]

const { fromUnit, toUnit, fromValue, result, swap } = useConverter(units)
</script>

<template>
  <ConverterLayout
    title="Luas"
    v-model:from-value="fromValue"
    v-model:from-unit="fromUnit"
    v-model:to-unit="toUnit"
    :result="result"
    :units="units"
    @swap="swap"
  />
</template>
