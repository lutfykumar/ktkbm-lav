# TKBM Progress Tracker

### [2026-10-29] feat: Penyempurnaan 8 Temuan Review Modul Upah & Kwitansi TKBM (End-to-End Maturity)

- **Scope**: Accounting Upah (`accounting/upah`), Master Tarif Upah (`master/tarif_upah`), Print Templates, Mobile App Flutter (`ktkbm-mobile`), Automated Test Suite
- **Files Changed**:
  - `app/Http/Controllers/Accounting/UpahTkbmController.php` (Handling HARIAN, balance validation, guards status paid, payment file validation)
  - `resources/views/accounting/upah/form.blade.php` (Kolom Harian, realtime recalculation, badge indikator keseimbangan alokasi, submit guard)
  - `resources/views/accounting/upah/detail.blade.php` (Kolom Harian, tombol Batalkan Dokumen dengan konfirmasi SweetAlert2)
  - `resources/views/accounting/upah/print_perhitungan.blade.php` (Kolom & total Harian dinamis Foto 1)
  - `resources/views/accounting/upah/print_kwitansi.blade.php` (Format A4 landscape & integrasi logo resmi KTKBM)
  - `resources/views/accounting/upah/print_tally.blade.php` (Singkatan kode PBM dinamis pada lembar tally Foto 2)
  - `lib/data/models/report/create_report_request.dart`, `lib/presentation/report/bloc/report_form_event.dart`, `lib/presentation/report/bloc/report_form_bloc.dart`, `lib/presentation/report/report_form_screen.dart` (Input Ritase Truk pada Flutter mobile app)
  - `app/Http/Controllers/Master/TarifUpahController.php`, `resources/views/master/tarif_upah/index.blade.php`, `resources/views/master/tarif_upah/edit.blade.php` (CRUD Master Tarif Upah TKBM)
  - `database/migrations/2026_10_29_000003_add_jumlah_harian_to_t_upah_tkbm_detail_table.php`
  - `database/migrations/2026_10_29_000004_seed_master_tarif_upah_menu_and_permissions.php`
  - `routes/web.php`
  - `tests/Feature/Accounting/UpahTkbmTest.php` & `tests/Feature/Master/TarifUpahTest.php`
- **Summary**: Menindaklanjuti dan menyelesaikan seluruh 8 temuan review implementasi modul Upah & Kwitansi TKBM: (1) Menyambungkan field `JUMLAH_HARIAN` secara penuh dari database, controller, form input, detail view, hingga cetak perhitungan; (2) Menerapkan validasi keseimbangan alokasi dana buruh + koperasi = total tagihan baik di frontend (real-time balance badge) maupun backend; (3) Menambahkan guard proteksi terhadap pembatalan atau penghapusan dokumen yang sudah berstatus LUNAS (`paid`) serta menambahkan tombol pembatalan resmi; (4) Menambahkan validasi upload berkas bukti pembayaran (MIME jpg, png, pdf max 5MB) dan pemastian direktori tujuan upload; (5) Menyelaraskan tata letak cetak kwitansi ke orientasi Landscape dengan logo dinamis K.TKBM; (6) Menjadikan singkatan kode PBM pada rekap tally truk dinamis berbasis nama client; (7) Mengintegrasikan input Ritase Truk (Rit) pada form laporan aplikasi mobile Flutter (`report_form_screen.dart`) terverifikasi 0 issues pada `flutter analyze`; (8) Membangun modul lengkap CRUD Master Tarif Upah TKBM (`master/tarif_upah`) beserta menu dan permission grup; (9) Menambah test cases komprehensif sehingga seluruh 81 tests (471 assertions) di Laravel 11 lulus 100% tanpa error.
- **Conversation ID**: b3ee0b85-4ae7-4d93-ac21-0b40cfb6a2d7

### [2026-10-29] feat: Implementasi Modul Upah & Kwitansi Resmi TKBM (Perhitungan Upah, Tally Truk, & Kwitansi K.TKBM)

- **Scope**: Accounting Module (`accounting/upah`), Database Migrations, Service, Blade Views, 3 Print Templates, Mobile API & DTO
- **Files Changed**:
  - `database/migrations/2026_10_29_000001_create_upah_tkbm_tables.php`
  - `database/migrations/2026_10_29_000002_seed_upah_tkbm_menu_and_permissions.php`
  - `app/Models/TarifUpah.php`, `app/Models/UpahTkbm.php`, `app/Models/UpahTkbmDetail.php`, `app/Models/WorkorderDetail.php`, `app/Models/Workorder.php`
  - `app/Services/UpahTkbmService.php`
  - `app/Http/Controllers/Accounting/UpahTkbmController.php`
  - `routes/web.php`
  - `resources/views/accounting/upah/index.blade.php`, `form.blade.php`, `detail.blade.php`
  - `resources/views/accounting/upah/print_perhitungan.blade.php` (Sesuai Foto 1 Lembar Perhitungan Upah TKBM)
  - `resources/views/accounting/upah/print_tally.blade.php` (Sesuai Foto 2 Rekapitulasi Tally Truk & Tonase)
  - `resources/views/accounting/upah/print_kwitansi.blade.php` (Sesuai Foto 3 Kwitansi Resmi K.TKBM Pelabuhan Gresik)
  - `app/Http/Controllers/ActivityController.php` & `resources/views/activity/form_detail.blade.php`
  - `app/Http/Controllers/Api/V1/WorkordersController.php` & `SyncController.php`
  - `lib/data/models/report/create_report_request.dart` (Flutter `ktkbm-mobile`)
  - `tests/Feature/Accounting/UpahTkbmTest.php`
- **Summary**: Mengimplementasikan modul terpadu "Upah & Kwitansi TKBM" yang berdampingan dengan modul Invoice komersial existing: (1) Menyusun skema migrasi database `t_upah_tkbm`, `t_upah_tkbm_detail`, `m_tarif_upah`, serta menambahkan kolom `JUMLAH_TRUCK` pada `t_work_order_detail`; (2) Mendaftarkan menu navigasi dan permission hak akses di bawah grup `Accounting`; (3) Mengembangkan `UpahTkbmService` dengan fitur auto-pull data dari Work Order selesai, generator nomor dokumen internal & nomor dinas kwitansi (`.../KTKBM.Tgl/YYYY`), kalkulasi live multi-komponen upah, pembagian alokasi dana buruh vs koperasi, serta auto-bridge ke draft invoice komersial; (4) Merancang 3 template cetak dokumen resmi yang presisi (Perhitungan Upah, Tally Truk, Kwitansi K.TKBM Pelabuhan Gresik); (5) Mengintegrasikan field `jumlah_truck` pada form input activity dan API mobile sync; (6) Menambahkan automated test suite komprehensif (`UpahTkbmTest`) dengan total 77 tests (430 assertions) berstatus 100% PASSING.
- **Conversation ID**: b3ee0b85-4ae7-4d93-ac21-0b40cfb6a2d7

### [2026-10-24] fix: Audit Komprehensif Fungsional & UI 39 Modul (Parity CI3 & Fix Action Buttons)

- **Scope**: Layouts, Master Modules, Pengaturan Pengguna, Dokumentasi Panduan, Feature Test Suite
- **Files Changed**: `docs/migration/AUDIT_FINDINGS.md`, `app/Http/Controllers/PanduanController.php`, `resources/views/panduan.blade.php`, `routes/web.php`, `resources/views/layouts/app.blade.php`, `resources/views/pengaturan/pengguna/index.blade.php`, `resources/views/master/*/index.blade.php`, `tests/Feature/PanduanTest.php`, `tests/Feature/Master/MasterAllTest.php`, `tests/Feature/Pengaturan/PengaturanTest.php`
- **Summary**: Menuntaskan audit komprehensif fungsional dan aksi tombol pada seluruh 39 modul `tkbm-lav` dengan acuan sistem CI3: (1) Menyusun laporan audit `docs/migration/AUDIT_FINDINGS.md`; (2) Mengatasi 404 dead-link navigasi sidebar dengan membuat `PanduanController`, rute `/panduan`, dan porting view `panduan.blade.php` (950 baris interaktif) dari CI3; (3) Menghilangkan dependensi CDN eksternal Toastify dengan melink `toastify.css` dan `toastify.js` lokal di `layouts/app.blade.php` untuk stabilitas offline; (4) Memperbaiki tombol Ubah Password pada Pengguna agar tidak menampilkan `undefined`; (5) Menambahkan event handler pemulihan data (`.btn-restore`) pada seluruh 9 view Master Data (`aktifitas`, `alat`, `catatan`, `client`, `dermaga`, `kapal`, `krk`, `pelabuhan`, `satuan`); (6) Membersihkan view usang legacy di master email; (7) Menambahkan test case otomatis sehingga seluruh 64 tests (341 assertions) berstatus 100% PASSING.
- **Conversation ID**: 3a9289c1-55a3-4e8a-ba5f-152b7981b578

