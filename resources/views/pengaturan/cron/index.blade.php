@extends('layouts.app')

@section('title', $title ?? 'Cron Job')
@section('title_header', 'Cron Job')
@section('title_parent', $title_parent ?? 'Pengaturan')

@section('content')
<div class="row">
  <div class="col-12">
    <div class="card">
      <div class="card-body">
        <div class="d-flex justify-content-between align-items-center mb-3">
          <h4 class="card-title mb-0"><i class="fal fa-clock me-2"></i>Cron Job Management</h4>
          <div class="d-flex gap-2">
            <button type="button" class="btn btn-sm btn-info" id="btn_stats">
              <i class="fal fa-chart-bar me-1"></i>Statistics
            </button>
            <button type="button" class="btn btn-sm btn-primary" id="btn_refresh">
              <i class="fal fa-sync me-1"></i>Refresh
            </button>
          </div>
        </div>

        <!-- Stats Cards -->
        <div class="row mb-4" id="stats_row" style="display:none">
          <div class="col-md-3">
            <div class="card card-animate border-0 bg-primary-subtle">
              <div class="card-body py-2">
                <p class="text-muted fw-medium mb-0">Total Jobs</p>
                <h5 class="fw-semibold mb-0" id="stat_total">0</h5>
              </div>
            </div>
          </div>
          <div class="col-md-3">
            <div class="card card-animate border-0 bg-success-subtle">
              <div class="card-body py-2">
                <p class="text-muted fw-medium mb-0">Active</p>
                <h5 class="fw-semibold mb-0" id="stat_active">0</h5>
              </div>
            </div>
          </div>
          <div class="col-md-3">
            <div class="card card-animate border-0 bg-warning-subtle">
              <div class="card-body py-2">
                <p class="text-muted fw-medium mb-0">Running</p>
                <h5 class="fw-semibold mb-0" id="stat_running">0</h5>
              </div>
            </div>
          </div>
          <div class="col-md-3">
            <div class="card card-animate border-0 bg-secondary-subtle">
              <div class="card-body py-2">
                <p class="text-muted fw-medium mb-0">Inactive</p>
                <h5 class="fw-semibold mb-0" id="stat_inactive">0</h5>
              </div>
            </div>
          </div>
        </div>

        <!-- Table -->
        <table id="crudTable" class="table table-hover table-nowrap dt-responsive mb-0 align-middle" style="width:100%">
          <thead class="table-light">
            <tr>
              <th>Job Name</th>
              <th>Slug</th>
              <th>Schedule</th>
              <th>Next Run</th>
              <th>Last Run</th>
              <th>Success Rate</th>
              <th>Avg Time</th>
              <th>Status</th>
              <th>Running</th>
              <th class="table_action">Opsi</th>
            </tr>
          </thead>
          <tbody></tbody>
        </table>
      </div>
    </div>
  </div>
</div>

<!-- Modal Detail -->
<div class="modal fade" id="modal_detail" tabindex="-1">
  <div class="modal-dialog modal-lg">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title">Job Detail</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
      </div>
      <div class="modal-body" id="detail_body">Loading...</div>
    </div>
  </div>
</div>

<!-- Modal Execution Logs -->
<div class="modal fade" id="modal_logs" tabindex="-1">
  <div class="modal-dialog modal-xl">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title">Execution Logs</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
      </div>
      <div class="modal-body" id="logs_body">Loading...</div>
    </div>
  </div>
</div>
@endsection

