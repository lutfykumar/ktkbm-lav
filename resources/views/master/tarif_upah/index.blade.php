@extends('layouts.app')

@section('content')
<div class="row">
  <div class="col-12">
    @if ($permission_create)
      <div class="accordion custom-accordionwithicon custom-accordion-border accordion-border-box accordion-primary mb-3" id="accordionBordered">
        <div class="accordion-item">
          <h2 class="accordion-header" id="accordionborderedForm">
            <button class="accordion-button fw-semibold btn-form-accor collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#accor_form" aria-expanded="false" aria-controls="accor_form">
              <i class="fal fa-plus me-2"></i> Tambah Data {{ $title }}
            </button>
          </h2>
          <div id="accor_form" class="accordion-collapse collapse" aria-labelledby="accordionborderedForm" data-bs-parent="#accordionBordered">
            <div class="accordion-body">
              <form id="form_add" class="kms-form needs-validation" novalidate>
                <input type="hidden" name="{{ $csrf_name }}" id="{{ $csrf_name }}" value="{{ $csrf_token }}">
                <div class="row">
                  <div class="col-sm-12 col-lg-8">
                    <div class="row g-3">
                      <div class="col-md-8">
                        <label for="nama_tarif" class="form-label required">Nama Tarif</label>
                        <input type="text" class="form-control" id="nama_tarif" name="nama_tarif" placeholder="e.g. Tarif Borongan Batu Bara (Jetty)" required>
                      </div>

                      <div class="col-md-4">
                        <label for="jenis_tarif" class="form-label required">Jenis Tarif</label>
                        <select class="form-select" id="jenis_tarif" name="jenis_tarif" required>
                          <option value="borongan" selected>Borongan (per Ton/M3)</option>
                          <option value="harian">Harian (per Shift/Orang)</option>
                          <option value="toeslag">Toeslag (Premi Khusus)</option>
                          <option value="adm_koperasi">Administrasi Koperasi</option>
                          <option value="lembur">Uang Lembur (Shift Malam)</option>
                        </select>
                      </div>

                      <div class="col-md-6">
                        <label for="nilai_tarif" class="form-label required">Nilai Tarif (Rp)</label>
                        <div class="input-group">
                          <span class="input-group-text">Rp</span>
                          <input type="number" step="0.01" min="0" class="form-control" id="nilai_tarif" name="nilai_tarif" placeholder="0" required>
                        </div>
                      </div>

                      <div class="col-md-6">
                        <label for="persen_kas_koperasi" class="form-label">Bagian Kas Koperasi (%)</label>
                        <div class="input-group">
                          <input type="number" step="0.1" min="0" max="100" class="form-control" id="persen_kas_koperasi" name="persen_kas_koperasi" value="0" placeholder="0">
                          <span class="input-group-text">%</span>
                        </div>
                        <small class="text-muted">Persentase potongan kas/kesejahteraan koperasi.</small>
                      </div>

                      <div class="col-md-6">
                        <label for="id_aktifitas" class="form-label">Komoditas / Aktifitas (Opsional)</label>
                        <select class="form-select" id="id_aktifitas" name="id_aktifitas">
                          <option value="">-- Semua Komoditas --</option>
                          @foreach ($aktifitasList as $akt)
                            <option value="{{ $akt->ID_AKTIFITAS }}">{{ $akt->NAMA }}</option>
                          @endforeach
                        </select>
                      </div>

                      <div class="col-md-6">
                        <label for="id_krk" class="form-label">Kriteria KRK (Opsional)</label>
                        <select class="form-select" id="id_krk" name="id_krk">
                          <option value="">-- Semua KRK --</option>
                          @foreach ($krkList as $krk)
                            <option value="{{ $krk->ID_KRK }}">{{ $krk->NAMA }}</option>
                          @endforeach
                        </select>
                      </div>

                      <div class="col-12">
                        <label for="keterangan" class="form-label">Keterangan / Dasar Keputusan</label>
                        <input type="text" class="form-control" id="keterangan" name="keterangan" placeholder="e.g. Sesuai Keputusan Bersama No: SK-TKBM/2026">
                      </div>

                      <div class="col-12">
                        <label class="form-label">Status Tarif</label>
                        <div>
                          <div class="form-check form-radio-success form-check-inline">
                            <input class="form-check-input" type="radio" name="is_active" id="active_1" value="1" checked>
                            <label class="form-check-label" for="active_1">Aktif</label>
                          </div>
                          <div class="form-check form-radio-success form-check-inline">
                            <input class="form-check-input" type="radio" name="is_active" id="active_0" value="0">
                            <label class="form-check-label" for="active_0">Tidak Aktif</label>
                          </div>
                        </div>
                      </div>
                    </div>

                    <div class="text-center mt-4">
                      <button type="button" class="btn btn-danger btn-sm w-sm waves-effect waves-light btn_batal_form me-3">Batal</button>
                      <button type="button" class="btn btn-success btn-sm w-sm waves-effect waves-light" id="btn_add">Simpan Tarif</button>
                    </div>
                  </div>

                  <div class="col-sm-12 col-lg-4 d-none d-lg-flex align-items-center justify-content-center">
                    <img src="{{ asset('assets/images/people.svg') }}" style="height:250px;" alt="people_image">
                  </div>
                </div>
              </form>
            </div>
          </div>
        </div>
      </div>
    @endif

    <div class="card">
      <div class="card-header border-0 pb-0">
        <h5 class="card-title mb-0"><i class="fal fa-tags me-1"></i> Daftar Master Tarif Upah TKBM</h5>
      </div>
      <div class="card-body">
        <table id="crudTable" class="table table-hover table-nowrap dt-responsive mb-0 align-middle" style="width:100%">
          <thead class="table-light">
            <tr>
              <th class="table_no" width="5%">No</th>
              <th width="30%">Nama Tarif & Komoditas</th>
              <th width="15%">Jenis Tarif</th>
              <th width="15%">Nilai Tarif</th>
              <th width="10%">Kas Koperasi</th>
              <th width="10%">Status</th>
              <th class="table_action" width="15%">Opsi</th>
            </tr>
          </thead>
          <tbody>
          </tbody>
        </table>
      </div>
    </div>
  </div>