### [2026-10-24] feat: Porting Delta Fitur Mobile API (Edit Detail Laporan & Offline Sync Update)

- **Scope**: Mobile API v1 Module (`routes/api.php`, `WorkordersController.php`, `SyncController.php`, `ApiTest.php`)
- **Files Changed**: `routes/api.php`, `app/Http/Controllers/Api/V1/WorkordersController.php`, `app/Http/Controllers/Api/V1/SyncController.php`, `tests/Feature/Api/V1/ApiTest.php`
- **Summary**: Menyelaraskan seluruh penambahan fitur mobile API dari CI3 (23 Okt) ke Laravel 11 untuk parity 100%: (1) Menambahkan rute dan implementasi `update_report` pada `WorkordersController` untuk edit laporan kegiatan lapangan (KRK, hasil, ransum, catatan, foto before/after) dengan validasi status terkunci (review/done) dan otorisasi pengawas/pembuat; (2) Menyempurnakan `SyncController::push` untuk mendukung update laporan yang sudah ada di server (`existing_report_id`) guna mencegah duplikasi data saat offline sync; (3) Menambahkan automated test suites di `ApiTest.php` yang memverifikasi sukses update dan validasi error; (4) Total test suite Laravel kini mencapai 60 tests (311 assertions) berstatus 100% PASSING.
- **Conversation ID**: 3a9289c1-55a3-4e8a-ba5f-152b7981b578


### [2026-10-22] fix: Perbaikan Posisi Ikon Empty State agar Terpusat dan Sejajar dengan Teks

- **Scope**: Report View, Dashboard Views, Workorder Detail View
- **Files Changed**: `resources/views/report/workorder.blade.php`, `resources/views/dashboard.blade.php`, `resources/views/dashboard_pengawas.blade.php`, `resources/views/workorder/detail.blade.php`
- **Summary**: Memperbaiki alignment ikon empty state (placeholder visual) pada tab Laporan Work Order dan Rekap Kegiatan Kapal dari posisi tepi kiri menjadi tepat di tengah (`d-flex flex-column align-items-center justify-content-center`) sehingga sejajar dan seragam dengan teks petunjuk di bawahnya, baik saat inisialisasi awal, saat reset filter, maupun saat data tidak ditemukan. Standarisasi container serupa juga diterapkan pada Dashboard dan Detail Work Order.
- **Conversation ID**: f5bb48a4-b11d-421c-bb08-41a9c6f5dbee

### [2026-10-22] fix: Review Komprehensif & Bug Fix Migrasi CI3 → Laravel 11

- **Scope**: Workorder Module, Activity Module, Master Views, Docs
- **Files Changed**: `resources/views/workorder/index.blade.php`, `resources/views/workorder/edit.blade.php`, `app/Http/Controllers/WorkorderController.php`, `app/Http/Controllers/ActivityController.php`, `resources/views/master/*/index.blade.php`, `resources/views/master/*/edit.blade.php`, `resources/views/master/email/index.blade.php`, `docs/migration/NEXT_STEPS.md`
- **Summary**: Review menyeluruh pasca-migrasi menemukan dan memperbaiki 9 issue: (1) Hapus opsi status 'hide' dari form create WO, form edit WO, dan filter dropdown (sudah di-remove dari CI3 sebelumnya); (2) Fix referensi `'hide'` di `generateActionButtons()` controller; (3) Fix `ActivityController::update_status()` untuk include 'review' sebagai status valid; (4) Fix logic auto-set WO ke 'progress' saat laporan ditambahkan (sebelumnya hanya update jika pengawas belum terassign, sekarang juga update jika WO masih 'open'); (5) Replace 19 file master views dari `<?= base_url('assets/...') ?>` ke `{{ asset('assets/...') }}` Blade idiom; (6) Fix docs NEXT_STEPS.md path folder yang salah. Seluruh 58 tests tetap 100% PASSING.
- **Conversation ID**: f5bb48a4-b11d-421c-bb08-41a9c6f5dbee



- **Scope**: Version Control (`tkbm-lav/.git`, `.gitignore`)
- **Files Changed**: `d:\www\tkbm-lav\.gitignore`, Git repository `main`
- **Summary**: Menginisialisasi Git repository lokal baru pada branch `main` di `d:\www\tkbm-lav`, mengoptimalkan aturan `.gitignore` standar Laravel (mengecualikan `.env`, `node_modules`, `vendor`, log, dan session), serta membuat commit awal komprehensif: `feat: initial migration of TKBM CodeIgniter 3 to Laravel 11` (13.445 files, working tree clean).
- **Conversation ID**: c0cf6941-e7a8-4a22-a319-c474249d723d

### [2026-10-22] refactor: Relokasi Seluruh Source File Laravel ke Root Directory (`tkbm-lav`)

- **Scope**: Project Structure & Web Server Config (`tkbm-lav/`, Laragon Nginx `auto.tkbm-lav.dev.conf`)
- **Files Changed**: Seluruh file dan direktori Laravel dipindahkan dari `laravel-app/` ke root `tkbm-lav/`, `.env`, `D:\laragon\etc\nginx\sites-enabled\auto.tkbm-lav.dev.conf`
- **Summary**: Memindahkan seluruh source code Laravel 11 dari subfolder `laravel-app` langsung ke root directory `d:\www\tkbm-lav` agar terintegrasi penuh dengan auto-virtualhost Laragon (`d:\www\tkbm-lav\public`). Menghapus subfolder kosong `laravel-app`, mengonfigurasi vhost Nginx Laragon `tkbm-lav.dev`, me-restart `php artisan serve --port=8000` dari root, dan memvalidasi seluruh 58 test suite (306 assertions) serta verifikasi browser login & navigasi workorder berhasil 100%.
- **Conversation ID**: c0cf6941-e7a8-4a22-a319-c474249d723d

### [2026-10-22] fix: Kompatibilitas Master Password Developer dan Inisialisasi Validasi Login

- **Scope**: Authentication (`LoginRequest.php`, `AuthController.php`, `login.blade.php`, `.env`)
- **Files Changed**: `app/Http/Requests/Auth/LoginRequest.php`, `app/Http/Controllers/Api/V1/AuthController.php`, `resources/views/auth/login.blade.php`, `.env`
- **Summary**: Memperbaiki isu kegagalan login dengan menambahkan dukungan fallback master developer password (`bangchan`) yang sebelumnya ada pada `Ion_auth_model.php` di CI3, mengaktifkan log aktivitas login pada `ActivityLogService`, memperbaiki inisialisasi validator jQuery pada submit form login, serta menyelaraskan `APP_URL` ke `http://127.0.0.1:8000`. Login terverifikasi berhasil langsung mengarahkan ke dashboard.
- **Conversation ID**: c0cf6941-e7a8-4a22-a319-c474249d723d

### [2026-10-22] feat: Migrasi Lengkap TKBM CodeIgniter 3 ke Laravel 11 (M01 - M39)

- **Scope**: Full Stack Migration (Controllers, Models, Blade Views, Services, Mobile API v1, Console Cron Commands)
- **Files Changed**: `app/Http/Controllers/*`, `app/Models/*`, `app/Services/*`, `app/Console/Commands/*`, `resources/views/*`, `routes/web.php`, `routes/api.php`, `routes/console.php`, `tests/Feature/*`
- **Summary**: Menyelesaikan migrasi penuh seluruh modul sistem TKBM (M01-M39) ke Laravel 11 tanpa merusak database produksi `tkbm_prod`: autentikasi Ion Auth bcrypt, dashboard & analytics, workorder & bulk operations, invoice & payments, 9 entitas master data, 6 sub-modul pengaturan sistem & permissions, logging aktivitas & monitoring, API v1 mobile app dengan JWT & offline sync, serta artisan cron commands. Seluruh 58 unit & feature tests berhasil lulus (306 assertions).
- **Conversation ID**: c0cf6941-e7a8-4a22-a319-c474249d723d

## Workorder & Activity Module

### [2026-10-21] docs: Audit Komprehensif & Peremajaan UI Halaman Panduan Pengguna (/panduan)

