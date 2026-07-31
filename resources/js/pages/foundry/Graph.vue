<script setup lang="ts">
import { ref, onMounted, onBeforeUnmount, watch } from 'vue'
import { Search, ZoomIn, ZoomOut, Maximize2 } from '@lucide/vue'
import BaseInput from '@purdia/ui/src/components/BaseInput.vue'
import BaseBadge from '@purdia/ui/src/components/BaseBadge.vue'
import type { GraphNode, GraphEdge } from '@/types/module-manager'
import * as api from '@/api/module-manager'

const canvasRef = ref<HTMLCanvasElement | null>(null)
const containerRef = ref<HTMLDivElement | null>(null)
const search = ref('')
const selectedNode = ref<GraphNode | null>(null)
const nodes = ref<GraphNode[]>([])
const edges = ref<GraphEdge[]>([])
const loading = ref(true)

// Graph rendering state
interface RenderNode {
  x: number
  y: number
  vx: number
  vy: number
  node: GraphNode
  highlighted: boolean
  selected: boolean
  dimmed: boolean
}

const renderNodes = ref<RenderNode[]>([])
let animationFrame: number | null = null
let zoom = 1
let panX = 0
let panY = 0
let isDragging = false
let dragNode: RenderNode | null = null
let lastMouse = { x: 0, y: 0 }
let isPanning = false

const tagColorMap: Record<string, string> = {
  productivity: '#6366f1',
  finance: '#10b981',
  utility: '#6b7280',
  'dev-tools': '#f59e0b',
  pos: '#3b82f6',
  integration: '#ef4444',
  core: '#8b5cf6',
  auth: '#8b5cf6',
  aggregation: '#6b7280',
  foundation: '#8b5cf6',
  business: '#3b82f6',
}

function getNodeColor(node: GraphNode): string {
  return tagColorMap[node.tags[0]] || '#6b7280'
}

function getNodeRadius(node: GraphNode): number {
  return Math.max(18, 14 + node.used_by_count * 2)
}

async function fetchData() {
  loading.value = true
  try {
    const res = await api.fetchGraph()
    nodes.value = res.data.nodes
    edges.value = res.data.edges
    initLayout()
  } catch {
    // handled
  } finally {
    loading.value = false
  }
}

function initLayout() {
  const count = nodes.value.length
  const radius = Math.max(250, count * 12)

  renderNodes.value = nodes.value.map((node, i) => {
    const angle = (2 * Math.PI * i) / count
    return {
      x: radius * Math.cos(angle),
      y: radius * Math.sin(angle),
      vx: 0,
      vy: 0,
      node,
      highlighted: false,
      selected: false,
      dimmed: false,
    }
  })

  // Run force simulation
  for (let i = 0; i < 200; i++) {
    simulateStep()
  }

  startRender()
}

function simulateStep() {
  const rn = renderNodes.value
  const repulsion = 8000
  const attraction = 0.005
  const damping = 0.85
  const idealLength = 120

  // Repulsion between all nodes
  for (let i = 0; i < rn.length; i++) {
    for (let j = i + 1; j < rn.length; j++) {
      const dx = rn[i].x - rn[j].x
      const dy = rn[i].y - rn[j].y
      const dist = Math.sqrt(dx * dx + dy * dy) || 1
      const force = repulsion / (dist * dist)
      const fx = (dx / dist) * force
      const fy = (dy / dist) * force
      rn[i].vx += fx
      rn[i].vy += fy
      rn[j].vx -= fx
      rn[j].vy -= fy
    }
  }

  // Attraction along edges
  for (const edge of edges.value) {
    const from = rn.find(n => n.node.id === edge.from)
    const to = rn.find(n => n.node.id === edge.to)
    if (!from || !to) continue

    const dx = to.x - from.x
    const dy = to.y - from.y
    const dist = Math.sqrt(dx * dx + dy * dy) || 1
    const force = (dist - idealLength) * attraction
    const fx = (dx / dist) * force
    const fy = (dy / dist) * force
    from.vx += fx
    from.vy += fy
    to.vx -= fx
    to.vy -= fy
  }

  // Apply velocity with damping
  for (const n of rn) {
    n.vx *= damping
    n.vy *= damping
    n.x += n.vx
    n.y += n.vy
  }
}

