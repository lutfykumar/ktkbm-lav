@extends('layouts.app')

@section('title', $title ?? 'Report Work Order')
@section('title_header', $title_header ?? 'Report')
@section('title_parent', $title_parent ?? 'Work Order')

@section('content')
<style>
  .sheet-outer {
    margin: 0;
  }

  .sheet {
    margin: 0;
    overflow: hidden;
    position: relative;
    box-sizing: border-box;
    page-break-after: always;
  }

  .sheet-outer.A4 .sheet {
    width: 210mm;
    min-height: 296mm;
  }

  .sheet.padding-5mm {
    padding: 5mm;
  }

  @media screen {
    .sheet-outer {
      background: #e0e0e0;
    }

    .sheet {
      background: white;
      box-shadow: 0 .5mm 2mm rgba(0, 0, 0, .3);
      margin: 5mm auto;
    }
  }

  @page {
    size: auto;
    margin: 8mm;
  }

  /* Nav Pills Custom Styling */
  .nav-pills-report .nav-link {
    border-radius: 20px;
    padding: 8px 22px;
    color: #495057;
    background-color: #f3f3f9;
    margin-right: 10px;
    font-size: 13px;
    font-weight: 600;
    transition: all 0.25s ease;
    border: 1px solid #e2e5e8;
  }

  .nav-pills-report .nav-link:hover {
    background-color: rgba(var(--vz-primary-rgb, 64, 81, 137), 0.08);
    color: var(--vz-primary, #405189);
    border-color: rgba(var(--vz-primary-rgb, 64, 81, 137), 0.3);
  }

  .nav-pills-report .nav-link.active {
    background-color: var(--vz-primary, #405189);
    color: #ffffff;
    border-color: var(--vz-primary, #405189);
    box-shadow: 0 3px 6px rgba(var(--vz-primary-rgb, 64, 81, 137), 0.25);
  }

  /* Rekap Kegiatan Kapal Screen Styles */
  .kegiatan-kapal-sheet {
    background: #ffffff;
    border: 1px solid #cbd5e1;
    border-radius: 6px;
    padding: 16px;
    box-shadow: 0 1px 3px rgba(0, 0, 0, 0.08);
  }

  .kegiatan-kapal-banner {
    background-color: var(--vz-primary, #405189);
    color: #ffffff;
    padding: 10px 15px;
    text-align: center;
    border-radius: 4px 4px 0 0;
    border: 1px solid #33416e;
  }

  .kegiatan-kapal-banner .banner-title {
    margin: 0;
    font-size: 16px;
    font-weight: 700;
    letter-spacing: 0.5px;
    color: #ffffff;
    text-transform: uppercase;
  }

  .kegiatan-kapal-banner .banner-subtitle {
    margin: 4px 0 0 0;
    font-size: 13px;
    font-weight: 600;
    color: #d8ddf0;
    text-transform: uppercase;
  }

  .table-kegiatan-kapal {
    width: 100%;
    border-collapse: collapse;
    font-size: 11px;
    margin-bottom: 0;
  }

  .table-kegiatan-kapal th,
  .table-kegiatan-kapal td {
    border: 1px solid #33416e;
    padding: 5px 8px;
    vertical-align: middle;
  }

  .table-kegiatan-kapal thead th {
    background-color: var(--vz-primary, #405189);
    color: #ffffff;
    font-weight: 700;
    text-align: center;
  }

  .table-kegiatan-kapal tbody tr:hover {
    background-color: #f3f5fa;
  }

  .table-kegiatan-kapal tfoot td {
    background-color: #eef0f7;
    font-weight: 700;
  }

  @media print {
    /* Hide everything except active report */
    .filter-section,
    .nav-pills-report,
    hr,
    .app-menu,
    .navbar,
    .footer,
    .page-title-box,
    .page-content > .container-fluid > .row:first-child,
    #back-to-top {
      display: none !important;
    }

    body,
    .page-content,
    .main-content,
    .container-fluid,
    .card,
    .card-body {
      margin: 0 !important;
      padding: 0 !important;
      background: #ffffff !important;
      box-shadow: none !important;
      border: none !important;
    }

    .tab-pane:not(.active) {
      display: none !important;
    }

    .sheet-outer.A4,
    .sheet-outer.A5.landscape {
      width: 210mm;
    }

    /* Print styles for Tab 2 */
    .kegiatan-kapal-sheet {
      border: none !important;
      box-shadow: none !important;
      padding: 0 !important;
    }

    .kegiatan-kapal-banner {
      background-color: #405189 !important;
      color: #ffffff !important;
      -webkit-print-color-adjust: exact !important;
      print-color-adjust: exact !important;
      border: 1px solid #33416e !important;
    }

    .kegiatan-kapal-banner .banner-title,
    .kegiatan-kapal-banner .banner-subtitle {
      color: #ffffff !important;
      -webkit-print-color-adjust: exact !important;
      print-color-adjust: exact !important;
    }

    .table-kegiatan-kapal th {
      background-color: #405189 !important;
      color: #ffffff !important;
      -webkit-print-color-adjust: exact !important;
      print-color-adjust: exact !important;
      border: 1px solid #33416e !important;
    }

    .table-kegiatan-kapal td {
      border: 1px solid #33416e !important;
    }

    .table-kegiatan-kapal tfoot td {
      background-color: #eef0f7 !important;
      -webkit-print-color-adjust: exact !important;
      print-color-adjust: exact !important;
    }

    .table-responsive {
      overflow: visible !important;
      margin-bottom: 8px !important;
    }

    .chart-print-block {
      display: flex !important;
      flex-direction: row !important;
      flex-wrap: nowrap !important;
      align-items: center !important;
      justify-content: flex-start !important;
      margin-bottom: 8px !important;
      page-break-inside: avoid;
    }

    .chart-print-block .col-print-table {
      width: 48% !important;
      max-width: 48% !important;
      flex: 0 0 48% !important;
      margin-bottom: 0 !important;
      padding-right: 15px !important;
    }

    .chart-print-block .col-print-chart {
      width: 52% !important;
      max-width: 52% !important;
      flex: 0 0 52% !important;
      margin-bottom: 0 !important;
      padding-left: 0 !important;
      display: flex !important;
      justify-content: center !important;
    }

    .chart-print-block .col-print-chart .apexcharts-canvas {
      margin: 0 auto !important;
    }
  }
</style>

<div class="row">
  <div class="col-12">
    <div class="card">
      <div class="card-body">

        <!-- Tab Pills Navigation -->
        <ul class="nav nav-pills nav-pills-report mb-4" id="report_tabs" role="tablist">
          <li class="nav-item" role="presentation">
            <button class="nav-link active" id="tab_wo_btn" data-bs-toggle="pill" data-bs-target="#tab_laporan_wo" type="button" role="tab" aria-controls="tab_laporan_wo" aria-selected="true">
              <i class="fal fa-file-alt me-1"></i> Laporan Work Order
            </button>
          </li>
          <li class="nav-item" role="presentation">
            <button class="nav-link" id="tab_kegiatan_btn" data-bs-toggle="pill" data-bs-target="#tab_kegiatan_kapal" type="button" role="tab" aria-controls="tab_kegiatan_kapal" aria-selected="false">
              <i class="fal fa-ship me-1"></i> Rekap Kegiatan Kapal
            </button>
          </li>
        </ul>

        <!-- Tab Content Panes -->
        <div class="tab-content" id="report_tab_content">

          <!-- ==================== TAB 1: LAPORAN WORK ORDER (DEFAULT) ==================== -->
          <div class="tab-pane fade show active" id="tab_laporan_wo" role="tabpanel" aria-labelledby="tab_wo_btn">
            <!-- Filter Section Tab 1 -->
            <div class="filter-section">
              <h5>Filter Work Order</h5>
              <div class="row">
                <div class="col-lg-3 col-md-6 col-sm-12">
                  <div class="fm mb-1">
                    <label for="filter_tgl_start" class="form-label mb-1">Pilih Periode Entry WO</label>
                    <div class="input-group">
                      <input type="date" class="form-control" id="filter_tgl_start" name="filter_tgl_start">
                      <span class="input-group-text">-</span>
                      <input type="date" class="form-control" id="filter_tgl_end" name="filter_tgl_end">
                    </div>
                  </div>
                </div>
                <div class="col-lg-3 col-md-6 col-sm-12">
                  <div class="fm mb-1">
                    <label for="filter_pelabuhan" class="form-label mb-1">Pilih Pelabuhan</label>
                    <select class="form-control filter_pelabuhan" id="filter_pelabuhan" name="filter_pelabuhan" style="width: 100%;">
                    </select>
                  </div>
                </div>
                <div class="col-lg-3 col-md-6 col-sm-12">
                  <div class="fm mb-1">
                    <label for="filter_client" class="form-label mb-1">Pilih Client</label>
                    <select class="form-control filter_client" id="filter_client" name="filter_client" style="width: 100%;">
                    </select>
                  </div>
                </div>
                <div class="col-lg-3 col-md-6 col-sm-12">
                  <div class="fm mb-1">
                    <label for="filter_pengawas" class="form-label mb-1">Pilih Pengawas / Penanggung Jawab</label>
                    <select class="form-control filter_pengawas" id="filter_pengawas" name="filter_pengawas" style="width: 100%;">
                    </select>
                  </div>
                </div>
                <div class="col-12 mb-0">
                  <div class="text-center pt-2">
                    <button type="button" class="btn btn-danger btn-sm w-sm waves-effect waves-light me-3" id="btn_reset"><i class="fal fa-sync me-1"></i> Reset</button>
                    <button type="button" class="btn btn-success btn-sm w-sm waves-effect waves-light me-3" id="btn_search"><i class="fal fa-search me-1"></i> Preview</button>
                    <button type="button" class="btn btn-primary btn-sm w-sm waves-effect waves-light" id="btn_print"><i class="fal fa-print me-1"></i> Cetak</button>
                  </div>
                </div>
              </div>
              <hr>
            </div>

            <!-- Document Container (dynamically rendered) -->
            <div id="documents_container">
              <div class="text-center text-muted py-5 d-flex flex-column align-items-center justify-content-center">
                <i class="fal fa-file-alt fa-3x mb-3"></i>
                <p class="mb-0">Pilih filter dan klik <strong>Preview</strong> untuk menampilkan laporan</p>
              </div>
            </div>
          </div>

          <!-- ==================== TAB 2: REKAP KEGIATAN KAPAL ==================== -->
          <div class="tab-pane fade" id="tab_kegiatan_kapal" role="tabpanel" aria-labelledby="tab_kegiatan_btn">
            <!-- Filter Section Tab 2 -->
            <div class="filter-section">
              <h5>Filter Rekap Kegiatan Kapal</h5>
              <div class="row align-items-end">
                <div class="col-lg-4 col-md-6 col-sm-12">
                  <div class="fm mb-2">
                    <label for="filter_kegiatan_pelabuhan" class="form-label mb-1">Pilih Pelabuhan <span class="text-danger">*</span></label>
                    <select class="form-control" id="filter_kegiatan_pelabuhan" name="filter_kegiatan_pelabuhan" style="width: 100%;">
                    </select>
                  </div>
                </div>
                <div class="col-lg-3 col-md-6 col-sm-12">
                  <div class="fm mb-2">
                    <label for="filter_kegiatan_bulan" class="form-label mb-1">Pilih Bulan & Tahun <span class="text-danger">*</span></label>
                    <input type="month" class="form-control" id="filter_kegiatan_bulan" name="filter_kegiatan_bulan" value="{{ date('Y-m') }}">
                  </div>
                </div>
                <div class="col-lg-5 col-md-12 col-sm-12 mb-2">
                  <div class="pt-1">
                    <button type="button" class="btn btn-danger btn-sm w-sm waves-effect waves-light me-2" id="btn_reset_kegiatan"><i class="fal fa-sync me-1"></i> Reset</button>
                    <button type="button" class="btn btn-success btn-sm w-sm waves-effect waves-light me-2" id="btn_search_kegiatan"><i class="fal fa-search me-1"></i> Preview</button>
                    <button type="button" class="btn btn-primary btn-sm w-sm waves-effect waves-light" id="btn_print_kegiatan"><i class="fal fa-print me-1"></i> Cetak</button>
                  </div>
                </div>
              </div>
              <hr>
            </div>

            <!-- Rekap Kegiatan Kapal Content Container -->
            <div id="kegiatan_kapal_container">
              <div class="text-center text-muted py-5 d-flex flex-column align-items-center justify-content-center">
                <i class="fal fa-ship fa-3x mb-3 text-primary"></i>
                <p class="mb-0">Pilih Pelabuhan dan Bulan, lalu klik <strong>Preview</strong> untuk menampilkan Rekap Kegiatan Kapal</p>
              </div>
            </div>
          </div>

        </div>

      </div>
    </div>
  </div>
</div>
@endsection

@push('scripts')
<!-- ApexCharts Library -->
<script src="{{ asset('assets/libs/apexcharts/apexcharts.min.js') }}"></script>

<script>
  const route_link = ip_server + 'report/workorder/';
  const current_username = '{{ auth()->user()->username ?? "User" }}';
  const current_date = '{{ date("d F Y") }}';
  const app_logo = '{{ asset(get_app_setting("site_logo", "assets/images/logo-dark.png")) }}';
  const app_name = '{{ get_app_setting("site_name", "TKBM") }}';
  const app_address = '{{ get_app_setting("company_address", "") }}';
  const app_phone = '{{ get_app_setting("company_phone", "") }}';
  const app_email = '{{ get_app_setting("company_email", "") }}';

  let chartPbmInstance = null;
  let chartKomoditasInstance = null;

  $(document).ready(function() {
    // Tab 1 filters initialization
    get_all_client('#filter_client');
    get_all_pelabuhan('#filter_pelabuhan');
    get_all_users_by_role('#filter_pengawas', '', 19);

    // Tab 2 filter pelabuhan initialization
    get_all_pelabuhan('#filter_kegiatan_pelabuhan');

    // Handle tab change to recalculate Select2 width in Tab 2
    $('button[data-bs-toggle="pill"]').on('shown.bs.tab', function(e) {
      if ($(e.target).attr('id') === 'tab_kegiatan_btn') {
        $('#filter_kegiatan_pelabuhan').css('width', '100%');
      }
    });

    // ==================== TAB 1 EVENTS ====================
    $("#btn_search").click(function(e) {
      e.preventDefault();
      if (!validatePeriode()) return;
      load_data(
        $('#filter_tgl_start').val(),
        $('#filter_tgl_end').val(),
        $('#filter_pelabuhan').val() || '',
        $('#filter_client').val() || '',
        $('#filter_pengawas').val() || ''
      );
    });

    $("#btn_reset").click(function(e) {
      e.preventDefault();
      $('#filter_tgl_start').val('');
      $('#filter_tgl_end').val('');
      $('#filter_pelabuhan').val('').trigger('change');
      $('#filter_client').val('').trigger('change');
      $('#filter_pengawas').val('').trigger('change');
      $('#documents_container').html(
        '<div class="text-center text-muted py-5 d-flex flex-column align-items-center justify-content-center">' +
        '<i class="fal fa-file-alt fa-3x mb-3"></i>' +
        '<p class="mb-0">Pilih filter dan klik <strong>Preview</strong> untuk menampilkan laporan</p></div>'
      );
    });

    $("#btn_print").click(function(e) {
      e.preventDefault();
      if (!validatePeriode()) return;
      if ($('.sheet').length === 0) {
        toast_error('Tidak ada data untuk dicetak. Klik Preview terlebih dahulu.');
        return;
      }
      window.print();
    });

    // ==================== TAB 2 EVENTS ====================
    $("#btn_search_kegiatan").click(function(e) {
      e.preventDefault();
      let pelabuhan = $('#filter_kegiatan_pelabuhan').val();
      let bulan = $('#filter_kegiatan_bulan').val();

      if (!pelabuhan) {
        toast_error('Pilih Pelabuhan terlebih dahulu');
        $('#filter_kegiatan_pelabuhan').focus();
        return;
      }

      if (!bulan) {
        toast_error('Pilih Bulan & Tahun');
        $('#filter_kegiatan_bulan').focus();
        return;
      }

      load_kegiatan_kapal(pelabuhan, bulan);
    });

    $("#btn_reset_kegiatan").click(function(e) {
      e.preventDefault();
      $('#filter_kegiatan_pelabuhan').val('').trigger('change');
      $('#filter_kegiatan_bulan').val('{{ date('Y-m') }}');
      if (chartPbmInstance) {
        chartPbmInstance.destroy();
        chartPbmInstance = null;
      }
      if (chartKomoditasInstance) {
        chartKomoditasInstance.destroy();
        chartKomoditasInstance = null;
      }
      $('#kegiatan_kapal_container').html(
        '<div class="text-center text-muted py-5 d-flex flex-column align-items-center justify-content-center">' +
        '<i class="fal fa-ship fa-3x mb-3 text-success"></i>' +
        '<p class="mb-0">Pilih Pelabuhan dan Bulan, lalu klik <strong>Preview</strong> untuk menampilkan Rekap Kegiatan Kapal</p>' +
        '</div>'
      );
    });

    $("#btn_print_kegiatan").click(function(e) {
      e.preventDefault();
      if ($('#kegiatan_kapal_container .table-kegiatan-kapal').length === 0) {
        toast_error('Tidak ada data untuk dicetak. Klik Preview terlebih dahulu.');
        return;
      }
      window.print();
    });
  });

  // ==================== TAB 1 FUNCTIONS ====================
  function validatePeriode() {
    let start = $('#filter_tgl_start').val();
    let end = $('#filter_tgl_end').val();

    if (!start || !end) {
      toast_error('Periode tanggal wajib diisi (Mulai dan Sampai)');
      !start ? $('#filter_tgl_start').focus() : $('#filter_tgl_end').focus();
      return false;
    }

    let dStart = new Date(start);
    let dEnd = new Date(end);

    if (isNaN(dStart.getTime())) {
      toast_error('Tanggal mulai tidak valid');
      $('#filter_tgl_start').focus();
      return false;
    }
    if (isNaN(dEnd.getTime())) {
      toast_error('Tanggal akhir tidak valid');
      $('#filter_tgl_end').focus();
      return false;
    }
    if (dStart > dEnd) {
      toast_error('Tanggal mulai tidak boleh lebih besar dari tanggal akhir');
      $('#filter_tgl_start').focus();
      return false;
    }

    return true;
  }

  function load_data(tgl_start, tgl_end, pelabuhan, client, pengawas) {
    let btn = $('#btn_search');
    btn.addClass('disabled').html('<i class="fal fa-spinner fa-spin me-1"></i> Loading...');

    let postData = {
      tgl_start: tgl_start,
      tgl_end: tgl_end,
      pelabuhan: pelabuhan,
      client: client,
      pengawas: pengawas,
      _token: '{{ csrf_token() }}'
    };

    $.ajax({
      url: route_link + 'get_report_data',
      type: 'POST',
      dataType: 'JSON',
      data: postData,
      success: function(res) {
        if (res.status && res.documents) {
          if (res.documents.length === 0) {
            $('#documents_container').html(
              '<div class="text-center text-muted py-5 d-flex flex-column align-items-center justify-content-center">' +
              '<i class="fal fa-search fa-3x mb-3 text-warning"></i>' +
              '<p class="mb-0">Tidak ada data ditemukan untuk filter yang dipilih</p></div>'
            );
          } else {
            let html = '';
            res.documents.forEach(function(doc, docIdx) {
              html += buildDocument(doc, res.periode, docIdx + 1, res.documents.length);
            });
            $('#documents_container').html(html);
          }
        } else {
          toast_error(res.msg || 'Gagal memuat data');
        }

        btn.removeClass('disabled').html('<i class="fal fa-search me-1"></i> Preview');
      },
      error: function() {
        toast_error('Gagal memuat data report');
        btn.removeClass('disabled').html('<i class="fal fa-search me-1"></i> Preview');
      }
    });
  }

  function buildDocument(doc, periode, pageNum, totalPages) {
    let tableRows = '';
    let woNo = 1;

    doc.rows.forEach(function(wo) {
      let detailCount = wo.details.length;
      let rowspan = detailCount > 0 ? detailCount : 1;

      let kapalHtml = '<strong>' + wo.kapal_line1 + '</strong>';
      kapalHtml += '<br><small>' + wo.kapal_line2 + '</small>';
      kapalHtml += '<br><small>' + wo.kapal_line3 + '</small>';
      if (wo.kapal_line4) {
        kapalHtml += '<br><small>' + wo.kapal_line4 + '</small>';
      }

      if (detailCount === 0) {
        tableRows += '<tr>' +
          '<td style="border: 1px solid #bdc3c7; padding: 6px; text-align: center; vertical-align: top;">' + woNo + '</td>' +
          '<td style="border: 1px solid #bdc3c7; padding: 6px; font-size: 10px; vertical-align: top;">' + kapalHtml + '</td>' +
          '<td style="border: 1px solid #bdc3c7; padding: 6px; text-align: center; font-size: 10px;">-</td>' +
          '<td style="border: 1px solid #bdc3c7; padding: 6px; text-align: center; font-size: 10px;">-</td>' +
          '<td style="border: 1px solid #bdc3c7; padding: 6px; text-align: center; font-size: 10px;">-</td>' +
          '<td style="border: 1px solid #bdc3c7; padding: 6px; text-align: center; font-size: 10px;">-</td>' +
          '<td style="border: 1px solid #bdc3c7; padding: 6px; font-size: 10px;">-</td>' +
          '</tr>';
      } else {
        wo.details.forEach(function(detail, dIdx) {
          tableRows += '<tr>';
          if (dIdx === 0) {
            tableRows += '<td style="border: 1px solid #bdc3c7; padding: 6px; text-align: center; vertical-align: top;" rowspan="' + rowspan + '">' + woNo + '</td>';
            tableRows += '<td style="border: 1px solid #bdc3c7; padding: 6px; font-size: 10px; vertical-align: top;" rowspan="' + rowspan + '">' + kapalHtml + '</td>';
          }
          tableRows += '<td style="border: 1px solid #bdc3c7; padding: 6px; text-align: center; font-size: 10px;">' + detail.tanggal + '</td>';
          tableRows += '<td style="border: 1px solid #bdc3c7; padding: 6px; text-align: center; font-size: 10px;">' + detail.nama_krk + '</td>';
          tableRows += '<td style="border: 1px solid #bdc3c7; padding: 6px; text-align: center; font-size: 10px;">' + detail.hasil + '</td>';
          tableRows += '<td style="border: 1px solid #bdc3c7; padding: 6px; text-align: center; font-size: 10px;">' + detail.ransum + '</td>';
          tableRows += '<td style="border: 1px solid #bdc3c7; padding: 6px; font-size: 10px;">' + detail.catatan + '</td>';
          tableRows += '</tr>';
        });
      }
      woNo++;
    });

    if (!tableRows) {
      tableRows = '<tr><td colspan="7" style="border: 1px solid #bdc3c7; padding: 20px; text-align: center; color: #95a5a6;">Tidak ada data</td></tr>';
    }

    return '<div class="sheet-outer A4 p-2">' +
      '<section class="sheet padding-5mm">' +
      '<article>' +
      '<div style="border-bottom: 3px solid #2c3e50; padding-bottom: 10px; margin-bottom: 20px;">' +
      '<table style="width: 100%; border: none;">' +
      '<tr>' +
      '<td style="width: 80px; vertical-align: top;">' +
      '<img src="' + app_logo + '" alt="Logo" style="width: 70px; height: 70px; object-fit: contain; border-radius: 8px;">' +
      '</td>' +
      '<td style="vertical-align: top; padding-left: 15px;">' +
      '<h2 style="margin: 0; color: #2c3e50; font-size: 24px; font-weight: bold;">' + app_name + '</h2>' +
      '<p style="margin: 5px 0 0 0; color: #7f8c8d; font-size: 12px; line-height: 1.5;">' +
      (app_address ? app_address + '<br>' : '') +
      (app_phone ? 'Telp: ' + app_phone : '') + (app_phone && app_email ? ' | ' : '') + (app_email ? 'Email: ' + app_email : '') + '</p>' +
      '</td>' +
      '</tr>' +
      '</table>' +
      '</div>' +

      '<div style="text-align: center; margin-bottom: 10px;">' +
      '<h3 style="margin: 0; color: #2c3e50; font-size: 18px; font-weight: bold; text-transform: uppercase; letter-spacing: 1px;">LAPORAN WORK ORDER</h3>' +
      '<p style="margin: 5px 0 0 0; color: #7f8c8d; font-size: 12px;">Periode: ' + periode + '</p>' +
      '</div>' +

      '<div style="background: #ecf0f1; padding: 7px 12px; border-radius: 6px; margin-bottom: 7px;">' +
      '<table style="width: 100%; font-size: 11px;">' +
      '<tr>' +
      '<td style="width: 20%; padding: 3px 0;"><strong>Pelabuhan</strong></td>' +
      '<td style="width: 30%; padding: 3px 0;">: ' + doc.pelabuhan + '</td>' +
      '<td style="padding: 3px 0;"><strong>Cetak Oleh / Tanggal</strong></td>' +
      '<td style="padding: 3px 0;">: ' + current_username + ' / ' + current_date + '</td>' +
      '</tr>' +
      '<tr>' +
      '<td style="padding: 3px 0;"><strong>Total Line/GG</strong></td>' +
      '<td style="padding: 3px 0;">: ' + doc.total_gg + '</td>' +
      '<td style="padding: 3px 0;"><strong>Halaman</strong></td>' +
      '<td style="padding: 3px 0;">: ' + pageNum + ' dari ' + totalPages + '</td>' +
      '</tr>' +
      (doc.client ? '<tr><td style="padding: 3px 0;"><strong>Client</strong></td><td style="padding: 3px 0;">: ' + doc.client + '</td><td></td><td></td></tr>' : '') +
      '</table>' +
      '</div>' +

      '<table style="width: 100%; border-collapse: collapse; font-size: 10px; margin-bottom: 20px;">' +
      '<thead>' +
      '<tr style="background: #34495e; color: white;">' +
      '<th style="border: 1px solid #2c3e50; padding: 8px; text-align: center; width: 30px;">NO</th>' +
      '<th style="border: 1px solid #2c3e50; padding: 8px; text-align: center; width: 180px;">NAMA KAPAL (PBM)</th>' +
      '<th style="border: 1px solid #2c3e50; padding: 8px; text-align: center; width: 65px;">TANGGAL</th>' +
      '<th style="border: 1px solid #2c3e50; padding: 8px; text-align: center; width: 85px;">NAMA KRK</th>' +
      '<th style="border: 1px solid #2c3e50; padding: 8px; text-align: center; width: 65px;">HASIL</th>' +
      '<th style="border: 1px solid #2c3e50; padding: 8px; text-align: center; width: 65px;">RANSUM</th>' +
      '<th style="border: 1px solid #2c3e50; padding: 8px; text-align: center;">CATATAN</th>' +
      '</tr>' +
      '</thead>' +
      '<tbody>' + tableRows + '</tbody>' +
      '</table>' +

      '<div style="margin-top: 30px;">' +
      '<table style="width: 100%; font-size: 11px;">' +
      '<tr>' +
      '<td style="width: 50%; text-align: center;">' +
      '<p style="margin: 0 0 50px 0;">Mengetahui,</p>' +
      '<p style="margin: 0; border-top: 1px solid #2c3e50; display: inline-block; padding-top: 5px; min-width: 150px;">' +
      '<strong>Manager</strong></p>' +
      '</td>' +
      '<td style="width: 50%; text-align: center;">' +
      '<p style="margin: 0 0 50px 0;">Dibuat Oleh,</p>' +
      '<p style="margin: 0; border-top: 1px solid #2c3e50; display: inline-block; padding-top: 5px; min-width: 150px;">' +
      '<strong>' + doc.pengawas + '</strong></p>' +
      '</td>' +
      '</tr>' +
      '</table>' +
      '</div>' +

      '<div style="position: absolute; bottom: 5mm; right: 5mm; font-size: 9px; color: #95a5a6;">' +
      'Halaman ' + pageNum + ' dari ' + totalPages +
      '</div>' +

      '</article>' +
      '</section>' +
      '</div>';
  }

  // ==================== TAB 2 FUNCTIONS ====================
  function load_kegiatan_kapal(pelabuhan, bulan) {
    let btn = $('#btn_search_kegiatan');
    btn.addClass('disabled').html('<i class="fal fa-spinner fa-spin me-1"></i> Loading...');

    let postData = {
      pelabuhan: pelabuhan,
      bulan_tahun: bulan,
      _token: '{{ csrf_token() }}'
    };

    $.ajax({
      url: route_link + 'get_kegiatan_kapal',
      type: 'POST',
      dataType: 'JSON',
      data: postData,
      success: function(res) {
        if (res.status) {
          let data = res.data ? res.data : res;
          renderKegiatanKapal(data);
        } else {
          toast_error(res.msg || 'Gagal memuat data');
        }
        btn.removeClass('disabled').html('<i class="fal fa-search me-1"></i> Preview');
      },
      error: function() {
        toast_error('Gagal memuat data Rekap Kegiatan Kapal');
        btn.removeClass('disabled').html('<i class="fal fa-search me-1"></i> Preview');
      }
    });
  }

  function renderKegiatanKapal(data) {
    if (chartPbmInstance) {
      chartPbmInstance.destroy();
      chartPbmInstance = null;
    }
    if (chartKomoditasInstance) {
      chartKomoditasInstance.destroy();
      chartKomoditasInstance = null;
    }

    let tableRows = '';
    if (!data.rows || data.rows.length === 0) {
      tableRows = '<tr><td colspan="8" class="text-center text-muted py-4"><i class="fal fa-info-circle me-1"></i> Tidak ada data kegiatan kapal pada periode ini</td></tr>';
    } else {
      data.rows.forEach(function(row) {
        tableRows += '<tr>' +
          '<td class="text-center">' + row.no + '</td>' +
          '<td class="fw-bold">' + row.kapal + '</td>' +
          '<td class="text-center">' + row.pbm + '</td>' +
          '<td class="text-center">' + row.no_inv + '</td>' +
          '<td class="text-center">' + (row.bongkar || '') + '</td>' +
          '<td class="text-center">' + (row.muat || '') + '</td>' +
          '<td class="text-end">' + row.party + '</td>' +
          '<td class="text-center">' + row.kegiatan + '</td>' +
          '</tr>';
      });
    }

    let pbmRows = '';
    if (data.summary_pbm && data.summary_pbm.length > 0) {
      data.summary_pbm.forEach(function(pbm) {
        pbmRows += '<tr>' +
          '<td class="text-center" style="width: 40px;">' + pbm.no + '</td>' +
          '<td>' + pbm.pbm + '</td>' +
          '<td class="text-center" style="width: 80px;">' + pbm.total + '</td>' +
          '</tr>';
      });
    } else {
      pbmRows = '<tr><td colspan="3" class="text-center text-muted">-</td></tr>';
    }

    let komoditasRows = '';
    if (data.summary_komoditas && data.summary_komoditas.length > 0) {
      data.summary_komoditas.forEach(function(kom) {
        komoditasRows += '<tr>' +
          '<td class="text-center" style="width: 40px;">' + kom.no + '</td>' +
          '<td>' + kom.komoditas + '</td>' +
          '<td class="text-end" style="width: 120px;">' + kom.total + '</td>' +
          '</tr>';
      });
    } else {
      komoditasRows = '<tr><td colspan="3" class="text-center text-muted">-</td></tr>';
    }

    let html = `
      <div class="kegiatan-kapal-sheet">
        <div class="kegiatan-kapal-banner">
          <div class="banner-title">${data.header_title}</div>
          <div class="banner-subtitle">${data.header_periode}</div>
        </div>

        <div class="table-responsive mb-4">
          <table class="table-kegiatan-kapal">
            <thead>
              <tr>
                <th style="width: 35px;">NO</th>
                <th>KAPAL</th>
                <th style="width: 100px;">PBM</th>
                <th style="width: 95px;">NO.INV</th>
                <th style="width: 140px;">BONGKAR</th>
                <th style="width: 140px;">MUAT</th>
                <th style="width: 110px;">PARTY</th>
                <th style="width: 110px;">KEGIATAN</th>
              </tr>
            </thead>
            <tbody>
              ${tableRows}
            </tbody>
            <tfoot>
              <tr>
                <td colspan="6" class="text-center">TTL ${data.total_kapal} KAPAL</td>
                <td class="text-end">${data.grand_total_party}</td>
                <td></td>
              </tr>
            </tfoot>
          </table>
        </div>

        <div class="row align-items-center mb-4 chart-print-block">
          <div class="col-lg-5 col-md-6 mb-3 mb-md-0 col-print-table">
            <table class="table-kegiatan-kapal">
              <thead>
                <tr>
                  <th style="width: 40px;">NO</th>
                  <th>PBM</th>
                  <th style="width: 80px;">TOTAL</th>
                </tr>
              </thead>
              <tbody>
                ${pbmRows}
              </tbody>
              <tfoot>
                <tr>
                  <td colspan="2" class="text-center">TOTAL</td>
                  <td class="text-center">${data.total_kapal}</td>
                </tr>
              </tfoot>
            </table>
          </div>
          <div class="col-lg-7 col-md-6 col-print-chart">
            <div id="chart_pbm" style="min-height: 190px;"></div>
          </div>
        </div>

        <div class="row align-items-center chart-print-block">
          <div class="col-lg-5 col-md-6 mb-3 mb-md-0 col-print-table">
            <table class="table-kegiatan-kapal">
              <thead>
                <tr>
                  <th style="width: 40px;">NO</th>
                  <th>BONGKAR/MUAT</th>
                  <th style="width: 120px;">TOTAL</th>
                </tr>
              </thead>
              <tbody>
                ${komoditasRows}
              </tbody>
              <tfoot>
                <tr>
                  <td colspan="2" class="text-center">TOTAL</td>
                  <td class="text-end">${data.grand_total_party}</td>
                </tr>
              </tfoot>
            </table>
          </div>
          <div class="col-lg-7 col-md-6 col-print-chart">
            <div id="chart_komoditas" style="min-height: 190px;"></div>
          </div>
        </div>
      </div>
    `;

    $('#kegiatan_kapal_container').html(html);

    if (data.pbm_chart_series && data.pbm_chart_series.length > 0) {
      let optionsPbm = {
        series: data.pbm_chart_series,
        labels: data.pbm_chart_labels,
        chart: {
          type: 'pie',
          height: 190,
          width: 320,
          toolbar: {
            show: false
          }
        },
        colors: ['#405189', '#3577f1', '#0ab39c', '#f7b84b', '#f06548', '#299cdb', '#6559cc', '#f672a7'],
        legend: {
          position: 'right',
          offsetY: 10,
          fontSize: '12px'
        },
        dataLabels: {
          enabled: true,
          formatter: function(val, opts) {
            return opts.w.config.series[opts.seriesIndex] + ' Kapal';
          }
        },
        tooltip: {
          y: {
            formatter: function(val) {
              return val + ' Kapal';
            }
          }
        }
      };
      chartPbmInstance = new ApexCharts(document.querySelector("#chart_pbm"), optionsPbm);
      chartPbmInstance.render();
    } else {
      $('#chart_pbm').html('<div class="text-center text-muted py-4"><i class="fal fa-chart-pie fa-2x mb-2 d-block"></i>Tidak ada data grafik PBM</div>');
    }

    if (data.komoditas_chart_series && data.komoditas_chart_series.length > 0) {
      let optionsKomoditas = {
        series: data.komoditas_chart_series,
        labels: data.komoditas_chart_labels,
        chart: {
          type: 'pie',
          height: 190,
          width: 320,
          toolbar: {
            show: false
          }
        },
        colors: ['#2f3b64', '#405189', '#4d62a5', '#5c74c1', '#3577f1', '#5c94f5', '#82aff7', '#a9c9fa'],
        legend: {
          position: 'right',
          offsetY: 10,
          fontSize: '12px'
        },
        dataLabels: {
          enabled: true,
          formatter: function(val) {
            return val.toFixed(1) + '%';
          }
        },
        tooltip: {
          y: {
            formatter: function(val) {
              return val.toLocaleString('id-ID', {
                minimumFractionDigits: 2,
                maximumFractionDigits: 2
              }) + ' Ton';
            }
          }
        }
      };
      chartKomoditasInstance = new ApexCharts(document.querySelector("#chart_komoditas"), optionsKomoditas);
      chartKomoditasInstance.render();
    } else {
      $('#chart_komoditas').html('<div class="text-center text-muted py-4"><i class="fal fa-chart-pie fa-2x mb-2 d-block"></i>Tidak ada data grafik Komoditas</div>');
    }
  }
</script>
@endpush