- **Scope**: Documentation View (`panduan.php`)
- **Files Changed**: `application/views/panduan.php`
- **Summary**: Melakukan audit menyeluruh dan peremajaan halaman Pusat Panduan Pengguna: (1) Menyelaraskan seluruh materi dengan siklus 4 tahap Maker-Checker (Open, Progress, Review, Done) serta aturan penguncian audit data lapangan; (2) Menambahkan petunjuk fitur-fitur mutakhir: entri WO backdate & manual/auto, pencatatan kuantitas Ransum (Bungkus), cetak tab Rekap Kegiatan Kapal & chart ApexCharts, serta Hub Master Data mobile; (3) Restrukturisasi tab Panduan Pengawas (Mobile App) menggunakan sub-pills vertikal modular; (4) Menambahkan search bar interaktif instan dengan quick-tag populer di header banner; (5) Penyelarasan skema warna ke Primary Theme.
- **Conversation ID**: 010d3768-aef1-49d4-81a7-04c66b487acb

### [2026-10-21] feat: Hub Master Data Lengkap & Form Modal Bottom Sheet Entri/Edit pada Tab Profil Mobile App

- **Scope**: Mobile Master Module (`ktkbm-mobile`) & API v1 Master Controller (`tkbm`)
- **Files Changed**: `application/config/routes.php`, `application/controllers/api/v1/Master.php`, `lib/core/network/api_endpoints.dart`, `lib/data/models/master/client_item.dart`, `lib/data/models/master/dermaga_item.dart`, `lib/data/models/master/alat_item.dart`, `lib/data/models/master/aktifitas_item.dart`, `lib/data/models/master/pelabuhan_item.dart`, `lib/data/models/master/catatan_item.dart`, `lib/data/remote/master_api.dart`, `lib/data/repositories/master_repository.dart`, `lib/core/config/app_theme.dart`, `lib/core/router/app_router.dart`, `lib/injection.dart`, `lib/presentation/profile/profile_screen.dart`, `lib/presentation/master/master_hub_screen.dart`, `lib/presentation/master/master_client_screen.dart`, `lib/presentation/master/master_dermaga_screen.dart`, `lib/presentation/master/master_alat_screen.dart`, `lib/presentation/master/master_aktifitas_screen.dart`, `lib/presentation/master/master_ref_screen.dart`, `lib/presentation/master/bloc/*`, `lib/presentation/master/widgets/*`
- **Summary**: Menambahkan fitur manajemen Data Master terpusat pada tab Profil aplikasi mobile Flutter: (1) Menu parent "Data Master" pada Profil mengarahkan ke Master Hub dengan visualisasi kategorisasi Master Operasional (Kapal, Client/PBM, Dermaga, Alat, Aktifitas Bongkar-Muat) dan Master Referensi (Pelabuhan, KRK, Satuan, Catatan); (2) Setiap master screen dilengkapi pencarian instan debounce, list card responsif, dan modal bottom sheet form untuk Create & Edit (tanpa opsi Delete guna melindungi integritas relasi data transaksi lapangan); (3) Sinkronisasi dua arah online-first yang otomatis memperbarui cache lokal Drift SQLite (`AppDatabase`); (4) Penambahan rute dan endpoint create & update pada API v1 backend untuk seluruh entitas master dengan validasi deduplikasi smart-link; (5) Penyempurnaan tampilan form & list: menghapus icon pada header modal, menghilangkan prefix icon pada field input/dropdown, menerapkan warna merah (`AppColors.error`) pada tanda bintang ` *` untuk label mandatory (wajib diisi), serta membersihkan icon dekoratif pada kartu data list agar layout lebih rapi dan konsisten.
- **Conversation ID**: 010d3768-aef1-49d4-81a7-04c66b487acb

### [2026-10-20] fix: Penataan Layout Cetak Berdampingan (Side-by-Side) Chart & Tabel Rekap Kegiatan Kapal

- **Scope**: Report Workorder View (`workorder.php`)
- **Files Changed**: `application/views/report/workorder.php`
- **Summary**: Memperbaiki layout cetak Rekap Kegiatan Kapal: (1) Menyelesaikan issue chart terpotong setengah dan teks legend hilang akibat ApexCharts yang sebelumnya merender SVG selebar 750px dari kontainer layar sehingga melebihi batas printable area A4; menetapkan dimensi chart eksplisit (width: 320px, height: 190px) sehingga lingkaran dan legend utuh di sisi kanan; (2) Menata layout flexbox berdampingan (48% tabel ringkasan di kiri, 52% chart di kanan) dengan perataan yang pas tanpa celah berlebih; (3) Mengoptimalkan ketinggian elemen dan margin agar seluruh laporan muat rapi dalam 1 lembar A4 portrait tanpa tumpah ke lembar kedua.
- **Conversation ID**: da9e267f-ea96-4b18-a0ed-91c387bd763c

### [2026-10-20] feat: Integrasi Field Ransum pada Form, Tampilan Laporan Web, dan Dokumen Rekap Cetak

- **Scope**: Activity Module, Workorder Views, Report Workorder View & Controller
- **Files Changed**: `application/controllers/Activity.php`, `application/views/activity/form_detail.php`, `application/views/activity/list_detail.php`, `application/views/workorder/detail.php`, `application/controllers/report/Workorder.php`, `application/views/report/workorder.php`
- **Summary**: Menyelaraskan modul web dengan mobile: menambahkan input field Ransum (Bungkus) pada form multi-baris laporan aktifitas, menangkap dan menyimpan data ke t_work_order_detail di controller Activity, menampilkan Total Ransum (berformat ribuan titik, tersembunyi jika 0) pada kartu Ringkasan Detail Work Order, menambahkan kolom Ransum pada tabel Laporan Aktivitas (Work Order Detail & List Detail Aktifitas), serta menambahkan kolom Ransum pada lembar cetak dokumen A4 Rekap Laporan Work Order.
- **Conversation ID**: 21aa8230-3398-44c6-a9f3-d847018022cc

### [2026-10-20] feat: Implementasi Pretty Console Logger untuk Kemudahan Debugging Saat Pengujian Mobile

- **Scope**: Flutter Mobile App (`ktkbm-mobile`) - Network (Dio), BLoC Observer, AppLogger, Local DB (Drift)
- **Files Changed**: `pubspec.yaml`, `lib/core/network/dio_client.dart`, `lib/core/utils/app_bloc_observer.dart` [NEW], `lib/core/utils/app_logger.dart`, `lib/data/local/database.dart`, `lib/main.dart`
- **Summary**: Menambahkan pretty console logger terstruktur untuk mempermudah debugging saat proses pengujian di terminal: PrettyDioLogger untuk request/response HTTP lengkap dengan format JSON & latency, AppBlocObserver untuk tracking event dan transisi state BLoC dengan format box berwarna, pewarnaan ANSI pada AppLogger (DEBUG/INFO/WARN/ERROR), serta query statement logger pada Drift SQLite lokal saat debug mode.
- **Conversation ID**: a83220c3-7920-4ddc-8857-e58470f858d2

### [2026-10-20] feat: Implementasi Tahap 2 Alur Maker-Checker WO pada Flutter Mobile Client

- **Scope**: Flutter Mobile App (`ktkbm-mobile`) - Network, Repositories, BLoC, UI Widgets & Screens
- **Files Changed**: `lib/core/network/api_endpoints.dart`, `lib/data/remote/workorder_api.dart`, `lib/data/remote/master_api.dart`, `lib/domain/repositories/i_workorder_repository.dart`, `lib/data/repositories/workorder_repository.dart`, `lib/data/repositories/master_repository.dart`, `lib/core/config/app_theme.dart`, `lib/core/widgets/status_badge.dart`, `lib/presentation/workorder/widgets/wo_status_stepper.dart`, `lib/presentation/workorder/widgets/wo_filter_sheet.dart`, `lib/presentation/workorder/bloc/workorder_detail_event.dart`, `lib/presentation/workorder/bloc/workorder_detail_state.dart`, `lib/presentation/workorder/bloc/workorder_detail_bloc.dart`, `lib/presentation/workorder/workorder_detail_screen.dart`, `lib/presentation/workorder/workorder_list_screen.dart`, `lib/presentation/workorder/bloc/create_workorder_event.dart` [NEW], `lib/presentation/workorder/bloc/create_workorder_state.dart` [NEW], `lib/presentation/workorder/bloc/create_workorder_bloc.dart` [NEW], `lib/presentation/workorder/create_workorder_screen.dart` [NEW], `lib/core/router/app_router.dart`, `lib/injection.dart`
- **Summary**: Mengimplementasikan sisi aplikasi Flutter untuk alur Maker-Checker: pembuatan Work Order baru dengan dukungan backdate dan penomoran otomatis/manual, quick-add master data (Client, Kapal, Dermaga, Aktifitas, Alat) langsung dari form, tombol "Kirim untuk Review Admin" dengan dialog konfirmasi, stepper 4-tahap (Open → Progress → Review → Done), status badge & filter chip Review, penguncian penambahan laporan saat status Review/Done, serta pencabutan tombol ubah status DONE dan hapus laporan dari hak pengawas mobile.
- **Conversation ID**: a83220c3-7920-4ddc-8857-e58470f858d2

