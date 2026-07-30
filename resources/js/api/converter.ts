import { get, post, put, del } from '@purdia/http'
import type { CustomCategory, CategoryPayload, UnitPayload } from '@/types/converter'

export function fetchCategories() {
  return get<CustomCategory[]>('/converter/categories')
}

export function createCategory(payload: CategoryPayload) {
  return post('/converter/categories', payload)
}

export function updateCategory(id: number, payload: CategoryPayload) {
  return put(`/converter/categories/${id}`, payload)
}

export function deleteCategory(id: number) {
  return del(`/converter/categories/${id}`)
}

export function createUnit(payload: UnitPayload) {
  return post('/converter/units', payload)
}

export function updateUnit(id: number, payload: UnitPayload) {
  return put(`/converter/units/${id}`, payload)
}

export function deleteUnit(id: number) {
  return del(`/converter/units/${id}`)
}
