@extends('layouts.app')

@section('title', $title ?? 'Menu Akses')
@section('title_header', 'Menu Akses')
@section('title_parent', $title_parent ?? 'Pengaturan')

@section('content')
<div class="row">
  <div class="col-12">
    @if (!empty($permission_create))
    <div class="accordion custom-accordionwithicon custom-accordion-border accordion-border-box accordion-primary mb-3" id="accordionBordered">
      <div class="accordion-item">
        <h2 class="accordion-header" id="accordionborderedForm">
          <button class="accordion-button fw-semibold btn-form-accor collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#accor_form" aria-expanded="false" aria-controls="accor_form">
            <i class="fal fa-plus me-2"></i> Tambah Data {{ $title ?? 'Menu Akses' }}
          </button>
        </h2>
        <div id="accor_form" class="accordion-collapse collapse" aria-labelledby="accordionborderedForm" data-bs-parent="#accordionBordered">
          <div class="accordion-body">
            <form id="form_add" class="kms-form needs-validation" enctype="multipart/form-data" novalidate>
              <input type="hidden" name="{{ $csrf_name }}" id="{{ $csrf_name }}" value="{{ $csrf_token }}">
              <div class="row">
                <div class="col-sm-12 col-md-6 col-lg-4">
                  <div class="fm mb-3">
                    <label for="name" class="form-label required">Nama {{ $title ?? 'Menu' }}</label>
                    <input type="text" class="form-control" id="name" name="name" placeholder="Enter..." required>
                  </div>
                </div>
                <div class="col-sm-12 col-md-6 col-lg-4">
                  <div class="fm mb-3">
                    <label for="header" class="form-label">Pilih Header</label>
                    <select class="form-control ajax_select field_all_header" id="header" name="header" data-width="100%">
                    </select>
                  </div>
                </div>
                <div class="col-sm-12 col-md-6 col-lg-4">
                  <div class="fm mb-3">
                    <label for="parent" class="form-label">Pilih Parent</label>
                    <select class="form-control ajax_select field_all_parent" id="parent" name="parent" data-width="100%">
                    </select>
                  </div>
                </div>
                 <div class="col-sm-12 col-md-6 col-lg-4">
                  <div class="fm mb-3">
                    <label for="icon" class="form-label">Icon</label>
                    <input type="text" class="form-control" id="icon" name="icon" placeholder="Contoh: ri-dashboard-2-line">
                  </div>
                </div>
                <div class="col-sm-12 col-md-6 col-lg-4">
                  <div class="fm mb-3">
                    <label for="link" class="form-label">Link</label>
                    <input type="text" class="form-control" id="link" name="link" placeholder="Contoh: master/kapal">
                  </div>
                </div>
                <div class="col-sm-12 col-md-6 col-lg-4">
                  <div class="fm mb-3">
                    <label for="urutan" class="form-label">Urutan</label>
                    <input type="number" class="form-control" id="urutan" name="urutan" placeholder="1" value="0">
                  </div>
                </div>
                <div class="col-sm-12 col-md-6 col-lg-4">
                  <div class="fm mb-3">
                    <label for="status" class="form-label">Status</label>
                    <select class="form-control select2" id="status" name="status">
                      <option value="1" selected>Aktif</option>
                      <option value="0">Tidak Aktif</option>
                    </select>
                  </div>
                </div>
                <div class="text-center">
                  <button type="button" class="btn btn-danger btn-sm w-sm waves-effect waves-light btn_batal_form me-5">Batal</button>
                  <button type="button" class="btn btn-success btn-sm w-sm waves-effect waves-light" id="btn_add">Simpan</button>
                </div>
              </div>
            </form>
          </div>
        </div>
      </div>
    </div>
    @endif

    <div class="card">
      <div class="card-body">
        <table id="crudTable" class="table table-hover table-nowrap mb-0" style="width: 100%;">
          <thead>
            <tr>
              <th class="table_no">No</th>
              <th>Nama</th>
              <th>Header</th>
              <th>Parent</th>
              <th>Icon</th>
              <th>Link</th>
              <th>Urutan</th>
              <th>Status</th>
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
  const route_link = ip_server + 'pengaturan/akses/';

  $(document).ready(function() {
    get_all_header('.field_all_header');
    get_all_parent('.field_all_parent');

    var table = $('#crudTable').DataTable({
      responsive: true,
      processing: true,
      serverSide: false,
      ajax: {
        url: route_link + 'table',
        type: 'GET',
        dataType: "JSON",
        timeout: 10000,
        dataSrc: "",
      },
      columnDefs: [{
        targets: [1, -1],
        responsivePriority: 1,
      }, {
        targets: [0, -1],
        className: 'text-center',
        orderable: false,
        searchable: false
      }, {
        targets: 1,
        className: 'fw-bold'
      }],
      drawCallback: function() {
        $('[data-bs-toggle="tooltip"]').tooltip();
      },
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
              toast_success(res.msg);
              table.ajax.reload();
              $('.btn-form-accor').trigger('click');
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
  });

  $(document).on('click', '.btn-edit', function(e) {
    e.preventDefault();
    var btn = $(this);
    var id = btn.data('id');
    var title = btn.data('title') || 'Edit Menu Akses';
    var modal = $('#modal_crud');
    var m_title = modal.find('#m_title');
    var m_body = modal.find('#m_body');
    var action_url = route_link + 'edit';
    btn.addClass('disabled');
    m_body.html('');
    $.ajax({
      type: "POST",
      url: action_url,
      dataType: "html",
      data: {
        id: id,
        [csrf_name]: $('#' + csrf_name).val()
      },
      success: function(res) {
        m_title.text(title);
        m_body.html(res);
        get_all_header('#u_header', '#modal_crud');
        get_all_parent('#u_parent', '#modal_crud');
        $('[data-bs-toggle="tooltip"]').tooltip('hide');
        btn.removeClass('disabled');
        modal.modal({
          backdrop: 'static',
          keyboard: false
        }).modal('show');
      },
      error: function() {
        btn.removeClass('disabled');
        toast_error('Terjadi kesalahan, silakan coba lagi');
      }
    });
  });

  $(document).on('click', '.btn-update', function(e) {
    e.preventDefault();
    var btn = $(this);
    var modal = $('#modal_crud');
    let action_form = $('#form_update');
    var action_url = route_link + 'update';
    var valid = action_form.validate();
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

  $(document).on('click', '.btn-delete', function(e) {
    e.preventDefault();
    var btn = $(this);
    var id = btn.data('id');
    var title = btn.data('title');
    var csrf_token = $('#' + csrf_name).val();
    btn.addClass('disabled');

    if (confirm('Anda yakin menghapus data : ' + title + ' ?')) {
      let postData = { id: id };
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
</script>
@endpush