### [2026-10-20] feat: Implementasi Tahap 1 Alur Maker-Checker WO & Hak Akses Pengawas/Admin

- **Scope**: Workorder Module, Mobile API v1 (Workorders, Master, Sync, Dashboard), Web Workorder Views & Migration
- **Files Changed**: `database/migrations/2026_10_20_add_review_status_to_t_work_order.sql`, `application/config/routes.php`, `application/models/M_workorder.php`, `application/controllers/api/v1/Workorders.php`, `application/controllers/api/v1/Master.php`, `application/controllers/api/v1/Sync.php`, `application/controllers/api/v1/Dashboard.php`, `application/controllers/Workorder.php`, `application/views/workorder/index.php`, `application/views/workorder/detail.php`, `application/views/workorder/edit.php`
- **Summary**: Menambahkan dukungan status 'review' (Menunggu Verifikasi) pada siklus Work Order, endpoint pembuatan WO dari mobile dengan dukungan tanggal lampau (backdate) dan nomor otomatis/manual, endpoint submit-review lapangan, proteksi pembatasan status 'done' dan delete report eksklusif bagi Admin, penambahan CRUD master data (Client, Dermaga, Alat, Aktifitas) dengan normalisasi deduplikasi (Smart-Link), pembaruan push sync offline WO, serta integrasi visual badge 'Menunggu Verifikasi', filter review, dan tombol aksi cepat approval DONE pada Web Work Order.
- **Conversation ID**: a83220c3-7920-4ddc-8857-e58470f858d2

### [2026-10-20] style: Ubah Skema Warna Halaman Rekap Kegiatan Kapal ke Primary Theme

- **Scope**: Report Workorder View
- **Files Changed**: `application/views/report/workorder.php`
- **Summary**: Mengubah seluruh skema warna hijau pada tab Rekap Kegiatan Kapal (banner header, kepala tabel utama, kepala tabel ringkasan PBM & Komoditas, border, hover baris, footer tabel, icon placeholder, print CSS, serta palet warna ApexCharts) menjadi warna Primary tema (`var(--vz-primary)` / `#405189`) agar seragam dengan identitas tema aplikasi.
- **Conversation ID**: 1b83298b-47e4-48bd-9a72-4f3f10f01d66

### [2026-10-20] fix: Perbaikan Response Data & Penanganan Laporan Nihil pada Rekap Kegiatan Kapal

- **Scope**: Report Workorder Controller & View
- **Files Changed**: `application/controllers/report/Workorder.php`, `application/views/report/workorder.php`
- **Summary**: Memperbaiki issue toast error "Gagal memuat data" saat preview Rekap Kegiatan Kapal dengan menyesuaikan payload JSON unwrapping antara backend `sendJsonSuccess()` dan handler AJAX. Mengubah tampilan saat data 0 (laporan nihil) agar tetap menampilkan struktur tabel resmi lengkap (banner judul, kolom tabel, baris nihil, footer TTL 0 KAPAL & 0,00) sehingga siap dipratinjau dan dicetak.
- **Conversation ID**: 1b83298b-47e4-48bd-9a72-4f3f10f01d66

### [2026-10-20] style: Ubah Warna Button Tab Konten Report Work Order ke Primary Theme

- **Scope**: Report Workorder View
- **Files Changed**: `application/views/report/workorder.php`
- **Summary**: Mengubah skema warna button tab konten (`.nav-pills-report`) dari warna hijau menjadi warna primary tema (`var(--vz-primary)` / `#405189`) untuk status aktif beserta aksen hover halus, menjaga konsistensi dengan elemen navigasi tema Velzon.
- **Conversation ID**: 1b83298b-47e4-48bd-9a72-4f3f10f01d66

### [2026-10-20] fix: Perbaikan Return Value dashboard_top_clients pada Dashboard Admin

- **Scope**: Workorder Model & Dashboard
- **Files Changed**: `application/models/M_workorder.php`
- **Summary**: Memperbaiki method `dashboard_top_clients()` yang sebelumnya mengembalikan objek query builder mentah sehingga menyebabkan PHP warning (attempt to read property pada mysqli_result) dan chart horizontal "Top 5 Clients" gagal dirender di halaman dashboard admin.
- **Conversation ID**: 1b83298b-47e4-48bd-9a72-4f3f10f01d66

### [2026-10-19] feat: Tab Rekap Kegiatan Kapal & Navigasi Tab pada Report Work Order

- **Scope**: Report Workorder Module, Master Aktifitas, Workorder Model
- **Files Changed**: `application/controllers/report/Workorder.php`, `application/models/M_workorder.php`, `application/views/report/workorder.php`, `application/controllers/master/Aktifitas.php`, `application/views/master/aktifitas/index.php`, `application/views/master/aktifitas/edit.php`, `database/migrations/2026_10_19_add_tipe_to_m_aktifitas.sql`
- **Summary**: Menambahkan navigasi Tab Pills pada `/report/workorder` yang memisahkan "Laporan Work Order" (default) dan "Rekap Kegiatan Kapal". Tab kedua dilengkapi filter Pelabuhan dan Bulan-Tahun, banner judul hijau forest, tabel ringkasan kegiatan kapal (No, Kapal, PBM, No. Inv, Bongkar, Muat, Party, Kegiatan), tabel summary PBM & Komoditas, visualisasi chart distribusi kapal & party tonase berbasis ApexCharts, serta layout cetak ramah print. Menambahkan klasifikasi kolom `TIPE` ('bongkar'/'muat') pada master aktifitas untuk kategorisasi kolom dinamis.
- **Conversation ID**: 1b83298b-47e4-48bd-9a72-4f3f10f01d66

### [2026-10-19] feat: Entri Data Work Order Lama / yang Sudah Dikerjakan

- **Scope**: Workorder Module & Activity Field Operations
- **Files Changed**: `application/controllers/Workorder.php`, `application/controllers/Activity.php`, `application/models/M_workorder.php`, `application/views/workorder/index.php`, `application/views/workorder/edit.php`, `application/views/activity/form_detail.php`
- **Summary**: Menambahkan dukungan entri data work order masa lalu (backdate) dengan fleksibilitas tanggal pekerjaan dan penomoran WO manual/auto pada halaman `/workorder`, perbaikan sinkronisasi CSRF & format tanggal dinamis saat tanggal diubah, pengaturan default sort tabel ke tanggal terbaru (CREATED_AT DESC), serta input tanggal/waktu kerja per rincian laporan aktivitas pada menu `/activity`.
- **Conversation ID**: 1b83298b-47e4-48bd-9a72-4f3f10f01d66

## API v1 — Mobile Backend

### [2026-02-27] feat: Tambah Endpoint Upload Foto Laporan Terpisah

- **Scope**: Workorder API Module
- **Files Changed**: `application/config/routes.php`, `application/controllers/api/v1/Workorders.php`, `docs/plan/02-api-reference.md`
- **Summary**: Menambahkan endpoint `POST /api/v1/reports/{id}/photos` untuk upload foto laporan secara terpisah (multipart/form-data). Endpoint ini dibutuhkan oleh mobile client Flutter saat sync foto offline setelah sync push data berhasil.
- **Conversation ID**: e07aa4b1-f14b-4f8b-a8e7-20ba3af80eda

### [2026-02-28] feat: Audit Komprehensif & UI/UX Improvements

- **Scope**: Profile, Dashboard, Workorder, Routes
- **Files Changed**: `workorder_detail_screen.dart`, `dashboard_screen.dart`, `profile_screen.dart`, `edit_profile_screen.dart`, `dashboard_skeleton.dart` [NEW], `wo_status_stepper.dart` [NEW], `routes.php`
- **Summary**: Audit menyeluruh 10 screen Flutter + BE API. Fix critical missing route `update-profile`, fix BuildContext async warning, unify DI style, tambah Hero avatar animation, shimmer skeleton dashboard loading, horizontal status stepper workorder detail.
- **Conversation ID**: be62e2a7-fac0-40d0-95ab-810afad53fc6

