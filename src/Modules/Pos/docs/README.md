# POS Module

Modul Point of Sale (POS) untuk mengelola outlet, produk, transaksi, diskon, voucher, member, QR ordering, shift kasir, dan laporan penjualan.

## Overview

Module ini dibangun sebagai **self-contained modular package** mengikuti arsitektur DDD Layered. Semua domain logic ada di dalam module, tidak bergantung pada `App\` namespace, dan siap diekstrak sebagai standalone package.

**Dependencies:** `Shared` module only.

## Architecture

```
src/Modules/Pos/
├── Domain/           → Pure business logic (no framework deps)
│   ├── Contracts/    → Repository interfaces
│   ├── Entities/     → Domain objects (immutable value holders)
│   ├── Enums/        → Status & type enumerations
│   └── Exceptions/   → Domain-specific exception classes
├── Application/      → Use cases & orchestration
│   ├── Actions/      → One action = one use case
│   ├── DTO/          → Data transfer objects between layers
│   └── Queries/      → Read-model queries (reports)
└── Infrastructure/   → Framework-specific implementations
    ├── Controllers/  → HTTP controllers (thin, delegates to Actions)
    ├── Migrations/   → Database schema
    ├── Models/       → Eloquent models
    ├── Providers/    → Service provider (bindings, routes, migrations)
    ├── Repositories/ → Eloquent implementations of Domain contracts
    ├── Requests/     → Form validation
    ├── Resources/    → API response transformation
    └── Routes/       → API route definitions
