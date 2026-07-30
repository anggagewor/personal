export interface TrashedItem {
  id: number
  type: 'note' | 'task'
  title: string
  deleted_at: string
}
