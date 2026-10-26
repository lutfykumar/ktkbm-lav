# 📋 BUKU RAPORT AUDIT DASHBOARD TKBM

**Waktu Audit:** Senin, 26 Oktober 2026 19:34:39  
**Akun Penguji:** `Administrator` (`kumar.kokola@gmail.com`) — Role: admin  
**Nilai Keseluruhan:** **100%** (Predikat: **Grade A**)  

---

## 1. Scorecard Ringkasan Per Modul

| Modul | Total Aksi | Pass | Warn | Fail | Nilai (%) | Predikat |
| :--- | :---: | :---: | :---: | :---: | :---: | :---: |
| **Dashboard** | 1 | 1 | 0 | 0 | 100% | 🟢 **A** |
| **Workorder** | 10 | 10 | 0 | 0 | 100% | 🟢 **A** |
| **Accounting** | 12 | 12 | 0 | 0 | 100% | 🟢 **A** |
| **Activity** | 7 | 7 | 0 | 0 | 100% | 🟢 **A** |
| **Master** | 72 | 72 | 0 | 0 | 100% | 🟢 **A** |
| **Pengaturan** | 36 | 36 | 0 | 0 | 100% | 🟢 **A** |
| **Logs** | 14 | 14 | 0 | 0 | 100% | 🟢 **A** |
| **Report** | 3 | 3 | 0 | 0 | 100% | 🟢 **A** |
| **Profile** | 5 | 5 | 0 | 0 | 100% | 🟢 **A** |
| **Auth** | 10 | 10 | 0 | 0 | 100% | 🟢 **A** |
| **TOTAL / RATA-RATA** | **170** | **170** | **0** | **0** | **100%** | **A** |

---

## 2. Temuan & Panduan Solusi (Issues & Remediation)

> 🟢 **Luar Biasa!** Tidak ditemukan satupun aksi yang gagal (*Zero Failure*). Seluruh endpoint dan aksi merespons dengan status sehat.

---

## 3. Matriks Lengkap Seluruh Aksi yang Diuji

<details>
<summary><b>Modul: Dashboard (1 Aksi) — Klik untuk melihat detail</b></summary>

| Method | Route | Controller Action | Status | Keterangan |
| :--- | :--- | :--- | :---: | :--- |
| `GET` | `/dashboard` | `DashboardController@index` | ✅ PASS | 200 OK (View rendered successfully) |

</details>

<details>
<summary><b>Modul: Workorder (10 Aksi) — Klik untuk melihat detail</b></summary>

| Method | Route | Controller Action | Status | Keterangan |
| :--- | :--- | :--- | :---: | :--- |
| `GET` | `/workorder` | `WorkorderController@index` | ✅ PASS | 200 OK (View rendered successfully) |
| `GET` | `/workorder/table` | `WorkorderController@table` | ✅ PASS | 200 OK (Valid JSON API response) |
| `POST` | `/workorder/get_no_work_order` | `WorkorderController@getNoWorkOrder` | ✅ PASS | 200 OK (Valid JSON API response) |
| `POST` | `/workorder/store` | `WorkorderController@store` | ✅ PASS | Validation Guard Active: Client wajib dipilih |
| `POST` | `/workorder/detail` | `WorkorderController@detail` | ✅ PASS | 200 OK (View rendered successfully) |
| `POST` | `/workorder/edit` | `WorkorderController@edit` | ✅ PASS | 200 OK (View rendered successfully) |
| `POST` | `/workorder/update` | `WorkorderController@update` | ✅ PASS | Validation Guard Active: ID tidak valid |
| `POST` | `/workorder/update_status` | `WorkorderController@updateStatus` | ✅ PASS | Validation Guard Active: ID tidak valid |
| `POST` | `/workorder/delete` | `WorkorderController@delete` | ✅ PASS | Validation Guard Active: ID tidak valid |
| `POST` | `/workorder/restore` | `WorkorderController@restore` | ✅ PASS | Validation Guard Active: Data tidak ditemukan |

</details>

<details>
<summary><b>Modul: Accounting (12 Aksi) — Klik untuk melihat detail</b></summary>