@push('scripts')
<script>
  const route_link = ip_server + 'pengaturan/cron/';

  $(document).ready(function() {
    let table = $('#crudTable').DataTable({
      responsive: true,
      processing: true,
      serverSide: false,
      ajax: {
        url: route_link + 'get_data',
        type: 'POST',
        dataType: 'JSON',
        dataSrc: '',
        data: function(d) {
          d[csrf_name] = $('#' + csrf_name).val();
        }
      },
      order: [
        [3, 'asc']
      ],
      columnDefs: [{
        targets: [-1],
        className: 'text-center',
        orderable: false,
        searchable: false
      }],
      drawCallback: function() {
        $('[data-bs-toggle="tooltip"]').tooltip();
      }
    });

    $('#btn_refresh').click(function() {
      table.ajax.reload();
    });

    // Stats toggle
    $('#btn_stats').click(function() {
      let row = $('#stats_row');
      if (row.is(':visible')) {
        row.slideUp();
        return;
      }
      $.ajax({
        url: route_link + 'statistics',
        type: 'GET',
        dataType: 'JSON',
        success: function(res) {
          if (res.status) {
            let s = res.job_stats || {};
            $('#stat_total').text(s.total_jobs || 0);
            $('#stat_active').text(s.active_jobs || 0);
            $('#stat_running').text(s.running_jobs || 0);
            $('#stat_inactive').text(s.inactive_jobs || 0);
            row.slideDown();
          }
        }
      });
    });

    // Detail
    $(document).on('click', '.btn-detail', function() {
      let id = $(this).data('id');
      let postData = { id: id };
      postData[csrf_name] = $('#' + csrf_name).val();

      $.ajax({
        url: route_link + 'detail',
        type: 'POST',
        dataType: 'JSON',
        data: postData,
        success: function(res) {
          if (res.csrf_token_name && res.csrf_token_hash) {
            $('input[name="' + res.csrf_token_name + '"]').val(res.csrf_token_hash);
          }
          if (res.status && res.data) {
            let d = res.data;
            let html = '<table class="table table-sm"><tbody>';
            for (let key in d) {
              let val = d[key] || '-';
              html += '<tr><th class="text-muted" style="width:30%">' + key + '</th><td>' + val + '</td></tr>';
            }
            html += '</tbody></table>';

            if (res.recent_executions && res.recent_executions.length > 0) {
              html += '<h6 class="mt-3">Recent Executions</h6><table class="table table-sm table-striped"><thead><tr><th>Started</th><th>Status</th><th>Time</th></tr></thead><tbody>';
              res.recent_executions.forEach(function(e) {
                html += '<tr><td>' + (e.STARTED_AT || '-') + '</td><td>' + (e.STATUS || '-') + '</td><td>' + (e.EXECUTION_TIME_MS || '-') + ' ms</td></tr>';
              });
              html += '</tbody></table>';
            }

            $('#detail_body').html(html);
            $('#modal_detail').modal('show');
          } else {
            toast_error(res.msg || 'Data tidak ditemukan');
          }
        },
        error: function() {
          toast_error('Gagal memuat detail');
        }
      });
    });

    // Execute job
    $(document).on('click', '.btn-execute', function() {
      let id = $(this).data('id');
      if (!confirm('Execute job ini secara manual?')) return;

      let postData = { job_id: id };
      postData[csrf_name] = $('#' + csrf_name).val();

      $.ajax({
        url: route_link + 'execute',
        type: 'POST',
        dataType: 'JSON',
        data: postData,
        success: function(res) {
          if (res.csrf_token_name && res.csrf_token_hash) {
            $('input[name="' + res.csrf_token_name + '"]').val(res.csrf_token_hash);
          }
          if (res.status) {
            toast_success(res.msg);
            table.ajax.reload();
          } else {
            toast_error(res.msg);
          }
        },
        error: function() {
          toast_error('Gagal execute job');
        }
      });
    });

    // Toggle status
    $(document).on('click', '.btn-toggle', function() {
      let id = $(this).data('id');
      let status = $(this).data('status');
      let label = status == 1 ? 'menonaktifkan' : 'mengaktifkan';

      if (!confirm('Yakin ingin ' + label + ' job ini?')) return;

      let postData = {
        job_id: id,
        current_status: status
      };
      postData[csrf_name] = $('#' + csrf_name).val();

      $.ajax({
        url: route_link + 'toggle_status',
        type: 'POST',
        dataType: 'JSON',
        data: postData,
        success: function(res) {
          if (res.csrf_token_name && res.csrf_token_hash) {
            $('input[name="' + res.csrf_token_name + '"]').val(res.csrf_token_hash);
          }
          if (res.status) {
            toast_success(res.msg);
            table.ajax.reload();
          } else {
            toast_error(res.msg);
          }
        },
        error: function() {
          toast_error('Gagal ubah status');
        }
      });
    });

    // View logs
    $(document).on('click', '.btn-logs', function() {
      let id = $(this).data('id');
      let postData = { job_id: id };
      postData[csrf_name] = $('#' + csrf_name).val();

      $.ajax({
        url: route_link + 'execution_logs',
        type: 'POST',
        dataType: 'JSON',
        data: postData,
        success: function(res) {
          if (res.csrf_token_name && res.csrf_token_hash) {
            $('input[name="' + res.csrf_token_name + '"]').val(res.csrf_token_hash);
          }
          if (res.status && res.data) {
            let html = '<table class="table table-sm table-striped"><thead><tr><th>Exec ID</th><th>Started</th><th>Completed</th><th>Time</th><th>Status</th><th>Memory</th></tr></thead><tbody>';
            res.data.forEach(function(row) {
              html += '<tr>';
              row.forEach(function(cell, i) {
                if (i < 6) html += '<td>' + cell + '</td>';
              });
              html += '</tr>';
            });
            html += '</tbody></table>';
            if (res.data.length === 0) html = '<p class="text-muted">Belum ada execution log</p>';
            $('#logs_body').html(html);
            $('#modal_logs').modal('show');
          } else {
            toast_error(res.msg || 'Gagal memuat logs');
          }
        },
        error: function() {
          toast_error('Gagal memuat logs');
        }
      });
    });
  });
</script>
@endpush
