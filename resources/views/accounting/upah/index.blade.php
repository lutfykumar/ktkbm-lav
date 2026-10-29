@extends('layouts.app')

@section('title', $title ?? 'Upah & Kwitansi TKBM')
@section('title_header', $title_header ?? 'Upah & Kwitansi TKBM')
@section('title_parent', $title_parent ?? 'Accounting')

@section('content')
<div class="row">
  <div class="col-xl-3 col-md-6">
    <div class="card card-animate text-center">
      <div class="card-body">
        <h5 class="text-muted text-uppercase fs-13">Total Dokumen</h5>
        <h3 class="mb-0 text-primary"><span class="counter-value" data-target="{{ $stats['total_dokumen'] ?? 0 }}">{{ $stats['total_dokumen'] ?? 0 }}</span> Berkas</h3>
      </div>
    </div>
  </div>
  <div class="col-xl-3 col-md-6">
    <div class="card card-animate text-center">
      <div class="card-body">
        <h5 class="text-muted text-uppercase fs-13">Total Nilai Tagihan</h5>
        <h3 class="mb-0 text-dark text-truncate">Rp <span class="counter-value" data-target="{{ $stats['total_nominal'] ?? 0 }}">{{ ribuan($stats['total_nominal'] ?? 0) }}</span></h3>
      </div>
    </div>
  </div>
  <div class="col-xl-3 col-md-6">
    <div class="card card-animate text-center">
      <div class="card-body">
        <h5 class="text-muted text-uppercase fs-13">Total Lunas</h5>
        <h3 class="mb-0 text-success text-truncate">Rp <span class="counter-value" data-target="{{ $stats['total_lunas'] ?? 0 }}">{{ ribuan($stats['total_lunas'] ?? 0) }}</span></h3>
      </div>
    </div>
  </div>
  <div class="col-xl-3 col-md-6">
    <div class="card card-animate text-center">
      <div class="card-body">
        <h5 class="text-muted text-uppercase fs-13">Pending / Tertagih</h5>
        <h3 class="mb-0 text-warning text-truncate">Rp <span class="counter-value" data-target="{{ $stats['total_pending'] ?? 0 }}">{{ ribuan($stats['total_pending'] ?? 0) }}</span></h3>
      </div>
    </div>
  </div>
</div>

<div class="row">
  <div class="col-12">
    <!-- FILTER -->
    <div class="card">
      <div class="card-body">
        <div class="d-flex justify-content-between align-items-center mb-3">
          <h5 class="mb-0">Daftar Perhitungan & Kwitansi Upah TKBM</h5>
          @if (!empty($permission_create))
            <a href="{{ url('accounting/upah/create') }}" class="btn btn-primary btn-sm waves-effect waves-light">
              <i class="fal fa-plus me-1"></i> Buat Perhitungan Upah
            </a>
          @endif
        </div>
        <div class="row g-3">
          <div class="col-lg-3 col-md-6 col-sm-12">
            <label for="filter_tgl_start" class="form-label mb-1">Periode Tanggal</label>
            <div class="input-group">
              <input type="date" class="form-control" id="filter_tgl_start" name="filter_tgl_start">
              <span class="input-group-text">-</span>
              <input type="date" class="form-control" id="filter_tgl_end" name="filter_tgl_end">
            </div>
          </div>
          <div class="col-lg-3 col-md-6 col-sm-12">
            <label for="filter_client" class="form-label mb-1">Pilih PBM / Client</label>
            <select class="form-control ajax_select field_all_client" id="filter_client" name="filter_client" data-width="100%"></select>
          </div>
          <div class="col-lg-3 col-md-6 col-sm-12">
            <label for="filter_status" class="form-label mb-1">Status</label>
            <select class="form-select" id="filter_status" name="filter_status">
              <option value="">Semua Status</option>
              <option value="draft">Draft</option>
              <option value="published">Terbit</option>
              <option value="paid">Lunas</option>
              <option value="cancelled">Dibatalkan</option>
            </select>
          </div>
          <div class="col-lg-3 col-md-6 col-sm-12 d-flex align-items-end">
            <button type="button" class="btn btn-danger btn-sm me-2" id="btn_reset"><i class="fal fa-sync me-1"></i> Reset</button>
            <button type="button" class="btn btn-success btn-sm" id="btn_search"><i class="fal fa-search me-1"></i> Cari</button>
          </div>
        </div>

        <hr>

        <!-- DATATABLE -->
        <table id="crudTable" class="table table-hover table-nowrap dt-responsive mb-0 align-middle" style="width:100%">
          <thead>
            <tr>
              <th>No Kwitansi / Dokumen</th>
              <th>PBM / Client</th>
              <th>Kapal & Komoditas</th>
              <th>Volume / Ritase</th>
              <th>Grand Total</th>
              <th>Status</th>
              <th>Periode Kegiatan</th>
              <th class="table_action">Opsi</th>
            </tr>
          </thead>
          <tbody>
          </tbody>
        </table>
      </div>
    </div>
  </div>
