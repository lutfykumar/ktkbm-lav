@extends('layouts.app')

@section('title', $title ?? 'Panduan Pengguna')
@section('title_header', 'Panduan Pengguna')
@section('title_parent', 'Dokumentasi')

@section('content')
<div class="row">
  <div class="col-lg-12">
    <!-- Header Banner & Interactive Search -->
    <div class="card bg-primary-subtle border-0 overflow-hidden mb-4 shadow-sm">
      <div class="card-body p-4">
        <div class="row align-items-center">
          <div class="col-lg-8">
            <span class="badge bg-primary text-white mb-2 fs-11 px-2 py-1"><i class="fal fa-book-reader me-1"></i> Dokumentasi Resmi & Alur Kerja</span>
            <h4 class="text-primary fw-bold mb-2">Pusat Panduan Sistem Informasi TKBM Pelabuhan</h4>
            <p class="text-muted mb-3 fs-13">Petunjuk komprehensif alur operasional, manajemen Work Order 4 tahap (Maker-Checker), pencatatan aktifitas & ransum, integrasi mobile app, master data hub, hingga pencetakan rekap & invoice.</p>
            
            <!-- Quick Search Bar -->
            <div class="position-relative mt-2" style="max-width: 580px;">
              <div class="input-group">
                <span class="input-group-text bg-white border-0 text-muted"><i class="fal fa-search"></i></span>
                <input type="text" id="searchPanduan" class="form-control border-0 py-2 fs-13" placeholder="Ketik topik panduan (misal: review, ransum, maker-checker, backdate, invoice)...">
                <button class="btn btn-primary px-3 fs-13" type="button" id="btnResetSearch"><i class="fal fa-times"></i> Reset</button>
              </div>
              <!-- Quick search tags -->
              <div class="d-flex flex-wrap gap-1 mt-2 align-items-center">
                <span class="text-muted fs-11 me-1"><i class="fal fa-tags"></i> Topik Populer:</span>
                <a href="javascript:void(0)" class="badge bg-white text-primary border quick-tag" data-query="maker-checker">#Maker-Checker</a>
                <a href="javascript:void(0)" class="badge bg-white text-primary border quick-tag" data-query="review">#Review Admin</a>
                <a href="javascript:void(0)" class="badge bg-white text-primary border quick-tag" data-query="ransum">#Ransum</a>
                <a href="javascript:void(0)" class="badge bg-white text-primary border quick-tag" data-query="backdate">#Backdate WO</a>
                <a href="javascript:void(0)" class="badge bg-white text-primary border quick-tag" data-query="rekap kegiatan kapal">#Rekap Kapal</a>
                <a href="javascript:void(0)" class="badge bg-white text-primary border quick-tag" data-query="data master">#Data Master Hub</a>
                <a href="javascript:void(0)" class="badge bg-white text-primary border quick-tag" data-query="invoice">#Invoice</a>
                <a href="javascript:void(0)" class="badge bg-white text-primary border quick-tag" data-query="offline">#Mode Offline</a>
              </div>
            </div>
          </div>
          <div class="col-lg-4 d-none d-lg-block text-center">
            <img src="{{ asset('assets/images/interaction_design.svg') }}" alt="Panduan TKBM" style="max-height: 140px;" class="img-fluid drop-shadow">
          </div>
        </div>
      </div>
    </div>

    <!-- Main Navigation Tabs (Primary Theme) -->
    <div class="d-flex mb-3">
      <ul class="nav nav-pills nav-customs gap-2 flex-grow-1" id="mainPanduanTabs" role="tablist">
        <li class="nav-item">
          <a class="nav-link fs-14 active" data-bs-toggle="tab" href="#general-tab" role="tab">
            <i class="fal fa-project-diagram me-1"></i> <span>Gambaran Umum & Alur Maker-Checker</span>
          </a>
        </li>
        <li class="nav-item">
          <a class="nav-link fs-14" data-bs-toggle="tab" href="#admin-tab" role="tab">
            <i class="fal fa-laptop me-1"></i> <span>Panduan Admin (Web Panel)</span>
          </a>
        </li>
        <li class="nav-item">
          <a class="nav-link fs-14" data-bs-toggle="tab" href="#pengawas-tab" role="tab">
            <i class="fal fa-mobile-android me-1"></i> <span>Panduan Pengawas (Mobile App)</span>
          </a>
        </li>
      </ul>
    </div>

    <!-- Tab Contents Container -->
    <div class="tab-content text-muted" id="panduanContentArea">

      <!-- ========================================== -->
      <!-- TAB 1: GAMBARAN UMUM & ALUR MAKER-CHECKER -->
      <!-- ========================================== -->
      <div class="tab-pane active" id="general-tab" role="tabpanel">
        <div class="row">
          <div class="col-xl-8">
            <!-- Pendahuluan Card -->
            <div class="card shadow-sm border-0 mb-3 search-target">
              <div class="card-header border-bottom bg-transparent d-flex align-items-center">
                <div class="avatar-xs me-2">
                  <span class="avatar-title bg-primary-subtle text-primary rounded-circle"><i class="fal fa-info-circle"></i></span>
                </div>
                <h5 class="card-title mb-0 flex-grow-1">Pendahuluan Sistem Informasi TKBM</h5>
              </div>
              <div class="card-body">
                <p>Sistem Informasi TKBM (Tenaga Kerja Bongkar Muat) adalah platform digital terintegrasi untuk mencatat, mengawasi, dan merekapitulasi seluruh kegiatan operasional bongkar muat kargo kapal di pelabuhan secara akurat dan real-time. Sistem ini menghubungkan dua entitas kerja utama:</p>

                <div class="row g-3 my-2">
                  <div class="col-md-6">
                    <div class="p-3 border border-dashed rounded text-center h-100 bg-light">
                      <div class="avatar-sm mx-auto mb-2">
                        <span class="avatar-title bg-primary-subtle text-primary rounded-circle fs-3">
                          <i class="fal fa-desktop"></i>
                        </span>
                      </div>
                      <h6 class="fs-15 fw-bold text-dark mb-1">Web Panel (Admin & Manajemen)</h6>
                      <p class="text-muted fs-13 mb-0">Digunakan oleh manajemen dan staf kantor untuk mengelola data master, merilis dan memverifikasi Work Order, memantau grafik dashboard, mencetak dokumen rekapitulasi kapal, serta menerbitkan invoice tagihan.</p>
                    </div>
                  </div>
                  <div class="col-md-6">
                    <div class="p-3 border border-dashed rounded text-center h-100 bg-light">
                      <div class="avatar-sm mx-auto mb-2">
                        <span class="avatar-title bg-success-subtle text-success rounded-circle fs-3">
                          <i class="fal fa-mobile-alt"></i>
                        </span>
                      </div>
                      <h6 class="fs-15 fw-bold text-dark mb-1">Mobile App (Pengawas Lapangan)</h6>
                      <p class="text-muted fs-13 mb-0">Aplikasi Android portabel yang digunakan pengawas di dermaga dengan arsitektur <em>offline-first</em>. Pengawas dapat membuat WO baru di lokasi, mengisi capaian kerja, konsumsi ransum, bukti foto, serta mengelola data master melalui modal bottom sheet.</p>
                    </div>
                  </div>
                </div>
              </div>
            </div>

            <!-- Workflow 4-Tahap Maker Checker -->
            <div class="card shadow-sm border-0 mb-3 search-target">
              <div class="card-header border-bottom bg-transparent d-flex align-items-center">
                <div class="avatar-xs me-2">
                  <span class="avatar-title bg-warning-subtle text-warning rounded-circle"><i class="fal fa-layer-group"></i></span>
                </div>
                <h5 class="card-title mb-0 flex-grow-1">Siklus 4 Tahap Work Order (Maker-Checker Workflow)</h5>
              </div>
              <div class="card-body">
                <p class="fs-13">Untuk menjamin integritas data lapangan dan mencegah manipulasi angka tonase, sistem menerapkan prinsip <strong>Maker-Checker</strong>: Pengawas lapangan bertindak sebagai <em>Maker</em> (pencatat), dan Admin Kantor bertindak sebagai <em>Checker</em> (pemeriksa akhir).</p>

                <!-- 4 Steps Interactive Layout -->
                <div class="row g-2 justify-content-center text-center my-3">
                  <div class="col-md-3">
                    <div class="bg-light p-3 rounded h-100 border border-warning-subtle position-relative">
                      <span class="badge bg-warning text-dark mb-2 fw-semibold px-2 py-1 fs-12">1. OPEN</span>
                      <h6 class="fs-13 fw-bold text-dark">Rilis Perintah Kerja</h6>
                      <p class="fs-12 text-muted mb-0">WO dibuat oleh Admin di Web atau Pengawas di HP (tersedia opsi backdate & nomor manual/auto). WO siap dieksekusi.</p>
                    </div>
                  </div>
                  <div class="col-md-3">
                    <div class="bg-light p-3 rounded h-100 border border-primary-subtle position-relative">
                      <span class="badge bg-primary text-white mb-2 fw-semibold px-2 py-1 fs-12">2. PROGRESS</span>
                      <h6 class="fs-13 fw-bold text-dark">Pelaksanaan Lapangan</h6>
                      <p class="fs-12 text-muted mb-0">Pengawas memulai pekerjaan, menginput laporan berkala, jumlah capaian kerja, alokasi ransum, dan dokumentasi foto before/after.</p>
                    </div>
                  </div>
                  <div class="col-md-3">
                    <div class="bg-light p-3 rounded h-100 border border-info-subtle position-relative">
                      <span class="badge bg-info text-white mb-2 fw-semibold px-2 py-1 fs-12">3. REVIEW</span>
                      <h6 class="fs-13 fw-bold text-dark">Verifikasi Checker</h6>
                      <p class="fs-12 text-muted mb-0">Pengawas menekan <em>"Kirim untuk Review Admin"</em>. Formulir laporan di HP otomatis terkunci untuk menunggu audit verifikasi Admin.</p>
                    </div>
                  </div>
                  <div class="col-md-3">
                    <div class="bg-light p-3 rounded h-100 border border-success-subtle position-relative">
                      <span class="badge bg-success text-white mb-2 fw-semibold px-2 py-1 fs-12">4. DONE</span>
                      <h6 class="fs-13 fw-bold text-dark">Selesai & Disahkan</h6>
                      <p class="fs-12 text-muted mb-0">Admin Web menyetujui laporan menjadi DONE. Data terkunci permanen, siap dicetak rekapitulasinya, dan ditarik ke tagihan Invoice.</p>
                    </div>
                  </div>
                </div>

                <div class="alert alert-info border-0 fs-13 mb-0">
                  <i class="fal fa-shield-check me-1"></i> <strong>Aturan Keamanan Maker-Checker:</strong> Pengawas lapangan <strong>tidak memiliki akses</strong> untuk langsung mengubah WO menjadi DONE atau menghapus laporan yang sudah terkirim. Hal ini menjamin bahwa seluruh data tonase dan biaya tenaga kerja telah melalui pemeriksaan berkas oleh Admin kantor sebelum ditagihkan ke klien (PBM).
                </div>
              </div>
            </div>

            <!-- Matriks Peran -->
            <div class="card shadow-sm border-0 mb-3 search-target">
              <div class="card-header border-bottom bg-transparent d-flex align-items-center">
                <div class="avatar-xs me-2">
                  <span class="avatar-title bg-info-subtle text-info rounded-circle"><i class="fal fa-users-cog"></i></span>
                </div>
                <h5 class="card-title mb-0 flex-grow-1">Matriks Peran & Tanggung Jawab Pengguna</h5>
              </div>
              <div class="card-body p-0">
                <div class="table-responsive">
                  <table class="table table-hover align-middle mb-0 fs-13">
                    <thead class="table-light">
                      <tr>
                        <th style="width: 25%;">Peran (Role)</th>
                        <th style="width: 30%;">Platform Akses</th>
                        <th>Tanggung Jawab Utama</th>
                      </tr>
                    </thead>
                    <tbody>
                      <tr>
                        <td><span class="badge bg-danger-subtle text-danger fw-semibold fs-12">Super Admin / Owner</span></td>
                        <td>Web Panel (Lengkap)</td>
                        <td>Konfigurasi global sistem, manajemen hak akses user/roles, pengaturan server email SMTP, dan audit monitoring log.</td>
                      </tr>
                      <tr>
                        <td><span class="badge bg-primary-subtle text-primary fw-semibold fs-12">Admin Operasional</span></td>
                        <td>Web Panel</td>
                        <td>Membuat & mengedit WO, verifikasi approval WO (Review → Done), entri laporan backdate, monitoring dashboard, dan mencetak rekap kegiatan kapal.</td>
                      </tr>
                      <tr>
                        <td><span class="badge bg-warning-subtle text-warning fw-semibold fs-12">Accounting / Keuangan</span></td>
                        <td>Web Panel</td>
                        <td>Menerbitkan tagihan invoice dari WO Done, memantau status jatuh tempo (Overdue), mencetak invoice ber-watermark, dan menginput bukti pembayaran.</td>
                      </tr>
                      <tr>
                        <td><span class="badge bg-success-subtle text-success fw-semibold fs-12">Pengawas Lapangan</span></td>
                        <td>Mobile App (Android)</td>
                        <td>Mencatat aktivitas bongkar/muat di dermaga, menginput porsi ransum, jepret foto before/after, entri data master lapangan via modal bottom sheet, dan mengirim WO untuk review.</td>
                      </tr>
                    </tbody>
                  </table>
                </div>
              </div>
            </div>
          </div>

          <!-- Sidebar Info & Highlights -->
          <div class="col-xl-4">
            <div class="card border-0 shadow-sm mb-3 search-target">
              <div class="card-body text-center p-4">
                <img src="<?= base_url('assets/images/location_tracking.svg'); ?>" style="max-height: 120px;" class="img-fluid mb-3" alt="Offline Mode">
                <h6 class="fw-bold text-dark mb-2">Keunggulan Desain Offline-First</h6>
                <p class="text-muted fs-13 mb-3 text-start">Aplikasi mobile dirancang khusus untuk kondisi dermaga pelabuhan dengan sinyal internet fluktuatif:</p>
                <ul class="text-start fs-13 text-muted ps-3 mb-3">
                  <li>Data master dan Work Order tersimpan di memori lokal ponsel (Drift SQLite).</li>
                  <li>Laporan harian dan foto tetap dapat disimpan meski tanpa koneksi internet.</li>
                  <li>Sinkronisasi otomatis di latar belakang saat perangkat kembali terhubung ke jaringan.</li>
                </ul>
                <div class="badge bg-success-subtle text-success border border-success-subtle py-2 px-3 w-100 fs-12">
                  <i class="fal fa-check-circle me-1"></i> Data Aman & Tersimpan Lokal
                </div>
              </div>
            </div>

            <div class="card border-0 shadow-sm search-target">
              <div class="card-header bg-transparent border-bottom">
                <h6 class="card-title mb-0 fs-14 fw-bold text-dark"><i class="fal fa-bolt text-warning me-1"></i> Fitur Terbaru Rilis Ini</h6>
              </div>
              <div class="card-body">
                <div class="d-flex mb-3">
                  <div class="flex-shrink-0 avatar-xs me-2">
                    <span class="avatar-title bg-primary-subtle text-primary rounded-circle fs-12">1</span>
                  </div>
                  <div class="flex-grow-1">
                    <h6 class="fs-13 fw-semibold mb-1">Status REVIEW & Maker-Checker</h6>
                    <p class="fs-12 text-muted mb-0">Verifikasi berjenjang antara pengawas lapangan dan admin kantor sebelum WO disahkan.</p>
                  </div>
                </div>
                <div class="d-flex mb-3">
                  <div class="flex-shrink-0 avatar-xs me-2">
                    <span class="avatar-title bg-success-subtle text-success rounded-circle fs-12">2</span>
                  </div>
                  <div class="flex-grow-1">
                    <h6 class="fs-13 fw-semibold mb-1">Master Data Hub Mobile</h6>
                    <p class="fs-12 text-muted mb-0">Menu Data Master di tab profil mobile dengan form modal bottom sheet tanpa icon dekoratif & mandatory label merah.</p>
                  </div>
                </div>
                <div class="d-flex mb-3">
                  <div class="flex-shrink-0 avatar-xs me-2">
                    <span class="avatar-title bg-info-subtle text-info rounded-circle fs-12">3</span>
                  </div>
                  <div class="flex-grow-1">
                    <h6 class="fs-13 fw-semibold mb-1">Pencatatan Ransum Lapangan</h6>
                    <p class="fs-12 text-muted mb-0">Input kuantitas ransum tenaga kerja per rincian kegiatan terintegrasi ke rekap cetak.</p>
                  </div>
                </div>
                <div class="d-flex">
                  <div class="flex-shrink-0 avatar-xs me-2">
                    <span class="avatar-title bg-warning-subtle text-warning rounded-circle fs-12">4</span>
                  </div>
                  <div class="flex-grow-1">
                    <h6 class="fs-13 fw-semibold mb-1">Rekap Kegiatan Kapal & Chart</h6>
                    <p class="fs-12 text-muted mb-0">Tab baru pada laporan dengan visualisasi ApexCharts dan cetak side-by-side A4 portrait.</p>
                  </div>
                </div>
              </div>
            </div>
          </div>
        </div>
      </div>

      <!-- ========================================== -->
      <!-- TAB 2: PANDUAN ADMIN (WEB PANEL)           -->
      <!-- ========================================== -->
      <div class="tab-pane" id="admin-tab" role="tabpanel">
        <div class="row">
          <div class="col-xl-3 col-md-4">
            <!-- Navigation Sub-Menu -->
            <div class="card shadow-sm border-0 mb-3">
              <div class="card-header bg-primary text-white py-2 px-3">
                <span class="fs-12 text-uppercase fw-bold"><i class="fal fa-list me-1"></i> Modul Web Panel</span>
              </div>
              <div class="card-body p-0">
                <div class="nav flex-column nav-pills nav-pills-custom" role="tablist">
                  <a class="nav-link active p-3 border-bottom rounded-0" data-bs-toggle="pill" href="#v-pills-dashboard" role="tab">
                    <i class="fal fa-chart-line me-2 text-primary"></i> Dashboard Utama
                  </a>
                  <a class="nav-link p-3 border-bottom rounded-0" data-bs-toggle="pill" href="#v-pills-wo" role="tab">
                    <i class="fal fa-file-signature me-2 text-primary"></i> Manajemen Work Order
                  </a>
                  <a class="nav-link p-3 border-bottom rounded-0" data-bs-toggle="pill" href="#v-pills-activity" role="tab">
                    <i class="fal fa-pen me-2 text-primary"></i> Aktifitas & Ransum
                  </a>
                  <a class="nav-link p-3 border-bottom rounded-0" data-bs-toggle="pill" href="#v-pills-report" role="tab">
                    <i class="fal fa-file-chart-line me-2 text-primary"></i> Cetak Laporan & Rekap
                  </a>
                  <a class="nav-link p-3 border-bottom rounded-0" data-bs-toggle="pill" href="#v-pills-invoice" role="tab">
                    <i class="fal fa-file-invoice me-2 text-primary"></i> Manajemen Invoice
                  </a>
                  <a class="nav-link p-3 border-bottom rounded-0" data-bs-toggle="pill" href="#v-pills-master" role="tab">
                    <i class="fal fa-server me-2 text-primary"></i> 10 Modul Master Data
                  </a>
                  <a class="nav-link p-3 border-bottom rounded-0" data-bs-toggle="pill" href="#v-pills-settings" role="tab">
                    <i class="fal fa-cogs me-2 text-primary"></i> Pengaturan & User Matrix
                  </a>
                  <a class="nav-link p-3 rounded-0" data-bs-toggle="pill" href="#v-pills-logs" role="tab">
                    <i class="fal fa-shield-alt me-2 text-primary"></i> Audit & Monitoring Logs
                  </a>
                </div>
              </div>
            </div>
          </div>

          <div class="col-xl-9 col-md-8">
            <div class="tab-content text-muted p-0">

              <!-- Sub Tab: Dashboard -->
              <div class="tab-pane fade show active search-target" id="v-pills-dashboard" role="tabpanel">
                <div class="card shadow-sm border-0">
                  <div class="card-body">
                    <h5 class="card-title text-primary mb-3"><i class="fal fa-chart-line me-2"></i>Dashboard Monitoring Utama</h5>
                    <p class="fs-13">Dashboard dirancang khusus sebagai ruang kendali bagi Admin untuk meninjau status operasional secara grafis dan real-time.</p>

                    <div class="alert alert-info border-0 fs-13">
                      <strong>Informasi Widget Dashboard:</strong>
                      <ul class="mb-0 mt-1 ps-3">
                        <li><strong>Stat Cards:</strong> Total Work Order, WO Hari Ini, dan status berjalan (Open, In Progress, Review, Done).</li>
                        <li><strong>Grafik Tren 6 Bulan:</strong> Visualisasi perbandingan jumlah penerbitan WO versus WO yang berhasil diselesaikan.</li>
                        <li><strong>Donut Status Distribusi:</strong> Persentase persebaran status operasional pada bulan berjalan.</li>
                        <li><strong>Top 5 Clients:</strong> Menampilkan peringkat perusahaan PBM atau pemilik kargo paling aktif berdasarkan jumlah transaksi WO.</li>
                      </ul>
                    </div>

                    <div class="border rounded p-3 bg-light my-3">
                      <div class="d-flex justify-content-between border-bottom pb-2 mb-3">
                        <span class="fw-semibold text-dark fs-13"><i class="fal fa-desktop me-1"></i> Representasi Statistik Dashboard</span>
                        <span class="badge bg-success-subtle text-success">Real-Time Data</span>
                      </div>
                      <div class="row g-2 text-center">
                        <div class="col-6 col-md-3">
                          <div class="card border mb-0 p-2 bg-white shadow-none">
                            <span class="text-muted fs-11 text-uppercase">Total WO</span>
                            <h4 class="fw-bold mb-0 text-dark">150</h4>
                          </div>
                        </div>
                        <div class="col-6 col-md-3">
                          <div class="card border mb-0 p-2 bg-white shadow-none">
                            <span class="text-warning fs-11 text-uppercase">Open / Antre</span>
                            <h4 class="fw-bold mb-0 text-warning">20</h4>
                          </div>
                        </div>
                        <div class="col-6 col-md-3">
                          <div class="card border mb-0 p-2 bg-white shadow-none">
                            <span class="text-primary fs-11 text-uppercase">In Progress</span>
                            <h4 class="fw-bold mb-0 text-primary">15</h4>
                          </div>
                        </div>
                        <div class="col-6 col-md-3">
                          <div class="card border mb-0 p-2 bg-white shadow-none">
                            <span class="text-info fs-11 text-uppercase">Review Admin</span>
                            <h4 class="fw-bold mb-0 text-info">10</h4>
                          </div>
                        </div>
                      </div>
                    </div>
                  </div>
                </div>
              </div>

              <!-- Sub Tab: WO -->
              <div class="tab-pane fade search-target" id="v-pills-wo" role="tabpanel">
                <div class="card shadow-sm border-0">
                  <div class="card-body">
                    <h5 class="card-title text-primary mb-3"><i class="fal fa-file-signature me-2"></i>Manajemen Work Order (Perintah Kerja)</h5>
                    <p class="fs-13">Modul inti untuk merilis tugas bongkar muat, memantau kemajuan pekerjaan, dan melakukan validasi verifikasi Maker-Checker.</p>

                    <h6 class="fw-bold text-dark mt-3 fs-14">1. Pembuatan Work Order Baru (Dukungan Backdate)</h6>
                    <p class="fs-13">Masuk ke menu <strong>Work Order -> Tambah Work Order</strong>. Lengkapi formulir pendaftaran:</p>
                    <ol class="fs-13 ps-3">
                      <li><strong>Tanggal Kerja:</strong> Secara default terisi hari ini, namun <em>mendukung tanggal lampau (backdate)</em> untuk mencatat kegiatan bongkar muat yang telah dikerjakan di hari-hari sebelumnya.</li>
                      <li><strong>Penomoran WO:</strong> Tersedia opsi <em>Otomatis</em> (contoh: <code>WO-2026-0045</code>) atau <em>Manual</em> jika perusahaan menggunakan kode referensi faktur khusus.</li>
                      <li><strong>Klien (PBM / Shipper):</strong> Pilih perusahaan pemilik kargo.</li>
                      <li><strong>Nama Kapal & Dermaga:</strong> Pilih kapal yang bersandar dan dermaga tempat tambat (otomatis tersaring sesuai pelabuhan terpilih).</li>
                      <li><strong>Daftar Alat Bantu:</strong> Centang satu atau beberapa alat (Crane Kapal, Forklift, dsb.) via checklist.</li>
                      <li><strong>Aktivitas & Satuan:</strong> Tentukan jenis kegiatan (Bongkar/Muat) serta satuan ukur target muatan (TON, M³, TEUS).</li>
                      <li><strong>Pengawas Lapangan:</strong> Tentukan akun pengawas yang bertanggung jawab mengawal operasional di dermaga.</li>
                      <li><strong>Status Awal:</strong> Pilih <em>Open</em> untuk pekerjaan baru, atau <em>In Progress</em> jika pekerjaan sudah mulai berjalan.</li>
                    </ol>

                    <h6 class="fw-bold text-dark mt-4 fs-14">2. Verifikasi Maker-Checker (Approval Status REVIEW → DONE)</h6>
                    <p class="fs-13">Ketika Pengawas Lapangan selesai mengerjakan tugas, mereka akan menekan tombol kirim review di mobile app. WO akan berstatus <span class="badge bg-info-subtle text-info">Review</span> (Menunggu Verifikasi Admin).</p>
                    <div class="p-3 bg-light border rounded fs-13 mb-3">
                      <strong>Langkah Pemeriksaan Admin:</strong>
                      <ol class="mb-0 mt-2 ps-3">
                        <li>Buka daftar Work Order dan gunakan filter cepat status <strong>"Review"</strong>.</li>
                        <li>Klik tombol <strong>Detail</strong> (ikon folder) untuk membuka tinjauan komprehensif.</li>
                        <li>Periksa akumulasi tonase, rincian ransum, serta klik thumbnail foto <strong>Before & After</strong> untuk memperbesar resolusi bukti fisik.</li>
                        <li>Jika data valid, klik tombol hijau <strong>"Setujui & Selesaikan (DONE)"</strong>. Status akan berubah menjadi Done dan data terkunci permanen.</li>
                        <li>Jika terdapat kekeliruan data, Admin dapat mengembalikan status WO ke <em>Progress</em> agar pengawas lapangan dapat memperbaiki data.</li>
                      </ol>
                    </div>
                  </div>
                </div>
              </div>

              <!-- Sub Tab: Activity -->
              <div class="tab-pane fade search-target" id="v-pills-activity" role="tabpanel">
                <div class="card shadow-sm border-0">
                  <div class="card-body">
                    <h5 class="card-title text-primary mb-3"><i class="fal fa-pen me-2"></i>Aktifitas Lapangan & Pencatatan Ransum</h5>
                    <p class="fs-13">Admin dapat memonitor dan mengelola rincian laporan per jam / shift yang dilaporkan dari lapangan.</p>

                    <h6 class="fw-bold text-dark mt-3 fs-14">1. Filter & Monitoring Harian</h6>
                    <ol class="fs-13 ps-3">
                      <li>Tentukan <strong>Tanggal Aktifitas Lapangan</strong> yang ingin ditinjau.</li>
                      <li>Pilih <strong>Pelabuhan</strong> kerja aktif.</li>
                      <li>Klik <strong>Cari</strong>. Sistem akan menampilkan seluruh baris laporan pekerjaan beserta nama kapal dan pengawas yang bertugas.</li>
                    </ol>

                    <h6 class="fw-bold text-dark mt-4 fs-14">2. Input & Rekapitulasi Ransum (Bungkus)</h6>
                    <p class="fs-13">Setiap baris aktivitas dilengkapi dengan kolom <strong>Ransum (Bungkus)</strong> untuk mencatat konsumsi makanan/minuman yang didistribusikan kepada regu kerja:</p>
                    <ul class="fs-13 ps-3">
                      <li>Diinput dalam satuan bungkus/porsi (angka bulat positif).</li>
                      <li>Akumulasi total ransum ditampilkan pada kartu ringkasan Work Order (dengan format ribuan titik).</li>
                      <li>Otomatis disertakan pada rekapitulasi cetak dan rincian penagihan biaya operasional.</li>
                    </ul>
                  </div>
                </div>
              </div>

              <!-- Sub Tab: Report -->
              <div class="tab-pane fade search-target" id="v-pills-report" role="tabpanel">
                <div class="card shadow-sm border-0">
                  <div class="card-body">
                    <h5 class="card-title text-primary mb-3"><i class="fal fa-file-chart-line me-2"></i>Pusat Pencetakan Laporan & Rekap Kegiatan Kapal</h5>
                    <p class="fs-13">Modul rekapitulasi resmi terbagi dalam dua tab navigasi utama pada menu <strong>Report -> Laporan Work Order</strong>:</p>

                    <div class="row g-3 my-2">
                      <div class="col-md-6">
                        <div class="p-3 border rounded h-100 bg-light">
                          <h6 class="fw-bold text-primary fs-14 mb-2"><i class="fal fa-file-alt me-1"></i> Tab 1: Laporan Work Order</h6>
                          <p class="fs-12 text-muted mb-2">Digunakan untuk rekapitulasi menyeluruh berbasis periode tanggal.</p>
                          <ul class="fs-12 text-muted ps-3 mb-0">
                            <li>Filter periode, pelabuhan, klien, atau pengawas spesifik.</li>
                            <li>Pengelompokan data berjenjang: Pelabuhan → Pengawas → Klien.</li>
                            <li><strong>Export PDF:</strong> Format resmi siap cetak lengkap dengan kop surat dan kolom legalitas tanda tangan.</li>
                            <li><strong>Export Excel:</strong> Format <code>.xlsx</code> mentah untuk audit pembukuan.</li>
                          </ul>
                        </div>
                      </div>
                      <div class="col-md-6">
                        <div class="p-3 border rounded h-100 bg-light">
                          <h6 class="fw-bold text-primary fs-14 mb-2"><i class="fal fa-ship me-1"></i> Tab 2: Rekap Kegiatan Kapal</h6>
                          <p class="fs-12 text-muted mb-2">Dirancang khusus untuk rekapitulasi operasional per pelabuhan & bulan berjalan.</p>
                          <ul class="fs-12 text-muted ps-3 mb-0">
                            <li>Filter Pelabuhan dan Bulan-Tahun kerja.</li>
                            <li>Tabel rincian kapal: Kapal, PBM, No Inv, Bongkar, Muat, Party, Kegiatan.</li>
                            <li>Tabel summary PBM & Komoditas terkelompok rapi.</li>
                            <li>Visualisasi <strong>ApexCharts</strong> grafik lingkaran persebaran kapal dan tonase party.</li>
                            <li>Layout cetak resmi A4 berdampingan (side-by-side) 1 halaman penuh.</li>
                          </ul>
                        </div>
                      </div>
                    </div>
                  </div>
                </div>
              </div>

              <!-- Sub Tab: Invoice -->
              <div class="tab-pane fade search-target" id="v-pills-invoice" role="tabpanel">
                <div class="card shadow-sm border-0">
                  <div class="card-body">
                    <h5 class="card-title text-primary mb-3"><i class="fal fa-file-invoice me-2"></i>Manajemen Invoice & Penagihan Klien</h5>
                    <p class="fs-13">Bagian keuangan menerbitkan faktur tagihan (Invoice) secara otomatis berdasarkan Work Order yang telah berstatus <strong>Done</strong>.</p>

                    <h6 class="fw-bold text-dark mt-3 fs-14">1. Langkah Menerbitkan Invoice Baru</h6>
                    <ol class="fs-13 ps-3">
                      <li>Buka menu <strong>Accounting -> Invoice</strong>.</li>
                      <li>Klik tombol <strong>Buat Invoice</strong> di header tabel.</li>
                      <li>Jendela pop-up modal akan menampilkan seluruh WO berstatus <strong>Done</strong> yang belum pernah ditagihkan. Cari WO klien yang dituju dan klik <strong>Buat Invoice</strong>.</li>
                      <li>Sistem otomatis menarik nomor WO, klien, kapal, pelabuhan, dermaga, serta volume capaian kerja ke dalam formulir invoice.</li>
                      <li>Tentukan tanggal invoice, tanggal jatuh tempo, tarif PPN (default 11%), serta harga satuan per komoditas. Subtotal dan total dihitung otomatis (<em>auto-calculate</em>).</li>
                      <li>Simpan invoice sebagai <strong>Draft</strong> atau langsung tandai <strong>Sent</strong> jika sudah dikirimkan ke klien.</li>
                    </ol>

                    <h6 class="fw-bold text-dark mt-4 fs-14">2. Pencetakan & Siklus Pembayaran</h6>
                    <ul class="fs-13 ps-3">
                      <li><strong>Watermark Status Dinamis:</strong> Cetakan PDF invoice dilengkapi stempel watermark otomatis sesuai status terkini (<em>DRAFT</em>, <em>PAID</em>, <em>CANCELLED</em>).</li>
                      <li><strong>Status Overdue Otomatis:</strong> Tagihan yang belum dibayar hingga melewati tanggal jatuh tempo otomatis berubah status menjadi <em>Overdue</em>.</li>
                      <li><strong>Input Pembayaran:</strong> Klik tombol <em>Input Pembayaran</em> untuk mencatat tanggal pembayaran, metode (Transfer Bank / Cash / Giro), dan upload bukti transfer bank. Status otomatis berubah menjadi <strong>Paid</strong>.</li>
                    </ul>
                  </div>
                </div>
              </div>

              <!-- Sub Tab: Master -->
              <div class="tab-pane fade search-target" id="v-pills-master" role="tabpanel">
                <div class="card shadow-sm border-0">
                  <div class="card-body">
                    <h5 class="card-title text-primary mb-3"><i class="fal fa-server me-2"></i>10 Modul Master Data Referensi</h5>
                    <p class="fs-13">Seluruh entitas operasional terkelola melalui 10 modul master terstandar:</p>

                    <div class="table-responsive">
                      <table class="table table-bordered table-striped fs-13 mb-0 align-middle">
                        <thead class="table-light">
                          <tr>
                            <th style="width: 25%;">Nama Modul Master</th>
                            <th>Fungsi Utama & Keterangan</th>
                            <th style="width: 30%;">Atribut Khusus / Relasi</th>
                          </tr>
                        </thead>
                        <tbody>
                          <tr>
                            <td><strong>1. Pelabuhan</strong></td>
                            <td>Daftar pelabuhan kerja aktif tempat operasional berjalan.</td>
                            <td>Entitas induk untuk relasi dermaga.</td>
                          </tr>
                          <tr>
                            <td><strong>2. Dermaga</strong></td>
                            <td>Lokasi sandar / tambatan kade kapal di pelabuhan.</td>
                            <td>Wajib ditautkan ke satu pelabuhan aktif.</td>
                          </tr>
                          <tr>
                            <td><strong>3. Client (PBM)</strong></td>
                            <td>Daftar perusahaan bongkar muat / shipper pemilik kargo.</td>
                            <td>Dilengkapi kode alias singkat dan kontak PIC.</td>
                          </tr>
                          <tr>
                            <td><strong>4. Kapal</strong></td>
                            <td>Armada kapal laut / tongkang pengangkut muatan.</td>
                            <td>Mencatat nama kapal dan tipe kapal.</td>
                          </tr>
                          <tr>
                            <td><strong>5. Aktifitas</strong></td>
                            <td>Jenis kegiatan operasional bongkar muat.</td>
                            <td>Memiliki klasifikasi <strong>Tipe (Bongkar / Muat)</strong> & satuan default.</td>
                          </tr>
                          <tr>
                            <td><strong>6. Alat</strong></td>
                            <td>Mesin & armada penunjang (Crane, Forklift, Loader).</td>
                            <td>Dilengkapi kode singkat untuk checklist WO.</td>
                          </tr>
                          <tr>
                            <td><strong>7. Satuan</strong></td>
                            <td>Unit satuan ukur tonase (TON, M³, TEUS, BAG, KG).</td>
                            <td>Menentukan satuan input laporan pengawas.</td>
                          </tr>
                          <tr>
                            <td><strong>8. KRK</strong></td>
                            <td>Komponen Rekapitulasi Kerja (Produktivitas, Idle, dsb).</td>
                            <td>Kategori rincian kegiatan harian lapangan.</td>
                          </tr>
                          <tr>
                            <td><strong>9. Catatan</strong></td>
                            <td>Template saran catatan untuk pengawas lapangan.</td>
                            <td>Muncul sebagai chip rekomendasi di mobile app.</td>
                          </tr>
                          <tr>
                            <td><strong>10. Template Email</strong></td>
                            <td>Format pesan notifikasi dan korespondensi otomatis sistem.</td>
                            <td>Variabel tag dinamis untuk notifikasi otomatis.</td>
                          </tr>
                        </tbody>
                      </table>
                    </div>
                  </div>
                </div>
              </div>

              <!-- Sub Tab: Settings -->
              <div class="tab-pane fade search-target" id="v-pills-settings" role="tabpanel">
                <div class="card shadow-sm border-0">
                  <div class="card-body">
                    <h5 class="card-title text-primary mb-3"><i class="fal fa-cogs me-2"></i>Pengaturan Aplikasi & Hak Akses User Matrix</h5>
                    <p class="fs-13">Konfigurasi pusat yang dikhususkan untuk Administrator dengan hak akses Super Admin.</p>

                    <h6 class="fw-bold text-dark mt-3 fs-14">A. Pengaturan Aplikasi</h6>
                    <ul class="fs-13 ps-3">
                      <li><strong>Profil Instansi:</strong> Menentukan nama sistem, teks copyright footer, logo besar sidebar, dan logo ringkas (favicon).</li>
                      <li><strong>Koneksi SMTP Email:</strong> Mengonfigurasi host, port, kredensial email pengirim, serta fitur <strong>Test Kirim Email</strong> untuk memvalidasi kelancaran notifikasi.</li>
                    </ul>

                    <h6 class="fw-bold text-dark mt-4 fs-14">B. Manajemen User & Permission Matrix</h6>
                    <ul class="fs-13 ps-3">
                      <li><strong>Pengguna:</strong> Pendaftaran akun staf kantor dan pengawas lapangan, reset password, dan status aktif/nonaktif.</li>
                      <li><strong>Permission Matrix:</strong> Tabel matriks centang peranan untuk membatasi aksi <em>Create, Read, Update, Delete</em> pada masing-masing modul menu.</li>
                    </ul>
                  </div>
                </div>
              </div>

              <!-- Sub Tab: Logs -->
              <div class="tab-pane fade search-target" id="v-pills-logs" role="tabpanel">
                <div class="card shadow-sm border-0">
                  <div class="card-body">
                    <h5 class="card-title text-primary mb-3"><i class="fal fa-shield-alt me-2"></i>Audit Trail & Monitoring Logs</h5>
                    <p class="fs-13">Menjamin transparansi operasional melalui rekaman audit komprehensif:</p>

                    <div class="accordion" id="accordionLogs">
                      <div class="accordion-item border mb-2 rounded overflow-hidden">
                        <h2 class="accordion-header">
                          <button class="accordion-button collapsed py-2 text-dark fs-13 fw-semibold bg-light" type="button" data-bs-toggle="collapse" data-bs-target="#collapseOne">
                            <i class="fal fa-user-tag me-2 text-primary"></i> 1. Log Aktifitas User (Audit Trail)
                          </button>
                        </h2>
                        <div id="collapseOne" class="accordion-collapse collapse" data-bs-parent="#accordionLogs">
                          <div class="accordion-body fs-13">
                            Merekam seluruh jejak login, pembuatan, pengeditan, dan penghapusan data (dilengkapi identitas pengguna, modul target, alamat IP, peramban, dan stempel waktu).
                          </div>
                        </div>
                      </div>
                      <div class="accordion-item border mb-2 rounded overflow-hidden">
                        <h2 class="accordion-header">
                          <button class="accordion-button collapsed py-2 text-dark fs-13 fw-semibold bg-light" type="button" data-bs-toggle="collapse" data-bs-target="#collapseTwo">
                            <i class="fal fa-network-wired me-2 text-success"></i> 2. Log API & Sinkronisasi Mobile
                          </button>
                        </h2>
                        <div id="collapseTwo" class="accordion-collapse collapse" data-bs-parent="#accordionLogs">
                          <div class="accordion-body fs-13">
                            Memantau permintaan HTTP dari aplikasi Flutter mobile (metode, endpoint, latency respons, dan kode status). Sangat berguna mendeteksi kendala koneksi perangkat lapangan.
                          </div>
                        </div>
                      </div>
                      <div class="accordion-item border mb-2 rounded overflow-hidden">
                        <h2 class="accordion-header">
                          <button class="accordion-button collapsed py-2 text-dark fs-13 fw-semibold bg-light" type="button" data-bs-toggle="collapse" data-bs-target="#collapseThree">
                            <i class="fal fa-envelope-open-text me-2 text-warning"></i> 3. Log Pengiriman Email
                          </button>
                        </h2>
                        <div id="collapseThree" class="accordion-collapse collapse" data-bs-parent="#accordionLogs">
                          <div class="accordion-body fs-13">
                            Menyimpan riwayat pengiriman email notifikasi tagihan atau akun ke pengguna, lengkap dengan status terkirim atau pesan error kegagalan SMTP.
                          </div>
                        </div>
                      </div>
                      <div class="accordion-item border rounded overflow-hidden">
                        <h2 class="accordion-header">
                          <button class="accordion-button collapsed py-2 text-dark fs-13 fw-semibold bg-light" type="button" data-bs-toggle="collapse" data-bs-target="#collapseFour">
                            <i class="fal fa-exclamation-triangle me-2 text-danger"></i> 4. Log Error Sistem & Aplikasi
                          </button>
                        </h2>
                        <div id="collapseFour" class="accordion-collapse collapse" data-bs-parent="#accordionLogs">
                          <div class="accordion-body fs-13">
                            Menangkap exception dan error PHP internal beserta stack trace kode untuk mempercepat investigasi oleh tim teknis IT.
                          </div>
                        </div>
                      </div>
                    </div>
                  </div>
                </div>
              </div>

            </div>
          </div>
        </div>
      </div>

      <!-- ========================================== -->
      <!-- TAB 3: PANDUAN PENGAWAS (MOBILE APP)       -->
      <!-- ========================================== -->
      <div class="tab-pane" id="pengawas-tab" role="tabpanel">
        <div class="row">
          <div class="col-xl-3 col-md-4">
            <!-- Navigation Sub-Menu Mobile -->
            <div class="card shadow-sm border-0 mb-3">
              <div class="card-header bg-success text-white py-2 px-3">
                <span class="fs-12 text-uppercase fw-bold"><i class="fal fa-mobile me-1"></i> Modul Mobile App</span>
              </div>
              <div class="card-body p-0">
                <div class="nav flex-column nav-pills nav-pills-custom" role="tablist">
                  <a class="nav-link active p-3 border-bottom rounded-0" data-bs-toggle="pill" href="#v-pills-m-intro" role="tab">
                    <i class="fal fa-sign-in-alt me-2 text-success"></i> Akses & Tampilan Utama
                  </a>
                  <a class="nav-link p-3 border-bottom rounded-0" data-bs-toggle="pill" href="#v-pills-m-wo" role="tab">
                    <i class="fal fa-plus-circle me-2 text-success"></i> Entri WO Baru (Mobile)
                  </a>
                  <a class="nav-link p-3 border-bottom rounded-0" data-bs-toggle="pill" href="#v-pills-m-report" role="tab">
                    <i class="fal fa-clipboard-list-check me-2 text-success"></i> Pelaporan Kerja & Ransum
                  </a>
                  <a class="nav-link p-3 border-bottom rounded-0" data-bs-toggle="pill" href="#v-pills-m-checker" role="tab">
                    <i class="fal fa-paper-plane me-2 text-success"></i> Alur Kirim Review (Maker)
                  </a>
                  <a class="nav-link p-3 border-bottom rounded-0" data-bs-toggle="pill" href="#v-pills-m-master" role="tab">
                    <i class="fal fa-cubes me-2 text-success"></i> Hub Data Master Mobile
                  </a>
                  <a class="nav-link p-3 rounded-0" data-bs-toggle="pill" href="#v-pills-m-sync" role="tab">
                    <i class="fal fa-sync-alt me-2 text-success"></i> Sinkronisasi Offline & Cache
                  </a>
                </div>
              </div>
            </div>
          </div>

          <div class="col-xl-9 col-md-8">
            <div class="tab-content text-muted p-0">

              <!-- Sub Tab: Intro Mobile -->
              <div class="tab-pane fade show active search-target" id="v-pills-m-intro" role="tabpanel">
                <div class="card shadow-sm border-0">
                  <div class="card-body">
                    <h5 class="card-title text-success mb-3"><i class="fal fa-mobile-android me-2"></i>Akses & Navigasi Utama Aplikasi Mobile</h5>
                    <p class="fs-13">Aplikasi mobile KTKBM didesain khusus bagi Pengawas Lapangan untuk menjalankan pencatatan langsung di dermaga.</p>

                    <h6 class="fw-bold text-dark mt-3 fs-14">1. Masuk Akun & Inisialisasi Data</h6>
                    <ul class="fs-13 ps-3">
                      <li>Buka aplikasi KTKBM pada ponsel Android.</li>
                      <li>Masukkan <strong>Username</strong> dan <strong>Password</strong> akun pengawas yang terdaftar.</li>
                      <li>Saat pertama kali login, pastikan ponsel terhubung ke internet selama beberapa detik agar aplikasi secara otomatis mengunduh referensi master data terbaru ke penyimpanan lokal ponsel (Drift SQLite).</li>
                    </ul>

                    <h6 class="fw-bold text-dark mt-4 fs-14">2. Struktur Navigasi Bawah (Bottom Navigation)</h6>
                    <div class="row g-2 text-center fs-13 my-2">
                      <div class="col-6 col-md-3">
                        <div class="p-2 border rounded bg-light">
                          <i class="fal fa-home text-primary fs-20 d-block mb-1"></i>
                          <strong>Dashboard</strong>
                          <div class="fs-11 text-muted">Ringkasan penugasan & statistik kerja</div>
                        </div>
                      </div>
                      <div class="col-6 col-md-3">
                        <div class="p-2 border rounded bg-light">
                          <i class="fal fa-file-alt text-primary fs-20 d-block mb-1"></i>
                          <strong>Work Order</strong>
                          <div class="fs-11 text-muted">Daftar penugasan & entri WO baru</div>
                        </div>
                      </div>
                      <div class="col-6 col-md-3">
                        <div class="p-2 border rounded bg-light">
                          <i class="fal fa-clipboard-check text-primary fs-20 d-block mb-1"></i>
                          <strong>Laporan</strong>
                          <div class="fs-11 text-muted">Aktivitas & dokumentasi lapangan</div>
                        </div>
                      </div>
                      <div class="col-6 col-md-3">
                        <div class="p-2 border rounded bg-light">
                          <i class="fal fa-user text-primary fs-20 d-block mb-1"></i>
                          <strong>Profil</strong>
                          <div class="fs-11 text-muted">Data Master, Sinkronisasi & Akun</div>
                        </div>
                      </div>
                    </div>
                  </div>
                </div>
              </div>

              <!-- Sub Tab: WO Baru Mobile -->
              <div class="tab-pane fade search-target" id="v-pills-m-wo" role="tabpanel">
                <div class="card shadow-sm border-0">
                  <div class="card-body">
                    <h5 class="card-title text-success mb-3"><i class="fal fa-plus-circle me-2"></i>Entri Work Order Baru Langsung dari Mobile</h5>
                    <p class="fs-13">Pengawas lapangan dapat membuat penugasan Work Order baru secara mandiri langsung dari aplikasi mobile ketika kapal bersandar mendadak.</p>

                    <h6 class="fw-bold text-dark mt-3 fs-14">Langkah Pembuatan:</h6>
                    <ol class="fs-13 ps-3">
                      <li>Buka tab <strong>Work Order</strong> pada aplikasi mobile.</li>
                      <li>Ketuk tombol melayang <strong>[＋ Buat Work Order]</strong> di kanan bawah.</li>
                      <li><strong>Dukungan Tanggal Backdate:</strong> Pilih tanggal kegiatan (dapat memilih hari ini maupun tanggal lampau jika mencatat kegiatan kemarin).</li>
                      <li><strong>Nomor WO Otomatis / Manual:</strong> Biarkan mode <em>Otomatis</em> untuk penomoran sistem, atau beralih ke <em>Manual</em> jika terdapat kode referensi invoice khusus.</li>
                      <li>Pilih Klien (PBM), Nama Kapal, Pelabuhan, Dermaga, dan centang Alat Bantu yang digunakan.</li>
                      <li><strong>Fitur Quick-Add:</strong> Jika nama kapal, client, atau dermaga belum terdaftar, terdapat tombol cepat <em>"+ Tambah Baru"</em> di samping dropdown untuk mendaftarkan data saat itu juga.</li>
                      <li>Tentukan jenis Aktivitas, Satuan Ukur, dan simpan. Status awal otomatis menjadi <strong>Open</strong>.</li>
                    </ol>
                  </div>
                </div>
              </div>

              <!-- Sub Tab: Pelaporan & Ransum -->
              <div class="tab-pane fade search-target" id="v-pills-m-report" role="tabpanel">
                <div class="card shadow-sm border-0">
                  <div class="card-body">
                    <h5 class="card-title text-success mb-3"><i class="fal fa-clipboard-list-check me-2"></i>Pelaporan Aktivitas Lapangan & Ransum</h5>
                    <p class="fs-13">Panduan pencatatan hasil kerja berkala, bukti foto, dan alokasi konsumsi tenaga kerja:</p>

                    <ol class="fs-13 ps-3">
                      <li>Buka WO yang bersangkutan. Jika masih berstatus <em>Open</em>, ubah status menjadi <strong>Progress</strong> melalui menu tindakan di kanan atas.</li>
                      <li>Ketuk tombol <strong>[＋ Tambah Laporan]</strong> di pojok kanan bawah layar.</li>
                      <li><strong>Pilih KRK & Nilai Capaian:</strong> Pilih klasifikasi (misal: <em>Produktivitas Bongkar</em>) dan ketik angka volume yang diselesaikan (misal: <em>150</em>). Satuan otomatis mengikuti WO induk (misal: <em>TON</em>).</li>
                      <li><strong>Input Ransum (Bungkus):</strong> Ketik jumlah ransum makan/minum yang disalurkan pada shift/kegiatan tersebut (contoh: <em>25 bungkus</em>).</li>
                      <li><strong>Dokumentasi Foto Before & After:</strong> Ketuk wadah foto untuk mengambil jepretan kamera langsung di dermaga. Aplikasi secara otomatis mengompresi gambar ke resolusi optimal (&lt; 1MB) agar hemat kuota dan cepat diunggah.</li>
                      <li><strong>Catatan Cepat (Quick Chips):</strong> Gunakan tombol chip sugesti di bawah kolom catatan (misal: <em>Cuaca Cerah</em>, <em>Hujan Lebat</em>, <em>Armada Macet</em>) untuk penulisan laporan instan tanpa perlu mengetik panjang.</li>
                      <li>Simpan. Laporan akan langsung tersimpan di database lokal HP dan otomatis disinkronkan ke server jika tersedia jaringan.</li>
                    </ol>
                  </div>
                </div>
              </div>

              <!-- Sub Tab: Kirim Review Maker -->
              <div class="tab-pane fade search-target" id="v-pills-m-checker" role="tabpanel">
                <div class="card shadow-sm border-0">
                  <div class="card-body">
                    <h5 class="card-title text-success mb-3"><i class="fal fa-paper-plane me-2"></i>Alur Kirim untuk Review Admin (Maker-Checker)</h5>
                    <p class="fs-13">Pengawas bertanggung jawab mengajukan hasil kerja lapangan untuk diverifikasi oleh staf kantor:</p>

                    <div class="p-3 border rounded bg-light fs-13 mb-3">
                      <h6 class="fw-bold text-dark fs-13"><i class="fal fa-info-circle text-primary me-1"></i> Prosedur Pengajuan:</h6>
                      <ol class="mb-0 mt-2 ps-3">
                        <li>Pastikan seluruh target volume pengerjaan kapal atau shift kerja telah selesai dilaporkan.</li>
                        <li>Pada bagian bawah halaman detail Work Order, ketuk tombol <strong>"Kirim untuk Review Admin"</strong>.</li>
                        <li>Dialog konfirmasi akan muncul mengingatkan bahwa setelah dikirim, status WO berubah menjadi <span class="badge bg-info text-white">Review</span>.</li>
                        <li>Setelah status berubah menjadi Review, tombol tambah laporan dan edit laporan di aplikasi mobile akan <strong>terkunci secara otomatis</strong> untuk menjaga keaslian berkas audit.</li>
                        <li>Admin kantor di web panel akan memeriksa laporan dan mengesahkannya menjadi <strong>DONE</strong>.</li>
                      </ol>
                    </div>
                  </div>
                </div>
              </div>

              <!-- Sub Tab: Hub Data Master Mobile -->
              <div class="tab-pane fade search-target" id="v-pills-m-master" role="tabpanel">
                <div class="card shadow-sm border-0">
                  <div class="card-body">
                    <h5 class="card-title text-success mb-3"><i class="fal fa-cubes me-2"></i>Pusat Data Master Lapangan (Master Hub)</h5>
                    <p class="fs-13">Pengawas berwenang dapat melihat, mendaftarkan, dan memperbarui seluruh data master operasional langsung dari ponsel:</p>

                    <h6 class="fw-bold text-dark mt-3 fs-14">1. Akses Master Hub</h6>
                    <p class="fs-13">Buka tab <strong>Profil</strong> -> pilih menu <strong>"Data Master"</strong>. Halaman akan menampilkan 2 kategori:</p>
                    <ul class="fs-13 ps-3">
                      <li><strong>Master Operasional:</strong> Kapal, Client (PBM), Dermaga, Alat, dan Aktifitas Bongkar-Muat.</li>
                      <li><strong>Master Referensi:</strong> Pelabuhan, KRK, Satuan, dan Catatan Saran.</li>
                    </ul>

                    <h6 class="fw-bold text-dark mt-4 fs-14">2. Fitur Form Modal Bottom Sheet</h6>
                    <p class="fs-13">Tampilan formulir entri dan edit menggunakan modal sheet bawah yang bersih dan terstandarisasi:</p>
                    <ul class="fs-13 ps-3">
                      <li><strong>Tampilan Bersih:</strong> Bebas dari icon dekoratif pada kolom input dan header untuk kenyamanan mengetik.</li>
                      <li><strong>Mandatory Label Merah:</strong> Kolom yang wajib diisi memiliki tanda bintang merah tegas (<span class="text-danger fw-bold">*</span>).</li>
                      <li><strong>Proteksi Integritas:</strong> Tidak tersedia tombol hapus (delete) pada mobile guna mencegah terputusnya relasi data riwayat kerja yang telah berjalan.</li>
                      <li><strong>Pencarian Cepat Debounce:</strong> Dilengkapi kolom pencarian instan untuk menemukan data dengan cepat di lapangan.</li>
                    </ul>
                  </div>
                </div>
              </div>

              <!-- Sub Tab: Sinkronisasi Offline -->
              <div class="tab-pane fade search-target" id="v-pills-m-sync" role="tabpanel">
                <div class="card shadow-sm border-0">
                  <div class="card-body">
                    <h5 class="card-title text-success mb-3"><i class="fal fa-sync-alt me-2"></i>Mekanisme Sinkronisasi Data Offline</h5>
                    <p class="fs-13">Penjelasan mengenai penyimpanan lokal dan etika penggunaan saat sinyal internet minim:</p>

                    <div class="alert alert-warning border-0 fs-13 mb-3">
                      <h6 class="text-warning fw-bold mb-1"><i class="fal fa-exclamation-triangle me-1"></i> PERINGATAN PENTING: JANGAN LOGOUT SAAT OFFLINE</h6>
                      <p class="mb-0">Jika terdapat kartu laporan bertanda merah <strong>"Belum sinkron"</strong>, <strong>JANGAN PERNAH</strong> menekan tombol Logout. Melakukan logout akan membersihkan sesi dan menghapus data antrean yang belum terkirim ke server database pusat. Lakukan sinkronisasi terlebih dahulu saat mendapatkan sinyal.</p>
                    </div>

                    <h6 class="fw-bold text-dark mt-3 fs-14">Cara Memastikan Sinkronisasi:</h6>
                    <ol class="fs-13 ps-3">
                      <li>Buka tab <strong>Profil</strong> -> periksa menu <strong>Status Sinkronisasi</strong>.</li>
                      <li>Jika status menunjukkan <em>"Synced"</em> (Sinkron) dengan tanda centang hijau, seluruh data dan foto telah aman tersimpan di server.</li>
                      <li>Pengawas dapat menekan tombol <strong>"Sync Sekarang"</strong> secara manual untuk memicu pengiriman antrean data offline saat ponsel telah mendeteksi sinyal internet.</li>
                    </ol>
                  </div>
                </div>
              </div>

            </div>
          </div>
        </div>
      </div>

    </div>
  </div>
