import { get, post, del, upload } from '@purdia/http'
import type { DriveFile, ConnectionStatus } from '@/types/drive'

export function fetchStatus() {
  return get<ConnectionStatus>('/drive/status')
}

export function fetchAuthUrl() {
  return get<{ url: string }>('/drive/auth-url')
}

export function submitCallback(code: string) {
  return post('/drive/callback', { code })
}

export function disconnect() {
  return del('/drive/disconnect')
}

export function fetchFiles(params?: { folder_id?: string }) {
  return get<DriveFile[]>('/drive/files', { params })
}

export function uploadFile(formData: FormData) {
  return upload('/drive/files/upload', formData)
}

export function downloadFile(fileId: string) {
  return get(`/drive/files/${fileId}/download`, { responseType: 'blob' as never })
}

export function deleteFile(fileId: string) {
  return del(`/drive/files/${fileId}`)
}

export function createFolder(payload: { name: string; parent_id: string | null }) {
  return post('/drive/folders', payload)
}
