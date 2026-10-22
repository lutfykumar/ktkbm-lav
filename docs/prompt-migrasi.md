Anda bertindak sebagai Lead Migration Engineer, Laravel Engineer,
Legacy CodeIgniter 3 Analyst, QA Engineer, dan UI Parity Engineer.

TUGAS UTAMA
Migrasikan aplikasi CodeIgniter 3 yang tersedia di workspace menjadi
aplikasi Laravel yang berfungsi penuh.

Migrasi mencakup:
- Seluruh fungsi bisnis.
- Database dan akses data.
- Autentikasi, otorisasi, dan session.
- Theme, layout, halaman, komponen, dan interaksi UI.
- API, AJAX, upload, download, export, report, dan integrasi.
- Scheduled tasks, background processing, dan deployment.
- Automated testing, dokumentasi, dan rencana cutover/rollback.

Anda harus mengerjakan implementasi, bukan hanya memberi saran atau
menghasilkan migration plan.

==================================================
A. KONFIGURASI PROJECT
==================================================

LEGACY_PATH = ./www/tkbm
TARGET_PATH = ./www/tkbm-lav
DOCS_PATH = ./www/tkbm-lav/docs/migration
REFERENCE_UI_PATH = ./www/tkbm-lav/docs/ui

LEGACY_BASE_URL = http://tkbm.dev
TARGET_BASE_URL = http://tkbm-lav.dev

TARGET_LARAVEL_VERSION = 13
TARGET_PHP_VERSION = 8.4
DATABASE_ENGINE = mysql
FRONTEND_STRATEGY = Laravel Blade dengan theme existing (Velzon)
UI_POLICY = preserve existing UI, no redesign
DATABASE_POLICY = preserve existing schema by default for old koneksi legacy app
EXECUTION_MODE = autonomous in local/sandbox, approval for risky actions

Jika nilai konfigurasi belum diisi:
- Cari informasi yang dapat dibuktikan dari repository dan environment.
- Jangan mengarang nilai.
- Catat asumsi dan blocker.
- Ajukan pertanyaan hanya untuk informasi yang benar-benar menghalangi
  pengerjaan aman.
- Tetap kerjakan bagian lain yang tidak terblokir.

==================================================
B. PRIORITAS DAN BATASAN
==================================================

Urutan prioritas:
1. Keamanan dan integritas data.
2. Kesetaraan business behavior.
3. Kesetaraan permission dan akses.
4. Kesetaraan UI/UX.
5. Kompatibilitas integrasi dan URL.
6. Maintainability.
7. Optimasi yang tidak mengubah perilaku.

Aturan wajib:
- Perlakukan source CI3 sebagai read-only.
- Jangan menghapus atau menimpa aplikasi lama.
- Jangan bekerja langsung pada database produksi.
- Gunakan database target/sandbox yang terpisah.
- Jangan menjalankan migrasi destruktif pada database existing.
- Jangan melakukan deploy atau cutover produksi tanpa persetujuan.
- Jangan mengirim email, SMS, pembayaran, webhook, atau notifikasi nyata.
- Jangan menyalin secret ke dokumentasi, test fixture, log, atau commit.
- Jangan membuang fitur karena terlihat lama atau sulit.
- Jangan mengganti theme, framework CSS, frontend stack, atau pola
  interaksi tanpa alasan yang terdokumentasi dan persetujuan.
- Jangan menambahkan React, Vue, Inertia, Livewire, atau starter kit UI
  hanya karena lebih familier.
- Jangan menambahkan SSO atau mengganti identity provider kecuali
  aplikasi existing menggunakannya atau saya memintanya.
- Jangan mempertahankan vulnerability demi parity.
- Untuk perubahan keamanan yang memengaruhi perilaku, dokumentasikan
  perbedaan dan dampak kompatibilitasnya.
- Jangan melakukan refactoring besar yang tidak diperlukan untuk migrasi.
- Jangan menggunakan placeholder, dummy response, atau hardcoded success
  untuk menyatakan fitur sudah selesai.

==================================================
C. PHASE 1 — AUDIT MENYELURUH
==================================================

Sebelum implementasi fitur, pahami aplikasi yang sebenarnya.

Audit sekurang-kurangnya:

1. Struktur dan framework
- Versi CI3 dan PHP.
- Composer dependencies dan library manual.
- application/controllers, models, views.
- application/config, routes, autoload, hooks.
- application/core dan MY_Controller/MY_Model.
- Helpers, libraries, third_party.
- HMVC/modules jika digunakan.
- Override framework, custom bootstrap, dan entry point tambahan.
- .htaccess, rewrite rules, base_url, index_page.
- Environment dan konfigurasi deployment.

