@extends('layouts.app')

@section('title', $title ?? 'Pengguna')
@section('title_header', 'Pengguna')
@section('title_parent', $title_parent ?? 'Advanced')

@section('content')
<div class="row">
  <div class="col-12">
    @if (!empty($permission_create))
      <div class="accordion custom-accordionwithicon custom-accordion-border accordion-border-box accordion-primary mb-3" id="accordionBordered">
        <div class="accordion-item">
          <h2 class="accordion-header" id="accordionborderedForm">
            <button class="accordion-button fw-semibold btn-form-accor collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#accor_form" aria-expanded="false" aria-controls="accor_form">
              <i class="fal fa-plus me-2"></i> Tambah Data {{ $title ?? 'Pengguna' }}
            </button>
          </h2>
          <div id="accor_form" class="accordion-collapse collapse" aria-labelledby="accordionborderedForm" data-bs-parent="#accordionBordered">
            <div class="accordion-body">
              <form id="form_add" class="kms-form needs-validation" enctype="multipart/form-data" novalidate>
                <input type="hidden" name="{{ $csrf_name }}" id="{{ $csrf_name }}" value="{{ $csrf_token }}">
                <div class="row">
                  <div class="col-sm-12 col-lg-6">
                    <div class="fm mb-3">
                      <label for="name" class="form-label required">Nama {{ $title ?? 'Pengguna' }}</label>
                      <input type="text" class="form-control" id="name" name="name" placeholder="Enter..." required>
                    </div>
                    <div class="fm mb-3">
                      <label for="email" class="form-label required">Alamat Email {{ $title ?? 'Pengguna' }}</label>
                      <input type="email" class="form-control" id="email" name="email" placeholder="Enter..." required>
                    </div>
                    <div class="fm mb-3">
                      <label for="password" class="form-label required">Password {{ $title ?? 'Pengguna' }}</label>
                      <input type="password" class="form-control" id="password" name="password" placeholder="Enter..." required>
                    </div>
                    <div class="fm mb-3">
                      <label for="roles" class="form-label required">Pilih Roles {{ $title ?? 'Pengguna' }}</label>
                      <select class="form-control ajax_select field_all_roles" id="roles" name="roles" data-width="100%" required>
                      </select>
                    </div>
                    <div class="text-center">
                      <button type="button" class="btn btn-danger btn-sm w-sm waves-effect waves-light btn_batal_form me-5">Batal</button>
                      <button type="button" class="btn btn-success btn-sm w-sm waves-effect waves-light" id="btn_add">Simpan</button>
                    </div>
                  </div>
                  <div class="col-sm-12 col-lg-6 d-none d-lg-flex d-xl-flex align-items-center justify-content-center">
                    <img src="{{ asset('assets/images/interaction_design.svg') }}" style="height:300px;" alt="pengguna_image">
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
        <table id="crudTable" class="table table-hover table-nowrap dt-responsive mb-0 align-middle" style="width:100%">
          <thead>
            <tr>
              <th class="table_no">No</th>
              <th>Name</th>
              <th>Email</th>
              <th>Jabatan</th>
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

<!-- Change Password Modal -->
<div class="modal fade" id="modal_change_password" tabindex="-1" aria-labelledby="modalChangePasswordLabel" aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title" id="modalChangePasswordLabel">Change Password</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      <div class="modal-body">
        <form id="form_change_password" class="kms-form needs-validation" novalidate>
          <input type="hidden" name="{{ $csrf_name }}" id="csrf_change_password" value="{{ $csrf_token }}">
          <input type="hidden" name="user_id" id="change_password_user_id">

          <div class="mb-3">
            <label class="form-label fw-bold" id="change_password_username">Username</label>
          </div>

          <div class="mb-3">
            <label for="new_password" class="form-label required">Password Baru</label>
            <input type="password" class="form-control" id="new_password" name="new_password" placeholder="Minimal 6 karakter" required minlength="6">
            <div class="invalid-feedback">Password minimal 6 karakter</div>
          </div>

          <div class="mb-3">
            <label for="confirm_password" class="form-label required">Konfirmasi Password</label>
            <input type="password" class="form-control" id="confirm_password" name="confirm_password" placeholder="Ulangi password baru" required>
            <div class="invalid-feedback">Konfirmasi password harus sama dengan password baru</div>
          </div>
        </form>
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
        <button type="button" class="btn btn-primary btn-save-password">Simpan Password</button>
      </div>
    </div>
  </div>