```

## Domain Entities

| Entity | Deskripsi |
|--------|-----------|
| `Outlet` | Unit bisnis (toko/kafe/warung) milik user |
| `Category` | Kategori produk per outlet (mendukung parent-child) |
| `Product` | Produk dengan varian, harga, stok |
| `ProductVariant` | Varian produk (size, type) dengan harga & stok sendiri |
| `Transaction` | Transaksi penjualan (completed, pending, voided, refunded) |
| `TransactionItem` | Line item dalam transaksi |
| `Refund` | Record refund (partial/full) linked ke transaksi |
| `RefundItem` | Line item yang di-refund |
| `Discount` | Promo/diskon (percentage, fixed, buy_x_get_y) |
| `Voucher` | Kode voucher redeemable |
| `VoucherRedemption` | Record pemakaian voucher |
| `Member` | Pelanggan terdaftar per outlet |
| `CashierShift` | Shift kasir (buka/tutup kasir dengan rekonsiliasi kas) |
| `Table` | Meja untuk QR ordering |
| `TableSession` | Sesi aktif per meja |
| `OrderQueue` | Antrian pesanan dari QR order |
| `PaymentMethod` | Metode pembayaran yang dikonfigurasi per outlet |
| `StockAdjustment` | Record penyesuaian stok |

## Fitur Utama

### 1. Multi-Outlet Management
- Satu user bisa punya banyak outlet
- Setiap outlet memiliki konfigurasi sendiri (business_type, payment_flow, tax, receipt)
- Business type: `retail`, `warung`, `kafe`, `warkop`
- Payment flow: `pay_first`, `pay_later`, `both`

### 2. Katalog Produk
- Kategori hierarkis (parent-child) dengan drag-reorder
- Produk dengan varian (size/type)
- Stock tracking per varian
- Upload gambar produk
- Status: active / inactive

### 3. Transaksi & Checkout
- **Pay First:** Bayar langsung → status `completed`
- **Pay Later (Open Bill):** Pesan dulu, bayar nanti → status `pending`
- **Both:** Outlet mendukung kedua mode
- Server-side discount & voucher evaluation untuk integritas data
- Tax/PPN otomatis dihitung berdasarkan setting outlet
- Auto-link ke shift kasir yang aktif

### 4. Tax / PPN
- Konfigurasi per outlet via `settings.tax_rate` (e.g. 11 untuk PPN 11%)
- Mode: **Tax Exclusive** (pajak ditambahkan di atas subtotal) atau **Tax Inclusive** (harga sudah termasuk pajak)
- Kalkulasi: `tax_amount = (subtotal - discount) * rate / 100` (exclusive)
- Disimpan per transaksi untuk audit trail

### 5. Discount Engine
- **Percentage:** Diskon persentase dari subtotal
- **Fixed:** Potongan nominal tetap
- **Buy X Get Y:** Beli X gratis Y (e.g. beli 2 gratis 1)
- Priority-based stacking (applied in order)
- Product-specific atau general (seluruh cart)
- Conditional: jam tertentu, hari tertentu, minimal quantity
- Member-only discounts

### 6. Voucher System
- Kode unik per voucher
- Batch generation (e.g. 100 voucher sekaligus)
- Usage limit & expiry date
- Product-specific atau general
- Validasi server-side sebelum checkout

### 7. Refund / Partial Refund
- Refund per item (pilih item & quantity yang di-refund)
- Pro-rata discount calculation (diskon didistribusi proporsional)
- Status tracking: `partially_refunded` → `refunded`
- Stock otomatis dikembalikan untuk item yang di-refund
- Refund method: `cash`, `original_method`, `store_credit`
- Refund number generation: `RFD-YYMMDD-XXXX`
- Riwayat refund per transaksi

### 8. Shift Management (Kasir)
- Buka shift: catat modal awal (opening amount)
- Tutup shift: catat uang aktual (closing amount)
- Rekonsiliasi otomatis: `expected = opening + cash_sales - cash_refunds`
- Selisih (difference): `closing - expected`
- Transaksi otomatis ter-link ke shift aktif
- Riwayat shift dengan pagination & filter

### 9. QR Ordering (Meja)
- Generate QR code per meja
- Customer scan → lihat menu → submit order (public endpoint, no auth)
- Order masuk ke antrian → kasir accept
- Mendukung outlet tipe kafe/warkop

### 10. Member / Loyalty
- Registrasi member per outlet (nama, telepon, email)
- Link member ke transaksi untuk tracking
- Member-only discounts
- Search member saat checkout

### 11. Reports & Analytics
- Daily summary (revenue, transaction count, top products)
- Date range report
- Product ranking
- Revenue by payment method
- Dashboard stats (today revenue, weekly trend)
- Export (CSV)

### 12. Receipt
- Template kustomisasi per outlet (header, footer, width)
- Generate receipt per transaksi

## Transaction Flow

```
┌─────────────┐     ┌───────────────┐     ┌─────────────────┐
│  Cart (FE)  │────▶│  Checkout API │────▶│ CreateTransaction│
│  Ephemeral  │     │  POST /trx    │     │     Action       │
└─────────────┘     └───────────────┘     └────────┬────────┘
                                                    │
                    ┌───────────────────────────────┼───────────────────┐
                    │                               │                   │
              ┌─────▼─────┐              ┌─────────▼────┐      ┌──────▼──────┐
              │ Validate   │              │ Calculate    │      │ Resolve     │
              │ Stock      │              │ Tax + Total  │      │ Shift ID    │
              └─────┬─────┘              └─────────┬────┘      └──────┬──────┘
                    │                               │                   │
                    └───────────────────────────────┼───────────────────┘
                                                    │
                                          ┌─────────▼────────┐
                                          │ Repository.create │
                                          │ (DB Transaction)  │
                                          └─────────┬────────┘
                                                    │
                                          ┌─────────▼────────┐
                                          │ Decrement Stock   │
                                          └─────────┬────────┘
                                                    │
                                          ┌─────────▼────────┐
                                          │ Return Transaction│
                                          └──────────────────┘
```

## Refund Flow

```
POST /transactions/{id}/refund
    │
    ├── Validate: status = completed | partially_refunded
    ├── Validate: each item quantity <= available (original - already refunded)
    ├── Calculate: pro-rata refund amount per item
    ├── Validate: total refund <= refundable amount
    │
    ├── Create: pos_refunds + pos_refund_items records
    ├── Update: transaction.refunded_amount
    ├── Update: transaction.status (partially_refunded | refunded)
    └── Restore: stock for refunded items
```

## Shift Flow

```
POST /outlets/{id}/shifts/open     → CashierShift (status: open)
    │
    │  ... transactions auto-linked via shift_id ...
    │
POST /shifts/{id}/close
    ├── Aggregate: cash sales during shift
    ├── Aggregate: cash refunds during shift
    ├── Calculate: expected = opening + cash_sales - cash_refunds
    ├── Calculate: difference = closing - expected
    └── Update: CashierShift (status: closed)
