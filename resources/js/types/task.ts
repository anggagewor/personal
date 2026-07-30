export interface Task {
  id: number
  title: string
  description: string | null
  status: 'pending' | 'in_progress' | 'completed'
  priority: 'low' | 'medium' | 'high'
  due_date: string | null
  position: number
  created_at: string
}

export type TaskStatus = Task['status']
export type TaskPriority = Task['priority']

export interface TaskPayload {
  title: string
  description: string | null
  priority: TaskPriority
  due_date: string | null
}
