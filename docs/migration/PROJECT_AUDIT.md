# PROJECT AUDIT — TKBM CI3 → Laravel Migration

**Audit Date:** 2026-10-22  
**Auditor:** AI Migration Agent  
**Conversation ID:** c0cf6941-e7a8-4a22-a319-c474249d723d

---

## 1. OVERVIEW APLIKASI

| Item | Value |
|------|-------|
| **Nama Aplikasi** | TKBM — Sistem Manajemen Bongkar Muat |
| **Perusahaan** | PT. PBS - Gresik |
| **Framework Legacy** | CodeIgniter 3 |
| **PHP Version (Legacy)** | >= 5.3.7 (runtime aktual: PHP 8.4.8) |
| **PHP Version (Target)** | 8.4.8 |
| **Laravel Version (Target)** | 11 (stable, aktif didukung) |
| **Database** | MySQL 8.0.30 |
| **Database Name** | `tkbm_prod` |
| **Legacy Base URL** | http://tkbm.dev |
| **Target Base URL** | http://tkbm-lav.dev |
| **Theme** | Velzon (Bootstrap 5 admin theme) |

---

## 2. FRAMEWORK DAN STRUKTUR LEGACY

### 2.1 CodeIgniter 3
- Standard CI3 project structure
- Custom `MY_Controller` (extends CI_Controller) — auth check, log activity, JSON response helpers
- Custom `MY_Api_Controller` — untuk API endpoints dengan JWT auth
- Custom `MY_Model` (extends CI_Model)
- **TIDAK menggunakan HMVC/modules**

### 2.2 Composer Dependencies
```json
{
  "phpoffice/phpspreadsheet": "^5.3",  // Export Excel
  "firebase/php-jwt": "^6.0"           // API JWT
}
```

### 2.3 Libraries yang Di-autoload
- `session` — CI3 native session
- `database` — CI3 database
- `form_validation` — server-side validation
- `ion_auth` — authentication library (Ben Edmunds)

### 2.4 Helpers yang Di-autoload
- `url` — CI3 URL helper
- `base` (base_helper.php) — custom utility functions
- `cookie` — CI3 cookie helper
- `security` — CI3 security helper

---

## 3. AUTHENTICATION

### 3.1 Ion Auth
- **Library:** Ion Auth (CodeIgniter-Ion-Auth by Ben Edmunds)
- **Identity field:** `email` (bukan username)
- **Hash method:** `bcrypt` dengan cost 10
- **Session management:** CI3 native session
- **Remember me:** enabled (86500 seconds)
- **Max login attempts:** 3, lockout 600 detik
- **Forgot password expiration:** 18000 detik (5 jam)

### 3.2 Tables Auth
| Table | Deskripsi |
|-------|-----------|
| `s_users` | User accounts (Ion Auth compatible) |
| `s_groups` | Roles/groups |
| `s_users_groups` | Pivot user-role |
| `login_attempts` | Failed login tracker |

### 3.3 s_users Schema
```
id, ip_address, username, password(bcrypt), email(UNIQUE),
activation_selector, activation_code,
forgotten_password_selector, forgotten_password_code, forgotten_password_time,
remember_selector, remember_code,
created_on(int unix), last_login(int unix), active,
avatar, phone,
CREATED_AT, UPDATED_AT, DELETED_AT
```

**Catatan:** `created_on` dan `last_login` adalah **Unix timestamp integer**, bukan datetime!

### 3.4 Authorization
- **Role-based:** via `s_groups` (admin, members, dll)
- **Permission:** `s_permission` (slug-based), linked ke `s_menu`
- **Group Permission:** `s_group_permissions` (pivot group ↔ permission)
- Permission check: `$this->hasPermission($slug)` → in_array di sess_permissions
- Dashboard access: role_id IN (1, 2, 3) → type_auth = 1

---

## 4. CONTROLLERS

### 4.1 Root Controllers
| Controller | File | Fungsi |
|------------|------|--------|
| Auth | Auth.php (30KB) | Login, logout, register, forgot password |
| Dashboard | Dashboard.php (3KB) | Halaman dashboard |
| Workorder | Workorder.php (32KB) | CRUD workorder utama |
| Bulk | Bulk.php (27KB) | Bulk operations workorder |
| Activity | Activity.php (14KB) | Log aktivitas user |
| Ajax | Ajax.php (8KB) | AJAX endpoints umum |
| Profile | Profile.php (8KB) | User profile management |
| Errors | Errors.php (4KB) | Error pages |
| Panduan | Panduan.php | Halaman panduan |
| Cron | Cron.php | Cron trigger (redirect to cron/) |

### 4.2 Master Controllers (application/controllers/master/)
| Controller | Fungsi |
|------------|--------|
| Aktifitas | Master jenis aktifitas bongkar muat |
| Alat | Master alat/peralatan |
| Catatan | Master catatan/keterangan standar |
| Client | Master client/pelanggan |
| Dermaga | Master dermaga |
| Email | Master template email |
| Kapal | Master kapal |
| Krk | Master KRK (Kartu Rencana Kerja) |
| Pelabuhan | Master pelabuhan |
| Satuan | Master satuan ukur |

