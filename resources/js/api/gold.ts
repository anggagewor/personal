import { get, upload } from '@purdia/http'
import type { GoldHistory, GoldDashboard } from '@/types/gold'

export function fetchDashboard() {
  return get<GoldDashboard>('/gold/dashboard')
}

export function fetchHistory(params: { period: string }) {
  return get<GoldHistory[]>('/gold/history', { params })
}

export function exportData(params: { format: 'csv' | 'json' }) {
  return get<Blob>('/gold/export', { params, responseType: 'blob' } as any)
}

export function downloadTemplate() {
  return get<Blob>('/gold/import/template', { responseType: 'blob' } as any)
}

export function importData(formData: FormData) {
  return upload<{ imported: number }>('/gold/import', formData)
}
