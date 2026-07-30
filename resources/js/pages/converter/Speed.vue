<script setup lang="ts">
import ConverterLayout from './ConverterLayout.vue'
import { useConverter, type UnitDef } from './useConverter'

const units: UnitDef[] = [
  { id: 'mps', label: 'Meter/detik (m/s)', toBase: 1 },
  { id: 'kmh', label: 'Kilometer/jam (km/h)', toBase: 0.277778 },
  { id: 'mph', label: 'Mil/jam (mph)', toBase: 0.44704 },
  { id: 'knot', label: 'Knot (kn)', toBase: 0.514444 },
  { id: 'fps', label: 'Kaki/detik (ft/s)', toBase: 0.3048 },
  { id: 'mach', label: 'Mach', toBase: 343 },
  { id: 'c', label: 'Kecepatan Cahaya (c)', toBase: 299792458 },
]

const { fromUnit, toUnit, fromValue, result, swap } = useConverter(units)
</script>

<template>
  <ConverterLayout
    title="Kecepatan"
    v-model:from-value="fromValue"
    v-model:from-unit="fromUnit"
    v-model:to-unit="toUnit"
    :result="result"
    :units="units"
    @swap="swap"
  />
</template>