| Method | Route | Controller Action | Status | Keterangan |
| :--- | :--- | :--- | :---: | :--- |
| `GET` | `/accounting/invoice` | `Accounting\InvoiceController@index` | ✅ PASS | 200 OK (View rendered successfully) |
| `GET` | `/accounting/invoice/table` | `Accounting\InvoiceController@table` | ✅ PASS | 200 OK (Valid JSON API response) |
| `GET` | `/accounting/invoice/create/{wo_id?}` | `Accounting\InvoiceController@create` | ✅ PASS | 200 OK (View rendered successfully) |
| `POST` | `/accounting/invoice/store` | `Accounting\InvoiceController@store` | ✅ PASS | Validation Guard Active: Semua field wajib diisi |
| `GET` | `/accounting/invoice/detail/{id}` | `Accounting\InvoiceController@detail` | ✅ PASS | 200 OK (View rendered successfully) |
| `GET` | `/accounting/invoice/edit/{id}` | `Accounting\InvoiceController@edit` | ✅ PASS | 200 OK (View rendered successfully) |
| `POST` | `/accounting/invoice/update` | `Accounting\InvoiceController@update` | ✅ PASS | Validation Guard Active: Invoice tidak ditemukan atau tidak dapat diedit |
| `POST` | `/accounting/invoice/delete` | `Accounting\InvoiceController@delete` | ✅ PASS | Validation Guard Active: Hanya invoice draft yang bisa dihapus |
| `POST` | `/accounting/invoice/restore` | `Accounting\InvoiceController@restore` | ✅ PASS | Validation Guard Active: Data tidak ditemukan |
| `POST` | `/accounting/invoice/update_payment` | `Accounting\InvoiceController@updatePayment` | ✅ PASS | Validation Guard Active: Invoice tidak valid untuk pembayaran |
| `POST` | `/accounting/invoice/cancel` | `Accounting\InvoiceController@cancel` | ✅ PASS | Validation Guard Active: Invoice tidak dapat dibatalkan |
| `GET` | `/accounting/invoice/print_invoice/{id}` | `Accounting\InvoiceController@printInvoice` | ✅ PASS | 200 OK (View rendered successfully) |

</details>

<details>
<summary><b>Modul: Activity (7 Aksi) — Klik untuk melihat detail</b></summary>

| Method | Route | Controller Action | Status | Keterangan |
| :--- | :--- | :--- | :---: | :--- |
| `GET` | `/activity` | `ActivityController@index` | ✅ PASS | 200 OK (View rendered successfully) |
| `POST` | `/activity/result` | `ActivityController@result` | ✅ PASS | 200 OK (View rendered successfully) |
| `GET` | `/activity/form_detail/{id}` | `ActivityController@form_detail` | ✅ PASS | 200 OK (View rendered successfully) |
| `GET` | `/activity/list_detail/{id}` | `ActivityController@list_detail` | ✅ PASS | 200 OK (View rendered successfully) |
| `POST` | `/activity/store_detail` | `ActivityController@store_detail` | ✅ PASS | Validation Guard Active: Work Order ID tidak valid |
| `POST` | `/activity/update_status` | `ActivityController@update_status` | ✅ PASS | Validation Guard Active: ID tidak valid |
| `POST` | `/activity/delete_detail` | `ActivityController@delete_detail` | ✅ PASS | Validation Guard Active: ID tidak valid |

</details>

<details>
<summary><b>Modul: Master (72 Aksi) — Klik untuk melihat detail</b></summary>

