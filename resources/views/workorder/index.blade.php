@extends('layouts.app')

@section('title', $title ?? 'Work Order')
@section('title_header', $title_header ?? 'Tasks')
@section('title_parent', $title_parent ?? 'Work Order')

@section('content')
<div class="row">
  <div class="col-12">
    @if (!empty($permission_create))
      <div class="accordion custom-accordionwithicon custom-accordion-border accordion-border-box accordion-primary mb-3" id="accordionBordered">
        <div class="accordion-item">
          <h2 class="accordion-header" id="accordionborderedForm">
            <button class="accordion-button fw-semibold btn-form-accor collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#accor_form" aria-expanded="false" aria-controls="accor_form">
              <i class="fal fa-plus me-2"></i> Tambah Data {{ $title ?? 'Work Order' }}
            </button>
          </h2>
          <div id="accor_form" class="accordion-collapse collapse" aria-labelledby="accordionborderedForm" data-bs-parent="#accordionBordered">
            <div class="accordion-body">
              <form id="form_add" class="kms-form needs-validation" enctype="multipart/form-data" novalidate>
                <input type="hidden" name="{{ $csrf_name }}" id="{{ $csrf_name }}" value="{{ $csrf_token }}">
                <div class="row">
                  <div class="col-sm-12 col-md-6 col-lg-3">
                    <div class="fm mb-3">
                      <label for="tgl_work_order" class="form-label required">Tanggal Pekerjaan</label>
                      <input type="date" class="form-control" id="tgl_work_order" name="tgl_work_order" value="{{ date('Y-m-d') }}" required>
                    </div>
                  </div>
                  <div class="col-sm-12 col-md-6 col-lg-3">
                    <div class="fm mb-3">
                      <label for="no_work_order" class="form-label required">Nomor Work Order</label>
                      <div class="input-group">
                        <input type="text" class="form-control" id="no_work_order" name="no_work_order" placeholder="TKBM-WO-..." readonly required>
                        <div class="input-group-text">
                          <input class="form-check-input mt-0 me-1" type="checkbox" id="check_manual_no" name="is_manual_no" value="1">
                          <label for="check_manual_no" class="mb-0 fs-12">Manual</label>
                        </div>
                      </div>
                      <small class="text-muted fs-11">Centang "Manual" untuk nomor arsip lama</small>
                    </div>
                  </div>
                  <div class="col-sm-12 col-md-6 col-lg-3">
                    <div class="fm mb-3">
                      <label for="client_id" class="form-label required">Pilih Client</label>
                      <select class="form-control ajax_select field_all_client" id="client_id" name="client_id" data-width="100%" required>
                      </select>
                    </div>
                  </div>
                  <div class="col-sm-12 col-md-6 col-lg-3">
                    <div class="fm mb-3">
                      <label for="kapal_id" class="form-label required">Pilih Kapal</label>
                      <select class="form-control ajax_select field_all_kapal" id="kapal_id" name="kapal_id" data-width="100%" required>
                      </select>
                    </div>
                  </div>
                  <div class="col-sm-12 col-md-6 col-lg-3">
                    <div class="fm mb-3">
                      <label for="dermaga_id" class="form-label required">Pilih Dermaga / Lokasi Sandaran</label>
                      <select class="form-control ajax_select field_all_dermaga" id="dermaga_id" name="dermaga_id" data-width="100%" required>
                      </select>
                    </div>
                  </div>
                  <div class="col-sm-12 col-md-6 col-lg-3">
                    <div class="fm mb-3">
                      <label for="alat_id" class="form-label">Pilih Alat Bantu Kerja</label>
                      <select class="form-control ajax_select field_all_alat" id="alat_id" name="alat_id[]" data-width="100%" multiple>
                      </select>
                    </div>
                  </div>
                  <div class="col-sm-12 col-md-6 col-lg-3">
                    <div class="fm mb-3">
                      <label for="aktifitas_id" class="form-label">Pilih Aktifitas / Pekerjaan</label>
                      <select class="form-control ajax_select field_all_aktifitas" id="aktifitas_id" name="aktifitas_id" data-width="100%">
                      </select>
                    </div>
                  </div>
                  <div class="col-sm-12 col-md-6 col-lg-3">
                    <div class="fm mb-3">
                      <label for="pengawas_id" class="form-label">Pilih Pengawas / Penanggung Jawab</label>
                      <select class="form-control ajax_select field_all_pengawas" id="pengawas_id" name="pengawas_id" data-width="100%">
                      </select>
                    </div>
                  </div>
                  <div class="col-sm-12 col-md-6 col-lg-3">
                    <div class="fm mb-3">
                      <label class="form-label">Pilih Status {{ $title ?? 'Work Order' }}</label>
                      <div>
                        <div class="form-check form-radio-success form-check-inline mb-3">
                          <input class="form-check-input" type="radio" name="status" id="typeA" value="hide">
                          <label class="form-check-label" for="typeA">Hide</label>
                        </div>
                        <div class="form-check form-radio-success form-check-inline mb-3">
                          <input class="form-check-input" type="radio" name="status" id="typeB" value="open" checked>
                          <label class="form-check-label" for="typeB">Open</label>
                        </div>
                        <div class="form-check form-radio-success form-check-inline mb-3">
                          <input class="form-check-input" type="radio" name="status" id="typeC" value="progress">
                          <label class="form-check-label" for="typeC">Progress</label>
                        </div>
                        <div class="form-check form-radio-warning form-check-inline mb-3">
                          <input class="form-check-input" type="radio" name="status" id="typeE" value="review">
                          <label class="form-check-label" for="typeE">Review</label>
                        </div>
                        <div class="form-check form-radio-success form-check-inline mb-3">
                          <input class="form-check-input" type="radio" name="status" id="typeD" value="done">
                          <label class="form-check-label" for="typeD">Done</label>
                        </div>
                      </div>
                    </div>
                  </div>
                </div>
                <div class="d-flex justify-content-center">
                  <button type="button" class="btn btn-danger btn-sm w-sm waves-effect waves-light btn_batal_form me-5">Batal</button>
                  <button type="button" class="btn btn-success btn-sm w-sm waves-effect waves-light" id="btn_add">Simpan</button>
                </div>
              </form>
            </div>
          </div>
        </div>
      </div>
    @endif

    <div class="card">
      <div class="card-body">
        <h5>Filter</h5>
        <div class="row">
          <div class="col-lg-3 col-md-6 col-sm-12">
            <div class="fm mb-1">
              <label for="filter_tgl_start" class="form-label mb-1">Pilih Periode Entry</label>
              <div class="input-group">
                <input type="month" class="form-control" id="filter_tgl_start" name="filter_tgl_start" placeholder="Mulai dari">
                <span class="input-group-text">-</span>
                <input type="month" class="form-control" id="filter_tgl_end" name="filter_tgl_end" placeholder="Sampai">
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
              <label for="filter_status" class="form-label mb-1">Pilih Status</label>
              <select class="form-control select2 filter_status" id="filter_status" name="filter_status" style="width: 100%;">
                <option value="">Pilih Salah Satu</option>
                <option value="hide">Hide</option>
                <option value="open">Open</option>
                <option value="progress">Progress</option>
                <option value="review">Menunggu Verifikasi (Review)</option>
                <option value="done">Done</option>
              </select>
            </div>
          </div>
          <div class="col-12 mb-0">
            <div class="text-center pt-2">
              <button type="button" class="btn btn-danger btn-sm w-sm waves-effect waves-light me-5" id="btn_reset"><i class="fal fa-sync me-1"></i> Reset</button>
              <button type="button" class="btn btn-success btn-sm w-sm waves-effect waves-light" id="btn_search"><i class="fal fa-search me-1"></i> Cari</button>
            </div>
          </div>
        </div>
        <hr>
        <table id="crudTable" class="table table-hover table-nowrap dt-responsive mb-0 align-middle" style="width:100%">
          <thead>
            <tr>
              <th class="table_no">No Work Order</th>
              <th>Client</th>
              <th>Kapal</th>
              <th>Pelabuhan</th>
              <th>Dermaga</th>
              <th>Aktifitas</th>
              <th>Status</th>
              <th>Dibuat Oleh</th>
              <th>Dibuat Tgl</th>
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
  const route_link = ip_server + 'workorder/';
  const table = $('#crudTable');
  const permission_create = parseInt('{{ $permission_create ?? 1 }}');
  let showDeleted = false;

  $(document).ready(function() {
    get_all_client('#client_id');
    get_all_kapal('#kapal_id');
    get_all_dermaga('#dermaga_id');
    get_all_alat('#alat_id');
    get_all_aktifitas('#aktifitas_id');
    get_all_users_by_role('#pengawas_id', '', 19);

    // Filter Form
    get_all_client('#filter_client');
    get_all_pelabuhan('#filter_pelabuhan');
    load_data();

    function update_no_work_order() {
      if ($('#check_manual_no').is(':checked')) {
        return;
      }
      let tgl = $('#tgl_work_order').val();
      if (!tgl) return;

      let csrf_name = '{{ $csrf_name }}';
      let csrf_token = $('#' + csrf_name).val() || $('input[name="' + csrf_name + '"]').val();
      let postData = {
        tgl: tgl
      };
      if (csrf_name && csrf_token) {
        postData[csrf_name] = csrf_token;
      }

      $.ajax({
        type: "POST",
        url: route_link + 'get_no_work_order',
        dataType: "JSON",
        data: postData,
        success: function(res) {
          if (res.csrf_token_name && res.csrf_token_hash) {
            $('input[name="' + res.csrf_token_name + '"]').val(res.csrf_token_hash);
          }
          let no_wo = res.no_work_order || (res.data && res.data.no_work_order);
          if (res.status == true && no_wo) {
            $('#no_work_order').val(no_wo);
          }
        },
        error: function(err) {
          console.error('Error fetching no work order:', err);
        }
      });
    }

    update_no_work_order();

    $('#accor_form').on('shown.bs.collapse', function() {
      update_no_work_order();
    });

    $(document).on('change input changeDate blur', '#tgl_work_order', function() {
      update_no_work_order();
    });

    $('#check_manual_no').on('change', function() {
      if ($(this).is(':checked')) {
        $('#no_work_order').prop('readonly', false).focus();
      } else {
        $('#no_work_order').prop('readonly', true);
        update_no_work_order();
      }
    });

    $('#btn_add').click(function(e) {
      e.preventDefault();
      var btn = $(this);
      var action_url = route_link + 'store';
      var action_form = $('#form_add');
      var valid = action_form.validate();
      if (action_form.valid()) {
        btn.addClass('disabled');
        btn.text('Mohon tunggu...');
        $.ajax({
          url: action_url,
          type: "POST",
          dataType: "JSON",
          data: action_form.serialize(),
          success: function(res) {
            if (res.csrf_token_name && res.csrf_token_hash) {
              $('input[name="' + res.csrf_token_name + '"]').val(res.csrf_token_hash);
            }

            if (res.status == true) {
              action_form.trigger('reset');
              $('#client_id').val('').trigger('change');
              $('#kapal_id').val('').trigger('change');
              $('#dermaga_id').val('').trigger('change');
              $('#alat_id').val('').trigger('change');
              $('#aktifitas_id').val('').trigger('change');
              $('#pengawas_id').val('').trigger('change');
              $('#tgl_work_order').val('{{ date('Y-m-d') }}');
              $('#check_manual_no').prop('checked', false);
              $('#no_work_order').prop('readonly', true);
              update_no_work_order();
              toast_success(res.msg);
              table.DataTable().ajax.reload();
              $('.btn_batal_form').trigger('click');
            } else {
              toast_error(res.msg);
            }
            btn.removeClass('disabled');
            btn.text('Simpan');
          },
          error: function() {
            btn.removeClass('disabled');
            btn.text('Simpan');
            toast_error('Terjadi kesalahan, silakan coba lagi');
          }
        });
      } else {
        btn.removeClass('disabled');
        btn.text('Simpan');
        valid.focusInvalid();
        return false;
      }
    });

    $("#btn_search").click(function(e) {
      e.preventDefault();
      let filter_tgl_start = $('#filter_tgl_start').val() ? $('#filter_tgl_start').val() : '';
      let filter_tgl_end = $('#filter_tgl_end').val() ? $('#filter_tgl_end').val() : '';
      let filter_pelabuhan = $('#filter_pelabuhan').val() ? $('#filter_pelabuhan').val() : '';
      let filter_client = $('#filter_client').val() ? $('#filter_client').val() : '';
      let filter_status = $('#filter_status').val() ? $('#filter_status').val() : '';
      $('#crudTable').DataTable().destroy();
      load_data(filter_tgl_start, filter_tgl_end, filter_pelabuhan, filter_client, filter_status);
    });

    $("#btn_reset").click(function(e) {
      e.preventDefault();
      $('#filter_tgl_start').val('').trigger('change');
      $('#filter_tgl_end').val('').trigger('change');
      $('#filter_pelabuhan').val('').trigger('change');
      $('#filter_client').val('').trigger('change');
      $('#filter_status').val('').trigger('change');
      $('#crudTable').DataTable().destroy();
      load_data();
    });
  });

  function load_data(tgl_start = '', tgl_end = '', pelabuhan = '', client = '', status = '') {
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
          d.pelabuhan = pelabuhan;
          d.client = client;
          d.status = status;
          d.show_deleted = showDeleted ? 1 : 0;
          d._token = '{{ csrf_token() }}';
        }
      },
      order: [
        [8, 'desc']
      ],
      columnDefs: [{
        targets: [0, -1],
        responsivePriority: 1,
      }, {
        targets: [-1],
        className: 'text-center',
        orderable: false,
        searchable: false
      }, {
        targets: [0],
        className: 'fw-bold'
      }],
      drawCallback: function() {
        $('[data-bs-toggle="tooltip"]').tooltip();
      },
      initComplete: function() {
        let rightButtonsHtml = '<button type="button" class="btn btn-sm btn-outline-danger waves-effect waves-light btn-icon" id="btn_show_deleted" data-bs-toggle="tooltip" data-bs-placement="top" title="Tampilkan Data Terhapus"><i class="fal fa-trash-restore"></i></button>';
        $('.right-buttons').prepend(rightButtonsHtml);
        $('[data-bs-toggle="tooltip"]').tooltip();
      }
    });
  }

  // Toggle soft deleted data
  $(document).on('click', '#btn_show_deleted', function() {
    let btn = $(this);
    showDeleted = !showDeleted;

    let tooltip = bootstrap.Tooltip.getInstance(btn[0]);
    if (tooltip) {
      tooltip.dispose();
    }

    if (showDeleted) {
      btn.removeClass('btn-outline-danger').addClass('btn-danger');
      btn.html('<i class="fal fa-eye-slash"></i>');
      btn.attr('data-bs-original-title', 'Sembunyikan Data Terhapus');
      btn.attr('title', 'Sembunyikan Data Terhapus');
    } else {
      btn.removeClass('btn-danger').addClass('btn-outline-danger');
      btn.html('<i class="fal fa-trash-restore"></i>');
      btn.attr('data-bs-original-title', 'Tampilkan Data Terhapus');
      btn.attr('title', 'Tampilkan Data Terhapus');
    }

    new bootstrap.Tooltip(btn[0]);
    $('#crudTable').DataTable().ajax.reload();
  });

  $(document).on('click', '.btn-edit', function(e) {
    e.preventDefault();
    let btn = $(this);
    let id = btn.data('id');
    let title = btn.attr('data-bs-original-title') || btn.attr('title') || 'Edit Work Order';
    let modal = $('#modal_crud');
    let m_title = modal.find('#m_title');
    let m_body = modal.find('#m_body');
    let action_url = route_link + 'edit';
    btn.addClass('disabled');
    m_body.html('');
    $.ajax({
      type: "POST",
      url: action_url,
      dataType: "html",
      data: {
        id: id,
        _token: '{{ csrf_token() }}'
      },
      success: function(res) {
        m_title.text(title);
        m_body.html(res);
        get_all_client('#u_client_id', '#modal_crud');
        get_all_kapal('#u_kapal_id', '#modal_crud');
        get_all_dermaga('#u_dermaga_id', '#modal_crud');
        get_all_alat('#u_alat_id', '#modal_crud');
        get_all_aktifitas('#u_aktifitas_id', '#modal_crud');
        get_all_users_by_role('#u_pengawas_id', '#modal_crud', 19);
        $('[data-bs-toggle="tooltip"]').tooltip('hide');
        btn.removeClass('disabled');
        modal.modal({
          backdrop: 'static',
          keyboard: false
        }).modal('show');
      },
      error: function() {
        btn.removeClass('disabled');
        toast_error('Gagal memuat form edit');
      }
    });
  });

  $(document).on('click', '.btn-update', function(e) {
    e.preventDefault();
    let btn = $(this);
    let modal = $('#modal_crud');
    let action_form = $('#form_update');
    let action_url = route_link + 'update';
    let valid = action_form.validate();
    if (action_form.valid()) {
      btn.addClass('disabled');
      btn.text('Mohon tunggu..');
      $.ajax({
        type: "POST",
        url: action_url,
        dataType: "JSON",
        data: action_form.serialize(),
        success: function(res) {
          if (res.csrf_token_name && res.csrf_token_hash) {
            $('input[name="' + res.csrf_token_name + '"]').val(res.csrf_token_hash);
          }

          if (res.status == true) {
            action_form.trigger('reset');
            toast_success(res.msg);
            $('#crudTable').DataTable().ajax.reload();
            modal.modal('hide');
          } else {
            toast_error(res.msg);
          }
          btn.removeClass('disabled');
          btn.text('Simpan');
        },
        error: function() {
          btn.removeClass('disabled');
          btn.text('Simpan');
          toast_error('Terjadi kesalahan, silakan coba lagi');
        }
      });
    } else {
      btn.removeClass('disabled');
      btn.text('Simpan');
      valid.focusInvalid();
      return false;
    }
  });

  $(document).on('click', '.btn-quick-done', function(e) {
    e.preventDefault();
    let btn = $(this);
    let id = btn.data('id');
    let title = btn.data('title');

    if (confirm('Sahkan dan kunci Work Order ' + title + ' menjadi status DONE?')) {
      let postData = {
        id: id,
        status: 'done',
        _token: '{{ csrf_token() }}'
      };

      btn.addClass('disabled');
      $.ajax({
        url: route_link + 'update_status',
        type: "POST",
        dataType: "JSON",
        data: postData,
        success: function(res) {
          if (res.csrf_token_name && res.csrf_token_hash) {
            $('input[name="' + res.csrf_token_name + '"]').val(res.csrf_token_hash);
          }
          if (res.status == true) {
            toast_success(res.msg);
            $('#crudTable').DataTable().ajax.reload();
          } else {
            toast_error(res.msg);
          }
          btn.removeClass('disabled');
        },
        error: function() {
          btn.removeClass('disabled');
          toast_error('Terjadi kesalahan sistem');
        }
      });
    }
  });

  $(document).on('click', '.btn-delete', function(e) {
    e.preventDefault();
    let btn = $(this);
    let id = btn.data('id');
    let title = btn.data('title');
    btn.addClass('disabled');

    if (confirm('Anda yakin menghapus data : ' + title + ' ?')) {
      let postData = {
        id: id,
        _token: '{{ csrf_token() }}'
      };

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
            $('#crudTable').DataTable().ajax.reload();
          } else {
            toast_error(res.msg);
          }
          btn.removeClass('disabled');
        },
        error: function() {
          btn.removeClass('disabled');
          toast_error('Terjadi kesalahan, silakan coba lagi');
        }
      });
    } else {
      btn.removeClass('disabled');
    }
  });

  $(document).on('click', '.btn-restore', function(e) {
    e.preventDefault();
    let btn = $(this);
    let id = btn.data('id');
    let title = btn.data('title');
    btn.addClass('disabled');

    if (confirm('Pulihkan data Work Order : ' + title + ' ?')) {
      $.ajax({
        url: route_link + 'restore',
        type: "POST",
        dataType: "JSON",
        data: {
          id: id,
          _token: '{{ csrf_token() }}'
        },
        success: function(res) {
          if (res.status == true) {
            toast_success(res.msg);
            $('#crudTable').DataTable().ajax.reload();
          } else {
            toast_error(res.msg);
          }
          btn.removeClass('disabled');
        },
        error: function() {
          btn.removeClass('disabled');
          toast_error('Terjadi kesalahan sistem');
        }
      });
    } else {
      btn.removeClass('disabled');
    }
  });

  $(document).on('click', '.btn-detail', function(e) {
    e.preventDefault();
    let btn = $(this);
    let id = btn.data('id');
    let title = btn.attr('data-bs-original-title') || btn.attr('title') || 'Detail Work Order';
    let modal = $('#modal_crud');
    let m_title = modal.find('#m_title');
    let m_body = modal.find('#m_body');
    let action_url = route_link + 'detail';
    btn.addClass('disabled');
    m_body.html('<div class="text-center my-4"><i class="fad fa-circle-notch fa-spin fa-2x"></i></div>');
    $.ajax({
      type: "POST",
      url: action_url,
      dataType: "html",
      data: {
        id: id,
        _token: '{{ csrf_token() }}'
      },
      success: function(res) {
        m_title.text(title);
        m_body.html(res);
        $('[data-bs-toggle="tooltip"]').tooltip('hide');
        btn.removeClass('disabled');
        modal.modal({
          backdrop: 'static',
          keyboard: false
        }).modal('show');
      },
      error: function() {
        btn.removeClass('disabled');
        m_body.html('<div class="alert alert-danger">Terjadi kesalahan, silakan coba lagi</div>');
        modal.modal('show');
      }
    });
  });
</script>
@endpush
