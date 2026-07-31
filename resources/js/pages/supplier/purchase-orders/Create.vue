<script setup lang="ts">
import { ref, computed, watch, onMounted } from 'vue'
import { useRoute, useRouter } from 'vue-router'
import { useToast } from '@purdia/toast'
import { formatCurrency } from '@purdia/utils'
import BaseButton from '@purdia/ui/src/components/BaseButton.vue'
import BaseInput from '@purdia/ui/src/components/BaseInput.vue'
import BaseSelect from '@purdia/ui/src/components/BaseSelect.vue'
import { Plus, Trash2, ArrowLeft, Save } from '@lucide/vue'
import type { Supplier, SupplierProduct, PurchaseOrder, PurchaseOrderPayload } from '@/types/supplier'
import type { Product, ProductVariant } from '@/types/pos'
import * as supplierApi from '@/api/supplier'
import * as posApi from '@/api/pos'

const route = useRoute()
const router = useRouter()
const toast = useToast()

const outletId = computed(() => Number(route.query.outlet))
const editId = computed(() => route.query.id ? Number(route.query.id) : null)
const isEditMode = computed(() => editId.value !== null)

// --- Data ---
interface FormItem {
  product_variant_id: number | null
  product_name: string
  variant_name: string
  quantity: number
  unit_cost: number
  subtotal: number
}

const form = ref({
  supplier_id: null as number | null,
  order_date: new Date().toISOString().slice(0, 10),
  expected_delivery_date: '' as string,
  notes: '',
  items: [] as FormItem[],
})

const suppliers = ref<Supplier[]>([])
const products = ref<Product[]>([])
const supplierCostMap = ref<Map<number, number>>(new Map())
const loading = ref(false)
const submitting = ref(false)
const supplierSearch = ref('')

// Flattened variants for selection
interface VariantOption {
  product_variant_id: number
  product_name: string
  variant_name: string
  label: string
}

const variantOptions = computed<VariantOption[]>(() => {
  const options: VariantOption[] = []
  for (const product of products.value) {
    if (product.variants && product.variants.length > 0) {
      for (const variant of product.variants) {
        options.push({
          product_variant_id: variant.id,
          product_name: product.name,
          variant_name: variant.name,
          label: `${product.name} — ${variant.name}`,
        })
      }
    }
  }
  return options
})

const grandTotal = computed(() => {
  return form.value.items.reduce((sum, item) => sum + item.subtotal, 0)
})

// --- Load data ---

async function fetchSuppliers() {
  if (!outletId.value) return
  try {
    const res = await supplierApi.fetchSuppliers(outletId.value, { per_page: 100 })
    if (Array.isArray(res.data)) {
      suppliers.value = res.data
    } else {
      const paginated = res.data as unknown as { data: Supplier[] }
      suppliers.value = paginated.data ?? res.data
    }
  } catch {
    // Error handled globally
  }
}

async function fetchProducts() {
  if (!outletId.value) return
  try {
    const res = await posApi.fetchProducts(outletId.value)
    products.value = Array.isArray(res.data) ? res.data : []
  } catch {
    // Error handled globally
  }
}

async function fetchSupplierCosts(supplierId: number) {
  try {
    const res = await supplierApi.fetchSupplierProducts(supplierId)
    const map = new Map<number, number>()
    const items: SupplierProduct[] = Array.isArray(res.data) ? res.data : []
    for (const sp of items) {
      if (sp.default_unit_cost !== null) {
        map.set(sp.product_variant_id, sp.default_unit_cost)
      }
    }
    supplierCostMap.value = map
  } catch {
    supplierCostMap.value = new Map()
  }
}

