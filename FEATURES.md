# Daftar Fitur — Purdia Dashboard

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

## 10. Reading List

| Fitur | Deskripsi |
|-------|-----------|
| Tambah Artikel | Simpan URL artikel untuk dibaca nanti |
| Toggle Read | Tandai sudah/belum dibaca |
| Toggle Favorite | Tandai sebagai favorit |
| Filter | Filter by read/unread, favorite |
| Hapus | Hapus dari daftar |

**Endpoints:** `/api/reading-list`, `/api/reading-list/{id}/toggle-read`, `/api/reading-list/{id}/toggle-favorite`

---

## 11. Journal & Mood Tracker

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

## 12. Goals & Milestones

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

## 13. Wishlist

| Fitur | Deskripsi |
|-------|-----------|
| CRUD | Buat, edit, hapus item wishlist |
| Category | Organisasi per kategori |
| Filter | Filter by completed/pending, category |
| Toggle Complete | Tandai sudah terpenuhi |
| Completed At | Timestamp kapan terpenuhi |

**Endpoints:** `/api/wishlists`, `/api/wishlists/{id}/toggle`

---

## 14. Tags (Polymorphic)

| Fitur | Deskripsi |
|-------|-----------|
| CRUD | Buat, edit, hapus tag |
| Color | Warna per tag |
| Attach/Detach | Pasang tag ke notes atau tasks |
| Per-user | Tag milik user masing-masing |

**Endpoints:** `/api/tags`, `/api/tags/{id}/attach`, `/api/tags/{id}/detach`

---

## 15. Daily Quotes

| Fitur | Deskripsi |
|-------|-----------|
| Quote of the Day | Satu quote motivasi per hari (rotasi berdasarkan day of year) |
| List & Search | Lihat semua quotes dengan pagination & search |
| Tambah Quote | Insert quote baru secara manual |
| Hapus Quote | Hapus quote dari database |
| Seeded | 20 quote motivasi awal di-seed |

**Endpoints:** `/api/quotes`, `/api/quotes/today`

---

## 16. Trash (Unified)

| Fitur | Deskripsi |
|-------|-----------|
| List | Lihat semua item yang sudah di-soft-delete (notes, tasks) |
| Restore | Kembalikan item ke state aktif |
| Force Delete | Hapus permanen |
| 30-day Limit | Hanya tampilkan trash dalam 30 hari terakhir |

**Endpoints:** `/api/trash`, `/api/trash/{type}/{id}/restore`, `/api/trash/{type}/{id}` (DELETE)

---

## 17. Activity Log

| Fitur | Deskripsi |
|-------|-----------|
| Auto Log | Catat aktivitas user |
| Paginated | List dengan pagination |
| Per-user | Hanya lihat aktivitas sendiri |

**Endpoints:** `/api/activity-logs`

---

## 18. Dashboard

| Fitur | Deskripsi |
|-------|-----------|
| Stat Cards | Total notes, tasks pending, bookmarks, events mendatang |
| Weekly Summary | Tasks completed/created, pomodoros, focus minutes, habits, notes, streak |
| Quote of the Day | Tampil di dashboard |
| Weather | Cuaca kota saat ini (OpenWeatherMap) |
| Quick Capture | Input cepat ke scratchpad |
| Recent Tasks | 5 task pending terbaru |
| Recent Notes | 3 catatan terbaru |

**Endpoints:** `/api/dashboard/weekly-summary`, `/api/weather/current`

---

## 19. Settings

| Halaman | Fitur |
|---------|-------|
| Account | Edit profil, ubah password, upload/hapus avatar |
| Appearance | Theme (dark/light), primary color |
| General | Locale, sidebar preference |
| Export | Export data (planned) |

---

## 20. Error Handling

| Fitur | Deskripsi |
|-------|-----------|
| Global Toast | Error API otomatis ditampilkan sebagai toast notification |
| Success Feedback | Operasi berhasil (create/update/delete) tampilkan toast sukses |
| Silent Refresh | Token expired otomatis di-refresh tanpa user tahu |
| Ownership Check | Reusable `AuthorizesOwnership` trait di semua controllers |

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
| Calendar | `/calendar` |
| Pomodoro | `/pomodoro` |
| Scratchpads | `/scratchpads` |
| Habits | `/habits` |
| Finance | `/finance` |
| Reading List | `/reading-list` |
| Journal | `/journal` |
| Goals | `/goals` |
| Quotes | `/quotes` |
| Wishlist | `/wishlist` |
| Streaks | `/streaks` |
| Activity | `/activity` |
| Trash | `/trash` |
| Settings | `/settings/general`, `/settings/appearance`, `/settings/account`, `/settings/export` |
