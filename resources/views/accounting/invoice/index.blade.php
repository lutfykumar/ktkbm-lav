@extends('layouts.app')

@section('title', $title ?? 'Invoice')
@section('title_header', $title_header ?? 'Invoice')
@section('title_parent', $title_parent ?? 'Accounting')

@section('content')
<div class="row">
  <div class="col-xl-4 col-md-4">
    <div class="card card-animate text-center">
      <div class="card-body">
        <h5 class="text-muted text-uppercase fs-13">Total Unpaid (Draft/Sent/Overdue)</h5>
        <h3 class="mb-0 text-danger text-truncate">Rp <span class="counter-value" data-target="{{ $stats['total_unpaid'] ?? 0 }}">{{ ribuan($stats['total_unpaid'] ?? 0) }}</span></h3>
      </div>
    </div>
  </div>
  <div class="col-xl-4 col-md-4">
    <div class="card card-animate text-center">
      <div class="card-body">
        <h5 class="text-muted text-uppercase fs-13">Total Paid (Bulan Ini)</h5>
        <h3 class="mb-0 text-success text-truncate">Rp <span class="counter-value" data-target="{{ $stats['total_paid'] ?? 0 }}">{{ ribuan($stats['total_paid'] ?? 0) }}</span></h3>
      </div>
    </div>
  </div>
  <div class="col-xl-4 col-md-4">
    <div class="card card-animate text-center">
      <div class="card-body">
        <h5 class="text-muted text-uppercase fs-13">Invoice Overdue</h5>
        <h3 class="mb-0 text-warning"><span class="counter-value" data-target="{{ $stats['total_overdue'] ?? 0 }}">{{ $stats['total_overdue'] ?? 0 }}</span> Invoice</h3>
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
          <h5 class="mb-0">Filter Data</h5>
          @if (!empty($permission_create))
            <button type="button" class="btn btn-primary btn-sm waves-effect waves-light" id="btn_create_invoice">
              <i class="fal fa-plus me-1"></i> Buat Invoice
            </button>
          @endif
        </div>
        <div class="row g-3">
          <div class="col-lg-3 col-md-6 col-sm-12">
            <label for="filter_tgl_start" class="form-label mb-1">Periode Invoice</label>
            <div class="input-group">
              <input type="date" class="form-control" id="filter_tgl_start" name="filter_tgl_start">
              <span class="input-group-text">-</span>
              <input type="date" class="form-control" id="filter_tgl_end" name="filter_tgl_end">
            </div>
          </div>
          <div class="col-lg-3 col-md-6 col-sm-12">
            <label for="filter_client" class="form-label mb-1">Pilih Client</label>
            <select class="form-control ajax_select field_all_client" id="filter_client" name="filter_client" data-width="100%"></select>
          </div>
          <div class="col-lg-3 col-md-6 col-sm-12">
            <label for="filter_status" class="form-label mb-1">Status</label>
            <select class="form-select" id="filter_status" name="filter_status">
              <option value="">Semua Status</option>
              <option value="draft">Draft</option>
              <option value="sent">Sent</option>
              <option value="paid">Paid</option>
              <option value="overdue">Overdue</option>
              <option value="cancelled">Cancelled</option>
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
              <th class="table_no">No Invoice</th>
              <th>Client</th>
              <th>No Work Order</th>
              <th>Total</th>
              <th>Status</th>
              <th>Tanggal</th>
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

<!-- MODAL PILIH WORK ORDER -->
<div class="modal fade" id="modal_pilih_wo" tabindex="-1" aria-hidden="true">
  <div class="modal-dialog modal-lg modal-dialog-centered modal-dialog-scrollable">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title"><i class="fal fa-tasks me-2"></i> Pilih Work Order untuk Invoice</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      <div class="modal-body">
        <div class="mb-3">
          <input type="text" class="form-control" id="wo_search" placeholder="Cari No WO, Client, atau Kapal...">
        </div>
        <div class="table-responsive">
          <table class="table table-hover align-middle mb-0">
            <thead class="table-light">
              <tr>
                <th>No Work Order</th>
                <th>Client</th>
                <th>Kapal</th>
                <th>Pelabuhan</th>
                <th class="text-center" width="150">Aksi</th>
              </tr>
            </thead>
            <tbody id="wo_list_body">
              <tr>
                <td colspan="5" class="text-center text-muted py-3">Klik tombol Buat Invoice untuk memuat data</td>
              </tr>
            </tbody>
          </table>
        </div>
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-light" data-bs-dismiss="modal">Tutup</button>
      </div>
    </div>
  </div>
</div>
@endsection