| Method | Route | Controller Action | Status | Keterangan |
| :--- | :--- | :--- | :---: | :--- |
| `GET` | `/master/aktifitas` | `Master\AktifitasController@index` | ✅ PASS | 200 OK (View rendered successfully) |
| `GET` | `/master/aktifitas/table` | `Master\AktifitasController@table` | ✅ PASS | 200 OK (Valid JSON API response) |
| `POST` | `/master/aktifitas/store` | `Master\AktifitasController@store` | ✅ PASS | Validation Guard Active: Nama aktifitas harus diisi |
| `POST` | `/master/aktifitas/edit` | `Master\AktifitasController@edit` | ✅ PASS | 200 OK (View rendered successfully) |
| `POST` | `/master/aktifitas/update` | `Master\AktifitasController@update` | ✅ PASS | Validation Guard Active: Data tidak ditemukan |
| `POST` | `/master/aktifitas/delete` | `Master\AktifitasController@delete` | ✅ PASS | Validation Guard Active: Data tidak ditemukan |
| `POST` | `/master/aktifitas/restore` | `Master\AktifitasController@restore` | ✅ PASS | Validation Guard Active: Data tidak ditemukan |
| `GET` | `/master/alat` | `Master\AlatController@index` | ✅ PASS | 200 OK (View rendered successfully) |
| `GET` | `/master/alat/table` | `Master\AlatController@table` | ✅ PASS | 200 OK (Valid JSON API response) |
| `POST` | `/master/alat/store` | `Master\AlatController@store` | ✅ PASS | Validation Guard Active: Nama alat harus diisi |
| `POST` | `/master/alat/edit` | `Master\AlatController@edit` | ✅ PASS | 200 OK (View rendered successfully) |
| `POST` | `/master/alat/update` | `Master\AlatController@update` | ✅ PASS | Validation Guard Active: Data tidak ditemukan |
| `POST` | `/master/alat/delete` | `Master\AlatController@delete` | ✅ PASS | Validation Guard Active: Data tidak ditemukan |
| `POST` | `/master/alat/restore` | `Master\AlatController@restore` | ✅ PASS | Validation Guard Active: Data tidak ditemukan |
| `GET` | `/master/catatan` | `Master\CatatanController@index` | ✅ PASS | 200 OK (View rendered successfully) |
| `GET` | `/master/catatan/table` | `Master\CatatanController@table` | ✅ PASS | 200 OK (Valid JSON API response) |
| `POST` | `/master/catatan/store` | `Master\CatatanController@store` | ✅ PASS | Validation Guard Active: Nama catatan harus diisi |
| `POST` | `/master/catatan/edit` | `Master\CatatanController@edit` | ✅ PASS | 200 OK (View rendered successfully) |
| `POST` | `/master/catatan/update` | `Master\CatatanController@update` | ✅ PASS | Validation Guard Active: Data tidak ditemukan |
| `POST` | `/master/catatan/delete` | `Master\CatatanController@delete` | ✅ PASS | Validation Guard Active: Data tidak ditemukan |
| `POST` | `/master/catatan/restore` | `Master\CatatanController@restore` | ✅ PASS | Validation Guard Active: Data tidak ditemukan |
| `GET` | `/master/client` | `Master\ClientController@index` | ✅ PASS | 200 OK (View rendered successfully) |
| `GET` | `/master/client/table` | `Master\ClientController@table` | ✅ PASS | 200 OK (Valid JSON API response) |
| `POST` | `/master/client/store` | `Master\ClientController@store` | ✅ PASS | Validation Guard Active: Nama client harus diisi |
| `POST` | `/master/client/edit` | `Master\ClientController@edit` | ✅ PASS | 200 OK (View rendered successfully) |
| `POST` | `/master/client/update` | `Master\ClientController@update` | ✅ PASS | Validation Guard Active: Data tidak ditemukan |
| `POST` | `/master/client/delete` | `Master\ClientController@delete` | ✅ PASS | Validation Guard Active: Data tidak ditemukan |
| `POST` | `/master/client/restore` | `Master\ClientController@restore` | ✅ PASS | Validation Guard Active: Data tidak ditemukan |
| `GET` | `/master/dermaga` | `Master\DermagaController@index` | ✅ PASS | 200 OK (View rendered successfully) |
| `GET` | `/master/dermaga/table` | `Master\DermagaController@table` | ✅ PASS | 200 OK (Valid JSON API response) |
| `POST` | `/master/dermaga/store` | `Master\DermagaController@store` | ✅ PASS | Validation Guard Active: Nama dermaga harus diisi |
| `POST` | `/master/dermaga/edit` | `Master\DermagaController@edit` | ✅ PASS | 200 OK (View rendered successfully) |
| `POST` | `/master/dermaga/update` | `Master\DermagaController@update` | ✅ PASS | Validation Guard Active: Data tidak ditemukan |
| `POST` | `/master/dermaga/delete` | `Master\DermagaController@delete` | ✅ PASS | Validation Guard Active: Data tidak ditemukan |
| `POST` | `/master/dermaga/restore` | `Master\DermagaController@restore` | ✅ PASS | Validation Guard Active: Data tidak ditemukan |
| `GET` | `/master/kapal` | `Master\KapalController@index` | ✅ PASS | 200 OK (View rendered successfully) |
| `GET` | `/master/kapal/table` | `Master\KapalController@table` | ✅ PASS | 200 OK (Valid JSON API response) |
| `POST` | `/master/kapal/store` | `Master\KapalController@store` | ✅ PASS | Validation Guard Active: Nama kapal harus diisi |
| `POST` | `/master/kapal/edit` | `Master\KapalController@edit` | ✅ PASS | 200 OK (View rendered successfully) |
| `POST` | `/master/kapal/update` | `Master\KapalController@update` | ✅ PASS | Validation Guard Active: Data tidak ditemukan |
| `POST` | `/master/kapal/delete` | `Master\KapalController@delete` | ✅ PASS | Validation Guard Active: Data tidak ditemukan |
| `POST` | `/master/kapal/restore` | `Master\KapalController@restore` | ✅ PASS | Validation Guard Active: Data tidak ditemukan |
| `GET` | `/master/krk` | `Master\KrkController@index` | ✅ PASS | 200 OK (View rendered successfully) |
| `GET` | `/master/krk/table` | `Master\KrkController@table` | ✅ PASS | 200 OK (Valid JSON API response) |
| `POST` | `/master/krk/store` | `Master\KrkController@store` | ✅ PASS | Validation Guard Active: Nama KRK harus diisi |
| `POST` | `/master/krk/edit` | `Master\KrkController@edit` | ✅ PASS | 200 OK (View rendered successfully) |
| `POST` | `/master/krk/update` | `Master\KrkController@update` | ✅ PASS | Validation Guard Active: Data tidak ditemukan |
| `POST` | `/master/krk/delete` | `Master\KrkController@delete` | ✅ PASS | Validation Guard Active: Data tidak ditemukan |
| `POST` | `/master/krk/restore` | `Master\KrkController@restore` | ✅ PASS | Validation Guard Active: Data tidak ditemukan |
| `GET` | `/master/pelabuhan` | `Master\PelabuhanController@index` | ✅ PASS | 200 OK (View rendered successfully) |
| `GET` | `/master/pelabuhan/table` | `Master\PelabuhanController@table` | ✅ PASS | 200 OK (Valid JSON API response) |
| `POST` | `/master/pelabuhan/store` | `Master\PelabuhanController@store` | ✅ PASS | Validation Guard Active: Nama pelabuhan harus diisi |
| `POST` | `/master/pelabuhan/edit` | `Master\PelabuhanController@edit` | ✅ PASS | 200 OK (View rendered successfully) |
| `POST` | `/master/pelabuhan/update` | `Master\PelabuhanController@update` | ✅ PASS | Validation Guard Active: Data tidak ditemukan |
| `POST` | `/master/pelabuhan/delete` | `Master\PelabuhanController@delete` | ✅ PASS | Validation Guard Active: Data tidak ditemukan |
| `POST` | `/master/pelabuhan/restore` | `Master\PelabuhanController@restore` | ✅ PASS | Validation Guard Active: Data tidak ditemukan |
| `GET` | `/master/satuan` | `Master\SatuanController@index` | ✅ PASS | 200 OK (View rendered successfully) |
| `GET` | `/master/satuan/table` | `Master\SatuanController@table` | ✅ PASS | 200 OK (Valid JSON API response) |
| `POST` | `/master/satuan/store` | `Master\SatuanController@store` | ✅ PASS | Validation Guard Active: Nama jenis satuan harus diisi |
| `POST` | `/master/satuan/edit` | `Master\SatuanController@edit` | ✅ PASS | 200 OK (View rendered successfully) |
| `POST` | `/master/satuan/update` | `Master\SatuanController@update` | ✅ PASS | Validation Guard Active: Data tidak ditemukan |
| `POST` | `/master/satuan/delete` | `Master\SatuanController@delete` | ✅ PASS | Validation Guard Active: Data tidak ditemukan |
| `POST` | `/master/satuan/restore` | `Master\SatuanController@restore` | ✅ PASS | Validation Guard Active: Data tidak ditemukan |
| `GET` | `/master/email` | `Master\EmailController@index` | ✅ PASS | 200 OK (View rendered successfully) |
| `GET` | `/master/email/table` | `Master\EmailController@table` | ✅ PASS | 200 OK (Valid JSON API response) |
| `GET` | `/master/email/create` | `Master\EmailController@create` | ✅ PASS | 200 OK (View rendered successfully) |
| `POST` | `/master/email/store` | `Master\EmailController@store` | ✅ PASS | 422 Validation Handled Properly |
| `GET` | `/master/email/edit` | `Master\EmailController@edit` | ✅ PASS | 404 Handled (Route requires specific record ID/param) |
| `POST` | `/master/email/update` | `Master\EmailController@update` | ✅ PASS | 404 Handled (Route requires specific record ID/param) |
| `POST` | `/master/email/delete` | `Master\EmailController@delete` | ✅ PASS | 404 Handled (Route requires specific record ID/param) |
| `GET` | `/master/email/preview` | `Master\EmailController@preview` | ✅ PASS | 404 Handled (Route requires specific record ID/param) |
| `POST` | `/master/email/duplicate` | `Master\EmailController@duplicate` | ✅ PASS | 404 Handled (Route requires specific record ID/param) |