function startRender() {
  function frame() {
    draw()
    animationFrame = requestAnimationFrame(frame)
  }
  frame()
}

function draw() {
  const canvas = canvasRef.value
  if (!canvas) return
  const ctx = canvas.getContext('2d')
  if (!ctx) return

  const w = canvas.width = canvas.offsetWidth * 2
  const h = canvas.height = canvas.offsetHeight * 2
  ctx.scale(2, 2)

  const cw = w / 2
  const ch = h / 2

  ctx.clearRect(0, 0, cw, ch)
  ctx.save()
  ctx.translate(cw / 2 + panX, ch / 2 + panY)
  ctx.scale(zoom, zoom)

  const rn = renderNodes.value

  // Draw edges
  for (const edge of edges.value) {
    const from = rn.find(n => n.node.id === edge.from)
    const to = rn.find(n => n.node.id === edge.to)
    if (!from || !to) continue

    const dimmed = from.dimmed || to.dimmed
    ctx.beginPath()
    ctx.moveTo(from.x, from.y)
    ctx.lineTo(to.x, to.y)
    ctx.strokeStyle = from.highlighted || to.highlighted
      ? '#6366f1'
      : dimmed ? 'rgba(156,163,175,0.15)' : 'rgba(156,163,175,0.4)'
    ctx.lineWidth = from.highlighted || to.highlighted ? 2 : 1
    ctx.stroke()

    // Arrow
    if (from.highlighted || to.highlighted || !dimmed) {
      const angle = Math.atan2(to.y - from.y, to.x - from.x)
      const r = getNodeRadius(to.node)
      const ax = to.x - Math.cos(angle) * r
      const ay = to.y - Math.sin(angle) * r
      ctx.beginPath()
      ctx.moveTo(ax, ay)
      ctx.lineTo(ax - 8 * Math.cos(angle - 0.3), ay - 8 * Math.sin(angle - 0.3))
      ctx.lineTo(ax - 8 * Math.cos(angle + 0.3), ay - 8 * Math.sin(angle + 0.3))
      ctx.closePath()
      ctx.fillStyle = from.highlighted || to.highlighted ? '#6366f1' : 'rgba(156,163,175,0.5)'
      ctx.fill()
    }
  }

  // Draw nodes
  for (const rn_ of rn) {
    const r = getNodeRadius(rn_.node)
    const color = getNodeColor(rn_.node)
    const alpha = rn_.dimmed ? 0.25 : 1

    ctx.beginPath()
    ctx.arc(rn_.x, rn_.y, r, 0, 2 * Math.PI)
    ctx.fillStyle = rn_.selected ? '#6366f1' : rn_.highlighted ? color : `${color}${Math.round(alpha * 255).toString(16).padStart(2, '0')}`
    ctx.fill()

    if (rn_.selected || rn_.highlighted) {
      ctx.strokeStyle = '#6366f1'
      ctx.lineWidth = 3
      ctx.stroke()
    }

    // Label
    ctx.fillStyle = rn_.dimmed ? 'rgba(107,114,128,0.3)' : '#374151'
    ctx.font = `${rn_.selected ? 'bold ' : ''}11px Inter, sans-serif`
    ctx.textAlign = 'center'
    ctx.fillText(rn_.node.id, rn_.x, rn_.y + r + 14)
  }

  ctx.restore()
}

function getMousePos(e: MouseEvent) {
  const canvas = canvasRef.value!
  const rect = canvas.getBoundingClientRect()
  const x = e.clientX - rect.left
  const y = e.clientY - rect.top
  // Convert to graph coords
  const cw = canvas.offsetWidth / 2
  const ch = canvas.offsetHeight / 2
  const gx = (x - cw - panX) / zoom
  const gy = (y - ch - panY) / zoom
  return { x: gx, y: gy }
}