</div>
@endsection

@push('scripts')
<script>
  const route_link = ip_server + 'pengaturan/pengguna/';
  let showDeleted = false;

  $(document).ready(function() {
    get_all_roles('.field_all_roles');

    let table = $('#crudTable').DataTable({
      responsive: true,
      processing: true,
      serverSide: false,
      dom: '<"row mb-2"<"col-sm-12 col-md-6 mb-2 mb-md-0"l><"col-sm-12 col-md-6"<"d-flex flex-column flex-md-row justify-content-md-end gap-2"Bf>>>rtip',
      buttons: [{
        text: '<i class="fal fa-trash-restore"></i>',
        className: 'btn btn-outline-danger waves-effect waves-light btn-icon',
        attr: {
          id: 'btn_show_deleted',
          'data-bs-toggle': 'tooltip',
          'data-bs-placement': 'top',
          'title': 'Tampilkan Data Terhapus'
        },
        action: function(e, dt, node, config) {
          // Handled by click event
        }
      }],
      ajax: {
        url: route_link + 'table',
        type: 'GET',
        dataType: "JSON",
        timeout: 10000,
        dataSrc: "",
        data: function(d) {
          d.show_deleted = showDeleted ? 1 : 0;
        }
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

    $(document).on('click', '#btn_show_deleted', function() {
      showDeleted = !showDeleted;
      if (showDeleted) {
        $(this).removeClass('btn-outline-danger').addClass('btn-danger');
      } else {
        $(this).removeClass('btn-danger').addClass('btn-outline-danger');
      }
      table.ajax.reload();
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
    var title = btn.data('title') || 'Edit Pengguna';
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
        get_all_roles('#u_roles', '#modal_crud');
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

  $(document).on('click', '.btn-restore', function(e) {
    e.preventDefault();
    var btn = $(this);
    var id = btn.data('id');
    var csrf_token = $('#' + csrf_name).val();
    btn.addClass('disabled');

    if (confirm('Anda yakin memulihkan pengguna ini?')) {
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

  // Change Password Modal Handler
  $(document).on('click', '.btn-change-password', function(e) {
    e.preventDefault();
    let id = $(this).data('id');
    let username = $(this).data('username');

    $('#change_password_user_id').val(id);
    $('#change_password_username').text('Pengguna: ' + username);
    $('#new_password').val('');
    $('#confirm_password').val('');
    $('#form_change_password').removeClass('was-validated');

    $('#modal_change_password').modal('show');
  });

  $('.btn-save-password').click(function(e) {
    e.preventDefault();
    let form = $('#form_change_password');
    let newPass = $('#new_password').val();
    let confirmPass = $('#confirm_password').val();

    if (newPass.length < 6) {
      toast_warning('Password minimal 6 karakter');
      return;
    }

    if (newPass !== confirmPass) {
      toast_warning('Konfirmasi password tidak cocok');
      return;
    }

    let btn = $(this);
    btn.prop('disabled', true).text('Menyimpan...');

    $.ajax({
      url: route_link + 'change_password',
      type: 'POST',
      dataType: 'JSON',
      data: form.serialize(),
      success: function(res) {
        if (res.csrf_token_name && res.csrf_token_hash) {
          $('input[name="' + res.csrf_token_name + '"]').val(res.csrf_token_hash);
        }

        if (res.status) {
          toast_success(res.msg);
          $('#modal_change_password').modal('hide');
        } else {
          toast_error(res.msg);
        }
        btn.prop('disabled', false).text('Simpan Password');
      },
      error: function() {
        btn.prop('disabled', false).text('Simpan Password');
        toast_error('Terjadi kesalahan sistem');
      }
    });
  });
</script>
@endpush
