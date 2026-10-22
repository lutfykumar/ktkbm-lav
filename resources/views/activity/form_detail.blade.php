@php
$wo = $workorder;
@endphp

<style>
  .detail-row .select2-container {
    width: 100% !important;
  }
  .detail-row .krk-group {
    display: flex;
    gap: 6px;
    align-items: flex-start;
  }
  .detail-row .krk-group .select2-container {
    flex: 1;
  }
  .detail-row .krk-group .btn-add-krk {
    flex-shrink: 0;
    height: 38px;
  }
  .detail-row .catatan-group {
    display: flex;
    flex-direction: column;
    gap: 6px;
  }
  .detail-row .catatan-group .select2-container {
    width: 100% !important;
  }
</style>

<div class="card border border-primary">
  <div class="card-header bg-primary-subtle">
    <div class="d-flex align-items-center justify-content-between">
      <div>
        <h5 class="card-title mb-1"><i class="fal fa-clipboard-list me-2"></i>Input Laporan Aktifitas</h5>
        <p class="text-muted mb-0 fs-12">Work Order: <strong>{{ $wo->NO_WORK_ORDER }}</strong></p>
      </div>
      <button type="button" class="btn btn-sm btn-outline-secondary btn-back-to-cards">
        <i class="fal fa-arrow-left me-1"></i> Kembali
      </button>
    </div>
  </div>
  <div class="card-body">
    <!-- WO Info Header -->
    <div class="row mb-3">
      <div class="col-md-3 col-6 mb-2">
        <small class="text-muted d-block">Kapal</small>
        <strong>{{ $wo->NAMA_KAPAL ?: '-' }}</strong>
      </div>
      <div class="col-md-3 col-6 mb-2">
        <small class="text-muted d-block">Client</small>
        <strong>{{ $wo->NAMA_CLIENT ?: '-' }}</strong>
      </div>
      <div class="col-md-3 col-6 mb-2">
        <small class="text-muted d-block">Dermaga</small>
        <strong>{{ $wo->NAMA_DERMAGA ?: '-' }}</strong>
      </div>
      <div class="col-md-3 col-6 mb-2">
        <small class="text-muted d-block">Aktifitas</small>
        <strong>{{ $wo->NAMA_AKTIFITAS ?: '-' }}</strong>
        @if(!empty($wo->NAMA_SATUAN))
          <span class="text-muted">({{ $wo->NAMA_SATUAN }})</span>
        @endif
      </div>
    </div>

    <!-- Existing Reports -->
    @if(!empty($details))
      <div class="alert alert-info py-2 mb-3">
        <i class="fal fa-info-circle me-1"></i> Work Order ini sudah memiliki <strong>{{ count($details) }}</strong> laporan sebelumnya.
      </div>
    @endif

    <hr>

    <!-- Multi-Input Form -->
    <form id="form_detail" enctype="multipart/form-data">
      <input type="hidden" name="work_order_id" value="{{ $wo->ID_WORK_ORDER }}">

      <div id="detail-rows">
        <!-- Row template -->
        <div class="detail-row border rounded p-3 mb-3 bg-white" data-row-index="0">
          <div class="d-flex justify-content-between align-items-center mb-2">
            <span class="badge bg-primary">Baris 1</span>
            <button type="button" class="btn btn-sm btn-outline-danger btn-remove-row d-none">
              <i class="fal fa-times me-1"></i> Hapus
            </button>
          </div>
          <div class="row">
            <!-- KRK Field -->
            <div class="col-lg-3 col-md-6 col-sm-12 mb-2">
              <label class="form-label required">Nama KRK</label>
              <div class="krk-group">
                <select class="form-control select-krk" name="krk_id[]" required></select>
                <button class="btn btn-outline-primary btn-add-krk" type="button" data-bs-toggle="tooltip" title="Tambah KRK baru">
                  <i class="fal fa-plus"></i>
                </button>
              </div>
            </div>

            <!-- Hasil -->
            <div class="col-lg-3 col-md-6 col-sm-12 mb-2">
              <label class="form-label required">Hasil</label>
              <input type="number" class="form-control hasil-field" name="hasil[]" placeholder="0" step="1" min="0">
            </div>

            <!-- Ransum -->
            <div class="col-lg-3 col-md-6 col-sm-12 mb-2">
              <label class="form-label">Ransum <span class="text-muted">(Opsional)</span></label>
              <div class="input-group">
                <input type="number" class="form-control ransum-field" name="ransum[]" placeholder="0" min="0" step="1">
                <span class="input-group-text">Bungkus</span>
              </div>
            </div>

            <!-- Tanggal & Waktu Pekerjaan -->
            <div class="col-lg-3 col-md-6 col-sm-12 mb-2">
              <label class="form-label required">Waktu Pekerjaan</label>
              <input type="datetime-local" class="form-control tgl-laporan-field" name="tgl_laporan[]"
                value="{{ !empty($wo->CREATED_AT) ? date('Y-m-d\TH:i', strtotime($wo->CREATED_AT)) : date('Y-m-d\TH:i') }}" required>
            </div>

            <!-- Foto Before -->
            <div class="col-lg-3 col-md-3 col-sm-6 mb-2">
              <label class="form-label">Foto Before <span class="text-muted">(Opsional)</span></label>
              <input type="file" class="form-control form-control-sm foto-input-before" name="foto_before_0" accept="image/*">
              <div class="foto-preview-before mt-1"></div>
            </div>

            <!-- Foto After -->
            <div class="col-lg-3 col-md-3 col-sm-6 mb-2">
              <label class="form-label">Foto After <span class="text-muted">(Opsional)</span></label>
              <input type="file" class="form-control form-control-sm foto-input-after" name="foto_after_0" accept="image/*">
              <div class="foto-preview-after mt-1"></div>
            </div>

            <!-- Catatan -->
            <div class="col-lg-6 col-md-6 col-sm-12 mb-2">
              <label class="form-label">Catatan <span class="text-muted">(Opsional)</span></label>
              <div class="catatan-group">
                <select class="form-control select-catatan-suggest">
                  <option value="">-- Pilih template catatan --</option>
                </select>
                <textarea class="form-control catatan-field" name="catatan[]" rows="2" placeholder="Tulis catatan..."></textarea>
              </div>
            </div>
          </div>
        </div>
      </div>

      <div class="d-flex justify-content-between align-items-center mt-3">
        <button type="button" class="btn btn-outline-primary btn-sm" id="btn-add-row">
          <i class="fal fa-plus me-1"></i> Tambah Baris
        </button>
        <button type="button" class="btn btn-success" id="btn-submit-detail">
          <i class="fal fa-save me-1"></i> Simpan Semua Laporan
        </button>
      </div>
    </form>
  </div>
