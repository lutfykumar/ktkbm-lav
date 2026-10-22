@php
$is_edit = isset($template) && !empty($template);
$template_id = $is_edit ? $template->ID_TEMPLATE : '';
$template_name = $is_edit ? $template->TEMPLATE_NAME : '';
$template_slug = $is_edit ? $template->TEMPLATE_SLUG : '';
$subject = $is_edit ? $template->SUBJECT : '';
$email_body = $is_edit ? $template->EMAIL_BODY : '';
$variables = $is_edit ? (is_array($template->VARIABLES) ? $template->VARIABLES : json_decode($template->VARIABLES, true)) : [];
$description = $is_edit ? $template->DESCRIPTION : '';
$category = $is_edit ? $template->CATEGORY : '';
$is_active = $is_edit ? $template->IS_ACTIVE : 1;
@endphp

<form id="form_template" method="post">
    @csrf
    @if($is_edit)
        <input type="hidden" name="id" value="{{ $template_id }}">
    @endif

    <div class="row">
        <!-- Left Column -->
        <div class="col-md-8">
            <!-- Basic Information -->
            <div class="card mb-3">
                <div class="card-header bg-light">
                    <h6 class="mb-0"><i class="fal fa-info-circle me-1"></i> Basic Information</h6>
                </div>
                <div class="card-body">
                    <div class="mb-3">
                        <label for="template_name" class="form-label">Template Name <span class="text-danger">*</span></label>
                        <input type="text" class="form-control" id="template_name" name="template_name"
                            value="{{ $template_name }}" required>
                    </div>

                    <div class="mb-3">
                        <label for="template_slug" class="form-label">Template Slug <span class="text-danger">*</span></label>
                        <input type="text" class="form-control" id="template_slug" name="template_slug"
                            value="{{ $template_slug }}" required
                            pattern="[a-z0-9_-]+" title="Only lowercase letters, numbers, underscores, and hyphens">
                        <div class="form-text">Unique identifier (lowercase, no spaces). Example: welcome_email</div>
                    </div>

                    <div class="mb-3">
                        <label for="subject" class="form-label">Email Subject <span class="text-danger">*</span></label>
                        <input type="text" class="form-control" id="subject" name="subject"
                            value="{{ $subject }}" required>
                        <div class="form-text">You can use variables like &#123;&#123;site_name&#125;&#125;, &#123;&#123;user_name&#125;&#125;</div>
                    </div>

                    <div class="mb-3">
                        <label for="description" class="form-label">Description</label>
                        <textarea class="form-control" id="description" name="description" rows="2">{{ $description }}</textarea>
                    </div>
                </div>
            </div>

            <!-- Email Body -->
            <div class="card mb-3">
                <div class="card-header bg-light">
                    <h6 class="mb-0"><i class="fal fa-envelope me-1"></i> Email Body <span class="text-danger">*</span></h6>
                </div>
                <div class="card-body">
                    <textarea class="form-control" id="email_body" name="email_body" rows="12" required>{{ $email_body }}</textarea>
                    <div class="form-text">HTML supported. You can insert variables into the body.</div>
                </div>
            </div>
        </div>

        <!-- Right Column -->
        <div class="col-md-4">
            <!-- Settings -->
            <div class="card mb-3">
                <div class="card-header bg-light">
                    <h6 class="mb-0"><i class="fal fa-cog me-1"></i> Settings</h6>
                </div>
                <div class="card-body">
                    <div class="mb-3">
                        <label for="category" class="form-label">Category</label>
                        <input type="text" class="form-control" id="category" name="category"
                            value="{{ $category }}" placeholder="e.g. auth, notification">
                    </div>

                    <div class="mb-3">
                        <label for="variables" class="form-label">Variables (comma-separated)</label>
                        <input type="text" class="form-control" id="variables" name="variables"
                            value="{{ is_array($variables) ? implode(', ', $variables) : '' }}"
                            placeholder="user_name, reset_link, date">
                        <div class="form-text">Available placeholders in subject and body</div>
                    </div>

                    <div class="form-check form-switch mb-3">
                        <input class="form-check-input" type="checkbox" id="is_active" name="is_active" value="1"
                            {{ $is_active ? 'checked' : '' }}>
                        <label class="form-check-label" for="is_active">Active</label>
                    </div>
                </div>
            </div>

            <!-- Variable Cheat Sheet -->
            <div class="card mb-3">
                <div class="card-header bg-light">
                    <h6 class="mb-0"><i class="fal fa-tags me-1"></i> Common Variables</h6>
                </div>
                <div class="card-body">
                    <ul class="list-unstyled mb-0 small">
                        <li class="mb-1"><code>&#123;&#123;site_name&#125;&#125;</code> - Application name</li>
                        <li class="mb-1"><code>&#123;&#123;user_name&#125;&#125;</code> - Recipient username</li>
                        <li class="mb-1"><code>&#123;&#123;email&#125;&#125;</code> - Recipient email</li>
                        <li class="mb-1"><code>&#123;&#123;date&#125;&#125;</code> - Current date</li>
                        <li class="mb-1"><code>&#123;&#123;year&#125;&#125;</code> - Current year</li>
                    </ul>
                </div>
            </div>
        </div>
    </div>

    <div class="modal-footer">
        <button type="button" class="btn btn-secondary waves-effect" data-bs-dismiss="modal">Close</button>
        <button type="submit" class="btn btn-primary waves-effect" id="btn_save_template">
            <i class="fal fa-save me-1"></i> Save Template
        </button>
    </div>
</form>

<script>
    $('#form_template').on('submit', function(e) {
        e.preventDefault();
        let isEdit = {{ $is_edit ? 'true' : 'false' }};
        let url = isEdit ? "{{ url('master/email/update') }}" : "{{ url('master/email/store') }}";

        let btn = $('#btn_save_template');
        btn.prop('disabled', true).html('<i class="fal fa-spinner fa-spin me-1"></i> Saving...');

        $.ajax({
            url: url,
            type: 'POST',
            data: $(this).serialize(),
            success: function(res) {
                btn.prop('disabled', false).html('<i class="fal fa-save me-1"></i> Save Template');
                if (res.status) {
                    $('#modal_form').modal('hide');
                    Swal.fire('Berhasil!', res.msg, 'success');
                    $('#table_templates').DataTable().ajax.reload();
                } else {
                    Swal.fire('Gagal!', res.msg, 'error');
                }
            },
            error: function(xhr) {
                btn.prop('disabled', false).html('<i class="fal fa-save me-1"></i> Save Template');
                let err = xhr.responseJSON?.message || 'Terjadi kesalahan pada server';
                Swal.fire('Error!', err, 'error');
            }
        });
    });
</script>
