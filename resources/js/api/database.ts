import { get, post, put, del } from '@purdia/http'
import type { TableInfo, TableStructure, RowsResponse, RowFilter, AlterTablePayload } from '@/types/database'

// --- Tables ---

export function fetchTables() {
  return get<TableInfo[]>('/database/tables')
}

export function fetchStructure(table: string) {
  return get<TableStructure>(`/database/tables/${table}/structure`)
}

// --- Rows ---

export function fetchRows(table: string, params?: { page?: number; per_page?: number; sort_by?: string; sort_dir?: string; filters?: RowFilter[] }) {
  const query: Record<string, unknown> = {
    page: params?.page ?? 1,
    per_page: params?.per_page ?? 25,
  }
  if (params?.sort_by) query.sort_by = params.sort_by
  if (params?.sort_dir) query.sort_dir = params.sort_dir
  if (params?.filters?.length) query.filters = JSON.stringify(params.filters)

  return get<RowsResponse>(`/database/tables/${table}/rows`, { params: query })
}

export function updateRow(table: string, payload: { primary_key: string; primary_value: unknown; data: Record<string, unknown> }) {
  return put(`/database/tables/${table}/rows`, payload)
}

export function deleteRow(table: string, payload: { primary_key: string; primary_value: unknown }) {
  return del(`/database/tables/${table}/rows`, { data: payload })
}

// --- Alter Table ---

export function alterTable(table: string, payload: AlterTablePayload) {
  return post(`/database/tables/${table}/alter`, payload)
}

// --- Query ---

export function executeQuery(sql: string) {
  return post<{ data: Record<string, unknown>[]; meta: { total: number } }>('/database/query', { sql })
}