2. Routing dan behavior
- Explicit routes.
- Implicit controller/method routes.
- Default controller dan 404 override.
- _remap dan route berbasis parameter.
- HTTP methods, redirects, query parameters.
- Endpoint AJAX/API dan response contract.
- Endpoint yang dipanggil sistem eksternal.
- Route yang ditemukan dari menu, JavaScript, cron, dan dokumentasi.

3. Fungsi bisnis
- Semua modul dan submodul.
- Alur CRUD.
- Approval, status transition, transaksi, dan workflow.
- Formula, rounding, pajak, diskon, saldo, stok, atau perhitungan lain.
- Soft delete, audit trail, dan histori.
- Import/export, report, cetak, PDF, Excel.
- Upload, download, pengelolaan file.
- Scheduled jobs, queue, CLI commands, dan cron.
- Integrasi eksternal dan error handling.

4. Database
- Schema, tabel, kolom, tipe, nullable, default.
- Primary key, composite key, foreign key, index.
- Nonstandard table names dan timestamp.
- Views, triggers, procedures, events.
- Charset, collation, timezone, SQL mode.
- Raw SQL dan query builder.
- Cross-database access jika ada.
- Transaction boundaries, locking, dan race-sensitive operations.
- Lokasi file yang direferensikan database.

5. Authentication dan authorization
- Login, logout, remember-me, reset password jika ada.
- Identitas user dan tabel yang dipakai.
- Algoritma password hash berdasarkan bukti kode.
- Session storage, key, flash message, timeout.
- Role, permission, menu visibility, ownership, tenant/data scope.
- Pembatasan langsung di controller, model, view, helper, atau hook.
- SSO/API token jika memang digunakan.

6. Theme dan UI
- Seluruh layout dan variasinya.
- Login page, dashboard, sidebar, navbar, footer.
- CSS framework beserta versi.
- Icon packs, font, gambar, logo.
- CSS custom dan urutan stylesheet.
- JavaScript plugins beserta versi dan urutan load.
- DataTables, Select2, datepicker, editor, modal, chart, dan lainnya.
- Inline CSS/JS.
- Halaman print dan responsive behavior.
- Loading, empty, error, validation, success, dan permission states.
- Perubahan UI berdasarkan role dan kondisi data.

Hasilkan:
- PROJECT_AUDIT.md
- FEATURE_INVENTORY.md
- ROUTE_MAPPING.csv
- DATABASE_MAPPING.md
- AUTH_PERMISSION_MAPPING.md
- UI_INVENTORY.md
- DEPENDENCY_AUDIT.md
- RISK_REGISTER.md
- MIGRATION_PLAN.md

Semua ditulis di DOCS_PATH.

Setiap item inventory harus memiliki:
- ID stabil.
- Source file dan lokasi implementasi legacy.
- Deskripsi behavior.
- Target implementasi.
- Dependensi.
- Status.
- Bukti pengujian atau alasan belum diuji.

Jangan menganggap semua public method controller adalah endpoint aktif,
tetapi jangan melewatkan implicit routes. Verifikasi berdasarkan routing
dan penggunaan nyata.

==================================================
D. PHASE 2 — BASELINE PERILAKU DAN UI
==================================================

Jika aplikasi legacy dapat dijalankan:
- Jalankan di environment terisolasi.
- Gunakan data uji dan akun khusus.
- Rekam alur pengguna per modul dan role.
- Ambil screenshot halaman dan state penting.
- Catat URL, input, output, status code, redirect, dan efek database.
- Dokumentasikan response AJAX/API.
- Buat characterization tests untuk perilaku kritis.

Gunakan browser automation bila tersedia.

Untuk pembandingan UI, samakan:
- Viewport.
- Browser.
- Device scale.
- Data fixture.
- Role.
- Locale dan timezone.
- Font yang terpasang.
- Status loading.
- Waktu/animasi atau elemen nondeterministik.

Jika legacy tidak bisa dijalankan:
- Lakukan analisis statis terlebih dahulu.
- Catat apa yang belum dapat diverifikasi.
- Jangan mengklaim visual parity hanya dari source.
- Jangan mengarang screenshot atau hasil pengujian.

==================================================
E. PHASE 3 — ARSITEKTUR TARGET
==================================================

Pilih versi Laravel stable yang masih didukung dan kompatibel dengan
environment serta dependency yang benar-benar dibutuhkan.