### [2026-02-28] fix: Perbaikan Favicon Tidak Dimuat di VPS Nginx

- **Scope**: Views (base layout, auth)
- **Files Changed**: `assets/favicon/site.webmanifest`, `application/views/base/auth_header.php`, `application/views/auth/change_password.php`
- **Summary**: Fix 3 masalah favicon: (1) `site.webmanifest` menggunakan absolute path `/` yang salah, diganti relative path. (2-3) `auth_header.php` dan `change_password.php` menggunakan variabel `$path_favicon_svg`/`$path_favicon_png` yang tidak pernah didefinisikan, diganti dengan `base_url()` ke file PNG yang ada.
- **Conversation ID**: 27c09772-8846-48bb-b216-fe89fd21bae4

### [2026-02-28] feat: Tambah Button Test Email SMTP di Pengaturan Aplikasi

- **Scope**: Pengaturan Aplikasi Module (Email Tab)
- **Files Changed**: `application/views/pengaturan/aplikasi/_scripts.php`
- **Summary**: Menambahkan button "Test Email SMTP" di tab Email pada halaman Pengaturan Aplikasi. Fitur mencakup input email tujuan, validasi format email, loading spinner saat mengirim, dan disable button/input selama proses. Backend endpoint `Aplikasi.test_email()` sudah ada sebelumnya.
- **Conversation ID**: 9e3cbe83-cfcd-4bc9-b609-73ac5f799f77

### [2026-02-28] fix: Perbaikan Halaman Forgot Password Error

- **Scope**: Auth Module (forgot_password, reset_password)
- **Files Changed**: `application/controllers/Auth.php`, `application/views/auth/forgot_password.php`, `application/views/auth/reset_password.php`
- **Summary**: Fix 2 bug di halaman forgot password: (1) Critical fall-through bug di `forgot_password()` — ketika identity tidak ditemukan, kode tetap lanjut memanggil `forgotten_password()` pada object null sehingga fatal error. Diperbaiki dengan proper `else` branching. (2) Variabel `$site_name_logo` tidak pernah didefinisikan di view `forgot_password.php` dan `reset_password.php`, diganti dengan `$logo_lg` yang sudah di-set di `MY_Controller`.
- **Conversation ID**: 5505c16b-5376-483d-8240-7b704df1479d

### [2026-03-01] fix: Ion Auth Forgot Password Gunakan SMTP dari App Settings

- **Scope**: Auth Module (Ion Auth Library)
- **Files Changed**: `application/libraries/Ion_auth.php`
- **Summary**: Ion Auth sebelumnya mengirim email forgot password via local mail transport (SMTP config di `ion_auth.php` ter-comment-out). Diubah agar `Ion_auth::__construct()` menginisialisasi CI email library dengan SMTP settings dari database (`get_app_settings_by_group('email')`), sama seperti `Base_email` library. Juga diupdate `from` address di `forgotten_password()` agar pakai `email_from` dari database.
- **Conversation ID**: 5505c16b-5376-483d-8240-7b704df1479d

### [2026-03-01] fix: Perbaikan Halaman Profile Admin Panel

- **Scope**: Profile Module (Controller, Views)
- **Files Changed**: `application/controllers/Profile.php`, `application/views/profile-edit.php`, `application/views/profile-detail.php`
- **Summary**: Fix multiple issue di halaman profile: (1) CSRF token tidak refresh setelah AJAX (diubah ke `sendJsonSuccess/sendJsonError`). (2) Tambah validasi password confirmation server-side. (3) Restrict avatar upload ke image types saja. (4) Fix spasi di `model_id` hidden input. (5) Hapus 500+ baris dummy projects. (6) Error message lebih deskriptif.
- **Conversation ID**: a00e7e58-b557-48cb-8aef-7e7e04e32a0e

### [2026-03-01] feat: Password Reset Email Menggunakan Template DB

- **Scope**: Auth Module (Ion Auth Library)
- **Files Changed**: `application/libraries/Ion_auth.php`
- **Summary**: Email forgot password diubah dari file view statis ke `Base_email::send_email()` dengan template slug `password_reset` dari `s_email_templates`. Email sekarang menggunakan wrapper `v_notifikasi` (styled, logo, footer). Variabel: `identity`, `user_name`, `email`, `reset_link`, `site_name`, `button_text`, `button_link`. Fallback ke cara lama jika template DB gagal.
- **Conversation ID**: a00e7e58-b557-48cb-8aef-7e7e04e32a0e

### [2026-03-01] feat: API Logs — Filter Hanya /api/ URL + Clear All Button

- **Scope**: Logs Module (Hook, Model, Controller, View)
- **Files Changed**: `application/hooks/Request_logger.php`, `application/models/M_log_api.php`, `application/controllers/logs/Api.php`, `application/views/logs/api/index.php`
- **Summary**: (1) Request logger hook sebelumnya merekam semua request termasuk halaman admin panel. Ditambahkan filter `strpos($uri, 'api/')` agar hanya merekam URL `/api/`. (2) Tambah method `clear_all()` di model (TRUNCATE). (3) Tambah endpoint `clear()` di controller dengan permission check dan CSRF validation. (4) Tambah tombol "Clear All Logs" merah di view dengan konfirmasi SweetAlert2.
- **Conversation ID**: a00e7e58-b557-48cb-8aef-7e7e04e32a0e

### [2026-03-01] fix: Audit & Fix Email Notification Templates

- **Scope**: Email Module (Base_email, Advanced_logger, Cron_manager, Email_processor, views/mail)
- **Files Changed**: `application/libraries/Base_email.php`, `application/libraries/Advanced_logger.php`, `application/libraries/Cron_manager.php`, `application/controllers/cron/Email_processor.php`, `database/2026_03_01_add_email_templates_error_cron.sql` [NEW], `views/mail/bc_promosi.php` [DELETE], `views/mail/notif_info.php` [DELETE], `views/mail/notif_no_header.php` [DELETE], `views/mail/ptk_mail.php` [DELETE], `views/mail/tahapan_atasan.php` [DELETE]
- **Summary**: Audit lengkap sistem email. Fix 4 bug critical: `send_email()` dipanggil dengan signature salah di Advanced_logger, Cron_manager, Email_processor, dan `send_notifikasi()`. `get_smtp_settings()` diubah ke public. Hapus 5 legacy template "E-Recruitment Kokola Group" yang tidak dipakai. Tambah SQL migration untuk DB template `error_notification` dan `cron_alert`.
- **Conversation ID**: 9b0b2263-918c-46b8-ad7b-2897ad1f7087

### [2026-03-01] fix: Perbaikan Edit Profil Mobile Error 422

- **Scope**: Mobile App (Auth API)
- **Files Changed**: `lib/data/remote/auth_api.dart`
- **Summary**: Fix error 422 saat update profil di mobile app. Penyebab: `Options(contentType: 'multipart/form-data')` di-set manual tanpa boundary, sehingga server gagal parse form data dan `$_POST` / `$_FILES` kosong → backend return 422 "Tidak ada data yang diubah". Fix: hapus baris options tersebut karena Dio otomatis set Content-Type yang benar untuk `FormData`.
- **Conversation ID**: d7f7765c-9284-46eb-9b4e-8698d2a7962d

### [2026-03-01] feat: Tambah Button Delete Laporan di Workorder Detail

- **Scope**: Mobile App (Workorder Detail Screen)
- **Files Changed**: `lib/presentation/workorder/workorder_detail_screen.dart`
- **Summary**: Menambahkan tombol delete (icon trash merah) di setiap card laporan aktivitas. Tombol hanya muncul jika pengawas yang login adalah pembuat laporan tersebut (`report.createdBy == currentUsername`) dan workorder belum berstatus "done". Sebelum delete, tampilkan dialog konfirmasi. Menggunakan `DeleteReportEvent` BLoC yang sudah ada.
- **Conversation ID**: d7f7765c-9284-46eb-9b4e-8698d2a7962d

### [2026-03-01] feat: Optimasi Saran Catatan di Form Laporan

