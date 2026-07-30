import { get, post, put, del } from '@purdia/http'
import type { WishlistItem, WishlistPayload } from '@/types/wishlist'

export function fetchWishlists() {
  return get('/wishlists')
}

export function createWishlist(payload: WishlistPayload) {
  return post('/wishlists', payload)
}

export function updateWishlist(id: number, payload: Partial<WishlistPayload> & { is_completed?: boolean }) {
  return put(`/wishlists/${id}`, payload)
}

export function deleteWishlist(id: number) {
  return del(`/wishlists/${id}`)
}
