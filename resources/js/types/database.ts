export interface TableInfo {
  name: string
  rows: number
}

export interface ColumnInfo {
  name: string
  type: string
  nullable: boolean
  key: string
  default: string | null
  extra: string
  comment: string
}

export interface TableStructure {
  table: string
  columns: ColumnInfo[]
  indexes: Record<string, { column: string; unique: boolean }[]>
}

export interface RowsResponse {
  data: Record<string, unknown>[]
  meta: {
    total: number
    per_page: number
    current_page: number
    last_page: number
  }
}

export interface RowFilter {
  column: string
  operator: string
  value: string
}

export interface AlterTablePayload {
  action: 'add_column' | 'drop_column' | 'modify_column'
  column: string
  type?: string
  nullable?: boolean
  default?: string | null
  after?: string | null
}