### 4.3 Pengaturan Controllers (application/controllers/pengaturan/)
| Controller | Fungsi |
|------------|--------|
| Akses | Role-based access control |
| Aplikasi | App settings (site_name, logo, dll) |
| Cron | Cron job management UI |
| Menu | Menu management |
| Pengguna | User management |
| Roles | Role/group management |

### 4.4 Report Controllers
| Controller | Fungsi |
|------------|--------|
| report/Workorder | Laporan workorder |

### 4.5 Accounting Controllers
| Controller | Fungsi |
|------------|--------|
| accounting/Invoice | Manajemen invoice |

### 4.6 Cron Controllers (application/controllers/cron/)
| Controller | Fungsi |
|------------|--------|
| Email_processor | Proses antrian email |
| Log_cleanup | Bersihkan log lama |
| Scheduler | Scheduler runner |

### 4.7 Logs Controllers (application/controllers/logs/)
| Controller | Fungsi |
|------------|--------|
| Aktifitas | Tampilkan activity log |
| Api | Tampilkan API log |
| Aplikasi | App config log |
| Dashboard | Log dashboard |
| Email | Email log |
| Errors | Error log |

### 4.8 API v1 Controllers (application/controllers/api/v1/)
| Controller | Fungsi |
|------------|--------|
| Auth | API auth (login, refresh, logout, FCM token) |
| Dashboard | API dashboard stats |
| Master | API master data (CRUD semua master) |
| Notifications | API notifikasi |
| Sync | API sync (pull/push) |
| Workorders | API workorder (CRUD + photo upload) |

---

## 5. MODELS

| Model | Fungsi |
|-------|--------|
| Ion_auth_model | Ion Auth model (user/group management) |
| M_app_settings | App settings CRUD |
| M_cron_execution | Cron execution log |
| M_cron_job | Cron job management |
| M_crud | Generic CRUD model (shared) |
| M_email_log | Email log |
| M_email_queue | Email queue |
| M_email_template | Template email |
| M_invoice | Invoice & invoice items |
| M_log_api | API request log |
| M_log_error | Error log |
| M_log_performance | Performance log |
| M_log_security | Security log |
| M_master | Master data CRUD (pelabuhan, kapal, dermaga, dll) |
| M_notification | Notifikasi |
| M_pengaturan | Settings, menu, permission, sidebar |
| M_user_activity | User activity log |
| M_workorder | Workorder CRUD + business logic |

---

## 6. DATABASE TABLES

### 6.1 Master Tables (prefix: m_)
| Table | Deskripsi |
|-------|-----------|
| `m_aktifitas` | Jenis aktifitas bongkar muat |
| `m_alat` | Daftar alat/peralatan |
| `m_catatan` | Template catatan standar |
| `m_client` | Daftar client/pelanggan |
| `m_dermaga` | Daftar dermaga |
| `m_kapal` | Daftar kapal |
| `m_krk` | Kartu Rencana Kerja |
| `m_pelabuhan` | Daftar pelabuhan |
| `m_satuan` | Satuan ukur |

### 6.2 System Tables (prefix: s_)
| Table | Deskripsi |
|-------|-----------|
| `s_users` | Ion Auth users |
| `s_groups` | Ion Auth groups/roles |
| `s_users_groups` | Pivot user-role |
| `s_menu` | Dynamic menu (ID_MENU, HEADER_ID, PARENT_ID, NAMA, SLUG, ICON, LINK) |
| `s_permission` | Permissions linked ke menu |
| `s_group_permissions` | Pivot group-permission |
| `s_group_menu` | (TBD) Group-menu visibility |
| `s_appconfig` | App configuration |
| `s_aturan` | Rules/aturan sistem |
| `s_cron_jobs` | Cron job definitions |
| `s_cron_execution_log` | Cron execution history |
| `s_cron_alerts` | Cron alerts |
| `s_email_queue` | Email queue |
| `s_email_templates` | Email templates |
| `s_log_api_request` | API request logs |
| `s_log_email` | Email logs |
| `s_log_error` | Error logs |
| `s_log_performance` | Performance logs |
| `s_log_security` | Security logs |
| `s_user_activity_logs` | User activity logs |

### 6.3 Transaction Tables (prefix: t_)
| Table | Deskripsi |
|-------|-----------|
| `t_work_order` | Work order utama |
| `t_work_order_detail` | Detail work order (KRK, hasil, foto) |
| `t_work_order_alat` | Alat yang digunakan di WO |
| `t_invoice` | Invoice |
| `t_invoice_item` | Invoice line items |
| `t_notifications` | Push notifications |
| `t_api_refresh_tokens` | JWT refresh tokens |
| `t_fcm_tokens` | Firebase Cloud Messaging tokens |

### 6.4 Config Tables
| Table | Deskripsi |
|-------|-----------|
| `app_settings` | App settings (key-value with group, type, options) |
| `app_settings_groups` | Settings group definitions |

---

## 7. THEME DAN ASSETS