```

## API Endpoints

Semua endpoint memerlukan autentikasi (`auth:sanctum`) kecuali QR Order public endpoints.

### Outlets
| Method | Path | Deskripsi |
|--------|------|-----------|
| GET | `/pos/outlets` | List outlets milik user |
| POST | `/pos/outlets` | Buat outlet baru |
| PUT | `/pos/outlets/{id}` | Update outlet |
| DELETE | `/pos/outlets/{id}` | Hapus outlet (soft delete) |

### Categories
| Method | Path | Deskripsi |
|--------|------|-----------|
| GET | `/pos/outlets/{id}/categories` | List kategori per outlet |
| POST | `/pos/outlets/{id}/categories` | Buat kategori |
| PUT | `/pos/categories/{id}` | Update kategori |
| DELETE | `/pos/categories/{id}` | Hapus kategori |
| POST | `/pos/categories/reorder` | Reorder kategori |

### Products
| Method | Path | Deskripsi |
|--------|------|-----------|
| GET | `/pos/outlets/{id}/products` | List produk (filter: category, status) |
| POST | `/pos/outlets/{id}/products` | Buat produk + varian |
| GET | `/pos/products/{id}` | Detail produk |
| PUT | `/pos/products/{id}` | Update produk |
| POST | `/pos/products/{id}/deactivate` | Nonaktifkan produk |

### Stock
| Method | Path | Deskripsi |
|--------|------|-----------|
| POST | `/pos/products/{id}/stock` | Adjust stok (set/adjust) |
| GET | `/pos/outlets/{id}/stock` | Overview stok per outlet |

### Transactions
| Method | Path | Deskripsi |
|--------|------|-----------|
| GET | `/pos/outlets/{id}/transactions` | List transaksi (paginated + filter) |
| POST | `/pos/outlets/{id}/transactions` | Checkout / buat transaksi |
| GET | `/pos/transactions/{id}` | Detail transaksi |
| POST | `/pos/transactions/{id}/void` | Void transaksi |
| POST | `/pos/transactions/{id}/refund` | Refund (partial/full) |
| GET | `/pos/transactions/{id}/refunds` | Riwayat refund per transaksi |

### Open Bills
| Method | Path | Deskripsi |
|--------|------|-----------|
| GET | `/pos/outlets/{id}/open-bills` | List open bills (pending) |
| POST | `/pos/open-bills/{id}/close` | Tutup open bill (bayar) |

### Shifts
| Method | Path | Deskripsi |
|--------|------|-----------|
| GET | `/pos/outlets/{id}/shifts` | List shift (paginated + filter) |
| GET | `/pos/outlets/{id}/shifts/active` | Shift aktif saat ini |
| POST | `/pos/outlets/{id}/shifts/open` | Buka shift baru |
| GET | `/pos/shifts/{id}` | Detail shift + summary |
| POST | `/pos/shifts/{id}/close` | Tutup shift |

### Payment Methods
| Method | Path | Deskripsi |
|--------|------|-----------|
| GET | `/pos/outlets/{id}/payment-methods` | List metode pembayaran |
| POST | `/pos/outlets/{id}/payment-methods` | Tambah metode |
| PUT | `/pos/payment-methods/{id}` | Update metode |
| DELETE | `/pos/payment-methods/{id}` | Hapus metode |

### Discounts
| Method | Path | Deskripsi |
|--------|------|-----------|
| GET | `/pos/outlets/{id}/discounts` | List diskon |
| POST | `/pos/outlets/{id}/discounts` | Buat diskon |
| PUT | `/pos/discounts/{id}` | Update diskon |
| DELETE | `/pos/discounts/{id}` | Hapus diskon |
| POST | `/pos/discounts/evaluate` | Evaluasi diskon untuk cart |

### Vouchers
| Method | Path | Deskripsi |
|--------|------|-----------|
| GET | `/pos/outlets/{id}/vouchers` | List voucher |
| POST | `/pos/outlets/{id}/vouchers` | Buat voucher |
| POST | `/pos/outlets/{id}/vouchers/batch` | Batch create voucher |
| GET | `/pos/vouchers/{id}` | Detail voucher |
| POST | `/pos/vouchers/validate` | Validasi kode voucher |

### Members
| Method | Path | Deskripsi |
|--------|------|-----------|
| GET | `/pos/outlets/{id}/members` | List member |
| POST | `/pos/outlets/{id}/members` | Buat member |
| GET | `/pos/members/{id}` | Detail member |
| PUT | `/pos/members/{id}` | Update member |
| DELETE | `/pos/members/{id}` | Hapus member |
| GET | `/pos/outlets/{id}/members/search` | Search member (by name/phone) |

### Tables & QR Order
| Method | Path | Deskripsi |
|--------|------|-----------|
| GET | `/pos/outlets/{id}/tables` | List meja |
| POST | `/pos/outlets/{id}/tables` | Buat meja |
| DELETE | `/pos/tables/{id}` | Hapus meja |
| POST | `/pos/tables/{id}/close-session` | Tutup sesi meja |
| GET | `/pos/outlets/{id}/order-queue` | Antrian order |
| POST | `/pos/order-queue/{id}/accept` | Accept order |

### QR Order (Public — No Auth)
| Method | Path | Deskripsi |
|--------|------|-----------|
| GET | `/pos/qr/{token}/menu` | Lihat menu via QR |
| POST | `/pos/qr/{token}/order` | Submit order via QR |
| GET | `/pos/qr/{token}/order/{id}` | Status order |

### Reports
| Method | Path | Deskripsi |
|--------|------|-----------|
| GET | `/pos/outlets/{id}/reports/daily` | Laporan harian |
| GET | `/pos/outlets/{id}/reports/range` | Laporan rentang tanggal |
| GET | `/pos/outlets/{id}/reports/products` | Ranking produk |
| GET | `/pos/outlets/{id}/reports/payments` | Revenue per metode bayar |
| GET | `/pos/outlets/{id}/reports/dashboard` | Dashboard stats |
| GET | `/pos/outlets/{id}/reports/export` | Export CSV |

### Receipts
| Method | Path | Deskripsi |
|--------|------|-----------|
| GET | `/pos/transactions/{id}/receipt` | Generate receipt |
| PUT | `/pos/outlets/{id}/receipt-template` | Update receipt template |

## Konfigurasi Outlet

Outlet settings disimpan sebagai JSON di kolom `settings`:

```json
{
  "currency": "IDR",
  "tax_rate": 11,
  "tax_inclusive": false,
  "receipt_header": "Kopi Nusantara",
  "receipt_footer": "Terima kasih!",
  "receipt_width": "58mm"
}
```

| Key | Type | Default | Deskripsi |
|-----|------|---------|-----------|
| `currency` | string | `IDR` | Mata uang |
| `tax_rate` | number | `0` | Tarif pajak (%) |
| `tax_inclusive` | boolean | `false` | Harga sudah termasuk pajak? |
| `receipt_header` | string | - | Header struk |
| `receipt_footer` | string | - | Footer struk |
| `receipt_width` | string | `58mm` | Lebar kertas struk |

## Frontend Architecture

```
resources/js/
├── api/pos.ts              → Semua API calls (typed)
├── types/pos.ts            → TypeScript interfaces
├── stores/
│   ├── pos-cart.ts         → Ephemeral cart state + discount evaluation
│   └── pos-outlet.ts       → Active outlet management + persistence
└── pages/pos/
    ├── Index.vue           → Outlet list + create
    ├── cashier/            → Cashier interface (checkout, open bills)
    ├── catalog/            → Product & category management
    ├── discount/           → Discount management
    ├── voucher/            → Voucher management
    ├── members/            → Member management
    ├── tables/             → Table & QR management
    ├── reports/            → Reports & analytics
    └── outlet/             → Outlet settings
```

## Konvensi

- Model naming: `{Name}Model` (e.g. `TransactionModel`)
- Entity naming: `{Name}` (e.g. `Transaction`) — pure PHP
- Action naming: `{Verb}{Noun}Action` (e.g. `CreateTransactionAction`)
- DTO naming: `{Name}Data` (e.g. `CheckoutData`)
- Resource naming: `{Name}Resource` (e.g. `TransactionResource`)
- Exception naming: `{Name}Exception` (e.g. `RefundNotAllowedException`)
- Transaction number: `TRX-YYMMDD-XXXX`
- Refund number: `RFD-YYMMDD-XXXX`
- All user-facing messages: Bahasa Indonesia
- API prefix: `/api/pos/`