</div>
@endsection

@push('scripts')
<script>
  const route_link = ip_server + 'accounting/upah/';
  const table = $('#crudTable');
  let showDeleted = false;

  $(document).ready(function() {
    get_all_client('#filter_client');
    load_data();
  });

  function load_data(tgl_start = '', tgl_end = '', client = '', status = '') {
    table.DataTable({
      responsive: true,
      processing: true,
      serverSide: false,
      ajax: {
        url: route_link + 'table',
        type: 'POST',
        dataType: "JSON",
        timeout: 10000,
        dataSrc: "",
        data: function(d) {
          d.tgl_start = tgl_start;
          d.tgl_end = tgl_end;
          d.client = client;
          d.status = status;
          d.show_deleted = showDeleted ? 1 : 0;
          d[csrf_name] = $('#' + csrf_name).val();
        }
      },
      dom: '<"row mb-2"<"col-sm-12 col-md-6 mb-2 mb-md-0"l<"d-flex gap-2 px-2"B>><"col-sm-12 col-md-6"<"d-flex flex-column flex-md-row justify-content-md-end gap-2 right-buttons"f>>>rtip',
      buttons: [],
      columnDefs: [
        { targets: [0, -1], responsivePriority: 1 },
        { targets: [-1], className: 'text-center', orderable: false, searchable: false },
        { targets: [0], className: 'fw-bold' }
      ],
      drawCallback: function() {
        $('[data-bs-toggle="tooltip"]').tooltip();
      },
      initComplete: function() {
        if ($('.right-buttons').find('#btn_show_deleted').length === 0) {
          let rightButtonsHtml = '<button type="button" class="btn btn-sm btn-outline-danger waves-effect btn-icon ms-2" id="btn_show_deleted" data-bs-toggle="tooltip" title="Lihat Data Dihapus"><i class="fal fa-trash-restore"></i></button>';
          $('.right-buttons').append(rightButtonsHtml);
          $('[data-bs-toggle="tooltip"]').tooltip();
        }
      }
    });
  }

  function reload_table() {
    table.DataTable().clear().destroy();
    load_data(
      $('#filter_tgl_start').val(),
      $('#filter_tgl_end').val(),
      $('#filter_client').val(),
      $('#filter_status').val()
    );
  }

  $('#btn_search').on('click', function() {
    reload_table();
  });

  $('#btn_reset').on('click', function() {
    $('#filter_tgl_start').val('');
    $('#filter_tgl_end').val('');
    $('#filter_client').val(null).trigger('change');
    $('#filter_status').val('');
    showDeleted = false;
    reload_table();
  });

  $(document).on('click', '#btn_show_deleted', function() {
    showDeleted = !showDeleted;
    if (showDeleted) {
      $(this).removeClass('btn-outline-danger').addClass('btn-danger');
      $(this).attr('data-bs-original-title', 'Tampilkan Data Aktif');
    } else {
      $(this).removeClass('btn-danger').addClass('btn-outline-danger');
      $(this).attr('data-bs-original-title', 'Lihat Data Dihapus');
    }
    reload_table();
  });

  $(document).on('click', '.btn-delete', function() {
    let id = $(this).data('id');
    let title = $(this).data('title');
    Swal.fire({
      title: 'Apakah Anda yakin?',
      text: "Dokumen " + title + " akan dihapus!",
      icon: 'warning',
      showCancelButton: true,
      confirmButtonColor: '#d33',
      cancelButtonColor: '#3085d6',
      confirmButtonText: 'Ya, Hapus!',
      cancelButtonText: 'Batal'
    }).then((result) => {
      if (result.isConfirmed) {
        let data = { id: id };
        data[csrf_name] = $('#' + csrf_name).val();
        $.post(route_link + 'delete', data, function(res) {
          if (res.status) {
            Swal.fire('Terhapus!', res.msg, 'success');
            reload_table();
          } else {
            Swal.fire('Gagal!', res.msg, 'error');
          }
        }, 'json');
      }
    });
  });

  $(document).on('click', '.btn-restore', function() {
    let id = $(this).data('id');
    let data = { id: id };
    data[csrf_name] = $('#' + csrf_name).val();
    $.post(route_link + 'restore', data, function(res) {
      if (res.status) {
        Swal.fire('Dipulihkan!', res.msg, 'success');
        reload_table();
      } else {
        Swal.fire('Gagal!', res.msg, 'error');
      }
    }, 'json');
  });
</script>
@endpush