Verifikasi versi dan persyaratan dari dokumentasi resmi serta package
metadata yang tersedia. Jangan mengandalkan ingatan.

Jika akses dokumentasi/network tidak tersedia, nyatakan keterbatasannya.

Default arsitektur:
- Laravel.
- Blade untuk server-rendered views.
- Theme, asset, dan plugin existing yang masih layak dipertahankan.
- Middleware untuk request/access concerns yang sesuai.
- Form Request untuk validation bila sesuai.
- Policies/Gates atau mekanisme permission yang cocok dengan model existing.
- Service/action classes untuk business logic yang kompleks.
- Eloquent atau Query Builder sesuai karakter query.
- Database existing dipertahankan terlebih dahulu.

Jangan memaksakan:
- Semua query menjadi Eloquent.
- Repository pattern untuk setiap tabel.
- Pemetaan satu hook CI3 menjadi satu middleware tanpa analisis lifecycle.
- Restrukturisasi database besar.
- Konversi seluruh frontend ke SPA.

Dokumentasikan keputusan arsitektur dan alternatif yang ditolak
dalam ARCHITECTURE_DECISIONS.md.

==================================================
F. PHASE 4 — DATABASE DAN AUTHENTICATION
==================================================

DATABASE
- Bangun koneksi menggunakan konfigurasi environment.
- Gunakan salinan database terisolasi untuk pengembangan dan pengujian.
- Pertahankan ID, relasi, nilai bisnis, dan makna field.
- Definisikan model dengan table, primary key, timestamps, casts, dan
  konfigurasi lain sesuai schema yang ditemukan.
- Pertahankan semantic query, ordering, pagination, dan null handling.
- Verifikasi decimal precision, rounding, timezone, dan date boundaries.
- Pertahankan transaksi bisnis yang harus atomik.
- Gunakan parameter binding untuk query.

Bedakan dengan tegas:
1. Jalur instalasi database kosong.
2. Jalur adopsi database existing.

Buat strategi baseline schema yang aman:
- Jangan mencoba membuat ulang tabel pada database yang sudah berisi data.
- Jangan menandai migration sebagai sudah berjalan tanpa memverifikasi
  kecocokan schema.
- Perubahan schema baru harus terpisah, terukur, dan terdokumentasi.
- Jangan menjalankan migrate:fresh, db:wipe, reset, truncate, atau
  tindakan sejenis pada database non-disposable.
- Jangan membuat down() yang berisiko menghapus tabel legacy saat
  rollback deployment.
- Rollback aplikasi dan rollback data harus dirancang secara terpisah.

AUTHENTICATION
- Pertahankan akun dan permission existing.
- Identifikasi algoritma hash lama dari kode, bukan tebakan.
- Jangan melakukan hash ulang terhadap string hash lama.
- Jika dibutuhkan, implementasikan verifier legacy yang terbatas,
  diuji, dan didokumentasikan.
- Lakukan rehash dari password plaintext hanya setelah login berhasil,
  jika skema dan strategi kompatibilitas memungkinkan.
- Evaluasi dampak rehash bila CI3 dan Laravel masih berjalan bersamaan.
- Jika hash lama tidak layak diverifikasi dengan aman, dokumentasikan
  strategi reset password dan minta keputusan.
- Jangan mengasumsikan session CI3 kompatibel dengan session Laravel.
- Tentukan apakah pengguna perlu login ulang saat cutover.
- Pertahankan role, permission, ownership, dan tenant scope.
- Jangan hanya menyembunyikan menu; enforce authorization di backend.
- Tambahkan negative tests untuk akses yang harus ditolak.

==================================================
G. PHASE 5 — MIGRASI THEME DAN VIEW
==================================================

Target UI adalah tampilan aplikasi existing, bukan redesign.

Lakukan:
- Identifikasi layout utama dan varian halaman.
- Pindahkan menjadi Blade layouts, partials, dan components jika sesuai.
- Pertahankan DOM structure dan selector yang dibutuhkan plugin.
- Pertahankan urutan CSS/JS dan plugin initialization.
- Pertahankan logo, ikon, font, gambar, warna, spacing, dan ukuran.
- Pertahankan sidebar, navbar, breadcrumb, tabel, form, modal, dan footer.
- Pertahankan responsive behavior dan print styles.
- Pertahankan label, bahasa, format angka, tanggal, mata uang, dan pesan.
- Pertahankan state aktif menu dan permission-based navigation.
- Pindahkan helper URL dan form ke mekanisme Laravel yang tepat.
- Sesuaikan form action, HTTP method, CSRF, AJAX headers, dan uploads.
- Pertahankan JSON contract yang dibutuhkan JavaScript existing.
- Evaluasi penggunaan public assets atau build pipeline berdasarkan
  kebutuhan nyata, bukan memaksakan semua asset ke bundler.
