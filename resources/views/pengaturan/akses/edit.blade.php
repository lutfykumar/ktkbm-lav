@if (!empty($model))
<form id="form_update" class="kms-form needs-validation" enctype="multipart/form-data" novalidate>
  <input type="hidden" name="{{ $csrf_name }}" id="{{ $csrf_name }}" value="{{ $csrf_token }}">
  <input type="hidden" name="id" value="{{ $model->ID_MENU }}">

  <div class="row">
    <div class="col-sm-12 col-md-6 col-lg-4">
      <div class="fm mb-3">
        <label for="u_name" class="form-label required">Nama Menu</label>
        <input type="text" class="form-control" id="u_name" name="u_name" value="{{ $model->NAMA }}" placeholder="Enter..." required>
      </div>
    </div>

    <div class="col-sm-12 col-md-6 col-lg-4">
      <div class="fm mb-3">
        <label for="u_header" class="form-label">Pilih Header</label>
        <select class="form-control" id="u_header" name="u_header" data-width="100%">
          @if (!empty($model->HEADER_ID))
            <option value="{{ $model->HEADER_ID }}" selected>{{ $model->HEADER_NAMA ?? '' }}</option>
          @endif
        </select>
      </div>
    </div>

    <div class="col-sm-12 col-md-6 col-lg-4">
      <div class="fm mb-3">
        <label for="u_parent" class="form-label">Pilih Parent</label>
        <select class="form-control" id="u_parent" name="u_parent" data-width="100%">
          @if (!empty($model->PARENT_ID))
            <option value="{{ $model->PARENT_ID }}" selected>{{ $model->PARENT_NAMA ?? '' }}</option>
          @endif
        </select>
      </div>
    </div>

    <div class="col-sm-12 col-md-6 col-lg-4">
      <div class="fm mb-3">
        <label for="u_icon" class="form-label">Icon</label>
        <input type="text" class="form-control" id="u_icon" name="u_icon" value="{{ $model->ICON }}" placeholder="ri-dashboard-2-line">
        <small class="text-muted">Contoh: ri-dashboard-2-line, fa-cog</small>
      </div>
    </div>

    <div class="col-sm-12 col-md-6 col-lg-4">
      <div class="fm mb-3">
        <label for="u_link" class="form-label">Link</label>
        <input type="text" class="form-control" id="u_link" name="u_link" value="{{ $model->LINK }}" placeholder="master/kapal">
      </div>
    </div>

    <div class="col-sm-12 col-md-6 col-lg-4">
      <div class="fm mb-3">
        <label for="u_urutan" class="form-label">Urutan</label>
        <input type="number" class="form-control" id="u_urutan" name="u_urutan" value="{{ $model->URUT }}" placeholder="1">
      </div>
    </div>

    <div class="col-sm-12 col-md-6 col-lg-4">
      <div class="fm mb-3">
        <label for="u_status" class="form-label">Status</label>
        <select class="form-control select2" id="u_status" name="u_status">
          <option value="1" {{ $model->TAMPIL == 1 ? 'selected' : '' }}>Aktif</option>
          <option value="0" {{ $model->TAMPIL == 0 ? 'selected' : '' }}>Tidak Aktif</option>
        </select>
      </div>
    </div>
  </div>

  <div class="text-center mt-3">
    <button type="button" class="btn btn-danger btn-sm w-sm waves-effect waves-light me-5" data-bs-dismiss="modal">Batal</button>
    <button type="button" class="btn btn-success btn-sm w-sm waves-effect waves-light btn-update">Simpan</button>
  </div>
</form>
@else
<div class="text-center">
  <h4 class="text-danger">Data tidak ditemukan</h4>
</div>
@endif
