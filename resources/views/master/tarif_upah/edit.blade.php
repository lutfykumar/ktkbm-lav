@if (!empty($model))
  <form id="form_update" class="kms-form needs-validation" novalidate>
    <input type="hidden" name="{{ $csrf_name }}" id="{{ $csrf_name }}" value="{{ $csrf_token }}">
    <input type="hidden" name="model_id" id="model_id" value="{{ $model->ID_TARIF }}">
    
    <div class="row g-3">
      <div class="col-md-8">
        <label for="u_nama_tarif" class="form-label required">Nama Tarif</label>
        <input type="text" class="form-control" id="u_nama_tarif" name="u_nama_tarif" value="{{ $model->NAMA_TARIF }}" required>
      </div>

      <div class="col-md-4">
        <label for="u_jenis_tarif" class="form-label required">Jenis Tarif</label>
        <select class="form-select" id="u_jenis_tarif" name="u_jenis_tarif" required>
          <option value="borongan" {{ $model->JENIS_TARIF == 'borongan' ? 'selected' : '' }}>Borongan (per Ton/M3)</option>
          <option value="harian" {{ $model->JENIS_TARIF == 'harian' ? 'selected' : '' }}>Harian (per Shift/Orang)</option>
          <option value="toeslag" {{ $model->JENIS_TARIF == 'toeslag' ? 'selected' : '' }}>Toeslag (Premi Khusus)</option>
          <option value="adm_koperasi" {{ $model->JENIS_TARIF == 'adm_koperasi' ? 'selected' : '' }}>Administrasi Koperasi</option>
          <option value="lembur" {{ $model->JENIS_TARIF == 'lembur' ? 'selected' : '' }}>Uang Lembur (Shift Malam)</option>
        </select>
      </div>

      <div class="col-md-6">
        <label for="u_nilai_tarif" class="form-label required">Nilai Tarif (Rp)</label>
        <div class="input-group">
          <span class="input-group-text">Rp</span>
          <input type="number" step="0.01" min="0" class="form-control" id="u_nilai_tarif" name="u_nilai_tarif" value="{{ floatval($model->NILAI_TARIF) }}" required>
        </div>
      </div>

      <div class="col-md-6">
        <label for="u_persen_kas_koperasi" class="form-label">Bagian Kas Koperasi (%)</label>
        <div class="input-group">
          <input type="number" step="0.1" min="0" max="100" class="form-control" id="u_persen_kas_koperasi" name="u_persen_kas_koperasi" value="{{ floatval($model->PERSEN_KAS_KOPERASI) }}">
          <span class="input-group-text">%</span>
        </div>
      </div>

      <div class="col-md-6">
        <label for="u_id_aktifitas" class="form-label">Komoditas / Aktifitas (Opsional)</label>
        <select class="form-select" id="u_id_aktifitas" name="u_id_aktifitas">
          <option value="">-- Semua Komoditas --</option>
          @foreach ($aktifitasList as $akt)
            <option value="{{ $akt->ID_AKTIFITAS }}" {{ $model->ID_AKTIFITAS == $akt->ID_AKTIFITAS ? 'selected' : '' }}>{{ $akt->NAMA }}</option>
          @endforeach
        </select>
      </div>

      <div class="col-md-6">
        <label for="u_id_krk" class="form-label">Kriteria KRK (Opsional)</label>
        <select class="form-select" id="u_id_krk" name="u_id_krk">
          <option value="">-- Semua KRK --</option>
          @foreach ($krkList as $krk)
            <option value="{{ $krk->ID_KRK }}" {{ $model->ID_KRK == $krk->ID_KRK ? 'selected' : '' }}>{{ $krk->NAMA }}</option>
          @endforeach
        </select>
      </div>

      <div class="col-12">
        <label for="u_keterangan" class="form-label">Keterangan / Dasar Keputusan</label>
        <input type="text" class="form-control" id="u_keterangan" name="u_keterangan" value="{{ $model->KETERANGAN }}">
      </div>

      <div class="col-12">
        <label class="form-label">Status Tarif</label>
        <div>
          <div class="form-check form-radio-success form-check-inline">
            <input class="form-check-input" type="radio" name="u_is_active" id="u_active_1" value="1" {{ $model->IS_ACTIVE ? 'checked' : '' }}>
            <label class="form-check-label" for="u_active_1">Aktif</label>
          </div>
          <div class="form-check form-radio-success form-check-inline">
            <input class="form-check-input" type="radio" name="u_is_active" id="u_active_0" value="0" {{ !$model->IS_ACTIVE ? 'checked' : '' }}>
            <label class="form-check-label" for="u_active_0">Tidak Aktif</label>
          </div>
        </div>
      </div>

      <div class="col-12 text-center mt-4">
        <button type="button" class="btn btn-danger w-sm waves-effect waves-light me-3" data-bs-dismiss="modal">Batal</button>
        <button type="button" class="btn btn-success w-sm waves-effect waves-light btn-update">Simpan Perubahan</button>
      </div>
    </div>
  </form>
@else
  <div class="text-center py-4">
    <h5 class="text-danger">Data tidak ditemukan</h5>
  </div>
@endif