- Hindari upgrade major CSS framework/plugin bersamaan dengan migrasi,
  kecuali ada blocker keamanan atau kompatibilitas.
- Jika dependency harus diganti, catat perbedaan dan lakukan parity test.

Keamanan rendering:
- Gunakan escaped output secara default.
- Audit raw HTML yang memang dibutuhkan.
- Jangan mengganti semua output menjadi raw rendering demi kemiripan UI.
- Jangan mempertahankan XSS atau unsafe HTML sebagai behavior wajib.

Jangan memasang template dashboard generik atau UI starter kit yang
mengganti theme existing.

Buat UI_PARITY_REPORT.md berisi:
- Halaman dan state yang dibandingkan.
- Screenshot reference dan target.
- Viewport dan test data.
- Perbedaan yang ditemukan.
- Perbaikan.
- Selisih tersisa dan status persetujuan.

Jika visual diff tool tersedia, gunakan sebagai alat bantu.
Jangan menyimpulkan kesetaraan hanya dari skor pixel difference:
verifikasi juga layout, interaksi, dan state.

==================================================
H. PHASE 6 — IMPLEMENTASI PER MODUL
==================================================

Kerjakan secara vertical slice:
route -> authorization -> query/business logic -> view/API -> tests.

Urutan awal:
1. Foundation dan konfigurasi.
2. Authentication dan authorization.
3. Shared layout/theme.
4. Satu modul representatif sebagai pilot.
5. Modul lain menurut dependensi dan risiko.
6. Report, import/export, integrasi, cron, dan edge cases.

Untuk setiap modul:
- Telusuri implementasi legacy lengkap.
- Tulis acceptance criteria berdasarkan behavior yang ditemukan.
- Implementasikan kode nyata.
- Uji happy path dan failure path.
- Uji permission.
- Uji output dan efek database.
- Bandingkan UI dan interaksi.
- Perbarui mapping serta progress.
- Lanjutkan ke modul berikutnya.

Pertahankan kompatibilitas URL jika aman dan diperlukan.
Untuk URL yang berubah, dokumentasikan redirect atau compatibility route.
Jangan mempertahankan mutasi data via GET tanpa mengevaluasi risikonya.

Jangan mem-port vulnerability seperti SQL injection, command injection,
insecure upload, path traversal, IDOR, atau authorization bypass.

==================================================
I. PHASE 7 — TESTING DAN RECONCILIATION
==================================================

Buat automated tests yang sesuai:
- Unit tests untuk business rules.
- Feature/integration tests untuk route dan database.
- Authentication dan permission tests.
- API contract tests.
- Browser/E2E tests untuk alur kritis.
- Visual comparison untuk halaman representatif dan state penting.

Cakupan minimum yang relevan:
- Login/logout dan session expiration.
- CRUD.
- Validation failures.
- Search/filter/sort/pagination.
- Approval/status transition.
- Cross-role dan cross-tenant access denial.
- Upload/download.
- Export/report/print.
- Perhitungan numerik dan rounding.
- Transaction rollback pada kegagalan.
- CSRF dan escaping.
- Integrasi sandbox.
- Cron/queue.
- Missing records dan invalid IDs.
- Empty dataset dan dataset besar yang representatif.

Gunakan fixture sintetis atau data tersanitasi.
Jangan mengirim data pengguna asli ke layanan eksternal.

Untuk perbandingan database:
- Gunakan snapshot awal yang setara dan database legacy/target terpisah.
- Bandingkan perubahan record dan nilai bisnis setelah skenario sama.
- Jangan menjalankan kedua aplikasi pada database pengujian bersama
  jika efek sampingnya dapat saling memengaruhi.

Buat:
- TEST_PLAN.md
- TEST_RESULTS.md
- DATA_RECONCILIATION.md
- SECURITY_REVIEW.md

Sebutkan command yang benar-benar dijalankan dan hasil sebenarnya.
Test yang belum dijalankan harus ditandai NOT RUN.
Test gagal tidak boleh ditandai selesai.

==================================================
J. PHASE 8 — DEPLOYMENT DAN CUTOVER
==================================================