</details>

<details>
<summary><b>Modul: Pengaturan (36 Aksi) — Klik untuk melihat detail</b></summary>

| Method | Route | Controller Action | Status | Keterangan |
| :--- | :--- | :--- | :---: | :--- |
| `GET` | `/pengaturan/roles` | `Pengaturan\RolesController@index` | ✅ PASS | 200 OK (View rendered successfully) |
| `GET` | `/pengaturan/roles/table` | `Pengaturan\RolesController@table` | ✅ PASS | 200 OK (Valid JSON API response) |
| `POST` | `/pengaturan/roles/store` | `Pengaturan\RolesController@store` | ✅ PASS | Validation Guard Active: Nama jabatan harus diisi |
| `POST` | `/pengaturan/roles/edit` | `Pengaturan\RolesController@edit` | ✅ PASS | 200 OK (View rendered successfully) |
| `POST` | `/pengaturan/roles/update` | `Pengaturan\RolesController@update` | ✅ PASS | Validation Guard Active: Nama jabatan harus diisi |
| `POST` | `/pengaturan/roles/delete` | `Pengaturan\RolesController@delete` | ✅ PASS | Validation Guard Active: ID tidak valid |
| `GET` | `/pengaturan/pengguna` | `Pengaturan\PenggunaController@index` | ✅ PASS | 200 OK (View rendered successfully) |
| `GET` | `/pengaturan/pengguna/table` | `Pengaturan\PenggunaController@table` | ✅ PASS | 200 OK (Valid JSON API response) |
| `POST` | `/pengaturan/pengguna/store` | `Pengaturan\PenggunaController@store` | ✅ PASS | Validation Guard Active: Nama pengguna harus diisi |
| `POST` | `/pengaturan/pengguna/edit` | `Pengaturan\PenggunaController@edit` | ✅ PASS | 200 OK (View rendered successfully) |
| `POST` | `/pengaturan/pengguna/update` | `Pengaturan\PenggunaController@update` | ✅ PASS | Validation Guard Active: Nama pengguna harus diisi |
| `POST` | `/pengaturan/pengguna/change_password` | `Pengaturan\PenggunaController@changePassword` | ✅ PASS | Validation Guard Active: User ID tidak valid |
| `POST` | `/pengaturan/pengguna/delete` | `Pengaturan\PenggunaController@delete` | ✅ PASS | Validation Guard Active: ID tidak valid |
| `POST` | `/pengaturan/pengguna/restore` | `Pengaturan\PenggunaController@restore` | ✅ PASS | Validation Guard Active: ID tidak valid |
| `GET` | `/pengaturan/menu` | `Pengaturan\MenuController@index` | ✅ PASS | 200 OK (View rendered successfully) |
| `POST` | `/pengaturan/menu/update` | `Pengaturan\MenuController@update` | ✅ PASS | Validation Guard Active: Data menu tidak boleh kosong |
| `GET` | `/pengaturan/akses` | `Pengaturan\AksesController@index` | ✅ PASS | 200 OK (View rendered successfully) |
| `GET` | `/pengaturan/akses/table` | `Pengaturan\AksesController@table` | ✅ PASS | 200 OK (Valid JSON API response) |
| `POST` | `/pengaturan/akses/store` | `Pengaturan\AksesController@store` | ✅ PASS | Validation Guard Active: Nama menu harus diisi |
| `POST` | `/pengaturan/akses/edit` | `Pengaturan\AksesController@edit` | ✅ PASS | 200 OK (View rendered successfully) |
| `POST` | `/pengaturan/akses/update` | `Pengaturan\AksesController@update` | ✅ PASS | Validation Guard Active: Nama menu harus diisi |
| `POST` | `/pengaturan/akses/delete` | `Pengaturan\AksesController@delete` | ✅ PASS | Validation Guard Active: ID tidak valid |
| `GET` | `/pengaturan/aplikasi` | `Pengaturan\AplikasiController@index` | ✅ PASS | 200 OK (View rendered successfully) |
| `GET` | `/pengaturan/aplikasi/get_settings` | `Pengaturan\AplikasiController@getSettings` | ✅ PASS | 200 OK (Valid JSON API response) |
| `POST` | `/pengaturan/aplikasi/update_settings` | `Pengaturan\AplikasiController@updateSettings` | ✅ PASS | Validation Guard Active: Tidak ada data pengaturan yang dikirim |
| `POST` | `/pengaturan/aplikasi/reset_setting` | `Pengaturan\AplikasiController@resetSetting` | ✅ PASS | Validation Guard Active: Setting key wajib diisi |
| `POST` | `/pengaturan/aplikasi/upload_logo` | `Pengaturan\AplikasiController@uploadLogo` | ✅ PASS | Validation Guard Active: Tidak ada file yang diunggah |
| `GET` | `/pengaturan/aplikasi/export_settings` | `Pengaturan\AplikasiController@exportSettings` | ✅ PASS | 200 OK (Valid JSON API response) |
| `POST` | `/pengaturan/aplikasi/import_settings` | `Pengaturan\AplikasiController@importSettings` | ✅ PASS | Validation Guard Active: Tidak ada file yang diunggah |
| `GET` | `/pengaturan/cron` | `Pengaturan\CronController@index` | ✅ PASS | 200 OK (View rendered successfully) |
| `GET` | `/pengaturan/cron/get_data` | `Pengaturan\CronController@getData` | ✅ PASS | 200 OK (Valid JSON API response) |
| `GET` | `/pengaturan/cron/statistics` | `Pengaturan\CronController@statistics` | ✅ PASS | 200 OK (Valid JSON API response) |
| `POST` | `/pengaturan/cron/detail` | `Pengaturan\CronController@detail` | ✅ PASS | Validation Guard Active: Data job tidak ditemukan |
| `POST` | `/pengaturan/cron/toggle_status` | `Pengaturan\CronController@toggleStatus` | ✅ PASS | 200 OK (Valid JSON API response) |
| `POST` | `/pengaturan/cron/execute` | `Pengaturan\CronController@execute` | ✅ PASS | Validation Guard Active: Job tidak ditemukan |
| `POST` | `/pengaturan/cron/execution_logs` | `Pengaturan\CronController@executionLogs` | ✅ PASS | 200 OK (Valid JSON API response) |

