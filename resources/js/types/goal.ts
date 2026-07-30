export interface Milestone {
  id: number
  title: string
  is_completed: boolean
  position: number
}

export interface Goal {
  id: number
  title: string
  description: string | null
  target_date: string | null
  status: string
  progress: number
  milestones: Milestone[]
}

export interface GoalPayload {
  title: string
  description: string | null
  target_date: string | null
  milestones: Array<{ title: string }>
}
