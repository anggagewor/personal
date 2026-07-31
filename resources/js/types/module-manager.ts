export interface ModuleInfo {
  name: string
  display_name: string
  description: string
  depends: string[]
  extractable: boolean
  tags: string[]
  has_manifest: boolean
}

export interface ModuleDetail extends ModuleInfo {
  dependency_tree: string[]
  used_by: string[]
}

export interface ExtractResult {
  module: string
  archive_path: string
  include_dependencies: boolean
}

export interface ImportResult {
  imported: string[]
  skipped: string[]
}

// Graph
export interface GraphNode {
  id: string
  label: string
  tags: string[]
  extractable: boolean
  standalone: boolean
  dep_count: number
  used_by_count: number
  used_by: string[]
}

export interface GraphEdge {
  from: string
  to: string
}

export interface GraphData {
  nodes: GraphNode[]
  edges: GraphEdge[]
}

// Health
export interface HealthCheck {
  name: string
  pass: boolean
  weight: number
}

export interface ModuleHealth {
  name: string
  display_name?: string
  overall_score: number
  categories: {
    architecture: number
    documentation: number
    extractability: number
    testing: number
  }
  max_scores?: {
    architecture: number
    documentation: number
    extractability: number
    testing: number
  }
  checks: HealthCheck[]
}

export interface HealthData {
  overall_score: number
  categories: {
    architecture: number
    documentation: number
    extractability: number
    testing: number
  }
  module_count: number
  modules: ModuleHealth[]
}

// Inspector
export interface InspectData {
  entities: number
  contracts: number
  value_objects: number
  enums: number
  events: number
  actions: number
  dtos: number
  queries: number
  controllers: number
  models: number
  repositories: number
  requests: number
  resources: number
  commands: number
  migrations: number
  tests: number
  total_files: number
  size_bytes: number
  has_domain: boolean
  has_application: boolean
  has_infrastructure: boolean
}

// Impact Analysis
export interface ImpactItem {
  name: string
  reason: string
}

export interface ImpactData {
  module: string
  affected_count: number
  affected: ImpactItem[]
}

// Extract Preview
export interface ExtractPreviewModule {
  name: string
  files: number
  migrations: number
  tests: number
  size_bytes: number
}

export interface ExtractPreviewData {
  module: string
  include_dependencies: boolean
  included_modules: ExtractPreviewModule[]
  totals: {
    modules: number
    files: number
    migrations: number
    tests: number
    size_bytes: number
  }
}
