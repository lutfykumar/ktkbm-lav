@extends('layouts.app')

@section('content')
<div class="row">
  <div class="col-12">
    <?php if ($permission_create) : ?>
      <div class="accordion custom-accordionwithicon custom-accordion-border accordion-border-box accordion-primary mb-3" id="accordionBordered">
        <div class="accordion-item">
          <h2 class="accordion-header" id="accordionborderedForm">
            <button class="accordion-button fw-semibold btn-form-accor collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#accor_form" aria-expanded="false" aria-controls="accor_form">
              <i class="fal fa-plus me-2"></i> Tambah Data <?= $title; ?>
            </button>
          </h2>
          <div id="accor_form" class="accordion-collapse collapse" aria-labelledby="accordionborderedForm" data-bs-parent="#accordionBordered">
            <div class="accordion-body">
              <form id="form_add" class="kms-form needs-validation" enctype="multipart/form-data" novalidate>
                <input type="hidden" name="<?= $csrf_name; ?>" id="<?= $csrf_name; ?>" value="<?= $csrf_token; ?>">
                <div class="row">
                  <div class="col-sm-12 col-lg-6">
                    <div class="fm mb-3">
                      <?php
                      $label = 'Nama ' . $title;
                      $field = 'name';
                      ?>
                      <label for="<?= $field; ?>" class="form-label required"><?= $label; ?></label>
                      <input type="text" class="form-control" id="<?= $field; ?>" name="<?= $field; ?>" placeholder="Enter..." required>
                    </div>
                    <div class="fm mb-3">
                      <?php
                      $label = 'Tipe ' . $title;
                      $field = 'tipe';
                      ?>
                      <label for="<?= $field; ?>" class="form-label"><?= $label; ?></label>
                      <input type="text" class="form-control" id="<?= $field; ?>" name="<?= $field; ?>" placeholder="Enter...">
                    </div>
                    <div class="fm mb-3">
                      <?php
                      $label = 'Pilih Status ' . $title;
                      $field = 'status';
                      $field1 = 'typeA';
                      $field2 = 'typeB';
                      ?>
                      <label class="form-label"><?= $label; ?></label>
                      <div>
                        <div class="form-check form-radio-success form-check-inline mb-3">
                          <input class="form-check-input" type="radio" name="<?= $field; ?>" id="<?= $field1; ?>" value="1" checked>
                          <label class="form-check-label" for="<?= $field1; ?>">
                            Aktif
                          </label>
                        </div>
                        <div class="form-check form-radio-success form-check-inline mb-3">
                          <input class="form-check-input" type="radio" name="<?= $field; ?>" id="<?= $field2; ?>" value="0">
                          <label class="form-check-label" for="<?= $field2; ?>">
                            Tidak Aktif
                          </label>
                        </div>
                      </div>
                    </div>
                    <div class="text-center">
                      <button type="button" class="btn btn-danger btn-sm w-sm waves-effect waves-light btn_batal_form me-5">Batal</button>
                      <button type="button" class="btn btn-success btn-sm w-sm waves-effect waves-light" id="btn_add">Simpan</button>
                    </div>
                  </div>
                  <div class="col-sm-12 col-lg-6 d-none d-lg-flex d-xl-flex align-items-center justify-content-center">
                    <img src="<?= base_url('assets/images/people.svg'); ?>" style="height:300px;" alt="client_image">
                  </div>
                </div>
              </form>
            </div>
          </div>
        </div>
      </div>
    <?php endif; ?>

    <div class="card">
      <div class="card-body">
        <table id="crudTable" class="table table-hover table-nowrap dt-responsive mb-0 align-middle" style="width:100%">
          <thead>
            <tr>
              <th class="table_no">No</th>
              <th>Nama</th>
              <th>Tipe</th>
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
<script>
  const route_link = ip_server + 'master/kapal/';
  let showDeleted = false;

  $(document).ready(function() {
    let table = $('#crudTable').DataTable({
      responsive: true,
      processing: true,
      serverSide: false,
      dom: '<"row mb-2"<"col-sm-12 col-md-6 mb-2 mb-md-0"l><"col-sm-12 col-md-6"<"d-flex flex-column flex-md-row justify-content-md-end gap-2"Bf>>>rtip',
      buttons: [{
        text: '<i class="fal fa-trash-restore"></i>',
        className: 'btn btn-sm btn-outline-danger waves-effect waves-light btn-icon',
        attr: {
          id: 'btn_show_deleted',
          'data-bs-toggle': 'tooltip',
          'data-bs-placement': 'top',
          'title': 'Tampilkan Data Terhapus'
        },
        action: function(e, dt, node, config) {
          // Will be handled by click event below
        }
      }],
      ajax: {
        url: route_link + 'table',
        type: 'GET',
        dataType: "JSON",
        timeout: 10000,
        dataSrc: "",
        data: function(d) {
          if (showDeleted) {
            d.show_deleted = 1;
          }
          return d;
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

    // Initialize tooltip for the button
    setTimeout(function() {
      $('[data-bs-toggle="tooltip"]').tooltip();
    }, 100);

    // Toggle soft deleted data
    $(document).on('click', '#btn_show_deleted', function() {
      let btn = $(this);
      showDeleted = !showDeleted;

      // Dispose existing tooltip
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

      // Reinitialize tooltip
      new bootstrap.Tooltip(btn[0]);

      table.ajax.reload();
    });

    $('#btn_add').click(function(e) {
      e.preventDefault();
      let btn = $(this);
      let action_url = route_link + 'store';
      let action_form = $('#form_add');
      let valid = action_form.validate();
      if (action_form.valid()) {
        btn.addClass('disabled');
        btn.text('Mohon tunggu...');
        $.ajax({
          url: action_url,
          type: "POST",
          dataType: "JSON",
          data: action_form.serialize(),
          success: function(res) {
            // Update CSRF token
            if (res.csrf_token_name && res.csrf_token_hash) {
              $('input[name="' + res.csrf_token_name + '"]').val(res.csrf_token_hash);
            }

            if (res.status == true) {
              action_form.trigger('reset');
              toast_success(res.msg)
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
    let btn = $(this);
    let id = btn.data('id');
    let title = btn.attr('data-bs-original-title');
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
        id: id
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
        toast_error('Terjadi kesalahan, silakan coba lagi');
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
          // Update CSRF token
          if (res.csrf_token_name && res.csrf_token_hash) {
            $('input[name="' + res.csrf_token_name + '"]').val(res.csrf_token_hash);
          }

          if (res.status == true) {
            action_form.trigger('reset');
            toast_success(res.msg)
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
    let btn = $(this);
    let id = btn.data('id');
    let title = btn.data('title');
    let csrf_token = $('#' + csrf_name).val();
    btn.addClass('disabled');

    if (confirm('anda yakin menghapus data : ' + title + ' ?')) {
      // Prepare data object with dynamic CSRF token name
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
          // Update CSRF token
          if (res.csrf_token_name && res.csrf_token_hash) {
            $('input[name="' + res.csrf_token_name + '"]').val(res.csrf_token_hash);
          }

          if (res.status == true) {
            toast_success(res.msg)
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

@endsection

