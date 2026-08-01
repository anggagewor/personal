/**
 * POS Cart Store
 *
 * Ephemeral cart state for the POS cashier interface.
 * Cart lives purely in frontend state and is submitted as a complete payload during checkout.
 */

import { ref, computed, watch } from 'vue'
import { defineStore } from 'pinia'
import type { CartItem, Member, Voucher, Discount } from '@/types/pos'
import * as posApi from '@/api/pos'
import { debounce } from '@purdia/utils'

export const usePosCartStore = defineStore('pos-cart', () => {
  // ---------------------------------------------------------------------------
  // State
  // ---------------------------------------------------------------------------

  const items = ref<CartItem[]>([])
  const member = ref<Member | null>(null)
  const voucher = ref<Voucher | null>(null)
  const applicableDiscounts = ref<Discount[]>([])
  const paymentFlow = ref<'pay_first' | 'pay_later'>('pay_first')
  const outletId = ref<number | null>(null)
  const evaluating = ref(false)

  // ---------------------------------------------------------------------------
  // Getters
  // ---------------------------------------------------------------------------

  const subtotal = computed(() =>
    items.value.reduce((sum, item) => sum + item.subtotal, 0),
  )

  const discountTotal = computed(() =>
    items.value.reduce((sum, item) => sum + (item.discount_amount || 0), 0),
  )

  const voucherTotal = computed(() =>
    items.value.reduce((sum, item) => sum + (item.voucher_amount || 0), 0),
  )

  const totalDeductions = computed(() => discountTotal.value + voucherTotal.value)

  const total = computed(() => Math.max(0, subtotal.value - totalDeductions.value))

  const itemCount = computed(() =>
    items.value.reduce((sum, item) => sum + item.quantity, 0),
  )

  // ---------------------------------------------------------------------------
  // Discount Evaluation
  // ---------------------------------------------------------------------------

  async function evaluateDiscountsNow() {
    if (!outletId.value || items.value.length === 0) {
      applicableDiscounts.value = []
      clearItemDiscounts()
      return
    }

    evaluating.value = true
    try {
      const payload = {
        outlet_id: outletId.value,
        items: items.value.map((item) => ({
          product_id: item.product_id,
          quantity: item.quantity,
          subtotal: item.subtotal,
        })),
        member_id: member.value?.id,
      }

      const res = await posApi.evaluateDiscounts(payload)
      applicableDiscounts.value = res.data.applicable

      // Map discounts per item
      applyDiscountsToItems()
    } catch {
      // Fail silently - discounts are non-critical
    } finally {
      evaluating.value = false
    }
  }

  const evaluateDiscountsDebounced = debounce(evaluateDiscountsNow, 300)

  function applyDiscountsToItems() {
    // Reset discount amounts
    for (const item of items.value) {
      item.discount_amount = 0
      item.discounts = []
    }

    let remainingSubtotal = subtotal.value

    for (const discount of applicableDiscounts.value) {
      if (discount.product_id !== null) {
        // Product-specific discount: apply to matching item
        const item = items.value.find((i) => i.product_id === discount.product_id)
        if (!item) continue

        const base = item.subtotal
        const amount = discount.type === 'percentage'
          ? base * (discount.value / 100)
          : Math.min(discount.value, base)

        const roundedAmount = Math.floor(amount)
        item.discount_amount = (item.discount_amount || 0) + roundedAmount
        if (!item.discounts) item.discounts = []
        item.discounts.push({ name: discount.name, amount: roundedAmount })
      } else {
        // General discount: distribute proportionally across all items
        if (remainingSubtotal <= 0) continue

        const base = remainingSubtotal
        const totalAmount = discount.type === 'percentage'
          ? base * (discount.value / 100)
          : Math.min(discount.value, base)

        // Distribute proportionally with remainder correction
        let distributed = 0
        for (let idx = 0; idx < items.value.length; idx++) {
          const item = items.value[idx]
          let itemAmount: number
          if (idx === items.value.length - 1) {
            // Last item gets remainder to avoid rounding drift
            itemAmount = Math.floor(totalAmount) - distributed
          } else {
            const proportion = item.subtotal / subtotal.value
            itemAmount = Math.floor(totalAmount * proportion)
            distributed += itemAmount
          }
          item.discount_amount = (item.discount_amount || 0) + itemAmount
          if (!item.discounts) item.discounts = []
          item.discounts.push({ name: discount.name, amount: itemAmount })
        }

        remainingSubtotal -= Math.floor(totalAmount)
      }
    }

    // Apply voucher per product if product-bound
    applyVoucherToItems()
  }

  function applyVoucherToItems() {
    // Reset voucher amounts
    for (const item of items.value) {
      item.voucher_amount = 0
      item.voucher_label = null
    }

    if (!voucher.value) return

    const v = voucher.value

    if (v.product_id !== null) {
      // Product-bound voucher
      const item = items.value.find((i) => i.product_id === v.product_id)
      if (!item) return

      const base = item.subtotal - (item.discount_amount || 0)
      const amount = v.discount_type === 'percentage'
        ? base * (v.discount_value / 100)
        : Math.min(v.discount_value, base)

      item.voucher_amount = Math.round(Math.max(0, amount))
      item.voucher_label = v.code
    } else {
      // General voucher: distribute proportionally
      const afterDiscount = subtotal.value - discountTotal.value
      if (afterDiscount <= 0) return

      const totalAmount = v.discount_type === 'percentage'
        ? afterDiscount * (v.discount_value / 100)
        : Math.min(v.discount_value, afterDiscount)

      let distributed = 0
      for (let idx = 0; idx < items.value.length; idx++) {
        const item = items.value[idx]
        const itemAfterDiscount = item.subtotal - (item.discount_amount || 0)
        let itemAmount: number
        if (idx === items.value.length - 1) {
          // Last item gets remainder to avoid rounding drift
          itemAmount = Math.floor(totalAmount) - distributed
        } else {
          const proportion = itemAfterDiscount / afterDiscount
          itemAmount = Math.floor(totalAmount * proportion)
          distributed += itemAmount
        }
        item.voucher_amount = Math.max(0, itemAmount)
        if (!item.voucher_label) {
          item.voucher_label = v.code
        }
      }
    }
  }

  function clearItemDiscounts() {
    for (const item of items.value) {
      item.discount_amount = 0
      item.discounts = []
      item.voucher_amount = 0
      item.voucher_label = null
    }
  }

  // Watch items + member changes to re-evaluate
  watch(
    [() => items.value.length, () => items.value.map((i) => `${i.product_id}:${i.quantity}`).join(','), () => member.value?.id],
    () => {
      if (items.value.length > 0 && outletId.value) {
        evaluateDiscountsDebounced()
      } else {
        applicableDiscounts.value = []
        clearItemDiscounts()
      }
    },
  )

  // Re-apply voucher when voucher changes
  watch(voucher, () => {
    applyVoucherToItems()
  })

  // ---------------------------------------------------------------------------
  // Actions
  // ---------------------------------------------------------------------------

  function setOutletId(id: number) {
    outletId.value = id
  }

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
        discount_amount: 0,
        discounts: [],
        voucher_amount: 0,
        voucher_label: null,
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
    applyDiscountsToItems()
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
    outletId,
    evaluating,
    // Getters
    subtotal,
    discountTotal,
    voucherTotal,
    totalDeductions,
    total,
    itemCount,
    // Actions
    setOutletId,
    addItem,
    removeItem,
    updateQuantity,
    setMember,
    setPaymentFlow,
    applyVoucher,
    setApplicableDiscounts,
    clearCart,
    evaluateDiscountsNow,
  }
})
