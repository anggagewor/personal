<script setup lang="ts">
import { ref, onMounted, onUnmounted, watch, computed } from 'vue'
import {
  createChart,
  type IChartApi,
  type ISeriesApi,
  type CandlestickSeriesOptions,
  type DeepPartial,
  type ChartOptions,
  type CandlestickData,
  type Time,
  ColorType,
} from 'lightweight-charts'

export interface CandlestickItem {
  time: string | number
  open: number
  high: number
  low: number
  close: number
}

interface Props {
  data: CandlestickItem[]
  height?: number
  options?: DeepPartial<ChartOptions>
  seriesOptions?: DeepPartial<CandlestickSeriesOptions>
  autoResize?: boolean
}

const props = withDefaults(defineProps<Props>(), {
  height: 400,
  autoResize: true,
})

const chartContainer = ref<HTMLElement | null>(null)
let chart: IChartApi | null = null
let series: ISeriesApi<'Candlestick'> | null = null
let resizeObserver: ResizeObserver | null = null

const defaultOptions: DeepPartial<ChartOptions> = {
  layout: {
    background: { type: ColorType.Solid, color: 'transparent' },
    textColor: '#64748b',
    fontFamily: 'Inter, system-ui, sans-serif',
  },
  grid: {
    vertLines: { color: '#f1f5f9' },
    horzLines: { color: '#f1f5f9' },
  },
  crosshair: {
    vertLine: { labelBackgroundColor: '#334155' },
    horzLine: { labelBackgroundColor: '#334155' },
  },
  timeScale: {
    borderColor: '#e2e8f0',
    timeVisible: true,
  },
  rightPriceScale: {
    borderColor: '#e2e8f0',
  },
}

const defaultSeriesOptions: DeepPartial<CandlestickSeriesOptions> = {
  upColor: '#22c55e',
  downColor: '#ef4444',
  borderUpColor: '#16a34a',
  borderDownColor: '#dc2626',
  wickUpColor: '#16a34a',
  wickDownColor: '#dc2626',
}

const mergedOptions = computed(() => ({
  ...defaultOptions,
  ...props.options,
  layout: {
    ...defaultOptions.layout,
    ...props.options?.layout,
  },
  grid: {
    ...defaultOptions.grid,
    ...props.options?.grid,
  },
}))

function initChart() {
  if (!chartContainer.value) return

  chart = createChart(chartContainer.value, {
    ...mergedOptions.value,
    width: chartContainer.value.clientWidth,
    height: props.height,
  })

  series = chart.addCandlestickSeries({
    ...defaultSeriesOptions,
    ...props.seriesOptions,
  })

  if (props.data.length > 0) {
    series.setData(props.data as CandlestickData<Time>[])
    chart.timeScale().fitContent()
  }

  if (props.autoResize) {
    resizeObserver = new ResizeObserver((entries) => {
      if (chart && entries.length > 0) {
        const { width } = entries[0].contentRect
        chart.applyOptions({ width })
      }
    })
    resizeObserver.observe(chartContainer.value)
  }
}

function destroyChart() {
  resizeObserver?.disconnect()
  resizeObserver = null
  chart?.remove()
  chart = null
  series = null
}

onMounted(() => {
  initChart()
})

onUnmounted(() => {
  destroyChart()
})

watch(
  () => props.data,
  (newData) => {
    if (series && chart) {
      series.setData(newData as CandlestickData<Time>[])
      chart.timeScale().fitContent()
    }
  },
  { deep: true },
)

watch(
  () => props.height,
  (newHeight) => {
    chart?.applyOptions({ height: newHeight })
  },
)
</script>

<template>
  <div ref="chartContainer" :style="{ height: `${height}px`, width: '100%' }" />
</template>
