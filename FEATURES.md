# Daftar Fitur — Purdia Dashboard

## 1. Authentication & User Management

| Fitur | Deskripsi |
|-------|-----------|
| Login | Email + password, return token |
| Register | Buat akun baru |
| Logout | Revoke token |
| Token Refresh | Perpanjang session |
| Profile | Update nama, email, password, avatar |
| Preferences | Theme (dark/light), primary color, locale, sidebar state |

**Endpoints:** `/api/auth/*`, `/api/profile`, `/api/preferences`

---

## 2. Notes

| Fitur | Deskripsi |
|-------|-----------|
| CRUD | Buat, baca, edit, hapus catatan |
| Rich Text | Content pakai longText (support HTML/markdown) |
| Pin | Toggle pin untuk catatan penting |
| Soft Delete | Bisa di-restore dari trash |

**Endpoints:** `/api/notes`, `/api/notes/{id}/toggle-pin`

---

## 3. Bookmarks

| Fitur | Deskripsi |
|-------|-----------|
| CRUD | Simpan, edit, hapus bookmark URL |
| Kategori | Organisasi bookmark per kategori |
| Icon | Custom icon per bookmark |
| Deskripsi | Catatan tambahan untuk tiap bookmark |

**Endpoints:** `/api/bookmarks`

---

## 4. Task Management

| Fitur | Deskripsi |
|-------|-----------|
| CRUD | Buat, edit, hapus task |
| Status | Todo → In Progress → Done |
| Priority | Low, Medium, High |
| Due Date | Tanggal deadline |
| Drag Reorder | Atur urutan task via drag & drop |
| Recurring | Task berulang (daily/weekly/monthly) dengan batas tanggal |
| Soft Delete | Bisa di-restore dari trash |

**Endpoints:** `/api/tasks`, `/api/tasks/reorder`

---

## 5. Calendar

| Fitur | Deskripsi |
|-------|-----------|
| Events CRUD | Buat, edit, hapus event di kalender |
| All-day Event | Event seharian tanpa jam spesifik |
| Color Coded | Warna berbeda per event |
| Hari Libur | Daftar hari libur nasional Indonesia (seeded) |

**Endpoints:** `/api/calendar-events`, `/api/holidays`

---

## 6. Pomodoro Timer

| Fitur | Deskripsi |
|-------|-----------|
| Start Session | Mulai timer pomodoro |
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

**Endpoints:** `/api/scratchpads`

---

## 8. Habit Tracker

| Fitur | Deskripsi |
|-------|-----------|
| CRUD | Buat, edit, hapus habit |
| Frequency | Daily atau weekly |
| Daily Toggle | Check-off harian (habit log per tanggal) |
| Active/Inactive | Nonaktifkan habit tanpa hapus |
| Icon & Color | Kustomisasi tampilan |

**Endpoints:** `/api/habits`, `/api/habits/{id}/toggle`

---

## 9. Finance Tracker

| Fitur | Deskripsi |
|-------|-----------|
| CRUD | Catat pemasukan & pengeluaran |
| Type | Income atau Expense |
| Category | Kategori (food, transport, salary, dll) |
| Summary | Ringkasan total income/expense/balance |
| Date | Tanggal transaksi |

**Endpoints:** `/api/finances`, `/api/finances/summary`

---

## 10. Reading List

| Fitur | Deskripsi |
|-------|-----------|
| Tambah Artikel | Simpan URL artikel untuk dibaca nanti |
| Auto Metadata | Thumbnail & domain otomatis |
| Toggle Read | Tandai sudah/belum dibaca |
| Toggle Favorite | Tandai sebagai favorit |
| Hapus | Hapus dari daftar |

**Endpoints:** `/api/reading-list`, `/api/reading-list/{id}/toggle-read`, `/api/reading-list/{id}/toggle-favorite`

---

## 11. Journal & Mood Tracker

