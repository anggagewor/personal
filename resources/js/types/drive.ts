export interface DriveFile {
  id: string
  name: string
  mimeType: string
  size: number | null
  iconLink: string | null
  webViewLink: string | null
  createdTime: string | null
  modifiedTime: string | null
  parentId: string | null
}

export interface ConnectionStatus {
  connected: boolean
  email?: string
  connected_at?: string
}