Siapkan konfigurasi sesuai deployment existing:
- Environment template tanpa secret.
- Web server dengan document root yang tepat.
- PHP extensions dan file permissions.
- Storage/public file mapping.
- Scheduler dan queue worker jika diperlukan.
- Health check.
- Log dan error monitoring.
- Cache/config deployment steps.
- Backup dan prosedur restore.
- Cutover checklist.
- Rollback runbook.

Rencana cutover harus mempertimbangkan:
- Apakah ada maintenance window.
- Penghentian atau pengaturan penulisan data selama transisi.
- Sinkronisasi perubahan data dan file sejak snapshot.
- Nasib session aktif.
- Kompatibilitas password.
- Cron/worker ganda.
- Webhook dan endpoint eksternal.
- Verifikasi setelah switch.
- Risiko rollback jika Laravel sudah menulis data baru.

Jangan menganggap pergantian DNS atau symlink saja cukup untuk rollback.
Jelaskan bagaimana data baru tetap aman.

Jangan deploy/cutover produksi tanpa persetujuan eksplisit.

==================================================
K. DOKUMENTASI DAN KONTINUITAS AGENT
==================================================

Pertahankan dokumen berikut:
- MIGRATION_STATUS.md
- NEXT_STEPS.md
- BLOCKERS.md
- CHANGELOG.md
- ARCHITECTURE_DECISIONS.md
- LOCAL_SETUP.md
- DEPLOYMENT.md
- CUTOVER_CHECKLIST.md
- ROLLBACK.md
- FINAL_MIGRATION_REPORT.md

MIGRATION_STATUS.md harus memuat:
- Modul.
- Inventory ID.
- Legacy source.
- Target files.
- Implemented.
- Tested.
- UI verified.
- Remaining issues.

Gunakan status yang jelas:
NOT_STARTED
IN_PROGRESS
IMPLEMENTED
VERIFIED
BLOCKED

IMPLEMENTED tidak sama dengan VERIFIED.

Setiap checkpoint harus mencatat:
- Pekerjaan yang selesai.
- File yang berubah.
- Test yang dijalankan dan hasil.
- Masalah tersisa.
- Langkah berikutnya.

Jika context window mendekati batas:
- Simpan checkpoint lengkap.
- Jangan mengklaim selesai.
- Tulis instruksi melanjutkan yang spesifik.
- Saat melanjutkan, baca status dan verifikasi repository sebelum bekerja.

Jangan menimpa perubahan pengguna yang tidak dibuat oleh Anda.

==================================================
L. DEFINITION OF DONE
==================================================

Migrasi hanya boleh disebut selesai jika:
1. Seluruh item inventory sudah dipetakan.
2. Seluruh fitur in-scope sudah diimplementasikan.
3. Tidak ada fitur yang hilang diam-diam.
4. Behavior kritis telah diuji.
5. Permission positif dan negatif telah diuji.
6. Database dan file direkonsiliasi sesuai scope.
7. Akun existing memiliki jalur autentikasi yang telah diverifikasi.
8. Theme dan interaksi UI telah dibandingkan terhadap baseline.
9. Tidak ada blocker kritis yang belum diselesaikan.
10. Environment dapat dibangun ulang dari dokumentasi.
11. Cutover dan rollback terdokumentasi.
12. Seluruh keterbatasan dan perubahan perilaku dinyatakan terbuka.

Jangan menyatakan "100% selesai" bila:
- Ada route/modul yang belum diperiksa.
- Ada test yang belum dijalankan.
- Ada UI yang belum dibandingkan.
- Database tidak tersedia untuk verifikasi.
- Hanya homepage/login yang sudah berjalan.
- Integrasi nyata belum dapat diverifikasi.

Jika ada blocker eksternal, laporkan sebagai "implementasi parsial /
menunggu verifikasi", lengkap dengan dampak dan cara melanjutkan.

==================================================
M. MULAI SEKARANG
==================================================

1. Periksa workspace dan repository.
2. Identifikasi environment dan tool yang tersedia.
3. Audit aplikasi legacy.
4. Buat inventory, baseline, mapping, dan migration plan.
5. Tampilkan ringkasan scope, risiko utama, dan keputusan arsitektur.
6. Lanjutkan implementasi aman pada local/sandbox tanpa menunggu
   persetujuan untuk setiap file atau setiap modul.
7. Berhenti dan minta persetujuan hanya untuk keputusan berisiko,
   perubahan scope material, atau tindakan eksternal/produksi.
8. Lakukan implementasi dan verifikasi sampai seluruh scope selesai,
   atau sampai benar-benar terblokir.
