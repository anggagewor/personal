export interface CustomUnit {
  id: number
  category_id: number
  name: string
  symbol: string
  to_base: number
  is_base: boolean
}

export interface CustomCategory {
  id: number
  name: string
  description: string | null
  icon: string | null
  units: CustomUnit[]
}

export interface CategoryPayload {
  name: string
  description: string
  icon: string
}

export interface UnitPayload {
  category_id: number
  name: string
  symbol: string
  to_base: number
  is_base: boolean
}
