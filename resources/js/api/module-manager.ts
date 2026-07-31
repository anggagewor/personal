import { get, post } from '@purdia/http'
import type {
  ModuleInfo,
  ModuleDetail,
  ExtractResult,
  ImportResult,
  GraphData,
  HealthData,
  InspectData,
  ImpactData,
  ExtractPreviewData,
} from '@/types/module-manager'

export function fetchModules(tag?: string) {
  const params: Record<string, unknown> = {}
  if (tag) params.tag = tag
  return get<ModuleInfo[]>('/modules', { params })
}

export function fetchModule(name: string) {
  return get<ModuleDetail>(`/modules/${name}`)
}

export function fetchGraph() {
  return get<GraphData>('/modules/graph')
}

export function fetchHealth(module?: string) {
  const params: Record<string, unknown> = {}
  if (module) params.module = module
  return get<HealthData>('/modules/health', { params })
}

export function fetchInspect(name: string) {
  return get<InspectData>(`/modules/${name}/inspect`)
}

export function fetchImpact(name: string) {
  return get<ImpactData>(`/modules/${name}/impact`)
}

export function fetchExtractPreview(name: string, includeDependencies = true) {
  return get<ExtractPreviewData>(`/modules/${name}/extract-preview`, {
    params: { include_dependencies: includeDependencies },
  })
}

export function extractModule(name: string, includeDependencies = true) {
  return post<ExtractResult>(`/modules/${name}/extract`, { include_dependencies: includeDependencies })
}

export function importModule(file: File, force = false) {
  const formData = new FormData()
  formData.append('archive', file)
  if (force) formData.append('force', '1')
  return post<ImportResult>('/modules/import', formData)
}
