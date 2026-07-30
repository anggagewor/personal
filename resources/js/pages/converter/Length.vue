<script setup lang="ts">
import ConverterLayout from './ConverterLayout.vue'
import { useConverter, type UnitDef } from './useConverter'

const units: UnitDef[] = [
  { id: 'km', label: 'Kilometer (km)', toBase: 1000 },
  { id: 'm', label: 'Meter (m)', toBase: 1 },
  { id: 'cm', label: 'Sentimeter (cm)', toBase: 0.01 },
  { id: 'mm', label: 'Milimeter (mm)', toBase: 0.001 },
  { id: 'um', label: 'Mikrometer (μm)', toBase: 0.000001 },
  { id: 'nm', label: 'Nanometer (nm)', toBase: 0.000000001 },
  { id: 'mile', label: 'Mil (mile)', toBase: 1609.344 },
  { id: 'yard', label: 'Yard (yd)', toBase: 0.9144 },
  { id: 'foot', label: 'Kaki (ft)', toBase: 0.3048 },
  { id: 'inch', label: 'Inci (in)', toBase: 0.0254 },
  { id: 'nmi', label: 'Mil Laut (nmi)', toBase: 1852 },
]

const { fromUnit, toUnit, fromValue, result, swap } = useConverter(units)
</script>

<template>
  <ConverterLayout
    title="Panjang"
    v-model:from-value="fromValue"
    v-model:from-unit="fromUnit"
    v-model:to-unit="toUnit"
    :result="result"
    :units="units"
    @swap="swap"
  />
</template>
