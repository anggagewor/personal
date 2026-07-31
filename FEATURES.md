# Daftar Fitur — Purdia Dashboard

> **Disclaimer:** Proyek ini adalah personal dashboard sekaligus playground untuk meracik module-module yang bisa di-reuse di proyek lain. Banyak module sengaja dibangun dengan standar production-grade (DDD 3-layer, property-based testing, proper state machines) meskipun ini "cuma" personal dashboard — tujuannya agar pola-polanya bisa langsung dicopy ke proyek klien tanpa perlu refactor ulang.

**Total: 34 fitur / 28 module**

## 1. Authentication & User Management

| Fitur | Deskripsi |
|-------|-----------|
| Login | Email + password, return token |
| Register | Buat akun baru |
| Logout | Revoke semua token |
| Token Refresh | Perpanjang session tanpa login ulang |
| Profile | Update nama, email, password, avatar |
| Preferences | Theme (dark/light), primary color, locale, sidebar state |

**Endpoints:** `/api/auth/*`, `/api/profile`, `/api/preferences`

---

## 2. Notes

| Fitur | Deskripsi |
|-------|-----------|
| CRUD | Buat, baca, edit, hapus catatan |
| Rich Text | Content pakai Tiptap editor (HTML) |
| Pin | Toggle pin untuk catatan penting (muncul di atas) |
| Search | Pencarian berdasarkan judul |
| Soft Delete | Bisa di-restore dari trash |

**Endpoints:** `/api/notes`, `/api/notes/{id}/toggle-pin`

---

## 3. Bookmarks

| Fitur | Deskripsi |
|-------|-----------|
| CRUD | Simpan, edit, hapus bookmark URL |
| Kategori | Organisasi bookmark per kategori (grouped response) |
| Icon | Custom icon per bookmark |
| Deskripsi | Catatan tambahan untuk tiap bookmark |

**Endpoints:** `/api/bookmarks`

---

## 4. Task Management

| Fitur | Deskripsi |
|-------|-----------|
| CRUD | Buat, edit, hapus task |
| Status | Pending → In Progress → Completed |
| Priority | Low, Medium, High |
| Due Date | Tanggal deadline |
| Reorder | Atur urutan task (position-based) |
| Recurrence | Task berulang (daily/weekly/monthly) |
| Soft Delete | Bisa di-restore dari trash |

**Endpoints:** `/api/tasks`, `/api/tasks/reorder`

---

## 5. Calendar

| Fitur | Deskripsi |
|-------|-----------|
| Events CRUD | Buat, edit, hapus event di kalender |
| Date Range Filter | Filter events berdasarkan start_date & end_date |
| All-day Event | Event seharian tanpa jam spesifik |
| Color Coded | Warna berbeda per event |
| Hari Libur | Daftar hari libur nasional Indonesia (seeded) |

**Endpoints:** `/api/calendar-events`, `/api/holidays`

---

## 6. Pomodoro Timer

| Fitur | Deskripsi |
|-------|-----------|
| Start Session | Mulai timer pomodoro (duration configurable) |
| Complete | Tandai session selesai |
| Cancel | Batalkan session yang sedang berjalan |
| Link to Task | Hubungkan pomodoro ke task tertentu (opsional) |
| Stats | Statistik jumlah & durasi pomodoro |

**Endpoints:** `/api/pomodoros`, `/api/pomodoros/{id}/complete`, `/api/pomodoros/{id}/cancel`, `/api/pomodoros/stats`

---

## 7. Scratchpads

| Fitur | Deskripsi |
|-------|-----------|
| CRUD | Quick notes singkat |
| Warna | Pilihan warna background |
| Posisi | Atur posisi/urutan scratchpad |
| Quick Capture | Input cepat dari dashboard |

**Endpoints:** `/api/scratchpads`

---

## 8. Habit Tracker

| Fitur | Deskripsi |
|-------|-----------|
| CRUD | Buat, edit, hapus habit |
| Frequency | Daily atau weekly |
| Daily Toggle | Check-off harian (habit log per tanggal) |
| Streak | Current streak & longest streak tracking |
| Active/Inactive | Nonaktifkan habit tanpa hapus |
| Color | Kustomisasi warna |

