/**
 * POS Cart Store
 *
 * Ephemeral cart state for the POS cashier interface.
 * Cart lives purely in frontend state and is submitted as a complete payload during checkout.
 */

import { ref, computed } from 'vue'
import { defineStore } from 'pinia'
import type { CartItem, Member, Voucher, Discount } from '@/types/pos'

export const usePosCartStore = defineStore('pos-cart', () => {
  // ---------------------------------------------------------------------------
  // State
  // ---------------------------------------------------------------------------

  const items = ref<CartItem[]>([])
  const member = ref<Member | null>(null)
  const voucher = ref<Voucher | null>(null)
  const applicableDiscounts = ref<Discount[]>([])
  const paymentFlow = ref<'pay_first' | 'pay_later'>('pay_first')

  // ---------------------------------------------------------------------------
  // Getters
  // ---------------------------------------------------------------------------

  const subtotal = computed(() =>
    items.value.reduce((sum, item) => sum + item.subtotal, 0),
  )

  const discountTotal = computed(() => {
    let total = 0

    for (const discount of applicableDiscounts.value) {
      if (discount.type === 'percentage') {
        total += subtotal.value * (discount.value / 100)
      } else if (discount.type === 'fixed') {
        total += Math.min(discount.value, subtotal.value)
      }
    }

    if (voucher.value) {
      if (voucher.value.discount_type === 'percentage') {
        total += subtotal.value * (voucher.value.discount_value / 100)
      } else if (voucher.value.discount_type === 'fixed') {
        total += Math.min(voucher.value.discount_value, subtotal.value)
      }
    }

    // Discount cannot exceed subtotal
    return Math.min(total, subtotal.value)
  })

  const total = computed(() => subtotal.value - discountTotal.value)

  const itemCount = computed(() =>
    items.value.reduce((sum, item) => sum + item.quantity, 0),
  )

  // ---------------------------------------------------------------------------
  // Actions
  // ---------------------------------------------------------------------------

  function addItem(item: Omit<CartItem, 'subtotal'>) {
    const existing = items.value.find(
      (i) =>
        i.product_id === item.product_id &&
        i.product_variant_id === item.product_variant_id,
    )

    if (existing) {
      existing.quantity += item.quantity
      existing.subtotal = existing.quantity * existing.unit_price
    } else {
      items.value.push({
        ...item,
        subtotal: item.quantity * item.unit_price,
      })
    }
  }

  function removeItem(productId: number, variantId: number | null) {
    items.value = items.value.filter(
      (i) => !(i.product_id === productId && i.product_variant_id === variantId),
    )
  }

  function updateQuantity(productId: number, variantId: number | null, quantity: number) {
    const item = items.value.find(
      (i) => i.product_id === productId && i.product_variant_id === variantId,
    )

    if (!item) return

    if (quantity <= 0) {
      removeItem(productId, variantId)
      return
    }

    item.quantity = quantity
    item.subtotal = item.quantity * item.unit_price
  }

  function setMember(m: Member | null) {
    member.value = m
  }

  function setPaymentFlow(flow: 'pay_first' | 'pay_later') {
    paymentFlow.value = flow
  }

  function applyVoucher(v: Voucher | null) {
    voucher.value = v
  }

  function setApplicableDiscounts(discounts: Discount[]) {
    applicableDiscounts.value = discounts
  }

  function clearCart() {
    items.value = []
    member.value = null
    voucher.value = null
    applicableDiscounts.value = []
    paymentFlow.value = 'pay_first'
  }

  return {
    // State
    items,
    member,
    voucher,
    applicableDiscounts,
    paymentFlow,
    // Getters
    subtotal,
    discountTotal,
    total,
    itemCount,
    // Actions
    addItem,
    removeItem,
    updateQuantity,
    setMember,
    setPaymentFlow,
    applyVoucher,
    setApplicableDiscounts,
    clearCart,
  }
})
