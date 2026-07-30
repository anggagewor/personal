import { get, post, put, del } from '@purdia/http'
import type { VaultEntry, VaultEntryPayload } from '@/types/vault'

export function fetchVaultEntries() {
  return get<VaultEntry[]>('/vault')
}

export function createVaultEntry(payload: VaultEntryPayload) {
  return post<VaultEntry>('/vault', payload)
}

export function updateVaultEntry(id: number, payload: VaultEntryPayload) {
  return put<VaultEntry>(`/vault/${id}`, payload)
}

export function deleteVaultEntry(id: number) {
  return del(`/vault/${id}`)
}