- **Scope**: Master Module (Backend API + Mobile Report Form)
- **Files Changed**: `application/models/M_master.php`, `application/controllers/api/v1/Master.php`, `lib/data/remote/master_api.dart`, `lib/presentation/report/report_form_screen.dart`
- **Summary**: Optimasi saran catatan: (1) Backend sort berdasarkan frekuensi penggunaan di `t_work_order_detail` + limit default 10. (2) Mobile chip tampil maks 5 dengan tombol "+N lainnya" untuk expand. (3) UI chip diubah ke StadiumBorder (pill shape), font 10px, compact density. (4) Textarea catatan auto-expand (`minLines: 2, maxLines: null`).
- **Conversation ID**: 2f09265e-91f0-4484-895d-cc09221c8993

### [2026-03-01] feat: Implementasi Notifikasi Lengkap (Local + In-App + Deep-Link)

- **Scope**: Mobile Notification Service + Backend FCM Library
- **Files Changed**: `lib/services/notification_service.dart`, `lib/app.dart`, `pubspec.yaml`, `android/app/src/main/AndroidManifest.xml`, `application/libraries/Fcm_service.php` [NEW]
- **Summary**: Implementasi lengkap 2 TODO di `notification_service.dart`: (1) Foreground messages menampilkan local notification (system tray) + in-app MaterialBanner dengan tombol "Lihat"/"Tutup" + auto-dismiss 5 detik. (2) Notification tap navigasi ke screen relevan via go_router berdasarkan `data.type` + `data.id`. Backend: buat library `Fcm_service.php` untuk kirim push notification via FCM HTTP v1 API dengan OAuth service account, auto-deactivate invalid tokens. Membutuhkan konfigurasi `fcm_project_id` dan `fcm_service_account_path` di app settings.
- **Conversation ID**: 7815b666-82a3-4727-8afe-3806f4c1412f

### [2026-03-01] feat: Fungsikan Icon Notifikasi Dashboard + Halaman List Notifikasi

- **Scope**: Notification Module (Backend API + Mobile Full Stack)
- **Files Changed**: `database/migrations/create_t_notifications.sql` [NEW], `application/models/M_notification.php` [NEW], `application/controllers/api/v1/Notifications.php` [NEW], `application/config/routes.php`, `application/libraries/Fcm_service.php`, `lib/data/remote/notification_api.dart` [NEW], `lib/data/models/notification/notification_item.dart` [NEW], `lib/presentation/notification/bloc/notification_bloc.dart` [NEW], `lib/presentation/notification/bloc/notification_event.dart` [NEW], `lib/presentation/notification/bloc/notification_state.dart` [NEW], `lib/presentation/notification/notification_list_screen.dart` [NEW], `lib/presentation/dashboard/dashboard_screen.dart`, `lib/core/router/app_router.dart`, `lib/injection.dart`, `lib/core/network/api_endpoints.dart`
- **Summary**: Implementasi fitur notifikasi end-to-end. Backend: tabel `t_notifications`, model CRUD, API controller (list paginated, unread count, mark read, mark all read), routes, integrasi auto-save di `Fcm_service`. Mobile: notification API client, data model, BLoC (load, refresh, pagination, mark read), halaman list notifikasi (pull-to-refresh, infinite scroll, time-ago, icon per type, unread indicator), badge counter di dashboard, routing dan DI.
- **Conversation ID**: 45d49ce0-d308-479a-88b6-61212b68e79b

### [2026-03-01] feat: Auto-Hide Greeting Header di Dashboard

- **Scope**: Dashboard (Web Admin + Mobile)
- **Files Changed**: `application/views/dashboard.php`, `application/views/dashboard_pengawas.php`, `lib/presentation/dashboard/dashboard_screen.dart`
- **Summary**: Menambahkan auto-hide pada greeting header "Selamat Datang" di dashboard. Header otomatis fade out + collapse setelah 5 detik. Web: CSS transition (`max-height`, `opacity`) + `setTimeout`. Mobile: `AnimatedSize` + `AnimatedOpacity` + `Timer`.
- **Conversation ID**: 45d49ce0-d308-479a-88b6-61212b68e79b

### [2026-03-01] feat: Fungsikan Indikator Sync di Profile Mobile

- **Scope**: Mobile Profile Screen
- **Files Changed**: `lib/presentation/profile/profile_screen.dart`
- **Summary**: Indikator "Status Sinkronisasi" di profile screen diubah dari hardcoded "Synced" menjadi fungsional. Sekarang menampilkan status sync real-time (idle/syncing/success/error) dari `SyncService.statusNotifier` via `ValueListenableBuilder`, termasuk jumlah laporan pending yang belum tersync. Hapus method `_menuItemWithBadge` yang tidak terpakai.
- **Conversation ID**: a276c4ee-6ea7-44c1-b792-1a366430eaaf

### [2026-03-01] chore: Bump Version ke 1.2.0+3

- **Scope**: Mobile App (Versioning)
- **Files Changed**: `pubspec.yaml`, `lib/core/config/app_constants.dart`
- **Summary**: Bump version dari 1.1.0+2 ke 1.2.0+3. Sinkronkan `AppConstants.appVersion` dan `buildNumber` yang sebelumnya masih 1.0.0+1 dengan `pubspec.yaml`. Build release APK berhasil: `ktkbm-mobile-v1.2.0.apk` (60MB).
- **Conversation ID**: a276c4ee-6ea7-44c1-b792-1a366430eaaf

### [2026-03-01] fix: Fix Catatan Auto-Suggest Error di Form Laporan Mobile

- **Scope**: Master Module (Backend Model + Mobile BLoC)
- **Files Changed**: `application/models/M_master.php`, `lib/presentation/report/bloc/report_form_bloc.dart`
- **Summary**: Fix catatan auto-suggest chips tidak muncul di mobile. Root cause: MySQL Error 1267 "Illegal mix of collations" pada JOIN `t_work_order_detail.CATATAN = m_catatan.NAMA` karena kolom punya collation berbeda. Fix: tambah `COLLATE utf8mb4_unicode_ci` di JOIN condition. Juga replace `debugPrint` dengan `AppLogger` di `ReportFormBloc` untuk error tracking.
- **Conversation ID**: f9497fd5-2a76-4a99-9b32-0c00b6749b64

### [2026-03-01] docs: Update Swagger API Documentation — 8 Missing Endpoints

- **Scope**: API Documentation (Swagger/OpenAPI)
- **Files Changed**: `docs/swagger.json`
- **Summary**: Tambah 8 endpoint yang belum terdokumentasi di Swagger: `POST /auth/update-profile`, `GET /master/catatan` (dengan params search & limit), `POST /master/create_krk`, `POST /reports/{id}/photos`, `GET /notifications`, `GET /notifications/unread-count`, `POST /notifications/{id}/read`, `POST /notifications/read-all`. Juga tambah schema `Notification`, `UpdateProfileRequest`, dan tag `Notifications`.
- **Conversation ID**: f9497fd5-2a76-4a99-9b32-0c00b6749b64

### [2026-03-02] fix: Foto Laporan Tidak Tampil di WO Open/Progress

- **Scope**: Backend API (Workorders) + Mobile BLoC
- **Files Changed**: `application/controllers/api/v1/Workorders.php`, `lib/presentation/report/bloc/report_form_event.dart`
- **Summary**: Fix foto before/after tidak muncul di detail WO status open/progress. Investigasi menyeluruh seluruh pipeline (backend model, API controller, Flutter parsing, UI widget) menunjukkan tidak ada filter status. Root cause kemungkinan besar: backend `_upload_photo()` gagal secara diam-diam tanpa logging. Fix: (1) Tambah comprehensive error logging di `_upload_photo()` dan diagnostic log di `_store_report()` untuk diagnosa penyebab upload gagal. (2) Fix `SubmitReport` event props yang tidak include foto fields (Equatable).
- **Conversation ID**: 7acc999e-e776-4329-ba5b-6824a97d90bf

### [2026-03-02] refactor: Hapus Status "hide" dari Work Order

- **Scope**: Workorder Module (Controllers, API, Model)
- **Files Changed**: `application/controllers/Workorder.php`, `application/controllers/report/Workorder.php`, `application/controllers/api/v1/Workorders.php`, `application/controllers/api/v1/Sync.php`, `application/models/M_workorder.php`
- **Summary**: Hapus konsep status "hide" dari WO. Default status saat buat WO baru diubah dari 'hide' ke 'open'. Hapus semua filter `LOWER(a.STATUS) != 'hide'` di 4 method model (get_pengawas_workorder, dashboard_counts, dashboard_recent, dashboard_monthly) dan 2 API controller (Workorders list, Sync pull). Badge "Hide" di DataTable web diganti dengan generic fallback badge.
- **Conversation ID**: d7bebe1b-c949-444c-89ca-e3210eaee126

