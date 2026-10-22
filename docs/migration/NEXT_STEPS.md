# NEXT STEPS & TESTING GUIDE

**Updated:** 2026-10-22  
**Current Milestone:** Full Migration Completed (M01 - M39)  

---

## 🚀 How to Run and Test the Application

### 1. Jalankan Development Server
Buka terminal di folder `d:\www\tkbm-lav\laravel-app` dan jalankan:
```bash
php artisan serve
```
Aplikasi web dapat diakses melalui browser di: `http://127.0.0.1:8000` (atau `http://localhost:8000`).

### 2. Akun Login untuk Pengujian
Gunakan kredensial yang sudah terdaftar di database `tkbm_prod`:
- **Username:** `Administrator`
- **Password:** `password` (atau password default Ion Auth Anda)

### 3. Menguji Modul Web (Browser Testing)
- **Dashboard:** `http://127.0.0.1:8000/dashboard`
- **Workorder:** `http://127.0.0.1:8000/workorder`
- **Laporan / Rekap Kegiatan Kapal:** `http://127.0.0.1:8000/report/workorder`
- **Invoice:** `http://127.0.0.1:8000/accounting/invoice`
- **Master Data:** `http://127.0.0.1:8000/master/pelabuhan`, `.../master/kapal`, dll.
- **Pengaturan:** `http://127.0.0.1:8000/pengaturan/pengguna`, `.../pengaturan/roles`, dll.
- **Logs:** `http://127.0.0.1:8000/logs/aktifitas`, `.../logs/api`, dll.
- **Profil:** `http://127.0.0.1:8000/profile`

### 4. Menguji Mobile API v1 (Postman / Curl)
Prefix: `http://127.0.0.1:8000/api/v1/`
- **Login API:**
  `POST http://127.0.0.1:8000/api/v1/auth/login`
  Body: `{"identity": "Administrator", "password": "your_password"}`
- **Refresh Token:**
  `POST http://127.0.0.1:8000/api/v1/auth/refresh`
  Body: `{"refresh_token": "..."}`
- **Workorders API:**
  `GET http://127.0.0.1:8000/api/v1/workorders`
  Header: `Authorization: Bearer <access_token>`
- **Sync Pull:**
  `GET http://127.0.0.1:8000/api/v1/sync/pull`
  Header: `Authorization: Bearer <access_token>`

### 5. Menguji Cron Commands
Jalankan di command line:
```bash
# Uji pemrosesan antrian email
php artisan cron:email-process --limit=10

# Uji pembersihan log (dry run tanpa menghapus data)
php artisan cron:log-cleanup --dry-run

# Uji master scheduler
php artisan cron:run-scheduled
```

### 6. Menjalankan Uji Otomatis (Full Automated Tests)
```bash
php artisan test
```
Semua 58 test suites saat ini berstatus **PASSING (306 assertions)**.
