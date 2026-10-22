<?php if (!empty($model)) : ?>
  <form id="form_update" class="kms-form needs-validation" enctype="multipart/form-data" novalidate>
    <input type="hidden" name="<?= $csrf_name; ?>" id="<?= $csrf_name; ?>" value="<?= $csrf_token; ?>">
    <input type="hidden" name="model_id" id="model_id" value="<?= $model->ID_EMAIL_TEMPLATE; ?>">
    <div class="fm mb-3">
      <?php
      $label = 'Subject ' . $title;
      $field = 'u_subject';
      ?>
      <label for="<?= $field; ?>" class="form-label required"><?= $label; ?></label>
      <input type="text" class="form-control" id="<?= $field; ?>" name="<?= $field; ?>" placeholder="Enter..." value="<?= !empty($model->SUBJECT) ? $model->SUBJECT : ''; ?>" required>
    </div>
    <div class="fm mb-3">
      <?php
      $label = 'Pesan ' . $title;
      $field = 'u_pesan';
      ?>
      <label for="<?= $field; ?>" class="form-label required"><?= $label; ?></label>
      <p class="text-muted">Gunakan <code>[[ variabel ]]</code> dengan tepat</p>
      <textarea class="ckeditor-classic" id="<?= $field; ?>" name="<?= $field; ?>" rows="5" placeholder="Enter..."><?= !empty($model->PESAN) ? htmlspecialchars_decode($model->PESAN) : ''; ?></textarea>
    </div>
    <div class="text-center mt-3 pt-2">
      <button type="button" class="btn btn-danger w-sm waves-effect waves-light me-5" data-bs-dismiss="modal">Batal</button>
      <button type="button" class="btn btn-success w-sm waves-effect waves-light btn-update">Simpan</button>
    </div>
  </form>
  <script>
    let myEditor;
    $(document).ready(function() {
      ClassicEditor
        .create(document.querySelector('#u_pesan'))
        .then(function(editor) {
          editor.ui.view.editable.element.style.height = '200px';
          myEditor = editor;
        })
        .catch(function(error) {
          console.error(error);
        });
    });
    $('.btn-update').on('click', function(e) {
      e.preventDefault();
      var btn = $(this);
      var modal = $('#modal_center_lg');
      let action_form = $('#form_update');
      var action_url = route_link + 'update';
      var valid = action_form.validate();
      if (action_form.valid()) {
        var form_data = new FormData(action_form[0]);
        form_data.append('u_pesan', myEditor.getData());
        console.log(myEditor.getData());
        btn.addClass('disabled');
        btn.text('Mohon tunggu..');
        $.ajax({
          type: "POST",
          url: action_url,
          dataType: "JSON",
          data: form_data,
          contentType: false,
          cache: false,
          processData: false,
          success: function(res) {
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
        });
      } else {
        btn.removeClass('disabled');
        btn.text('Simpan');
        valid.focusInvalid();
        return false;
      }
    });
  </script>
<?php else : ?>
  <div class="text-center">
    <h4 class="text-danger">Data tidak ditemukan</h4>
  </div>
<?php endif; ?>