</div>

<!-- Image Preview Modal -->
<div class="modal fade" id="imagePreviewModal" tabindex="-1" aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered modal-lg">
    <div class="modal-content">
      <div class="modal-header py-2">
        <h6 class="modal-title" id="imagePreviewTitle">Preview Foto</h6>
        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
      </div>
      <div class="modal-body text-center p-2">
        <img id="imagePreviewImg" src="" class="img-fluid rounded" style="max-height: 75vh;" alt="Preview">
      </div>
    </div>
  </div>
</div>

<script>
  const formDetailRoute = "{{ url('activity') }}/";
  let rowIndex = 0;

  function loadKrkDropdown(selectElem) {
    $.post("{{ url('ajax/get_all_krk') }}", {
      _token: $('meta[name="csrf-token"]').attr('content')
    }, function(res) {
      let opts = '<option value="">-- Pilih KRK --</option>';
      if (Array.isArray(res)) {
        res.forEach(function(item) {
          opts += `<option value="${item.ID_KRK || item.id}">${item.NAMA || item.text}</option>`;
        });
      }
      selectElem.html(opts);
    });
  }

  function loadCatatanDropdown(selectElem) {
    $.post("{{ url('ajax/get_all_catatan') }}", {
      _token: $('meta[name="csrf-token"]').attr('content')
    }, function(res) {
      let opts = '<option value="">-- Pilih template catatan --</option>';
      if (Array.isArray(res)) {
        res.forEach(function(item) {
          opts += `<option value="${item.NAMA || item.text}">${item.NAMA || item.text}</option>`;
        });
      }
      selectElem.html(opts);
    });
  }

  function initRowSelect2(idx) {
    let row = $('[data-row-index="' + idx + '"]');
    loadKrkDropdown(row.find('.select-krk'));
    loadCatatanDropdown(row.find('.select-catatan-suggest'));

    row.find('.select-catatan-suggest').on('change', function() {
      let val = $(this).val();
      if (!val) return;
      let textarea = row.find('.catatan-field');
      let current = textarea.val();
      textarea.val(current ? current + '\n' + val : val);
      $(this).val('');
    });

    row.find('.foto-input-before').on('change', function() {
      let preview = row.find('.foto-preview-before');
      preview.html('');
      if (this.files && this.files[0]) {
        let reader = new FileReader();
        reader.onload = function(e) {
          preview.html(
            '<a href="#" class="preview-img-link" data-src="' + e.target.result + '" data-label="Foto Before">' +
            '<img src="' + e.target.result + '" class="img-thumbnail" style="max-height: 60px; cursor: pointer;" title="Klik untuk preview">' +
            '</a>'
          );
        };
        reader.readAsDataURL(this.files[0]);
      }
    });

    row.find('.foto-input-after').on('change', function() {
      let preview = row.find('.foto-preview-after');
      preview.html('');
      if (this.files && this.files[0]) {
        let reader = new FileReader();
        reader.onload = function(e) {
          preview.html(
            '<a href="#" class="preview-img-link" data-src="' + e.target.result + '" data-label="Foto After">' +
            '<img src="' + e.target.result + '" class="img-thumbnail" style="max-height: 60px; cursor: pointer;" title="Klik untuk preview">' +
            '</a>'
          );
        };
        reader.readAsDataURL(this.files[0]);
      }
    });
  }

  initRowSelect2(0);

  $('#btn-add-row').click(function() {
    rowIndex++;
    let firstRow = $('#detail-rows .detail-row:first');
    let newRow = firstRow.clone();

    newRow.attr('data-row-index', rowIndex);
    newRow.find('.btn-remove-row').removeClass('d-none');
    newRow.find('input[type="number"]').val('');
    newRow.find('textarea').val('');
    newRow.find('.foto-preview-before').html('');
    newRow.find('.foto-preview-after').html('');
    newRow.find('.foto-input-before').val('').attr('name', 'foto_before_' + rowIndex);
    newRow.find('.foto-input-after').val('').attr('name', 'foto_after_' + rowIndex);

    $('#detail-rows').append(newRow);
    initRowSelect2(rowIndex);
    renumberRows();
  });

  $(document).on('click', '.btn-remove-row', function() {
    $(this).closest('.detail-row').remove();
    renumberRows();
  });

  function renumberRows() {
    $('#detail-rows .detail-row').each(function(i) {
      $(this).find('.badge').text('Baris ' + (i + 1));
    });
  }

  $(document).on('click', '.preview-img-link', function(e) {
    e.preventDefault();
    let src = $(this).data('src');
    let label = $(this).data('label') || 'Preview Foto';
    $('#imagePreviewImg').attr('src', src);
    $('#imagePreviewTitle').text(label);
    $('#imagePreviewModal').modal('show');
  });

  $(document).on('click', '.btn-add-krk', function() {
    let btn = $(this);
    let row = btn.closest('.detail-row');
    Swal.fire({
      title: 'Tambah KRK Baru',
      input: 'text',
      inputPlaceholder: 'Masukkan nama KRK...',
      showCancelButton: true,
      confirmButtonText: 'Simpan',
      cancelButtonText: 'Batal',
    }).then((result) => {
      if (result.isConfirmed && result.value) {
        let nama = result.value.trim();
        $.post("{{ url('ajax/store_krk') }}", {
          _token: $('meta[name="csrf-token"]').attr('content'),
          nama: nama
        }, function(res) {
          if (res && res.id) {
            let sel = row.find('.select-krk');
            let newOpt = new Option(nama, res.id, true, true);
            sel.append(newOpt).trigger('change');
            Swal.fire('Berhasil!', `KRK "${nama}" berhasil ditambahkan`, 'success');
          } else {
            Swal.fire('Gagal!', res.msg || 'Gagal menambah KRK', 'error');
          }
        });
      }
    });
  });

  $('#btn-submit-detail').click(function(e) {
    e.preventDefault();
    let btn = $(this);
    let form = document.getElementById('form_detail');
    let formData = new FormData(form);
    formData.append('_token', $('meta[name="csrf-token"]').attr('content'));

    let hasKrk = false;
    $('select.select-krk').each(function() {
      if ($(this).val()) hasKrk = true;
    });
    if (!hasKrk) {
      Swal.fire('Validasi', 'Minimal isi satu nama KRK', 'warning');
      return;
    }

    btn.addClass('disabled').html('<i class="fal fa-spinner fa-spin me-1"></i> Menyimpan...');

    $.ajax({
      type: "POST",
      url: formDetailRoute + 'store_detail',
      data: formData,
      processData: false,
      contentType: false,
      dataType: "JSON",
      success: function(res) {
        btn.removeClass('disabled').html('<i class="fal fa-save me-1"></i> Simpan Semua Laporan');
        if (res.status) {
          Swal.fire('Berhasil!', res.msg, 'success').then(() => {
            $('#btn_search').trigger('click');
          });
        } else {
          Swal.fire('Gagal!', res.msg, 'error');
        }
      },
      error: function(xhr) {
        btn.removeClass('disabled').html('<i class="fal fa-save me-1"></i> Simpan Semua Laporan');
        let err = xhr.responseJSON?.message || 'Terjadi kesalahan sistem';
        Swal.fire('Error!', err, 'error');
      }
    });
  });
</script>