function findNodeAt(x: number, y: number): RenderNode | null {
  for (const rn_ of renderNodes.value) {
    const r = getNodeRadius(rn_.node)
    const dx = rn_.x - x
    const dy = rn_.y - y
    if (dx * dx + dy * dy <= r * r) {
      return rn_
    }
  }
  return null
}

function handleMouseDown(e: MouseEvent) {
  const pos = getMousePos(e)
  const node = findNodeAt(pos.x, pos.y)

  if (node) {
    isDragging = true
    dragNode = node
    selectNode(node)
  } else {
    isPanning = true
    lastMouse = { x: e.clientX, y: e.clientY }
    clearSelection()
  }
}

function handleMouseMove(e: MouseEvent) {
  if (isDragging && dragNode) {
    const pos = getMousePos(e)
    dragNode.x = pos.x
    dragNode.y = pos.y
  } else if (isPanning) {
    panX += e.clientX - lastMouse.x
    panY += e.clientY - lastMouse.y
    lastMouse = { x: e.clientX, y: e.clientY }
  }
}

function handleMouseUp() {
  isDragging = false
  dragNode = null
  isPanning = false
}

function handleWheel(e: WheelEvent) {
  e.preventDefault()
  const delta = e.deltaY > 0 ? 0.9 : 1.1
  zoom = Math.max(0.2, Math.min(3, zoom * delta))
}

function selectNode(rn_: RenderNode) {
  selectedNode.value = rn_.node

  const deps = new Set<string>()
  const dependents = new Set<string>()

  // Find all deps (downstream)
  function findDeps(name: string) {
    for (const e of edges.value) {
      if (e.from === name && !deps.has(e.to)) {
        deps.add(e.to)
        findDeps(e.to)
      }
    }
  }

  // Find all dependents (upstream)
  function findDependents(name: string) {
    for (const e of edges.value) {
      if (e.to === name && !dependents.has(e.from)) {
        dependents.add(e.from)
        findDependents(e.from)
      }
    }
  }

  findDeps(rn_.node.id)
  findDependents(rn_.node.id)

  for (const n of renderNodes.value) {
    n.selected = n.node.id === rn_.node.id
    n.highlighted = deps.has(n.node.id) || dependents.has(n.node.id)
    n.dimmed = !n.selected && !n.highlighted
  }
}

function clearSelection() {
  selectedNode.value = null
  for (const n of renderNodes.value) {
    n.selected = false
    n.highlighted = false
    n.dimmed = false
  }
}

function handleZoomIn() { zoom = Math.min(3, zoom * 1.2) }
function handleZoomOut() { zoom = Math.max(0.2, zoom * 0.8) }
function handleFit() { zoom = 1; panX = 0; panY = 0 }

watch(search, (q) => {
  if (!q.trim()) {
    clearSelection()
    return
  }
  const found = renderNodes.value.find(n => n.node.id.toLowerCase().includes(q.toLowerCase()))
  if (found) {
    selectNode(found)
    panX = -found.x * zoom
    panY = -found.y * zoom
  }
})

onMounted(fetchData)

onBeforeUnmount(() => {
  if (animationFrame) cancelAnimationFrame(animationFrame)
})
</script>

