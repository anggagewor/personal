import { get } from '@purdia/http'
import type { LogEntry, LogFile, LogEntriesParams } from '@/types/log-reader'

export function fetchLogFiles() {
  return get<LogFile[]>('/logs/files')
}

export function fetchLogEntries(params: LogEntriesParams) {
  return get<LogEntry[]>('/logs/entries', { params })
}
