# Setup Google Drive Integration

Panduan lengkap untuk menghubungkan Purdia Dashboard dengan Google Drive API.

---

## 1. Buat Project di Google Cloud Console

1. Buka [Google Cloud Console](https://console.cloud.google.com/)
2. Klik **Select a project** → **New Project**
3. Isi nama project (misal: `Purdia Dashboard`) → **Create**
4. Pastikan project yang baru dibuat sudah terpilih di dropdown atas

---

## 2. Aktifkan Google Drive API

1. Buka menu **APIs & Services** → **Library**
2. Cari **Google Drive API**
3. Klik → **Enable**

---

## 3. Konfigurasi OAuth Consent Screen

1. Buka **APIs & Services** → **OAuth consent screen**
2. Pilih **External** → **Create**
3. Isi form:
   - **App name**: `Purdia Dashboard`
   - **User support email**: email kamu
   - **Developer contact**: email kamu
4. Klik **Save and Continue**
5. Di halaman **Scopes**, klik **Add or Remove Scopes**, tambahkan:
   - `https://www.googleapis.com/auth/drive.file`
   - `https://www.googleapis.com/auth/userinfo.email`
6. **Save and Continue**
7. Di halaman **Test users**, tambahkan email Google yang akan dipakai
8. **Save and Continue** → **Back to Dashboard**

> **Note:** Selama masih "Testing", hanya email yang ditambahkan di Test Users yang bisa login. Untuk production, submit ke Google untuk review.

---

## 4. Buat OAuth 2.0 Credentials

1. Buka **APIs & Services** → **Credentials**
2. Klik **+ Create Credentials** → **OAuth client ID**
3. Pilih **Application type**: `Web application`
4. **Name**: `Purdia Dashboard Web`
5. **Authorized JavaScript origins**:
   - `http://localhost:8000` (development)
   - `https://yourdomain.com` (production)
6. **Authorized redirect URIs**:
   - `http://localhost:8000/drive/callback` (development)
   - `https://yourdomain.com/drive/callback` (production)
7. Klik **Create**
8. Catat **Client ID** dan **Client Secret**

---

## 5. Konfigurasi Environment Variables

Tambahkan ke file `.env`:

```env
GOOGLE_CLIENT_ID=your-client-id.apps.googleusercontent.com
GOOGLE_CLIENT_SECRET=your-client-secret
GOOGLE_REDIRECT_URI=http://localhost:8000/drive/callback
```

Untuk production, ganti `GOOGLE_REDIRECT_URI` ke URL production kamu.

---

## 6. Install Dependencies

```bash
composer require google/apiclient:^2.16
```

---

## 7. Jalankan Migration

```bash
php artisan migrate
```

Ini akan membuat tabel `drive_connections` untuk menyimpan token OAuth per user.

---

## 8. Setup Laravel Route untuk Callback

Karena OAuth callback membuka halaman frontend (`/drive/callback`), pastikan route catch-all Laravel mengarah ke SPA:

Di `routes/web.php`, pastikan ada:

```php
Route::get('/{any}', function () {
    return view('app');
})->where('any', '.*');
```

---

## 9. Testing

1. Jalankan server: `composer dev` atau `php artisan serve`
2. Login ke dashboard
3. Buka menu **Google Drive** di sidebar
4. Klik **Hubungkan Google Drive**
5. Popup Google OAuth akan muncul — login dengan akun Google yang ada di Test Users
6. Setelah berhasil, kamu bisa:
   - **Browse** file & folder
   - **Upload** file ke Drive
   - **Download** file dari Drive
   - **Buat folder** baru
   - **Hapus** file

---

## 10. Fitur yang Tersedia

### File Manager (`/drive`)
- Browse file & folder (navigasi masuk/keluar folder)
- Upload file (max 10MB per file)
- Download file
- Buat folder baru
- Hapus file/folder
- Buka file di Google Drive (webViewLink)

### Backup (`/settings/export`)
- **Backup Semua Data** → Upload JSON lengkap ke folder `Purdia Backups` di Drive
- **Sync Catatan** → Upload semua notes sebagai file HTML ke folder `Purdia Notes`

### API Endpoints
| Method | Endpoint | Fungsi |
|--------|----------|--------|
| GET | `/api/drive/status` | Cek status koneksi |
| GET | `/api/drive/auth-url` | Dapatkan URL OAuth |
| POST | `/api/drive/callback` | Tukar code → token |
| DELETE | `/api/drive/disconnect` | Putuskan koneksi |
| GET | `/api/drive/files` | List file (query: `folder_id`) |
| POST | `/api/drive/files/upload` | Upload file (multipart) |
| GET | `/api/drive/files/{id}/download` | Download file |
| DELETE | `/api/drive/files/{id}` | Hapus file |
| POST | `/api/drive/folders` | Buat folder |
| POST | `/api/drive/backup` | Backup semua data ke Drive |
| POST | `/api/drive/sync-notes` | Sync catatan ke Drive |

---

## Troubleshooting

### Error: "redirect_uri_mismatch"
- Pastikan `GOOGLE_REDIRECT_URI` di `.env` sama persis dengan yang didaftarkan di Google Cloud Console (termasuk trailing slash).

### Error: "access_denied" / User not in test users
- Selama app masih dalam mode Testing, hanya email yang ada di OAuth Consent Screen → Test Users yang bisa authorize.

### Token expired
- Token otomatis di-refresh menggunakan refresh_token. Jika gagal, user perlu reconnect.

### Quota / Rate limit
- Google Drive API punya quota default 12,000 requests/day. Cukup untuk personal use.

---

## Security Notes

- Access token & refresh token disimpan **terenkripsi** di database (menggunakan Laravel's `encrypted` cast).
- Token hanya bisa diakses oleh user yang memilikinya (ownership check per user_id).
- Scope minimal: `drive.file` — hanya bisa akses file yang dibuat oleh app ini, bukan seluruh Drive.