<template>
  <div class="flex h-[calc(100vh-8rem)] flex-col">
    <!-- Header -->
    <div class="flex items-center justify-between">
      <div>
        <h1 class="text-2xl font-semibold text-gray-900 dark:text-white">Dependency Graph</h1>
        <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">Klik node untuk highlight dependencies & dependents</p>
      </div>
      <div class="flex items-center gap-2">
        <div class="w-48">
          <BaseInput v-model="search" placeholder="Cari module..." :icon="Search" />
        </div>
        <button class="rounded-lg border border-gray-200 p-2 hover:bg-gray-50 dark:border-gray-700 dark:hover:bg-gray-700" @click="handleZoomIn">
          <ZoomIn :size="16" class="text-gray-500" />
        </button>
        <button class="rounded-lg border border-gray-200 p-2 hover:bg-gray-50 dark:border-gray-700 dark:hover:bg-gray-700" @click="handleZoomOut">
          <ZoomOut :size="16" class="text-gray-500" />
        </button>
        <button class="rounded-lg border border-gray-200 p-2 hover:bg-gray-50 dark:border-gray-700 dark:hover:bg-gray-700" @click="handleFit">
          <Maximize2 :size="16" class="text-gray-500" />
        </button>
      </div>
    </div>

    <!-- Graph + Sidebar -->
    <div class="mt-4 flex flex-1 gap-4 overflow-hidden">
      <!-- Canvas -->
      <div ref="containerRef" class="relative flex-1 overflow-hidden rounded-xl border border-gray-200 bg-white dark:border-gray-700 dark:bg-gray-900">
        <div v-if="loading" class="absolute inset-0 flex items-center justify-center text-sm text-gray-400">
          Memuat graph...
        </div>
        <canvas
          ref="canvasRef"
          class="h-full w-full cursor-grab active:cursor-grabbing"
          @mousedown="handleMouseDown"
          @mousemove="handleMouseMove"
          @mouseup="handleMouseUp"
          @mouseleave="handleMouseUp"
          @wheel="handleWheel"
        />
      </div>

      <!-- Node Detail Panel -->
      <Transition name="slide">
        <div
          v-if="selectedNode"
          class="w-72 shrink-0 overflow-y-auto rounded-xl border border-gray-200 bg-white p-4 dark:border-gray-700 dark:bg-gray-800"
        >
          <h3 class="text-base font-semibold text-gray-900 dark:text-white">{{ selectedNode.label }}</h3>
          <p class="text-xs text-gray-500">{{ selectedNode.id }}</p>

          <div class="mt-3 flex flex-wrap gap-1">
            <BaseBadge v-for="tag in selectedNode.tags" :key="tag" size="xs" variant="default">{{ tag }}</BaseBadge>
            <BaseBadge v-if="selectedNode.extractable" size="xs" variant="success">extractable</BaseBadge>
            <BaseBadge v-if="selectedNode.standalone" size="xs" variant="info">standalone</BaseBadge>
          </div>

          <div class="mt-4 space-y-3">
            <!-- Depends On -->
            <div v-if="selectedNode.dep_count">
              <p class="text-xs font-medium text-gray-500 dark:text-gray-400">Depends On</p>
              <div class="mt-1 space-y-1">
                <div
                  v-for="edge in edges.filter(e => e.from === selectedNode!.id)"
                  :key="edge.to"
                  class="rounded bg-blue-50 px-2 py-1 text-xs text-blue-700 dark:bg-blue-900/30 dark:text-blue-300"
                >
                  {{ edge.to }}
                </div>
              </div>
            </div>

            <!-- Used By -->
            <div v-if="selectedNode.used_by.length">
              <p class="text-xs font-medium text-gray-500 dark:text-gray-400">Used By ({{ selectedNode.used_by_count }})</p>
              <div class="mt-1 space-y-1">
                <div
                  v-for="name in selectedNode.used_by"
                  :key="name"
                  class="rounded bg-green-50 px-2 py-1 text-xs text-green-700 dark:bg-green-900/30 dark:text-green-300"
                >
                  {{ name }}
                </div>
              </div>
            </div>
          </div>
        </div>
      </Transition>
    </div>
  </div>
</template>

<style scoped>
.slide-enter-active,
.slide-leave-active {
  transition: transform 0.2s ease, opacity 0.2s ease;
}
.slide-enter-from,
.slide-leave-to {
  transform: translateX(20px);
  opacity: 0;
}
</style>