**Endpoints:** `/api/habits`, `/api/habits/{id}/toggle`

---

## 9. Finance Tracker

| Fitur | Deskripsi |
|-------|-----------|
| CRUD | Catat pemasukan & pengeluaran |
| Type | Income atau Expense |
| Category | Kategori per type (makanan, transport, gaji, dll) |
| Month Filter | Filter transaksi per bulan |
| Summary | Ringkasan total income/expense/balance per bulan |
| Date | Tanggal transaksi |

**Endpoints:** `/api/finances`, `/api/finances/summary`

---

## 10. Market Watchlist

| Fitur | Deskripsi |
|-------|-----------|
| Watchlist CRUD | Tambah/hapus simbol yang ingin dipantau (maks 15) |
| Tipe Aset | Forex, crypto, stock, commodity |
| Harga Real-time | Data dari Twelve Data API (cache sesuai refresh interval) |
| Sparkline | Mini chart per simbol di dashboard |
| History | Riwayat harga tersimpan di DB (30 hari) |
| Cron Fetch | Scheduler otomatis tiap 15 menit (configurable) |
| Rate Limit Aware | Chunk 8 symbols/request, estimasi daily calls di settings |
| Combined Chart | Semua simbol dalam 1 chart performa (% change) |
| Dashboard Widget | Compact view top 8 symbols + sparkline + harga + change |

**Endpoints:** `/api/market/watchlist`, `/api/market/prices`, `/api/market/dashboard`, `/api/market/history/{symbol}`, `/api/market/config`

**Commands:** `php artisan market:fetch-prices`

---

## 11. Emas Antam

| Fitur | Deskripsi |
|-------|-----------|
| Harga Harian | Harga emas Antam per gram (Rp) |
| Histori 15+ Tahun | Data dari 2010 - sekarang |
| Import JSON | Seed database dari file `storage/antam.json` |
| Daily Cron | Fetch harga terbaru otomatis jam 10:00 WIB |
| Chart | Grafik interaktif dengan period selector (1m/3m/6m/1y/5y/all) |
| Stats | Harga hari ini, 30d high/low, 30d perubahan |
| Dashboard Widget | Harga terkini + sparkline 30 hari + compact stats |

**Endpoints:** `/api/gold/dashboard`, `/api/gold/history?period=1y`

**Commands:** `php artisan gold:import-history`, `php artisan gold:fetch-daily`

---

## 12. Reading List

| Fitur | Deskripsi |
|-------|-----------|
| Tambah Artikel | Simpan URL artikel untuk dibaca nanti |
| Toggle Read | Tandai sudah/belum dibaca |
| Toggle Favorite | Tandai sebagai favorit |
| Filter | Filter by read/unread, favorite |
| Hapus | Hapus dari daftar |

**Endpoints:** `/api/reading-list`, `/api/reading-list/{id}/toggle-read`, `/api/reading-list/{id}/toggle-favorite`

---

## 13. Journal & Mood Tracker

| Fitur | Deskripsi |
|-------|-----------|
| Daily Entry | Satu jurnal per hari per user (upsert) |
| Mood | Pilihan mood: happy, neutral, sad, stressed, energized |
| Content | Tulis isi jurnal bebas |
| Month Filter | Filter journal per bulan |
| Mood Stats | Statistik mood dari waktu ke waktu |
| Show by Date | Lihat jurnal berdasarkan tanggal |
| Hapus | Hapus entry jurnal |

**Endpoints:** `/api/journals`, `/api/journals/moods`, `/api/journals/{date}`

---

## 14. Goals & Milestones

| Fitur | Deskripsi |
|-------|-----------|
| CRUD | Buat, edit, hapus goal |
| Target Date | Tanggal target penyelesaian |
| Status | Active, Completed, Abandoned |
| Milestones | Sub-target dalam satu goal |
| Toggle Milestone | Tandai milestone selesai/belum |
| Add Milestone | Tambah milestone ke goal yang sudah ada |

**Endpoints:** `/api/goals`, `/api/goals/{id}/milestones`, `/api/goals/{id}/milestones/{milestoneId}/toggle`

