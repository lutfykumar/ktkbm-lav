@if (!empty($model))
  <form id="form_update" class="kms-form needs-validation" enctype="multipart/form-data" novalidate>
    <input type="hidden" name="{{ $csrf_name }}" id="{{ $csrf_name }}" value="{{ $csrf_token }}">
    <input type="hidden" name="model_id" id="model_id" value="{{ $model->ID_WORK_ORDER }}">
    <div class="row g-3">
      <div class="col-sm-12 col-md-6 col-lg-3">
        <div class="fm mb-3">
          <label for="u_tgl_work_order" class="form-label required">Tanggal Pekerjaan</label>
          <input type="date" class="form-control" id="u_tgl_work_order" name="u_tgl_work_order" value="{{ !empty($model->CREATED_AT) ? date('Y-m-d', strtotime($model->CREATED_AT)) : date('Y-m-d') }}" required>
        </div>
      </div>
      <div class="col-sm-12 col-md-6 col-lg-3">
        <div class="fm mb-3">
          <label for="u_no_work_order" class="form-label required">Nomor Work Order</label>
          <input type="text" class="form-control" id="u_no_work_order" name="u_no_work_order" value="{{ !empty($model->NO_WORK_ORDER) ? htmlspecialchars($model->NO_WORK_ORDER) : '' }}" required>
        </div>
      </div>
      <div class="col-sm-12 col-md-6 col-lg-3">
        <div class="fm mb-3">
          <label for="u_client_id" class="form-label required">Pilih Client</label>
          <select class="form-control ajax_select u_client_id" id="u_client_id" name="u_client_id" data-width="100%" required>
            @if (!empty($model->ID_CLIENT))
              <option value="{{ $model->ID_CLIENT }}" selected>{{ $model->NAMA_CLIENT }}</option>
            @endif
          </select>
        </div>
      </div>
      <div class="col-sm-12 col-md-6 col-lg-3">
        <div class="fm mb-3">
          <label for="u_kapal_id" class="form-label required">Pilih Kapal</label>
          <select class="form-control ajax_select u_kapal_id" id="u_kapal_id" name="u_kapal_id" data-width="100%" required>
            @if (!empty($model->ID_KAPAL))
              <option value="{{ $model->ID_KAPAL }}" selected>{{ $model->NAMA_KAPAL }}</option>
            @endif
          </select>
        </div>
      </div>
      <div class="col-sm-12 col-md-6 col-lg-3">
        <div class="fm mb-3">
          <label for="u_dermaga_id" class="form-label required">Pilih Dermaga / Lokasi Sandaran</label>
          <select class="form-control ajax_select u_dermaga_id" id="u_dermaga_id" name="u_dermaga_id" data-width="100%" required>
            @if (!empty($model->ID_DERMAGA))
              <option value="{{ $model->ID_DERMAGA }}" selected>{{ $model->NAMA_DERMAGA }}</option>
            @endif
          </select>
        </div>
      </div>
      <div class="col-sm-12 col-md-6 col-lg-3">
        <div class="fm mb-3">
          <label for="u_alat_id" class="form-label">Pilih Alat Bantu Kerja</label>
          <select class="form-control ajax_select u_alat_id" id="u_alat_id" name="u_alat_id[]" data-width="100%" multiple>
            @if (!empty($wo_alat))
              @foreach ($wo_alat as $alat)
                <option value="{{ $alat->ID_ALAT }}" selected>{{ $alat->NAMA ?? $alat->NAMA_ALAT }}</option>
              @endforeach
            @endif
          </select>
        </div>
      </div>
      <div class="col-sm-12 col-md-6 col-lg-3">
        <div class="fm mb-3">
          <label for="u_aktifitas_id" class="form-label">Pilih Aktifitas / Pekerjaan</label>
          <select class="form-control ajax_select u_aktifitas_id" id="u_aktifitas_id" name="u_aktifitas_id" data-width="100%">
            @if (!empty($model->ID_AKTIFITAS))
              <option value="{{ $model->ID_AKTIFITAS }}" selected>{{ $model->NAMA_AKTIFITAS }}</option>
            @endif
          </select>
        </div>
      </div>
      <div class="col-sm-12 col-md-6 col-lg-3">
        <div class="fm mb-3">
          <label for="u_pengawas_id" class="form-label">Pilih Pengawas / Penanggung Jawab</label>
          <select class="form-control ajax_select u_pengawas_id" id="u_pengawas_id" name="u_pengawas_id" data-width="100%">
            @if (!empty($model->ID_PENGAWAS))
              <option value="{{ $model->ID_PENGAWAS }}" selected>{{ $model->PENGAWAS_USER }}</option>
            @endif
          </select>
        </div>
      </div>
      <div class="col-sm-12 col-md-6 col-lg-3">
        <div class="fm mb-3">
          <label class="form-label">Pilih Status {{ $title ?? 'Work Order' }}</label>
          <div>
            <div class="form-check form-radio-success form-check-inline mb-3">
              <input class="form-check-input" type="radio" name="u_status" id="u_typeB" value="open" {{ (!in_array($model->STATUS, ['progress', 'review', 'done'])) ? 'checked' : '' }}>
              <label class="form-check-label" for="u_typeB">Open</label>
            </div>
            <div class="form-check form-radio-success form-check-inline mb-3">
              <input class="form-check-input" type="radio" name="u_status" id="u_typeC" value="progress" {{ $model->STATUS == 'progress' ? 'checked' : '' }}>
              <label class="form-check-label" for="u_typeC">Progress</label>
            </div>
            <div class="form-check form-radio-warning form-check-inline mb-3">
              <input class="form-check-input" type="radio" name="u_status" id="u_typeE" value="review" {{ $model->STATUS == 'review' ? 'checked' : '' }}>
              <label class="form-check-label" for="u_typeE">Review</label>
            </div>
            <div class="form-check form-radio-success form-check-inline mb-3">
              <input class="form-check-input" type="radio" name="u_status" id="u_typeD" value="done" {{ $model->STATUS == 'done' ? 'checked' : '' }}>
              <label class="form-check-label" for="u_typeD">Done</label>
            </div>
          </div>
        </div>
      </div>
      <div class="d-flex justify-content-center">
        <button type="button" class="btn btn-danger w-sm waves-effect waves-light me-5" data-bs-dismiss="modal">Batal</button>
        <button type="button" class="btn btn-success w-sm waves-effect waves-light btn-update">Update</button>
      </div>
    </div>
  </form>
@else
  <div class="text-center">
    <h4 class="text-danger">Data tidak ditemukan</h4>
  </div>
@endif
