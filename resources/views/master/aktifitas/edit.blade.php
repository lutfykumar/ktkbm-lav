<?php if (!empty($model)) : ?>
  <form id="form_update" class="kms-form needs-validation" enctype="multipart/form-data" novalidate>
    <input type="hidden" name="<?= $csrf_name; ?>" id="<?= $csrf_name; ?>" value="<?= $csrf_token; ?>">
    <input type="hidden" name="model_id" id="model_id" value="<?= $model->ID_AKTIFITAS; ?>">
    <div class="row g-3">
      <div class="col-sm-12 col-lg-6">
        <div class="fm mb-3">
          <?php
          $label = 'Nama ' . $title;
          $field = 'u_name';
          ?>
          <label for="<?= $field; ?>" class="form-label required"><?= $label; ?></label>
          <input type="text" class="form-control" id="<?= $field; ?>" name="<?= $field; ?>" placeholder="Enter..." value="<?= !empty($model->NAMA) ? $model->NAMA : ''; ?>" required>
        </div>
        <div class="fm mb-3">
          <label class="form-label required">Tipe Pekerjaan</label>
          <div>
            <div class="form-check form-radio-success form-check-inline mb-2">
              <input class="form-check-input" type="radio" name="u_tipe" id="u_tipe_bongkar" value="bongkar" <?= (empty($model->TIPE) || $model->TIPE == 'bongkar') ? 'checked' : ''; ?>>
              <label class="form-check-label" for="u_tipe_bongkar">Bongkar</label>
            </div>
            <div class="form-check form-radio-success form-check-inline mb-2">
              <input class="form-check-input" type="radio" name="u_tipe" id="u_tipe_muat" value="muat" <?= (!empty($model->TIPE) && $model->TIPE == 'muat') ? 'checked' : ''; ?>>
              <label class="form-check-label" for="u_tipe_muat">Muat</label>
            </div>
          </div>
        </div>
        <div class="fm mb-3">
          <?php
          $label = 'Pilih Jenis Satuan ' . $title;
          $field = 'u_satuan_id';
          ?>
          <label for="<?= $field; ?>" class="form-label"><?= $label; ?></label>
          <select class="form-control" id="<?= $field; ?>" name="<?= $field; ?>" data-width="100%">
            <?php if (!empty($model->ID_SATUAN)) : ?>
              <option value="<?= !empty($model->ID_SATUAN) ? $model->ID_SATUAN : ''; ?>" selected><?= !empty($model->NAMA_SATUAN) ? $model->NAMA_SATUAN : ''; ?></option>
            <?php endif; ?>
          </select>
        </div>
        <div class="fm mb-3">
          <?php
          $label = 'Pilih Status ' . $title;
          $field = 'u_status';
          $field1 = 'u_typeA';
          $field2 = 'u_typeB';
          ?>
          <label class="form-label"><?= $label; ?></label>
          <div>
            <div class="form-check form-radio-success form-check-inline mb-3">
              <input class="form-check-input" type="radio" name="<?= $field; ?>" id="<?= $field1; ?>" value="1" <?= $model->STATUS == 1 ? 'checked' : ''; ?>>
              <label class="form-check-label" for="<?= $field1; ?>">
                Aktif
              </label>
            </div>
            <div class="form-check form-radio-success form-check-inline mb-3">
              <input class="form-check-input" type="radio" name="<?= $field; ?>" id="<?= $field2; ?>" value="0" <?= $model->STATUS == 0 ? 'checked' : ''; ?>>
              <label class="form-check-label" for="<?= $field2; ?>">
                Tidak Aktif
              </label>
            </div>
          </div>
        </div>
      </div>
      <div class="col-sm-12 col-lg-6">
        <img src="{{ asset('assets/images/people.svg') }}" class="img-responsive d-none d-lg-block d-xl-block mx-auto" style="height:300px;" alt="roles_image">
      </div>
      <div class="col-lg-12">
        <div class="hstack gap-2 justify-content-center mt-3">
          <button type="button" class="btn btn-danger w-sm waves-effect waves-light me-5" data-bs-dismiss="modal">Batal</button>
          <button type="button" class="btn btn-success w-sm waves-effect waves-light btn-update">Simpan</button>
        </div>
      </div>
    </div>
  </form>
<?php else : ?>
  <div class="text-center">
    <h4 class="text-danger">Data tidak ditemukan</h4>
  </div>
<?php endif; ?>