---

## 15. Wishlist

| Fitur | Deskripsi |
|-------|-----------|
| CRUD | Buat, edit, hapus item wishlist |
| Category | Organisasi per kategori |
| Filter | Filter by completed/pending, category |
| Toggle Complete | Tandai sudah terpenuhi |
| Completed At | Timestamp kapan terpenuhi |

**Endpoints:** `/api/wishlists`, `/api/wishlists/{id}/toggle`

---

## 16. Tags (Polymorphic)

| Fitur | Deskripsi |
|-------|-----------|
| CRUD | Buat, edit, hapus tag |
| Color | Warna per tag |
| Attach/Detach | Pasang tag ke notes atau tasks |
| Per-user | Tag milik user masing-masing |

**Endpoints:** `/api/tags`, `/api/tags/{id}/attach`, `/api/tags/{id}/detach`

---

## 17. Daily Quotes

| Fitur | Deskripsi |
|-------|-----------|
| Quote of the Day | Satu quote motivasi per hari (rotasi berdasarkan day of year) |
| List & Search | Lihat semua quotes dengan pagination & search |
| Tambah Quote | Insert quote baru secara manual |
| Hapus Quote | Hapus quote dari database |
| Seeded | 20 quote motivasi awal di-seed |

**Endpoints:** `/api/quotes`, `/api/quotes/today`

---

## 18. Trash (Unified)

| Fitur | Deskripsi |
|-------|-----------|
| List | Lihat semua item yang sudah di-soft-delete (notes, tasks) |
| Restore | Kembalikan item ke state aktif |
| Force Delete | Hapus permanen |
| 30-day Limit | Hanya tampilkan trash dalam 30 hari terakhir |

**Endpoints:** `/api/trash`, `/api/trash/{type}/{id}/restore`, `/api/trash/{type}/{id}` (DELETE)

---

## 19. Activity Log

| Fitur | Deskripsi |
|-------|-----------|
| Auto Log | Catat aktivitas user |
| Paginated | List dengan pagination |
| Per-user | Hanya lihat aktivitas sendiri |

**Endpoints:** `/api/activity-logs`

---

## 20. Dashboard

| Fitur | Deskripsi |
|-------|-----------|
| Stat Cards | Total notes, tasks pending, bookmarks, events mendatang |
| Weekly Summary | Tasks completed/created, pomodoros, focus minutes, habits, notes, streak |
| Quote of the Day | Tampil di dashboard |
| Weather | Cuaca kota saat ini (OpenWeatherMap, cache 1 jam) |
| World Clock | Timezone yang dikonfigurasi user, live update tiap detik |
| Market Widget | Watchlist symbols + harga + sparkline chart (dari DB) |
| Gold Widget | Harga emas Antam terkini + sparkline 30 hari + 30d stats |
| Quick Capture | Input cepat ke scratchpad |
| Recent Tasks | 5 task pending terbaru |
| Recent Notes | 3 catatan terbaru |

**Endpoints:** `/api/dashboard/weekly-summary`, `/api/weather/current`

---

## 21. Settings

| Halaman | Fitur |
|---------|-------|
| Account | Edit profil, ubah password, upload/hapus avatar |
| Appearance | Theme (dark/light), primary color |
| General | Locale, sidebar preference, world clock timezone config |
| Market | Kelola watchlist symbols (add/remove), API status & rate limit info |
| Export | Export data (planned) |

---

## 22. Error Handling

| Fitur | Deskripsi |
|-------|-----------|
| Global Toast | Error API otomatis ditampilkan sebagai toast notification |
| Success Feedback | Operasi berhasil (create/update/delete) tampilkan toast sukses |
| Silent Refresh | Token expired otomatis di-refresh tanpa user tahu |
| Ownership Check | Reusable `AuthorizesOwnership` trait di semua controllers |

---

## 23. Quick Command (Cmd+K)

| Fitur | Deskripsi |
|-------|-----------|
| Keyboard Shortcut | `Cmd+K` / `Ctrl+K` membuka command palette |
| Navigasi Cepat | Cari dan jump ke semua halaman di dashboard |
| Dynamic Search | Ketik 2+ karakter → search notes, tasks, bookmarks via API |
| Keyboard Navigation | Arrow up/down + Enter untuk pilih |
| Debounced | API call di-debounce 300ms agar tidak spam |

