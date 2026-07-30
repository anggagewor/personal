export interface ActivityLog {
  id: number
  action: string
  description: string
  metadata: Record<string, unknown> | null
  created_at: string
}