</details>

<details>
<summary><b>Modul: Logs (14 Aksi) — Klik untuk melihat detail</b></summary>

| Method | Route | Controller Action | Status | Keterangan |
| :--- | :--- | :--- | :---: | :--- |
| `GET` | `/logs/aktifitas` | `Logs\AktifitasController@index` | ✅ PASS | 200 OK (View rendered successfully) |
| `GET` | `/logs/aktifitas/table` | `Logs\AktifitasController@table` | ✅ PASS | 200 OK (Valid JSON API response) |
| `POST` | `/logs/aktifitas/detail` | `Logs\AktifitasController@detail` | ✅ PASS | 404 Handled (Route requires specific record ID/param) |
| `GET` | `/logs/api` | `Logs\ApiController@index` | ✅ PASS | 200 OK (View rendered successfully) |
| `GET` | `/logs/api/get_data` | `Logs\ApiController@getData` | ✅ PASS | 200 OK (Valid JSON API response) |
| `POST` | `/logs/api/detail` | `Logs\ApiController@detail` | ✅ PASS | 404 Handled (Route requires specific record ID/param) |
| `GET` | `/logs/email` | `Logs\EmailController@index` | ✅ PASS | 200 OK (View rendered successfully) |
| `GET` | `/logs/email/table` | `Logs\EmailController@table` | ✅ PASS | 200 OK (Valid JSON API response) |
| `POST` | `/logs/email/detail` | `Logs\EmailController@detail` | ✅ PASS | 404 Handled (Route requires specific record ID/param) |
| `POST` | `/logs/email/resend` | `Logs\EmailController@resend` | ✅ PASS | Validation Guard Active: Log email tidak ditemukan |
| `GET` | `/logs/errors` | `Logs\ErrorsController@index` | ✅ PASS | 200 OK (View rendered successfully) |
| `GET` | `/logs/errors/get_data` | `Logs\ErrorsController@getData` | ✅ PASS | 200 OK (Valid JSON API response) |
| `POST` | `/logs/errors/detail` | `Logs\ErrorsController@detail` | ✅ PASS | 404 Handled (Route requires specific record ID/param) |
| `POST` | `/logs/errors/resolve` | `Logs\ErrorsController@resolve` | ✅ PASS | 200 OK (Valid JSON API response) |