| Fitur | Deskripsi |
|-------|-----------|
| Daily Entry | Satu jurnal per hari per user |
| Mood | Pilihan mood: happy, neutral, sad, stressed, energized |
| Content | Tulis isi jurnal bebas |
| Mood Stats | Statistik mood dari waktu ke waktu |
| Hapus | Hapus entry jurnal |

**Endpoints:** `/api/journals`, `/api/journals/moods`, `/api/journals/{date}`

---

## 12. Goals & Milestones

| Fitur | Deskripsi |
|-------|-----------|
| CRUD | Buat, edit, hapus goal |
| Target Date | Tanggal target penyelesaian |
| Status | Active, Completed, Abandoned |
| Progress | Persentase progress (0-100%) |
| Milestones | Sub-target dalam satu goal |
| Toggle Milestone | Tandai milestone selesai/belum |

**Endpoints:** `/api/goals`, `/api/goals/{id}/milestones`, `/api/milestones/{id}/toggle`

---

## 13. Wishlist

| Fitur | Deskripsi |
|-------|-----------|
| CRUD | Buat, edit, hapus item wishlist |
| Category | Organisasi per kategori |
| Toggle Complete | Tandai sudah terpenuhi |
| Completed At | Timestamp kapan terpenuhi |

**Endpoints:** `/api/wishlists`, `/api/wishlists/{id}/toggle`

---

## 14. Tags (Polymorphic)

| Fitur | Deskripsi |
|-------|-----------|
| CRUD | Buat, edit, hapus tag |
| Color | Warna per tag |
| Attach/Detach | Pasang tag ke item apapun (notes, tasks, bookmarks, dll) |
| Polymorphic | Satu sistem tag untuk semua jenis konten |

**Endpoints:** `/api/tags`, `/api/tags/{id}/attach`, `/api/tags/{id}/detach`

---

## 15. Daily Quotes

| Fitur | Deskripsi |
|-------|-----------|
| Quote of the Day | Satu quote motivasi per hari |
| Seeded | Data quote sudah di-seed ke database |
| Author | Nama pengarang quote |

**Endpoints:** `/api/quotes/today`

---

## 16. Trash (Unified)

| Fitur | Deskripsi |
|-------|-----------|
| List | Lihat semua item yang sudah di-soft-delete |
| Restore | Kembalikan item ke state aktif |
| Force Delete | Hapus permanen |
| Multi-type | Support notes, tasks, dan entity lain yang pakai soft delete |

**Endpoints:** `/api/trash`, `/api/trash/{type}/{id}/restore`, `/api/trash/{type}/{id}` (DELETE)

---

## 17. Activity Log

| Fitur | Deskripsi |
|-------|-----------|
| Auto Log | Catat aktivitas user secara otomatis |
| Action & Description | Jenis aksi + deskripsi |
| Metadata | Data tambahan dalam format JSON |
| Timeline | Riwayat aktivitas dari waktu ke waktu |

**Endpoints:** `/api/activity-logs`

---

## 18. Settings

| Halaman | Fitur |
|---------|-------|
| Account | Edit profil, ubah password, upload avatar |
| Appearance | Theme (dark/light), primary color |
| General | Locale, sidebar preference |
| Export | Export data (TODO) |

---

## 19. Dashboard

Halaman utama yang menampilkan ringkasan dari berbagai modul:
- Overview task yang aktif
- Habit progress hari ini
- Pomodoro stats
- Quote of the day
- Calendar events mendatang
- Finance summary

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
| Activity | `/activity` |
| Pomodoro | `/pomodoro` |
| Scratchpads | `/scratchpads` |
| Habits | `/habits` |
| Finance | `/finance` |
| Reading List | `/reading-list` |
| Journal | `/journal` |
| Goals | `/goals` |
| Streaks | `/streaks` |
| Wishlist | `/wishlist` |
| Trash | `/trash` |
| Settings | `/settings/*` |