---

## 24. Budget Planning

| Fitur | Deskripsi |
|-------|-----------|
| CRUD | Buat, edit, hapus budget per kategori per bulan |
| Upsert | Satu kategori/bulan hanya boleh satu budget (auto-update jika sudah ada) |
| Summary | Total budget, total terpakai, sisa budget |
| Progress Bar | Visual bar per kategori (hijau/amber/merah) |
| Alert Near Limit | Warning saat spending ≥80% budget |
| Alert Exceeded | Alert saat spending melebihi budget |
| Integration Finance | Spending dihitung otomatis dari data Finance module |

**Endpoints:** `/api/budgets`, `/api/budgets/summary`

---

## 25. Password Vault

| Fitur | Deskripsi |
|-------|-----------|
| CRUD | Tambah, edit, hapus credential |
| Client-side Encryption | Password dienkripsi di browser sebelum dikirim ke server |
| @purdia/crypto | Menggunakan AES-GCM via Web Crypto API |
| Toggle Show | Lihat/sembunyikan password (decrypt on-demand) |
| Copy to Clipboard | Salin password tanpa harus lihat |
| Search | Cari berdasarkan nama, username, URL |
| Category | Organisasi credential per kategori |
| URL Link | Buka URL langsung dari vault |

**Endpoints:** `/api/vault`, `/api/vault/categories`

---

## 26. Kanban Board

| Fitur | Deskripsi |
|-------|-----------|
| 3 Kolom | To Do, In Progress, Selesai |
| Drag & Drop | HTML5 native drag & drop antar kolom |
| Optimistic Update | Status langsung berubah, API call di background |
| Priority Badge | Warna badge sesuai priority (low/medium/high) |
| Due Date | Tampil di setiap card |
| Link ke List | Tombol kembali ke view list biasa |

**Route:** `/tasks/kanban`

---

## 27. Google Drive Integration

| Fitur | Deskripsi |
|-------|-----------|
| OAuth Connect | Hubungkan akun Google via OAuth 2.0 popup |
| Disconnect | Putuskan koneksi kapan saja |
| File Manager | Browse file & folder, navigasi masuk/keluar folder |
| Upload | Upload file ke Drive (max 10MB) |
| Download | Download file dari Drive |
| Create Folder | Buat folder baru |
| Delete | Hapus file/folder |
| Backup All Data | Export semua data sebagai JSON ke folder "Purdia Backups" |
| Sync Notes | Upload semua catatan sebagai HTML ke folder "Purdia Notes" |
| Auto Refresh Token | Token expired otomatis di-refresh |
| Encrypted Storage | Token disimpan terenkripsi di database |

**Endpoints:** `/api/drive/status`, `/api/drive/auth-url`, `/api/drive/callback`, `/api/drive/disconnect`, `/api/drive/files`, `/api/drive/files/upload`, `/api/drive/files/{id}/download`, `/api/drive/files/{id}`, `/api/drive/folders`, `/api/drive/backup`, `/api/drive/sync-notes`

**Setup:** Lihat `docs/google-drive-setup.md`

---

## 28. Accounting (Double-Entry Bookkeeping)

| Fitur | Deskripsi |
|-------|-----------|
| Chart of Accounts | Daftar akun terstruktur (aset, kewajiban, ekuitas, pendapatan, beban) |
| Jurnal Umum | CRUD jurnal dengan multi-line debit/kredit (harus seimbang) |
| Buku Besar | Ledger per akun dengan saldo berjalan + filter tanggal |
| Trial Balance | Neraca saldo per periode |
| Laba Rugi | Income statement (pendapatan - beban) |
| Neraca | Balance sheet dengan persamaan akuntansi |
| Sample Data | Load data contoh untuk belajar |
| Reset | Reset jurnal saja atau reset semua data akuntansi |

