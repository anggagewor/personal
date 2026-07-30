import { get } from '@purdia/http'
import type { ActivityLog } from '@/types/activity'

export function fetchActivityLogs(params: { page?: number; per_page?: number }) {
  return get<ActivityLog[]>('/activity-logs', { params })
}
