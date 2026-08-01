import { get } from '@purdia/http'
import type { AuditLogEntry, AuditLogFilters } from '@/types/audit-log'

export function fetchAuditLogs(params: AuditLogFilters = {}) {
  return get<AuditLogEntry[]>('/audit-logs', { params })
}

export function fetchAuditableHistory(type: string, id: number, params: { page?: number; per_page?: number } = {}) {
  return get<AuditLogEntry[]>(`/audit-logs/${type}/${id}`, { params })
}
