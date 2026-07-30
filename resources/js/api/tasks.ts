import { get, put, post, del } from '@purdia/http'
import type { Task, TaskPayload, TaskStatus } from '@/types/task'

export function fetchTasks(params?: { status?: string }) {
  return get<Task[]>('/tasks', { params })
}

export function createTask(payload: TaskPayload) {
  return post<Task>('/tasks', payload)
}

export function updateTask(id: number, payload: Partial<TaskPayload> & { status?: TaskStatus }) {
  return put<Task>(`/tasks/${id}`, payload)
}

export function deleteTask(id: number) {
  return del(`/tasks/${id}`)
}