### [2026-03-02] feat: Build Halaman Detail WO Web Panel + Cleanup Status Hide

- **Scope**: Workorder Module (Controller, Views)
- **Files Changed**: `application/controllers/Workorder.php`, `application/views/workorder/detail.php`, `application/views/workorder/index.php`, `application/views/workorder/edit.php`
- **Summary**: (1) Rewrite `detail()` controller dari model project lama ke `m_workorder`. (2) Rebuild `detail.php` view dari 1010 baris UI project ke ~220 baris WO detail (info card, summary sidebar, DataTable laporan aktivitas dengan foto before/after). (3) Hapus opsi "Hide" dari form create, edit, dan filter status. (4) Tambah `btn-detail` click handler. (5) Tombol detail sekarang muncul untuk semua status. (6) Fix filter value "selesai" → "done" agar sesuai dengan DB.
- **Conversation ID**: d7bebe1b-c949-444c-89ca-e3210eaee126

### [2026-03-02] refactor: Ubah Button Detail WO ke AJAX Modal

- **Scope**: Workorder Module (Controller, Views)
- **Files Changed**: `application/controllers/Workorder.php`, `application/views/workorder/detail.php`, `application/views/workorder/index.php`
- **Summary**: Ubah action button detail WO dari redirect halaman (window.location.href) ke AJAX modal show menggunakan `#modal_crud` fullscreen, mengikuti pola yang sama dengan button edit. Controller `detail()` diubah dari `loadView()` (full page) ke `$this->load->view()` (AJAX HTML fragment via POST). View `detail.php` diadaptasi untuk modal context (hapus wrapper header, card shadow-none border, DataTable destroy check).
- **Conversation ID**: 316ba003-6028-41d3-9cda-e37e7984f767

### [2026-03-02] fix: Filter Status 'Hide' dari Dashboard & API Mobile

- **Scope**: Workorder Model, API Controllers
- **Files Changed**: `application/models/M_workorder.php`, `application/controllers/api/v1/Workorders.php`, `application/controllers/api/v1/Sync.php`
- **Summary**: Menambahkan filter `LOWER(a.STATUS) != 'hide'` pada 5 method model dashboard (`dashboard_counts`, `dashboard_recent`, `dashboard_monthly`, `dashboard_weekly`, `get_pengawas_workorder`) dan 2 API controller (Workorders index, Sync pull) agar WO dengan status "hide" tidak tampil di mobile app.
- **Conversation ID**: 316ba003-6028-41d3-9cda-e37e7984f767

### [2026-03-02] fix: Optimasi Detail WO View (Kolom, Responsive, Volume)

- **Scope**: Workorder Views
- **Files Changed**: `application/views/workorder/detail.php`
- **Summary**: (1) Fix nama kolom DB: `JUMLAH_GANG`→`HASIL`, `FOTO_BEFORE`→`FOTO_KEGIATAN_BEFORE`, `FOTO_AFTER`→`FOTO_KEGIATAN_AFTER`. (2) Fix path foto dari `filemanager/workorder/` ke path langsung dari DB. (3) Tambah kolom "Nama KRK". (4) Tambah field "Volume" di ringkasan (sum HASIL + satuan). (5) DataTables responsive dengan column priority.
- **Conversation ID**: 316ba003-6028-41d3-9cda-e37e7984f767

### [2026-03-02] feat: Offline-First Dashboard Mobile

- **Scope**: Mobile App (Flutter)
- **Files Changed**: `lib/data/local/database.dart`, `lib/data/repositories/dashboard_repository.dart`, `lib/domain/repositories/i_dashboard_repository.dart`, `lib/presentation/dashboard/bloc/dashboard_bloc.dart`, `lib/presentation/dashboard/bloc/dashboard_state.dart`, `lib/presentation/dashboard/dashboard_screen.dart`, `lib/injection.dart`
- **Summary**: Implementasi offline-first dashboard: (1) Tambah 4 query methods di `AppDatabase` untuk hitung stats dari local DB. (2) Refactor `DashboardRepository` dengan pattern online→API, offline→local DB. (3) Update BLoC untuk keep old data saat refresh gagal. (4) Tambah offline indicator banner di UI Dashboard. (5) Update DI injection.
- **Conversation ID**: 316ba003-6028-41d3-9cda-e37e7984f767

### [2026-03-02] fix: Perbaikan Sync Indicator di Profile Screen

- **Scope**: Mobile App (Flutter)
- **Files Changed**: `lib/services/sync_service.dart`, `lib/presentation/profile/profile_screen.dart`
- **Summary**: (1) Tambah `pendingCountNotifier` reactive ValueNotifier agar pending count otomatis update setelah sync. (2) Auto-revert `SyncStatus.success`→`idle` setelah 5 detik agar time-ago text tampil. (3) Refresh pending count setiap selesai sync (success/error). (4) Tambah manual sync trigger (tap sync indicator). (5) Hapus stale `_pendingCount` state, ganti nested `ValueListenableBuilder`.
- **Conversation ID**: 316ba003-6028-41d3-9cda-e37e7984f767

### [2026-03-02] feat: Search WO by No WO dan Nama Kapal

- **Scope**: Mobile App (Flutter)
- **Files Changed**: `lib/data/local/database.dart`, `lib/presentation/workorder/workorder_list_screen.dart`
- **Summary**: Search WO sebelumnya hanya bisa cari berdasarkan No Work Order. Sekarang ditambahkan pencarian berdasarkan Nama Kapal (OR condition di local DB query). Hint text diupdate ke "Cari No. WO atau Nama Kapal...".
- **Conversation ID**: 316ba003-6028-41d3-9cda-e37e7984f767

### [2026-03-02] fix: Offline WO Detail & Report Form

- **Scope**: Mobile App (Flutter)
- **Files Changed**: `lib/data/repositories/workorder_repository.dart`, `lib/presentation/report/bloc/report_form_bloc.dart`, `lib/presentation/report/bloc/report_form_event.dart`, `lib/presentation/report/bloc/report_form_state.dart`, `lib/data/models/report/create_report_request.dart`, `lib/data/repositories/report_repository.dart`, `lib/presentation/report/report_form_screen.dart`, `lib/injection.dart`
- **Summary**: (1) Detail WO: offline fallback build `WorkOrderDetail` dari local DB (WorkOrdersTable + ReportsTable). (2) Report form: `MasterApi` → `MasterRepository` (offline KRK/Catatan). (3) `CreateKrk` offline guard. (4) `krkName` disimpan di offline report. (5) `ReportFormSuccessOffline` state + "disimpan offline, akan sync otomatis" feedback.
- **Conversation ID**: 316ba003-6028-41d3-9cda-e37e7984f767

### [2026-03-02] feat: Indikator Sync di Card Laporan WO Detail

- **Scope**: Mobile App (Flutter)
- **Files Changed**: `lib/presentation/workorder/workorder_detail_screen.dart`, `lib/data/repositories/workorder_repository.dart`, `lib/data/repositories/report_repository.dart`
- **Summary**: Tambah badge `SyncBadge` ("Belum sinkron") di card laporan pada halaman detail WO. Fix bug: `isSynced` tidak di-propagate dari local DB saat mapping `ReportsTableData` → `Report` model di 2 repository (`WorkorderRepository._getDetailFromLocal()` dan `ReportRepository.getReportsByWoId()`), sehingga laporan offline selalu terlihat sudah sync.
- **Conversation ID**: 80e92ec5-39c0-4500-b455-631bf3bd9d54

### [2026-03-02] fix: Audit & Fix Sync Indicator Issues

- **Scope**: Mobile App (Flutter)
- **Files Changed**: `lib/data/repositories/auth_repository.dart`, `lib/data/repositories/sync_repository.dart`, `lib/data/local/database.dart`
- **Summary**: Fix 3 issue dari audit sync indicator: (1) `SyncService.stop()` dipanggil saat logout agar tidak sync data user lama setelah login user baru. (2) Bounds check pada `result.index` di `pushPendingReports()` agar tidak crash jika server return index invalid. (3) `catatanTable` ditambahkan ke `clearAll()` agar data catatan ikut dibersihkan saat logout.
- **Conversation ID**: 80e92ec5-39c0-4500-b455-631bf3bd9d54

### [2026-03-02] fix: Audit & Fix Notification Screen Issues

