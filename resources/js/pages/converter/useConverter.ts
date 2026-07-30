import { ref, computed, watch } from 'vue'

export interface UnitDef {
  id: string
  label: string
  /** Factor to convert FROM this unit TO the base unit. For base unit this is 1. */
  toBase: number
}

/**
 * Composable for unit conversion within a category.
 * All conversions go through a base unit (factor = 1).
 */
export function useConverter(units: UnitDef[]) {
  const fromUnit = ref(units[0].id)
  const toUnit = ref(units.length > 1 ? units[1].id : units[0].id)
  const fromValue = ref<string>('1')

  const result = computed(() => {
    const val = parseFloat(fromValue.value)
    if (isNaN(val)) return ''

    const from = units.find(u => u.id === fromUnit.value)
    const to = units.find(u => u.id === toUnit.value)
    if (!from || !to) return ''

    const baseValue = val * from.toBase
    const converted = baseValue / to.toBase
    return formatResult(converted)
  })

  function swap() {
    const temp = fromUnit.value
    fromUnit.value = toUnit.value
    toUnit.value = temp
  }

  return {
    fromUnit,
    toUnit,
    fromValue,
    result,
    swap,
    units,
  }
}

/**
 * Special composable for temperature (non-linear conversions).
 */
export function useTemperatureConverter() {
  const units: UnitDef[] = [
    { id: 'celsius', label: 'Celsius (°C)', toBase: 1 },
    { id: 'fahrenheit', label: 'Fahrenheit (°F)', toBase: 1 },
    { id: 'kelvin', label: 'Kelvin (K)', toBase: 1 },
  ]

  const fromUnit = ref('celsius')
  const toUnit = ref('fahrenheit')
  const fromValue = ref<string>('1')

  const result = computed(() => {
    const val = parseFloat(fromValue.value)
    if (isNaN(val)) return ''

    // Convert to Celsius first
    let celsius: number
    switch (fromUnit.value) {
      case 'celsius': celsius = val; break
      case 'fahrenheit': celsius = (val - 32) * 5 / 9; break
      case 'kelvin': celsius = val - 273.15; break
      default: return ''
    }

    // Convert from Celsius to target
    let converted: number
    switch (toUnit.value) {
      case 'celsius': converted = celsius; break
      case 'fahrenheit': converted = celsius * 9 / 5 + 32; break
      case 'kelvin': converted = celsius + 273.15; break
      default: return ''
    }

    return formatResult(converted)
  })

  function swap() {
    const temp = fromUnit.value
    fromUnit.value = toUnit.value
    toUnit.value = temp
  }

  return {
    fromUnit,
    toUnit,
    fromValue,
    result,
    swap,
    units,
  }
}

function formatResult(value: number): string {
  if (Number.isInteger(value)) return value.toString()
  // Show up to 10 significant decimal places, strip trailing zeros
  return parseFloat(value.toPrecision(10)).toString()
}