</div>

<!-- Search & Tab Filter Script -->

@endsection

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
  const searchInput = document.getElementById('searchPanduan');
  const btnReset = document.getElementById('btnResetSearch');
  const quickTags = document.querySelectorAll('.quick-tag');
  const targetCards = document.querySelectorAll('.search-target');

  function doSearch(keyword) {
    keyword = keyword.trim().toLowerCase();
    
    if (keyword === '') {
      targetCards.forEach(card => {
        card.style.display = '';
        removeHighlight(card);
      });
      return;
    }

    let matchCount = 0;
    let firstMatchTarget = null;

    targetCards.forEach(card => {
      const text = card.textContent.toLowerCase();
      if (text.includes(keyword)) {
        card.style.display = '';
        matchCount++;
        if (!firstMatchTarget) firstMatchTarget = card;
      } else {
        card.style.display = 'none';
      }
    });

    // Auto-switch to tab containing match if currently hidden
    if (firstMatchTarget) {
      // Find parent tab-pane
      const parentTabPane = firstMatchTarget.closest('.tab-pane');
      if (parentTabPane) {
        // If it's a sub-pill tab-pane
        const subPillId = parentTabPane.getAttribute('id');
        const subPillLink = document.querySelector(`a[href="#${subPillId}"]`);
        if (subPillLink) {
          const bsTab = bootstrap.Tab.getOrCreateInstance(subPillLink);
          bsTab.show();
        }

        // Check if nested in top-level main tab
        const mainTabPane = parentTabPane.closest('#panduanContentArea > .tab-pane') || parentTabPane;
        const mainTabId = mainTabPane.getAttribute('id');
        const mainTabLink = document.querySelector(`#mainPanduanTabs a[href="#${mainTabId}"]`);
        if (mainTabLink) {
          const bsMainTab = bootstrap.Tab.getOrCreateInstance(mainTabLink);
          bsMainTab.show();
        }
      }
    }
  }

  function removeHighlight(element) {
    // Clean state
  }

  if (searchInput) {
    searchInput.addEventListener('input', function() {
      doSearch(this.value);
    });
  }

  if (btnReset) {
    btnReset.addEventListener('click', function() {
      if (searchInput) {
        searchInput.value = '';
        doSearch('');
      }
    });
  }

  quickTags.forEach(tag => {
    tag.addEventListener('click', function() {
      const q = this.getAttribute('data-query');
      if (searchInput) {
        searchInput.value = q;
        doSearch(q);
        searchInput.focus();
      }
    });
  });
});
</script>

@endpush