**Endpoints:** `/api/accounting/accounts`, `/api/accounting/journal-entries`, `/api/accounting/ledger/{id}`, `/api/accounting/reports/*`, `/api/accounting/sample-data`, `/api/accounting/reset/*`

---

## 29. Unit Converter

| Fitur | Deskripsi |
|-------|-----------|
| 8 Kategori Bawaan | Panjang, berat, suhu, luas, volume, kecepatan, waktu, data digital |
| Custom Converter | Buat kategori & satuan konversi sendiri (CRUD) |
| Live Calculation | Hasil konversi langsung tampil saat input berubah |
| Swap | Tukar unit from ↔ to |
| Non-linear | Suhu pakai formula khusus (bukan faktor linear) |

**Endpoints:** `/api/converter/categories`, `/api/converter/units`

---

## 30. SQL Generator (Tools)

| Fitur | Deskripsi |
|-------|-----------|
| Form-based | Input nama tabel, kolom, tipe data, constraint via form |
| Multi-dialect | MySQL/MariaDB, PostgreSQL, SQLite |
| Column Options | Primary key, auto increment, nullable, unique, index, default value |
| Searchable Types | Dropdown tipe data bisa di-search (BaseSelect) |
| Timestamps | Otomatis tambah created_at/updated_at |
| Soft Deletes | Otomatis tambah deleted_at |
| IF NOT EXISTS | Toggle opsional |
| Index Generation | CREATE INDEX statement terpisah |
| Copy to Clipboard | Satu klik salin hasil SQL |
| Reset | Bersihkan form ke default |
| Frontend-only | Tidak membutuhkan backend/API |

**Route:** `/tools/sql-generator`

---

---

## 31. Point of Sale (POS)

| Fitur | Deskripsi |
|-------|-----------|
| Multi-Outlet | Kelola banyak outlet dalam satu akun |
| Outlet Settings | Tipe bisnis (retail/warung/kafe/warkop), alur pembayaran (bayar dulu/nanti/keduanya) |
| Katalog Produk | Produk + varian, SKU, harga, stock tracking |
| Kategori | Hierarki kategori produk dengan sorting |
| Kasir | Interface kasir untuk input transaksi |
| Transaksi | Checkout, hitung kembalian, cetak struk |
| Void | Batalkan transaksi dengan alasan |
| Metode Pembayaran | Cash, QRIS, transfer bank, e-wallet (konfigurasi per outlet) |
| Diskon | Diskon produk/kategori/total, otomatis evaluate |
| Voucher | Generate voucher (single/batch), validasi + redemption |
| Member | Membership dengan poin (CRUD, search) |
| Meja | Manajemen meja + session untuk model dine-in |
| Open Bill | Buka tagihan, tutup nanti (bayar belakangan) |
| QR Order | Menu publik (tanpa auth) via QR code, customer bisa order sendiri |
| Stock Management | Track stock per variant, adjustment (restock/set/adjust) |
| Laporan | Harian, rentang tanggal, per produk, per metode bayar, export |
| Dashboard | Ringkasan penjualan, top products, grafik |

**Endpoints:** `/api/pos/*` (25+ endpoints)
**Route:** `/pos`, `/pos/cashier/:outletId`, `/pos/catalog/:outletId`, dll.

---

## 32. Supplier Management

| Fitur | Deskripsi |
|-------|-----------|
| Supplier CRUD | Nama, alamat, telepon, email, rekening bank, catatan |
| Soft Delete | Hapus supplier tanpa kehilangan histori PO |
| Nama Unik per Outlet | Validasi duplikat nama dalam satu outlet |
| Search | Cari supplier berdasarkan nama, telepon, email |
| Purchase Order | PO lifecycle: draft → confirmed → partial/received, atau cancelled |
| PO Number Auto | Format PO-YYYYMMDD-SEQ (otomatis generate) |
| PO Items | Dynamic line items (produk, qty, harga beli, subtotal) |
| Total Calculation | Total PO = sum(qty × unit_cost) |
| Confirm & Cancel | Validasi state sebelum transisi (empty PO gabisa confirm, dsb) |
| Goods Receiving | Terima barang per PO, partial/full delivery |
| Over-delivery Prevention | Qty terima tidak boleh melebihi qty pesan |
| Auto Stock Update | Penerimaan barang otomatis tambah stock di POS |
| PO Status Auto | Partial/received ditentukan otomatis dari penerimaan |
| Payment Recording | Catat pembayaran ke supplier (tunai/transfer/e-wallet) |
| Overpayment Prevention | Bayar tidak boleh melebihi sisa tagihan |
| Payment Status Auto | Unpaid/partial/paid otomatis dari total bayar |
| Supplier-Product Link | Hubungkan supplier ke produk + default harga beli |
| Default Cost Pre-fill | Harga beli otomatis terisi saat buat PO |
| Unlink Preserves History | Putus hubungan tanpa merusak data PO lama |
| Outstanding Debt | Total utang per supplier (exclude cancelled PO) |
| Laporan Pembelian | Summary, per supplier, per produk, date range, CSV export |
| Dashboard Supplier | Total utang, PO pending, pembelian terakhir |