</div>

<!-- Modal Edit -->
<div class="modal fade" id="modal_crud" tabindex="-1" aria-hidden="true">
  <div class="modal-dialog modal-lg modal-dialog-centered">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title" id="m_title">Edit Tarif Upah</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      <div class="modal-body" id="m_body">
      </div>
    </div>
  </div>
</div>
@endsection

@push('scripts')
<script>
  const route_link = "{{ url('master/tarif_upah') }}/";
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
        action: function(e, dt, node, config) {}
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
        targets: [0, 4, 5, -1],
        className: 'text-center',
        orderable: false,
        searchable: false
      }],
      drawCallback: function() {
        $('[data-bs-toggle="tooltip"]').tooltip();
      },
    });

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
        btn.attr('title', 'Sembunyikan Data Terhapus');
      } else {
        btn.removeClass('btn-danger').addClass('btn-outline-danger');
        btn.html('<i class="fal fa-trash-restore"></i>');
        btn.attr('title', 'Tampilkan Data Terhapus');
      }

      new bootstrap.Tooltip(btn[0]);
      table.ajax.reload();
    });

    $('.btn_batal_form').on('click', function() {
      $('.btn-form-accor').trigger('click');
    });

    $('#btn_add').click(function(e) {
      e.preventDefault();
      let btn = $(this);
      let action_url = route_link + 'store';
      let action_form = $('#form_add');

      if (!$('#nama_tarif').val()) {
        toast_error('Nama Tarif harus diisi');
        return;
      }

      btn.addClass('disabled').text('Menyimpan...');
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
          btn.removeClass('disabled').text('Simpan Tarif');
        },
        error: function() {
          btn.removeClass('disabled').text('Simpan Tarif');
          toast_error('Terjadi kesalahan, silakan coba lagi');
        }
      });
    });

    $(document).on('click', '.btn_edit', function(e) {
      e.preventDefault();
      let btn = $(this);
      let id = btn.data('id');
      let modal = $('#modal_crud');
      let m_body = modal.find('#m_body');
      let action_url = route_link + 'edit';
      btn.addClass('disabled');
      m_body.html('<div class="text-center p-4"><i class="fal fa-spinner fa-spin fa-2x"></i></div>');
      modal.modal('show');

      $.ajax({
        type: "POST",
        url: action_url,
        dataType: "html",
        data: {
          id: id,
          _token: $('input[name="{{ $csrf_name }}"]').val()
        },
        success: function(res) {
          btn.removeClass('disabled');
          m_body.html(res);
        },
        error: function() {
          btn.removeClass('disabled');
          toast_error('Terjadi kesalahan saat memuat form edit');
        }
      });
    });

    $(document).on('click', '.btn-update', function(e) {
      e.preventDefault();
      let btn = $(this);
      let modal = $('#modal_crud');
      let action_form = $('#form_update');
      let action_url = route_link + 'update';

      btn.addClass('disabled').text('Menyimpan...');
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
            toast_success(res.msg);
            table.ajax.reload();
            modal.modal('hide');
          } else {
            toast_error(res.msg);
          }
          btn.removeClass('disabled').text('Simpan Perubahan');
        },
        error: function() {
          btn.removeClass('disabled').text('Simpan Perubahan');
          toast_error('Terjadi kesalahan, silakan coba lagi');
        }
      });
    });

    $(document).on('click', '.btn_delete', function(e) {
      e.preventDefault();
      let btn = $(this);
      let id = btn.data('id');
      let title = btn.data('name');
      let csrf_token = $('input[name="{{ $csrf_name }}"]').val();

      if (confirm('Anda yakin ingin menghapus tarif : ' + title + ' ?')) {
        let postData = { id: id, _token: csrf_token };

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
              table.ajax.reload();
            } else {
              toast_error(res.msg);
            }
          },
          error: function() {
            toast_error('Terjadi kesalahan saat menghapus data');
          }
        });
      }
    });

    $(document).on('click', '.btn_restore', function(e) {
      e.preventDefault();
      let btn = $(this);
      let id = btn.data('id');
      let title = btn.data('name') || 'data ini';
      let csrf_token = $('input[name="{{ $csrf_name }}"]').val();

      if (confirm('Anda yakin ingin memulihkan tarif : ' + title + ' ?')) {
        let postData = { id: id, _token: csrf_token };

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
              table.ajax.reload();
            } else {
              toast_error(res.msg);
            }
          },
          error: function() {
            toast_error('Terjadi kesalahan saat memulihkan data');
          }
        });
      }
    });
  });
</script>
@endpush