async function fetchPurchaseOrder(id: number) {
  loading.value = true
  try {
    const res = await supplierApi.fetchPurchaseOrder(id)
    const po: PurchaseOrder = res.data
    form.value.supplier_id = po.supplier_id
    form.value.order_date = po.order_date
    form.value.expected_delivery_date = po.expected_delivery_date ?? ''
    form.value.notes = po.notes ?? ''
    form.value.items = po.items.map(item => ({
      product_variant_id: item.product_variant_id,
      product_name: item.product_name,
      variant_name: item.variant_name,
      quantity: item.quantity,
      unit_cost: item.unit_cost,
      subtotal: item.quantity * item.unit_cost,
    }))

    // Load supplier costs for prefill
    if (po.supplier_id) {
      await fetchSupplierCosts(po.supplier_id)
    }
  } catch {
    toast.error('Gagal memuat data purchase order.')
  } finally {
    loading.value = false
  }
}

// --- Supplier change handler ---

watch(() => form.value.supplier_id, async (newVal) => {
  if (newVal) {
    await fetchSupplierCosts(newVal)
  } else {
    supplierCostMap.value = new Map()
  }
})

// --- Item management ---

function addItem() {
  form.value.items.push({
    product_variant_id: null,
    product_name: '',
    variant_name: '',
    quantity: 1,
    unit_cost: 0,
    subtotal: 0,
  })
}

function removeItem(index: number) {
  form.value.items.splice(index, 1)
}

function onVariantSelect(index: number, variantId: number | string) {
  const id = Number(variantId)
  const option = variantOptions.value.find(v => v.product_variant_id === id)
  if (option) {
    form.value.items[index].product_variant_id = option.product_variant_id
    form.value.items[index].product_name = option.product_name
    form.value.items[index].variant_name = option.variant_name

    // Auto-fill unit cost from supplier-product link
    const defaultCost = supplierCostMap.value.get(option.product_variant_id)
    if (defaultCost !== undefined && form.value.items[index].unit_cost === 0) {
      form.value.items[index].unit_cost = defaultCost
    }

    recalcSubtotal(index)
  }
}

function recalcSubtotal(index: number) {
  const item = form.value.items[index]
  item.subtotal = item.quantity * item.unit_cost
}

function onQuantityChange(index: number) {
  const item = form.value.items[index]
  if (item.quantity < 1) item.quantity = 1
  recalcSubtotal(index)
}

function onUnitCostChange(index: number) {
  const item = form.value.items[index]
  if (item.unit_cost < 0) item.unit_cost = 0
  recalcSubtotal(index)
}

// --- Validation & Submit ---

function validate(): boolean {
  if (!form.value.supplier_id) {
    toast.error('Pilih supplier terlebih dahulu.')
    return false
  }
  if (!form.value.order_date) {
    toast.error('Tanggal order wajib diisi.')
    return false
  }
  if (form.value.items.length === 0) {
    toast.error('Tambahkan minimal 1 item.')
    return false
  }
  for (let i = 0; i < form.value.items.length; i++) {
    const item = form.value.items[i]
    if (!item.product_variant_id) {
      toast.error(`Item ke-${i + 1}: pilih produk.`)
      return false
    }
    if (item.quantity < 1) {
      toast.error(`Item ke-${i + 1}: quantity minimal 1.`)
      return false
    }
    if (item.unit_cost <= 0) {
      toast.error(`Item ke-${i + 1}: harga beli harus lebih dari 0.`)
      return false
    }
  }
  return true
}

async function submit() {
  if (!validate()) return

  submitting.value = true
  try {
    const payload: PurchaseOrderPayload = {
      supplier_id: form.value.supplier_id!,
      order_date: form.value.order_date,
      expected_delivery_date: form.value.expected_delivery_date || null,
      notes: form.value.notes || null,
      items: form.value.items.map(item => ({
        product_variant_id: item.product_variant_id!,
        product_name: item.product_name,
        variant_name: item.variant_name,
        quantity: item.quantity,
        unit_cost: item.unit_cost,
      })),
    }

    if (isEditMode.value) {
      await supplierApi.updatePurchaseOrder(editId.value!, payload)
      toast.success('Purchase order berhasil diperbarui.')
    } else {
      const res = await supplierApi.createPurchaseOrder(outletId.value, payload)
      toast.success('Purchase order berhasil dibuat.')
      // Navigate to PO detail
      router.push({
        name: 'supplier.purchase-orders.detail',
        query: { outlet: outletId.value, id: res.data.id },
      })
      return
    }

    router.push({
      name: 'supplier.purchase-orders.detail',
      query: { outlet: outletId.value, id: editId.value },
    })
  } catch {
    toast.error('Gagal menyimpan purchase order.')
  } finally {
    submitting.value = false
  }
}

