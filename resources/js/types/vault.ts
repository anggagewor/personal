export interface VaultEntry {
  id: number
  name: string
  username: string | null
  encryptedPassword: string
  url: string | null
  notes: string | null
  category: string | null
}

export interface VaultEntryPayload {
  name: string
  username: string | null
  encrypted_password: string
  url: string | null
  notes: string | null
  category: string | null
}
