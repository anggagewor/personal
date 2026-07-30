<script setup lang="ts">
import ConverterLayout from './ConverterLayout.vue'
import { useTemperatureConverter } from './useConverter'

const { fromUnit, toUnit, fromValue, result, swap, units } = useTemperatureConverter()
</script>

<template>
  <ConverterLayout
    title="Suhu"
    v-model:from-value="fromValue"
    v-model:from-unit="fromUnit"
    v-model:to-unit="toUnit"
    :result="result"
    :units="units"
    @swap="swap"
  />
</template>