function goBack() {
  router.push({ name: 'supplier.purchase-orders', query: { outlet: outletId.value } })
}

// --- Supplier options for select ---
const supplierOptions = computed(() => {
  return suppliers.value.map(s => ({
    label: s.name,
    value: s.id,
  }))
})

// --- Init ---
onMounted(async () => {
  loading.value = true
  await Promise.all([fetchSuppliers(), fetchProducts()])

  if (isEditMode.value) {
    await fetchPurchaseOrder(editId.value!)
  }
  loading.value = false
})
</script>

<template>
  <div>
    <!-- Header -->
    <div class="flex items-center gap-3">
      <button
        class="rounded-lg p-2 text-gray-400 hover:bg-gray-100 hover:text-gray-600 dark:hover:bg-gray-700 dark:hover:text-gray-300"
        @click="goBack"
      >
        <ArrowLeft :size="20" />
      </button>
      <div>
        <h1 class="text-2xl font-semibold text-gray-900 dark:text-white">
          {{ isEditMode ? 'Edit Purchase Order' : 'Buat Purchase Order' }}
        </h1>
        <p class="mt-0.5 text-sm text-gray-500 dark:text-gray-400">
          {{ isEditMode ? 'Perbarui data purchase order.' : 'Buat pesanan pembelian baru ke supplier.' }}
        </p>
      </div>
    </div>

    <!-- Loading -->
    <div v-if="loading" class="mt-8 py-12 text-center text-sm text-gray-400">Memuat data...</div>

    <template v-else>
      <!-- Form -->
      <div class="mt-6 space-y-6">
        <!-- Supplier & Dates -->
        <div class="rounded-xl border border-gray-200 bg-white p-6 dark:border-gray-700 dark:bg-gray-800">
          <h2 class="text-sm font-semibold text-gray-900 dark:text-white">Informasi Umum</h2>
          <div class="mt-4 grid grid-cols-1 gap-4 sm:grid-cols-3">
            <!-- Supplier -->
            <div>
              <label class="mb-1.5 block text-sm font-medium text-gray-700 dark:text-gray-300">
                Supplier <span class="text-red-500">*</span>
              </label>
              <BaseSelect
                v-model="form.supplier_id"
                :options="supplierOptions"
                placeholder="Pilih supplier..."
              />
            </div>

            <!-- Order Date -->
            <div>
              <label class="mb-1.5 block text-sm font-medium text-gray-700 dark:text-gray-300">
                Tanggal Order <span class="text-red-500">*</span>
              </label>
              <BaseInput
                v-model="form.order_date"
                type="date"
              />
            </div>

            <!-- Expected Delivery -->
            <div>
              <label class="mb-1.5 block text-sm font-medium text-gray-700 dark:text-gray-300">
                Estimasi Pengiriman
              </label>
              <BaseInput
                v-model="form.expected_delivery_date"
                type="date"
              />
            </div>
          </div>
        </div>

        <!-- Line Items -->
        <div class="rounded-xl border border-gray-200 bg-white p-6 dark:border-gray-700 dark:bg-gray-800">
          <div class="flex items-center justify-between">
            <h2 class="text-sm font-semibold text-gray-900 dark:text-white">Item Pembelian</h2>
            <BaseButton variant="secondary" size="sm" :icon="Plus" @click="addItem">
              Tambah Item
            </BaseButton>
          </div>

          <!-- Empty -->
          <div v-if="!form.items.length" class="mt-6 rounded-lg border-2 border-dashed border-gray-200 px-6 py-8 text-center dark:border-gray-700">
            <p class="text-sm text-gray-500 dark:text-gray-400">Belum ada item. Klik "Tambah Item" untuk menambahkan produk.</p>
          </div>

          <!-- Items table -->
          <div v-else class="mt-4 overflow-x-auto">
            <table class="w-full text-left text-sm">
              <thead>
                <tr class="border-b border-gray-200 dark:border-gray-700">
                  <th class="px-3 py-2 font-medium text-gray-500 dark:text-gray-400">Produk / Varian</th>
                  <th class="px-3 py-2 font-medium text-gray-500 dark:text-gray-400 w-28">Qty</th>
                  <th class="px-3 py-2 font-medium text-gray-500 dark:text-gray-400 w-40">Harga Beli</th>
                  <th class="px-3 py-2 font-medium text-gray-500 dark:text-gray-400 text-right w-36">Subtotal</th>
                  <th class="px-3 py-2 w-12"></th>
                </tr>
              </thead>
              <tbody>
                <tr
                  v-for="(item, index) in form.items"
                  :key="index"
                  class="border-b border-gray-100 last:border-0 dark:border-gray-700/50"
                >
                  <!-- Product variant selector -->
                  <td class="px-3 py-2">
                    <BaseSelect
                      :model-value="item.product_variant_id"
                      :options="variantOptions.map(v => ({ label: v.label, value: v.product_variant_id }))"
                      placeholder="Pilih produk..."
                      @update:model-value="onVariantSelect(index, $event)"
                    />
                  </td>

                  <!-- Quantity -->
                  <td class="px-3 py-2">
                    <BaseInput
                      v-model.number="item.quantity"
                      type="number"
                      :min="1"
                      @input="onQuantityChange(index)"
                    />
                  </td>

                  <!-- Unit cost -->
                  <td class="px-3 py-2">
                    <BaseInput
                      v-model.number="item.unit_cost"
                      type="number"
                      :min="0"
                      @input="onUnitCostChange(index)"
                    />
                  </td>

                  <!-- Subtotal -->
                  <td class="px-3 py-2 text-right font-medium text-gray-900 dark:text-white">
                    {{ formatCurrency(item.subtotal) }}
                  </td>

                  <!-- Remove -->
                  <td class="px-3 py-2 text-center">
                    <button
                      class="rounded p-1.5 text-gray-400 hover:bg-red-50 hover:text-red-500 dark:hover:bg-red-900/20"
                      @click="removeItem(index)"
                    >
                      <Trash2 :size="16" />
                    </button>
                  </td>
                </tr>
              </tbody>
            </table>
          </div>

          <!-- Grand total -->
          <div v-if="form.items.length" class="mt-4 flex justify-end border-t border-gray-200 pt-4 dark:border-gray-700">
            <div class="text-right">
              <span class="text-sm text-gray-500 dark:text-gray-400">Total</span>
              <p class="text-lg font-bold text-gray-900 dark:text-white">{{ formatCurrency(grandTotal) }}</p>
            </div>
          </div>
        </div>

        <!-- Notes -->
        <div class="rounded-xl border border-gray-200 bg-white p-6 dark:border-gray-700 dark:bg-gray-800">
          <h2 class="text-sm font-semibold text-gray-900 dark:text-white">Catatan</h2>
          <div class="mt-3">
            <textarea
              v-model="form.notes"
              rows="3"
              class="w-full rounded-lg border border-gray-300 bg-white px-3 py-2 text-sm text-gray-900 placeholder-gray-400 focus:border-blue-500 focus:outline-none focus:ring-1 focus:ring-blue-500 dark:border-gray-600 dark:bg-gray-700 dark:text-white dark:placeholder-gray-500 dark:focus:border-blue-400 dark:focus:ring-blue-400"
              placeholder="Catatan tambahan (opsional)..."
            />
          </div>
        </div>

        <!-- Actions -->
        <div class="flex items-center justify-end gap-3">
          <BaseButton variant="secondary" size="md" @click="goBack">
            Batal
          </BaseButton>
          <BaseButton
            variant="primary"
            size="md"
            :icon="Save"
            :loading="submitting"
            @click="submit"
          >
            Simpan
          </BaseButton>
        </div>
      </div>
    </template>
  </div>
</template>