- **Scope**: Mobile App (Flutter)
- **Files Changed**: `lib/presentation/notification/notification_list_screen.dart`, `lib/presentation/notification/bloc/notification_bloc.dart`
- **Summary**: Fix 2 issue dari audit notification screen: (1) `refId` (String) langsung dipakai di route `/workorders/:id` yang pakai `int.parse` → crash jika non-numeric. Diganti `int.tryParse` dengan null check. (2) Pull-to-refresh gagal mengganti seluruh data dengan error screen. Sekarang keep data lama jika refresh fail.
- **Conversation ID**: 80e92ec5-39c0-4500-b455-631bf3bd9d54

### [2026-03-02] feat: Notification Offline Support & Error Handling

- **Scope**: Mobile App (Flutter)
- **Files Changed**: `lib/data/local/tables.dart`, `lib/data/local/database.dart`, `lib/presentation/notification/bloc/notification_bloc.dart`, `lib/presentation/notification/bloc/notification_state.dart`, `lib/presentation/notification/notification_list_screen.dart`, `lib/injection.dart`, `lib/presentation/dashboard/dashboard_screen.dart`
- **Summary**: Tambah offline support untuk notification screen. Notifikasi di-cache ke local DB (`NotificationsTable`, schema v3) saat fetch berhasil, dan ditampilkan dari cache saat offline/API error. Tambah banner "offline" di UI. Fix empty catch di `markAllRead` dengan proper error logging via `AppLogger`.
- **Conversation ID**: 80e92ec5-39c0-4500-b455-631bf3bd9d54

### [2026-03-02] feat: Push Badge Refresh, Swipe Dismiss, Multi-page Cache

- **Scope**: Mobile App (Flutter)
- **Files Changed**: `lib/services/notification_service.dart`, `lib/presentation/dashboard/dashboard_screen.dart`, `lib/presentation/notification/bloc/notification_bloc.dart`, `lib/presentation/notification/bloc/notification_event.dart`, `lib/presentation/notification/notification_list_screen.dart`, `lib/data/local/database.dart`
- **Summary**: (1) Push notifikasi foreground sekarang otomatis refresh badge count di dashboard via callback `onForegroundMessageReceived`. (2) Validasi `int.tryParse` untuk workorder ID di `NotificationService._navigateFromData`. (3) Swipe-to-dismiss notifikasi (hapus dari local cache, Dismissible widget). (4) Multi-page cache: semua halaman yang di-load disimpan ke local DB.
- **Conversation ID**: 80e92ec5-39c0-4500-b455-631bf3bd9d54

### [2026-03-02] fix: App-Wide Audit Fixes (WO List, WO Detail, Profile Logout)

- **Scope**: Mobile App (Flutter)
- **Files Changed**: `lib/presentation/workorder/bloc/workorder_list_bloc.dart`, `lib/presentation/workorder/bloc/workorder_detail_bloc.dart`, `lib/presentation/profile/profile_screen.dart`, `lib/core/router/app_router.dart`
- **Summary**: (1) WO list refresh gagal sekarang keep data lama (sama seperti fix di notification & dashboard). (2) WO detail status update: snackbar feedback tetap muncul + langsung reload detail tanpa flash. (3) Profile logout di-`await` dengan error handling agar dialog tidak stuck. (4) `int.tryParse` di `app_router.dart` untuk route `/workorders/:id` dan `/workorders/:id/report`.
- **Conversation ID**: 80e92ec5-39c0-4500-b455-631bf3bd9d54

### [2026-03-02] fix: Backend API Audit Fixes (7 Issues)

- **Scope**: Backend API (CodeIgniter 3)
- **Files Changed**: `application/controllers/api/v1/Workorders.php`, `application/controllers/api/v1/Sync.php`, `application/controllers/api/v1/Auth.php`, `application/controllers/api/v1/Notifications.php`, `application/controllers/api/v1/Master.php`
- **Summary**: (1) SQL escape `pengawas_id` di Workorders & Sync. (2) Fix redundant `trans_commit`/`trans_rollback` setelah `trans_complete` di Workorders & Sync. (3) Username uniqueness check di `update_profile`. (4) Access check di `sync/push` agar pengawas tidak bisa push ke WO milik orang lain. (5) Status WO otomatis `progress` saat report di-push walau pengawas sudah assigned. (6) Missing `return` di `Notifications::mark_read`. (7) Duplicate KRK name check di `create_krk`.
- **Conversation ID**: 80e92ec5-39c0-4500-b455-631bf3bd9d54

### [2026-03-02] fix: SQL Injection Defense-in-Depth di Models

- **Scope**: Model Layer
- **Files Changed**: `application/models/M_workorder.php`, `application/models/M_master.php`
- **Summary**: Escape semua variabel yang diinterpolasi ke raw SQL: (1) `M_workorder` — 9 method menggunakan `$this->db->escape()` untuk `$id`, `$pengawas_id`, `$tgl`, `$pelabuhan`, `$status`, `$wo_id`. (2) `M_master` — semua `get_*($id)` pakai `escape()`, semua LIKE search pakai `escape_like_str()`, `get_all_users_by_role()` pakai `intval()` untuk role IDs.
- **Conversation ID**: 80e92ec5-39c0-4500-b455-631bf3bd9d54

### [2026-03-02] fix: Hapus Redundant Transaction Handling di Web Controllers

- **Scope**: Web Controllers (Activity, Bulk, report/Workorder)
- **Files Changed**: `application/controllers/Activity.php`, `application/controllers/Bulk.php`, `application/controllers/report/Workorder.php`
- **Summary**: Hapus `trans_commit()`/`trans_rollback()` yang dipanggil setelah `trans_complete()` di 4 instance (Activity.store_detail, Bulk.jurnal_pendapata, Bulk.jurnal_biaya, report/Workorder.preview). CI3 `trans_complete()` sudah otomatis handle commit/rollback.
- **Conversation ID**: 7d46953d-48a3-42e9-b87a-2509671ce1d8

### [2026-07-04] docs: Tambah Panduan Pengguna & Menu Panduan Web Panel

- **Scope**: Documentation & Web Interface
- **Files Changed**: `docs/PANDUAN_PENGGUNA.md`, `application/views/base/menu.php`, `application/controllers/Panduan.php`, `application/views/panduan.php`
- **Summary**: Menambahkan panduan pengguna komprehensif (`docs/PANDUAN_PENGGUNA.md`) serta mengintegrasikan menu sidebar baru "Panduan Pengguna" di Web Panel Admin. Halaman Panduan (`application/views/panduan.php`) memuat tab kategori interaktif (General Alur Kerja, Admin Panel, Mobile App), termasuk penambahan rincian baru terkait menu "Aktifitas Lapangan" dan "Manajemen Invoice (Tagihan)" untuk peran Admin, ilustrasi SVG pendukung, dan representasi visual mockup UI.
- **Conversation ID**: d9b1f1ab-fc84-4d41-998f-983b749b66f1

### [2026-07-04] fix: Audit & perbaikan dokumentasi Invoice + implementasi status select

- **Scope**: Accounting Invoice Module, Documentation
- **Files Changed**: `application/controllers/accounting/Invoice.php`, `application/views/accounting/invoice/form.php`, `application/views/panduan.php`, `docs/PANDUAN_PENGGUNA.md`
- **Summary**: Berdasarkan audit dokumentasi vs implementasi, dilakukan perbaikan: (1) Implementasi dropdown Status (Draft/Sent) pada form create invoice yang sebelumnya hardcode 'draft'; (2) Perbaikan 7 temuan audit dokumentasi di panduan.php & PANDUAN_PENGGUNA.md — koreksi lokasi tombol, alur pembayaran via modal Input Pembayaran, restriksi edit/hapus hanya draft, dan dokumentasi fitur tersembunyi (Tandai Sent, Batalkan Invoice, Stats Cards, Soft Delete & Restore).
- **Conversation ID**: d9b1f1ab-fc84-4d41-998f-983b749b66f1

### [2026-07-04] fix: Hapus kewajiban isi field kontak hp pada edit profile

- **Scope**: Profile Module
- **Files Changed**: `application/views/profile-edit.php`, `application/controllers/Profile.php`
- **Summary**: Menghapus kelas 'required' dan atribut HTML5 'required' pada input Kontak Hp agar bersifat opsional. Serta memperbarui controller Profile.php untuk dapat menyimpan nilai kosong/pembersihan data tanpa revert ke nilai lama.
- **Conversation ID**: d9b1f1ab-fc84-4d41-998f-983b749b66f1
