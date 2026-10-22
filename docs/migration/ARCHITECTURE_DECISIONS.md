# ARCHITECTURE DECISIONS

**Created:** 2026-10-22  
**Project:** TKBM CI3 → Laravel 11 Migration

---

## AD-001: Laravel Version — Laravel 11

**Decision:** Gunakan Laravel 11  
**Alternatives rejected:**
- Laravel 12 — terlalu baru, ekosistem package belum mature
- Laravel 10 — masih didukung tapi bukan LTS terbaru

**Rationale:** Laravel 11 adalah versi stable aktif dengan dukungan PHP 8.2-8.4, sesuai dengan runtime server yang sudah ada (PHP 8.4.8).

---

## AD-002: Authentication — Laravel Breeze (Blade-only) + Custom Ion Auth Verifier

**Decision:** Laravel Breeze dengan stack Blade (tanpa Livewire/Inertia) sebagai scaffolding auth, dikombinasi dengan custom password verifier untuk kompatibilitas password bcrypt Ion Auth.

**Rationale:**
- Ion Auth menggunakan bcrypt standard PHP `password_hash()` — kompatibel langsung dengan `Hash::check()` Laravel
- Tidak perlu custom verifier, Laravel `Hash` facade bisa verifikasi hash bcrypt Ion Auth
- Breeze Blade menghasilkan controller auth minimal yang bisa dimodifikasi
- Menghindari Livewire/Inertia sesuai constraint prompt

**Migration Note:**
- `s_users.password` berisi hash bcrypt — bisa langsung digunakan Laravel
- `s_users.created_on` dan `last_login` adalah Unix timestamp integer — perlu custom casting di model User
- Field `email` sebagai identity (bukan username) — sesuai default Laravel Auth

---

## AD-003: Database — Gunakan Database Existing (tkbm_prod)

**Decision:** Terhubung langsung ke `tkbm_prod` yang sudah ada.  
**Strategy:**
- Tidak menjalankan `migrate:fresh` atau `db:wipe`
- Tidak membuat ulang tabel yang sudah ada
- Schema baru (jika ada) ditambah sebagai migration terpisah
- Model Laravel dikonfigurasi dengan nama tabel eksplisit sesuai schema existing

**Rationale:** Sesuai alignment — satu database yang sama, schema dipertahankan.

---

## AD-004: Frontend — Blade dengan Assets Existing

**Decision:** Reuse seluruh assets dari `tkbm/assets/` (Velzon theme, jQuery, Bootstrap 5, DataTables, dll.)

**Rationale:**
- UI policy: preserve existing UI, no redesign
- Assets sudah terintegrasi sempurna dengan theme Velzon
- Menghindari upgrade CSS/JS framework bersamaan dengan migrasi
- Tidak menggunakan Vite bundler untuk assets — serve langsung dari public/

**Implementation:**
- Copy `d:\www\tkbm\assets\` ke `d:\www\tkbm-lav\laravel-app\public\assets\`
- Blade views menggunakan `asset('assets/...')` yang ekuivalen dengan `base_url('assets/...')`

---

## AD-005: Authorization — Custom Gate + DB-driven Permission

**Decision:** Implementasi custom Gate/Policy menggunakan data dari `s_permission` dan `s_group_permissions` di database.

**Rationale:**
- Permission model di legacy adalah slug-based, DB-driven
- Tidak cocok dengan Spatie Permission (terlalu banyak tabel baru)
- Gate dengan closure per-permission lebih fleksibel dan sesuai dengan existing model

**Implementation:**
```php
// Gate::define per permission slug, di-load dari DB
Gate::define('workorder.create', fn(User $user) => $user->hasPermission('workorder.create'));
```

---

## AD-006: API — Tetap JWT (tidak migrasi ke Sanctum)

**Decision:** Tetap gunakan JWT untuk API v1 (mobile app integration)  
**Library:** `firebase/php-jwt` (sama dengan CI3)  
**Table:** `t_api_refresh_tokens` (tetap digunakan)

**Rationale:**
- Mobile app sudah terpasang dan menggunakan JWT
- Mengganti ke Sanctum butuh update mobile app juga
- `t_fcm_tokens` dan `t_api_refresh_tokens` tetap dipertahankan

---

## AD-007: App Settings — Service Class dengan Cache

**Decision:** Buat `AppSettingService` yang membaca `app_settings` dari DB dengan Laravel Cache.

**Migration dari CI3:**
```php
// CI3: get_app_setting('site_name', 'Default')
// Laravel: app(AppSettingService::class)->get('site_name', 'Default')
```

---

## AD-008: CSRF — Gunakan Laravel Built-in CSRF

**Decision:** Ganti custom CSRF CI3 dengan Laravel CSRF middleware bawaan.

**Migration:**
- Form views: gunakan `@csrf` directive
- AJAX: `X-CSRF-TOKEN` header atau `_token` field
- JSON response tidak perlu return CSRF token (Laravel handles automatically)

---

## AD-009: Sidebar — Service + Middleware

**Decision:** Buat `SidebarService` yang dipanggil di middleware dan di-share ke semua views via `View::share()`.

**Migration dari CI3:**
```php
// CI3 MY_Controller:
// $this->data['sidebar'] = $this->m_pengaturan->msidebar();

// Laravel AppServiceProvider atau Middleware:
// View::share('sidebar', app(SidebarService::class)->getForUser(auth()->user()));
```

---

## AD-010: Laravel Project Directory Structure

**Decision:** Install Laravel di `d:\www\tkbm-lav\laravel-app\` (subdirectory)

**Virtual host:** `tkbm-lav.dev` → `D:\www\tkbm-lav\laravel-app\public`
