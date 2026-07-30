import { get, post, del } from '@purdia/http'
import type { Goal, GoalPayload } from '@/types/goal'

export function fetchGoals() {
  return get<Goal[]>('/goals')
}

export function createGoal(payload: GoalPayload) {
  return post<Goal>('/goals', payload)
}

export function deleteGoal(id: number) {
  return del(`/goals/${id}`)
}

export function toggleMilestone(milestoneId: number) {
  return post<Goal>(`/milestones/${milestoneId}/toggle`)
}

export function addMilestone(goalId: number, payload: { title: string }) {
  return post(`/goals/${goalId}/milestones`, payload)
}