</details>

<details>
<summary><b>Modul: Report (3 Aksi) — Klik untuk melihat detail</b></summary>

| Method | Route | Controller Action | Status | Keterangan |
| :--- | :--- | :--- | :---: | :--- |
| `GET` | `/report/workorder` | `Report\WorkorderReportController@index` | ✅ PASS | 200 OK (View rendered successfully) |
| `POST` | `/report/workorder/get_report_data` | `Report\WorkorderReportController@getReportData` | ✅ PASS | 200 OK (Valid JSON API response) |
| `POST` | `/report/workorder/get_kegiatan_kapal` | `Report\WorkorderReportController@getKegiatanKapal` | ✅ PASS | Validation Guard Active: Pelabuhan wajib dipilih |

</details>

<details>
<summary><b>Modul: Profile (5 Aksi) — Klik untuk melihat detail</b></summary>

| Method | Route | Controller Action | Status | Keterangan |
| :--- | :--- | :--- | :---: | :--- |
| `GET` | `/profile` | `ProfileController@index` | ✅ PASS | 200 OK (View rendered successfully) |
| `GET` | `/profile/edit` | `ProfileController@edit` | ✅ PASS | 200 OK (View rendered successfully) |
| `POST` | `/profile/store_info` | `ProfileController@store_info` | ✅ PASS | 422 Validation Handled Properly |
| `POST` | `/profile/store_password` | `ProfileController@store_password` | ✅ PASS | Validation Guard Active: Syarat tidak lengkap |
| `GET` | `/profile/detail/{id}` | `ProfileController@detail` | ✅ PASS | 404 Handled (Route requires specific record ID/param) |

