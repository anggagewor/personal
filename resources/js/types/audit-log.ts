export interface AuditLogEntry {
  id: number
  user_id: number | null
  event: string
  auditable_type: string
  auditable_id: number | null
  old_values: Record<string, unknown> | null
  new_values: Record<string, unknown> | null
  changed_fields: Record<string, { old: unknown; new: unknown }> | null
  url: string | null
  method: string | null
  ip_address: string | null
  user_agent: string | null
  tags: string | null
  metadata: Record<string, unknown> | null
  created_at: string
}

export interface AuditLogFilters {
  event?: string
  auditable_type?: string
  auditable_id?: number
  tags?: string
  date_from?: string
  date_to?: string
  per_page?: number
  page?: number
}