@push('scripts')
<script>
  const route_link = ip_server + 'accounting/invoice/';
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
      columnDefs: [{
          targets: [0, -1],
          responsivePriority: 1
        },
        {
          targets: [-1],
          className: 'text-center',
          orderable: false,
          searchable: false
        },
        {
          targets: [0],
          className: 'fw-bold'
        }
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

  $("#btn_search").click(function(e) {
    e.preventDefault();
    let tgl_start = $('#filter_tgl_start').val() || '';
    let tgl_end = $('#filter_tgl_end').val() || '';
    let client = $('#filter_client').val() || '';
    let status = $('#filter_status').val() || '';
    table.DataTable().destroy();
    load_data(tgl_start, tgl_end, client, status);
  });

  $("#btn_reset").click(function() {
    $('#filter_tgl_start, #filter_tgl_end').val('');
    $('#filter_client').val('').trigger('change');
    $('#filter_status').val('');
    table.DataTable().destroy();
    load_data();
  });

  $(document).on('click', '#btn_show_deleted', function() {
    showDeleted = !showDeleted;
    if (showDeleted) {
      $(this).removeClass('btn-outline-danger').addClass('btn-danger');
    } else {
      $(this).removeClass('btn-danger').addClass('btn-outline-danger');
    }
    table.DataTable().ajax.reload();
  });

  $(document).on('click', '.btn-delete', function(e) {
    let id = $(this).data('id');
    let title = $(this).data('title');
    let csrf_token = $('#' + csrf_name).val();
    if (confirm('Anda yakin menghapus invoice : ' + title + ' ?')) {
      let postData = {
        id: id
      };
      postData[csrf_name] = csrf_token;
      $.ajax({
        url: route_link + 'delete',
        type: "POST",
        dataType: "JSON",
        data: postData,
        success: function(res) {
          if (res.csrf_token_name && res.csrf_token_hash) {
            $('input[name="' + res.csrf_token_name + '"]').val(res.csrf_token_hash);
          }
          if (res.status == true) {
            toast_success(res.msg);
            table.DataTable().ajax.reload();
          } else {
            toast_error(res.msg);
          }
        }
      });
    }
  });

  $(document).on('click', '.btn-restore', function(e) {
    let id = $(this).data('id');
    let csrf_token = $('#' + csrf_name).val();
    if (confirm('Anda yakin memulihkan invoice ini?')) {
      let postData = { id: id };
      postData[csrf_name] = csrf_token;
      $.ajax({
        url: route_link + 'restore',
        type: "POST",
        dataType: "JSON",
        data: postData,
        success: function(res) {
          if (res.csrf_token_name && res.csrf_token_hash) {
            $('input[name="' + res.csrf_token_name + '"]').val(res.csrf_token_hash);
          }
          if (res.status == true) {
            toast_success(res.msg);
            table.DataTable().ajax.reload();
          } else {
            toast_error(res.msg);
          }
        }
      });
    }
  });

  // Button Create Invoice — buka modal pilih WO
  $('#btn_create_invoice').click(function() {
    $('#modal_pilih_wo').modal('show');
    load_wo_list();
  });

  let wo_table_loaded = false;

  function load_wo_list(search = '') {
    let tbody = $('#wo_list_body');
    tbody.html('<tr><td colspan="5" class="text-center py-3"><i class="fal fa-spinner fa-spin me-2"></i> Memuat data...</td></tr>');

    $.ajax({
      url: ip_server + 'ajax/get_all_workorder_done',
      type: 'POST',
      dataType: 'JSON',
      data: {
        search: search,
        [csrf_name]: $('#' + csrf_name).val()
      },
      success: function(res) {
        if (!res || res.length === 0) {
          tbody.html('<tr><td colspan="5" class="text-center text-muted py-3">Tidak ada Work Order yang tersedia</td></tr>');
          return;
        }
        let rows = '';
        $.each(res, function(i, wo) {
          rows += `<tr>
            <td class="fw-bold">${wo.NO_WORK_ORDER || '-'}</td>
            <td>${wo.NAMA_CLIENT || '-'}</td>
            <td>${wo.NAMA_KAPAL || '-'}</td>
            <td>${wo.NAMA_PELABUHAN || '-'}</td>
            <td class="text-center">
              <a href="${ip_server}accounting/invoice/create/${wo.ID_WORK_ORDER}" class="btn btn-sm btn-primary waves-effect waves-light">
                <i class="fal fa-file-invoice me-1"></i> Buat Invoice
              </a>
            </td>
          </tr>`;
        });
        tbody.html(rows);
      },
      error: function() {
        tbody.html('<tr><td colspan="5" class="text-center text-danger py-3">Gagal memuat data</td></tr>');
      }
    });
  }

  // Search WO in modal
  let wo_search_timer;
  $('#wo_search').on('input', function() {
    clearTimeout(wo_search_timer);
    wo_search_timer = setTimeout(() => load_wo_list($(this).val()), 400);
  });
</script>
@endpush
