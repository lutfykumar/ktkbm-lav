@if (!empty($model))
  <form id="form_update" class="kms-form needs-validation" enctype="multipart/form-data" novalidate>
    <input type="hidden" name="{{ $csrf_name }}" id="{{ $csrf_name }}" value="{{ $csrf_token }}">
    <input type="hidden" name="model_id" id="model_id" value="{{ $model->id }}">
    <div class="row g-3">
      <div class="col-xxl-6">
        <div class="fm mb-3">
          <label for="u_name" class="form-label required">Nama {{ $title ?? 'Pengguna' }}</label>
          <input type="text" class="form-control" id="u_name" name="u_name" placeholder="Enter..." value="{{ $model->username ?? '' }}" required>
        </div>
        <div class="fm mb-3">
          <label for="u_email" class="form-label required">Alamat Email {{ $title ?? 'Pengguna' }}</label>
          <input type="email" class="form-control" id="u_email" name="u_email" placeholder="Enter..." value="{{ $model->email ?? '' }}" required>
        </div>
        <div class="fm mb-3">
          <label for="u_roles" class="form-label required">Pilih Roles {{ $title ?? 'Pengguna' }}</label>
          <select class="form-control" id="u_roles" name="u_roles" data-width="100%" required>
            @if (!empty($model->role_id))
              <option value="{{ $model->role_id }}" selected>{{ $model->role_name ?? '' }}</option>
            @endif
          </select>
        </div>
      </div>
      <div class="col-xxl-6 d-none d-lg-block">
        <img src="{{ asset('assets/images/interaction_design.svg') }}" class="img-responsive d-block mx-auto" style="height:250px;" alt="roles_image">
      </div>
      <div class="col-lg-12">
        <div class="hstack gap-2 justify-content-center mt-3">
          <button type="button" class="btn btn-danger w-sm waves-effect waves-light me-5" data-bs-dismiss="modal">Batal</button>
          <button type="button" class="btn btn-success w-sm waves-effect waves-light btn-update">Simpan</button>
        </div>
      </div>
    </div>
  </form>
@else
  <div class="text-center">
    <h4 class="text-danger">Data tidak ditemukan</h4>
  </div>
@endif
