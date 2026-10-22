<?php
$is_edit = isset($template);
$template_id = $is_edit ? $template->ID_TEMPLATE : '';
$template_name = $is_edit ? $template->TEMPLATE_NAME : '';
$template_slug = $is_edit ? $template->TEMPLATE_SLUG : '';
$subject = $is_edit ? $template->SUBJECT : '';
$email_body = $is_edit ? $template->EMAIL_BODY : '';
$variables = $is_edit ? json_decode($template->VARIABLES, true) : [];
$description = $is_edit ? $template->DESCRIPTION : '';
$category = $is_edit ? $template->CATEGORY : '';
$is_active = $is_edit ? $template->IS_ACTIVE : 1;
?>

<form id="form_template" method="post">
  <input type="hidden" name="<?= $csrf_name ?>" value="<?= $csrf_token ?>">
  <?php if ($is_edit): ?>
    <input type="hidden" name="id" value="<?= $template_id ?>">
  <?php endif; ?>

  <div class="row">
    <!-- Left Column -->
    <div class="col-md-8">
      <!-- Basic Information -->
      <div class="card mb-3">
        <div class="card-header bg-light">
          <h6 class="mb-0"><i class="fa fa-info-circle me-1"></i> Basic Information</h6>
        </div>
        <div class="card-body">
          <div class="mb-3">
            <label for="template_name" class="form-label">Template Name <span class="text-danger">*</span></label>
            <input type="text" class="form-control" id="template_name" name="template_name"
              value="<?= htmlspecialchars($template_name) ?>" required>
          </div>

          <div class="mb-3">
            <label for="template_slug" class="form-label">Template Slug <span class="text-danger">*</span></label>
            <input type="text" class="form-control" id="template_slug" name="template_slug"
              value="<?= htmlspecialchars($template_slug) ?>" required
              pattern="[a-z0-9_-]+" title="Only lowercase letters, numbers, underscores, and hyphens">
            <div class="form-text">Unique identifier (lowercase, no spaces). Example: welcome_email</div>
          </div>

          <div class="mb-3">
            <label for="subject" class="form-label">Email Subject <span class="text-danger">*</span></label>
            <input type="text" class="form-control" id="subject" name="subject"
              value="<?= htmlspecialchars($subject) ?>" required>
            <div class="form-text">You can use variables like {{site_name}}, {{user_name}}</div>
          </div>

          <div class="mb-3">
            <label for="description" class="form-label">Description</label>
            <textarea class="form-control" id="description" name="description" rows="2"><?= htmlspecialchars($description) ?></textarea>
          </div>
        </div>
      </div>

      <!-- Email Body -->
      <div class="card mb-3">
        <div class="card-header bg-light">
          <h6 class="mb-0"><i class="fa fa-envelope me-1"></i> Email Body <span class="text-danger">*</span></h6>
        </div>
        <div class="card-body">
          <textarea class="form-control" id="email_body" name="email_body" rows="15" required><?= htmlspecialchars($email_body) ?></textarea>
          <div class="form-text mt-2">
            <strong>Available Variables:</strong> {{site_name}}, {{user_name}}, {{email}}, etc.<br>
            <strong>Conditional:</strong> {{#if variable}}...{{/if}}
          </div>
        </div>
      </div>
    </div>

    <!-- Right Column -->
    <div class="col-md-4">
      <!-- Settings -->
      <div class="card mb-3">
        <div class="card-header bg-light">
          <h6 class="mb-0"><i class="fa fa-cog me-1"></i> Settings</h6>
        </div>
        <div class="card-body">
          <div class="mb-3">
            <label for="category" class="form-label">Category</label>
            <select class="form-select" id="category" name="category">
              <option value="">-- Select Category --</option>
              <?php foreach ($categories as $cat): ?>
                <option value="<?= $cat ?>" <?= $category == $cat ? 'selected' : '' ?>><?= ucfirst($cat) ?></option>
              <?php endforeach; ?>
              <option value="auth" <?= $category == 'auth' ? 'selected' : '' ?>>Auth</option>
              <option value="user" <?= $category == 'user' ? 'selected' : '' ?>>User</option>
              <option value="general" <?= $category == 'general' ? 'selected' : '' ?>>General</option>
              <option value="notification" <?= $category == 'notification' ? 'selected' : '' ?>>Notification</option>
            </select>
          </div>

          <div class="mb-3">
            <div class="form-check form-switch">
              <input class="form-check-input" type="checkbox" id="is_active" name="is_active" value="1" <?= $is_active ? 'checked' : '' ?>>
              <label class="form-check-label" for="is_active">Active</label>
            </div>
          </div>
        </div>
      </div>

      <!-- Variables -->
      <div class="card mb-3">
        <div class="card-header bg-light">
          <h6 class="mb-0"><i class="fa fa-code me-1"></i> Variables</h6>
        </div>
        <div class="card-body">
          <div class="mb-3">
            <label class="form-label">Template Variables</label>
            <div id="variables_container">
              <?php if (!empty($variables)): ?>
                <?php foreach ($variables as $var): ?>
                  <div class="input-group mb-2 variable-item">
                    <span class="input-group-text">{{</span>
                    <input type="text" class="form-control" name="variables[]" value="<?= htmlspecialchars($var) ?>">
                    <span class="input-group-text">}}</span>
                    <button type="button" class="btn btn-danger btn-remove-variable">
                      <i class="fa fa-times"></i>
                    </button>
                  </div>
                <?php endforeach; ?>
              <?php else: ?>
                <div class="input-group mb-2 variable-item">
                  <span class="input-group-text">{{</span>
                  <input type="text" class="form-control" name="variables[]" placeholder="site_name">
                  <span class="input-group-text">}}</span>
                  <button type="button" class="btn btn-danger btn-remove-variable">
                    <i class="fa fa-times"></i>
                  </button>
                </div>
              <?php endif; ?>
            </div>
            <button type="button" class="btn btn-sm btn-secondary" id="btn_add_variable">
              <i class="fa fa-plus me-1"></i> Add Variable
            </button>
          </div>

          <div class="alert alert-info">
            <small>
              <strong>Common Variables:</strong><br>
              - site_name<br>
              - user_name<br>
              - email<br>
              - button_text<br>
              - button_link
            </small>
          </div>
        </div>
      </div>

      <!-- Variable Picker -->
      <div class="card">
        <div class="card-header bg-light">
          <h6 class="mb-0"><i class="fa fa-copy me-1"></i> Quick Insert</h6>
        </div>
        <div class="card-body">
          <div class="d-grid gap-2">
            <button type="button" class="btn btn-sm btn-outline-primary insert-var" data-var="{{site_name}}">
              Insert {{site_name}}
            </button>
            <button type="button" class="btn btn-sm btn-outline-primary insert-var" data-var="{{user_name}}">
              Insert {{user_name}}
            </button>
            <button type="button" class="btn btn-sm btn-outline-primary insert-var" data-var="{{email}}">
              Insert {{email}}
            </button>
            <button type="button" class="btn btn-sm btn-outline-primary insert-var" data-var="{{button_text}}">
              Insert {{button_text}}
            </button>
            <button type="button" class="btn btn-sm btn-outline-primary insert-var" data-var="{{button_link}}">
              Insert {{button_link}}
            </button>
          </div>
        </div>
      </div>
    </div>
  </div>

  <div class="row mt-3">
    <div class="col-12">
      <div class="d-flex justify-content-end gap-2">
        <button type="button" class="btn btn-danger" data-bs-dismiss="modal">Cancel</button>
        <button type="submit" class="btn btn-primary">
          <i class="fa fa-save me-1"></i> <?= $is_edit ? 'Update' : 'Create' ?> Template
        </button>
      </div>
    </div>
  </div>
</form>

<script>
  $(document).ready(function() {
    // Auto-generate slug from name
    $('#template_name').on('input', function() {
      if (!<?= $is_edit ? 'true' : 'false' ?>) {
        var slug = $(this).val()
          .toLowerCase()
          .replace(/[^a-z0-9]+/g, '_')
          .replace(/^_+|_+$/g, '');
        $('#template_slug').val(slug);
      }
    });

    // Add variable
    $('#btn_add_variable').on('click', function() {
      var html = `
        <div class="input-group mb-2 variable-item">
          <span class="input-group-text">{{</span>
          <input type="text" class="form-control" name="variables[]" placeholder="variable_name">
          <span class="input-group-text">}}</span>
          <button type="button" class="btn btn-danger btn-remove-variable">
            <i class="fa fa-times"></i>
          </button>
        </div>
      `;
      $('#variables_container').append(html);
    });

    // Remove variable
    $(document).on('click', '.btn-remove-variable', function() {
      if ($('.variable-item').length > 1) {
        $(this).closest('.variable-item').remove();
      } else {
        toast_error('At least one variable field is required');
      }
    });

    // Insert variable into email body
    $('.insert-var').on('click', function() {
      var variable = $(this).data('var');
      var textarea = document.getElementById('email_body');
      var cursorPos = textarea.selectionStart;
      var textBefore = textarea.value.substring(0, cursorPos);
      var textAfter = textarea.value.substring(cursorPos);

      textarea.value = textBefore + variable + textAfter;
      textarea.selectionStart = textarea.selectionEnd = cursorPos + variable.length;
      textarea.focus();
    });

    // Form submit
    $('#form_template').on('submit', function(e) {
      e.preventDefault();

      var formData = $(this).serialize();
      var url = <?= $is_edit ? 'true' : 'false' ?> ?
        route_link + 'update' :
        route_link + 'store';

      $.ajax({
        url: url,
        type: 'POST',
        data: formData,
        dataType: 'JSON',
        beforeSend: function() {
          $('#form_template button[type="submit"]').prop('disabled', true).html('<i class="fa fa-spinner fa-spin me-1"></i> Saving...');
        },
        success: function(res) {
          if (res.csrf_token_name && res.csrf_token_hash) {
            $('input[name="' + res.csrf_token_name + '"]').val(res.csrf_token_hash);
            csrf_token = res.csrf_token_hash;
          }

          if (res.status == true) {
            toast_success(res.msg);
            $('#modal_form').modal('hide');
            $('#table_templates').DataTable().ajax.reload();
          } else {
            toast_error(res.msg);
          }
        },
        error: function() {
          toast_error('Failed to save template');
        },
        complete: function() {
          $('#form_template button[type="submit"]').prop('disabled', false).html('<i class="fa fa-save me-1"></i> <?= $is_edit ? "Update" : "Create" ?> Template');
        }
      });
    });
  });
</script>