**Endpoints:** `/api/supplier/*` (25 endpoints)
**Route:** `/supplier/:outletId`, `/supplier/suppliers`, `/supplier/purchase-orders`, `/supplier/reports`

---

## 33. Log Viewer

| Fitur | Deskripsi |
|-------|-----------|
| List Log Files | Lihat semua file log Laravel |
| Read Entries | Baca log entry dengan pagination |
| Filter by Level | Filter: emergency, alert, critical, error, warning, notice, info, debug |
| Search | Pencarian teks dalam log |
| Reverse Read | Baca dari bawah (terbaru dulu) |

**Endpoints:** `/api/logs/files`, `/api/logs/entries`
**Route:** `/logs`

---

## 34. Database Manager

| Fitur | Deskripsi |
|-------|-----------|
| List Tables | Lihat semua tabel + jumlah row |
| Table Structure | Kolom, tipe data, nullable, key, default, extra, indexes |
| Browse Rows | View data paginated (25 per page) |
| Sort | Klik header kolom untuk sort asc/desc |
| Filter | Multi-filter: kolom + operator (=, !=, >, <, LIKE, IS NULL, dll) + value |
| Inline Edit | Edit row langsung di tabel (klik pencil → ubah → save) |
| Delete Row | Hapus row per primary key (dengan confirm) |
| Alter Table | Tambah kolom, hapus kolom, ubah tipe/nullable kolom |
| Raw Query | Execute SELECT query (read-only, untuk keamanan) |

**Endpoints:** `/api/database/*` (7 endpoints)
**Route:** `/database`, `/database/:table`

---

## Frontend Pages

| Halaman | Route |
|---------|-------|
| Login | `/login` |
| Register | `/register` |
| Dashboard | `/` |
| Notes | `/notes` |
| Bookmarks | `/bookmarks` |
| Tasks | `/tasks` |
| Kanban Board | `/tasks/kanban` |
| Calendar | `/calendar` |
| Pomodoro | `/pomodoro` |
| Scratchpads | `/scratchpads` |
| Habits | `/habits` |
| Finance | `/finance` |
| Budget | `/budget` |
| Password Vault | `/vault` |
| Google Drive | `/drive` |
| Accounting | `/accounting`, `/accounting/journal`, `/accounting/ledger`, `/accounting/reports/*` |
| Market | `/market` |
| Emas Antam | `/gold` |
| Unit Converter | `/converter`, `/converter/*` |
| Reading List | `/reading-list` |
| Journal | `/journal` |
| Goals | `/goals` |
| Quotes | `/quotes` |
| Wishlist | `/wishlist` |
| Streaks | `/streaks` |
| Activity | `/activity` |
| Trash | `/trash` |
| SQL Generator | `/tools/sql-generator` |
| POS | `/pos`, `/pos/cashier/:outletId`, `/pos/catalog/:outletId` |
| Supplier | `/supplier/:outletId`, `/supplier/suppliers`, `/supplier/purchase-orders`, `/supplier/reports` |
| Database Manager | `/database`, `/database/:table` |
| Log Viewer | `/logs` |
| Settings | `/settings/general`, `/settings/appearance`, `/settings/account`, `/settings/market`, `/settings/export` |

