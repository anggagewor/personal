<script setup lang="ts">
import ConverterLayout from './ConverterLayout.vue'
import { useConverter, type UnitDef } from './useConverter'

const units: UnitDef[] = [
  { id: 'bit', label: 'Bit (b)', toBase: 1 },
  { id: 'byte', label: 'Byte (B)', toBase: 8 },
  { id: 'kb', label: 'Kilobyte (KB)', toBase: 8 * 1024 },
  { id: 'mb', label: 'Megabyte (MB)', toBase: 8 * 1024 * 1024 },
  { id: 'gb', label: 'Gigabyte (GB)', toBase: 8 * 1024 * 1024 * 1024 },
  { id: 'tb', label: 'Terabyte (TB)', toBase: 8 * 1024 * 1024 * 1024 * 1024 },
  { id: 'pb', label: 'Petabyte (PB)', toBase: 8 * 1024 * 1024 * 1024 * 1024 * 1024 },
  { id: 'kib', label: 'Kibibyte (KiB)', toBase: 8 * 1024 },
  { id: 'mib', label: 'Mebibyte (MiB)', toBase: 8 * 1048576 },
  { id: 'gib', label: 'Gibibyte (GiB)', toBase: 8 * 1073741824 },
]

const { fromUnit, toUnit, fromValue, result, swap } = useConverter(units)
</script>

<template>
  <ConverterLayout
    title="Data Digital"
    v-model:from-value="fromValue"
    v-model:from-unit="fromUnit"
    v-model:to-unit="toUnit"
    :result="result"
    :units="units"
    @swap="swap"
  />
</template>
