import { get, post } from '@purdia/http'
import type { ModuleInfo, ModuleDetail, ExtractResult, ImportResult } from '@/types/module-manager'

export function fetchModules(tag?: string) {
  const params: Record<string, unknown> = {}
  if (tag) params.tag = tag
  return get<ModuleInfo[]>('/modules', { params })
}

export function fetchModule(name: string) {
  return get<ModuleDetail>(`/modules/${name}`)
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
