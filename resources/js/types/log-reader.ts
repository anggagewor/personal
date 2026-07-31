export type LogLevel = 'emergency' | 'alert' | 'critical' | 'error' | 'warning' | 'notice' | 'info' | 'debug'

export interface LogEntry {
  datetime: string
  level: LogLevel
  level_color: string
  environment: string
  message: string
  stack_trace: string
  context: Record<string, unknown>
  has_stack_trace: boolean
}

export interface LogFile {
  name: string
  size: number
  modified_at: string
}

export interface LogMeta {
  file_size: number
  next_offset: number | null
  count: number
  has_more: boolean
}

export interface LogEntriesParams {
  file: string
  per_page?: number
  offset?: number
  level?: LogLevel
  search?: string
}