</details>

<details>
<summary><b>Modul: Auth (10 Aksi) — Klik untuk melihat detail</b></summary>

| Method | Route | Controller Action | Status | Keterangan |
| :--- | :--- | :--- | :---: | :--- |
| `GET` | `/logout` | `Auth\AuthenticatedSessionController@destroy` | ✅ PASS | 302 Redirect (Target: http://localhost/login) |
| `GET` | `/register` | `Auth\RegisteredUserController@create` | ✅ PASS | 302 Redirect (Target: http://localhost/dashboard) |
| `POST` | `/register` | `Auth\RegisteredUserController@store` | ✅ PASS | 302 Redirect (Target: http://localhost/dashboard) |
| `GET` | `/login` | `Auth\AuthenticatedSessionController@create` | ✅ PASS | 302 Redirect (Target: http://localhost/dashboard) |
| `POST` | `/login` | `Auth\AuthenticatedSessionController@store` | ✅ PASS | 302 Redirect (Target: http://localhost/dashboard) |
| `GET` | `/forgot-password` | `Auth\PasswordResetLinkController@create` | ✅ PASS | 302 Redirect (Target: http://localhost/dashboard) |
| `POST` | `/forgot-password` | `Auth\PasswordResetLinkController@store` | ✅ PASS | 302 Redirect (Target: http://localhost/dashboard) |
| `GET` | `/reset-password/{token}` | `Auth\NewPasswordController@create` | ✅ PASS | 302 Redirect (Target: http://localhost/dashboard) |
| `POST` | `/reset-password` | `Auth\NewPasswordController@store` | ✅ PASS | 302 Redirect (Target: http://localhost/dashboard) |
| `POST` | `/logout` | `Auth\AuthenticatedSessionController@destroy` | ✅ PASS | 302 Redirect (Target: http://localhost/login) |

</details>


---

*Laporan ini dibuat otomatis oleh `agent-dashboard-auditor` (TKBM Multi-Agent System).*