### 7.1 Theme
- **Velzon** — Bootstrap 5 admin dashboard theme
- Dynamic theme settings dari `app_settings` group='theme'
- Data attributes: `data-layout`, `data-topbar`, `data-sidebar`, `data-sidebar-size`, dll.

### 7.2 CSS Libraries
- Bootstrap 5 (`assets/css/bootstrap.min.css`)
- Font Awesome (`assets/libs/fontawesome/css/all.min.css`)
- DataTables 1.12.1 + Bootstrap5 extension
- DataTables Select, Responsive, RowGroup, Buttons
- Select2
- Bootstrap Datepicker
- Daterangepicker
- FilePond + plugins (image preview, validate, encode, exif)
- Velzon App CSS (`assets/css/app.min.css`)
- Custom CSS (`assets/css/custom.min.css`)

### 7.3 JavaScript Libraries
- jQuery 3.6.0
- Bootstrap 5 Bundle
- Font Awesome JS
- jQuery Validation + localization ID
- DataTables + extensions (sama dengan CSS)
- Node Waves
- Select2
- Moment.js
- Bootstrap Datepicker
- Daterangepicker
- Cleave.js
- CKEditor 5 (classic build)
- FilePond + plugins
- FusionCharts
- SimplerBar
- Velzon App JS (`assets/js/app.js`)
- Custom plugins (`assets/js/plugins.js`)
- Custom JS (`assets/js/custom.js`)

---

## 8. CUSTOM CSRF IMPLEMENTATION

CI3 menggunakan **custom CSRF** (bukan bawaan CI3):
- Generate: `csrf_token_generate()` — SHA256 hash dari time(), disimpan di session
- Verify: `cek_csrf_token()` — compare POST[$csrf_name] vs session
- Setiap response JSON refresh token: `sendJsonSuccess()` / `sendJsonError()`
- Token name dari `app_settings` key='csrf_name'

---

## 9. KEY BUSINESS LOGIC

### 9.1 Workorder Flow
```
Status: hide → open → progress → review → done
```
- Workorder dibuat → status: open
- Mobile app/operator update → progress
- Kirim review → review
- Pengawas approve → done
- Soft delete support (DELETED_AT)

### 9.2 Invoice Flow
```
Status: draft → sent → paid/overdue/cancelled
```
- Invoice linked ke work order
- Tax: PAJAK_PERSEN (default 11%), PAJAK_NOMINAL kalkulasi
- SUBTOTAL, TOTAL dihitung dari invoice items

### 9.3 Sidebar Dynamic
- Sidebar diload dari DB via `m_pengaturan->msidebar()`
- Filter berdasarkan permission user
- Struktur hierarkis: header → parent → child menu

---

## 10. API (Mobile App Integration)

### 10.1 Auth Flow
- Login: POST `/api/v1/auth/login` → JWT access + refresh token
- Token stored di `t_api_refresh_tokens`
- FCM token stored di `t_fcm_tokens`

### 10.2 API Contract
- Response: `{"status": bool, "msg": string, "data": ...}`
- Auth header: `Authorization: Bearer {token}`
- JWT library: `firebase/php-jwt`

---

## 11. HELPER FUNCTIONS (base_helper.php)

| Function | Deskripsi |
|----------|-----------|
| `mnow()` | Current datetime Y-m-d H:i:s |
| `sendJSON($data)` | Output JSON response |
| `sendJSONDT(...)` | DataTables JSON response |
| `isAvatar($val)` | Resolve avatar URL |
| `format_indo($date)` | Format tanggal Indonesia |
| `format_periode_bulan($date)` | Format bulan-tahun Indonesia |
| `csrf_token_generate()` | Generate/get CSRF token |
| `cek_csrf_token()` | Verify CSRF token |
| `encrypt_decrypt($action, $string)` | AES-256-CBC encrypt/decrypt |
| `ribuan($angka)` | Number format ribuan |
| `rupiah($angka)` | Format Rupiah |
| `terbilang($x)` | Angka ke teks Indonesia |
| `convert_nilai_for_db($value)` | Cleave.js value → int |
| `get_app_setting($key, $default)` | Get app setting (session cached) |
| `get_app_settings_by_group($group)` | Get settings by group |

---

## 12. TEMUAN RISIKO AWAL

| Risk | Level | Keterangan |
|------|-------|-----------|
| `created_on` & `last_login` di `s_users` adalah Unix int | HIGH | Laravel Eloquent expects datetime; perlu custom casting |
| Password hash bcrypt Ion Auth harus kompatibel dengan Laravel | MEDIUM | bcrypt PHP standard, Laravel `Hash::check()` kompatibel |
| CSRF custom implementation (bukan CSRF CI3 standard) | LOW | Migrasi ke Laravel CSRF bawaan |
| Sidebar dinamis dari DB dengan permission filter | MEDIUM | Perlu service/middleware untuk load sidebar |
| API JWT dengan `firebase/php-jwt` | LOW | Laravel Sanctum/Passport atau tetap JWT |
| CKEditor 5 integration | LOW | Hanya perlu script src |
| `encrypt_decrypt()` dengan hardcoded key | HIGH | Secret key di kode — harus dipindah ke .env |
