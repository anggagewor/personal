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
