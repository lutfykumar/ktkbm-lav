@extends('layouts.app')

@section('title', $title ?? 'Pengaturan Aplikasi')
@section('title_header', 'Aplikasi')
@section('title_parent', $title_parent ?? 'Pengaturan')

@push('styles')
<style>
  /* Visual indicator for modified settings */
  .setting-modified {
    position: relative;
  }

  .setting-modified label::after {
    content: " *";
    color: #f06548;
    font-weight: bold;
  }

  .btn-reset-setting {
    margin-left: 10px;
    padding: 0.25rem 0.5rem;
    font-size: 0.875rem;
  }
</style>
@endpush

@section('content')
<div class="row">
  <div class="col-12">
    <div class="card">
      <div class="card-header d-flex justify-content-between align-items-center">
        <h4 class="card-title mb-0"><i class="fal fa-cog me-2"></i>Pengaturan Aplikasi</h4>
        <div class="btn-group">
          <button type="button" class="btn btn-sm btn-primary" id="btn-export-settings" title="Export Settings">
            <i class="fal fa-download me-1"></i> Export
          </button>
          <button type="button" class="btn btn-sm btn-success" id="btn-import-settings" title="Import Settings">
            <i class="fal fa-upload me-1"></i> Import
          </button>
        </div>
      </div>
      <div class="card-body">

        <!-- Nav Tabs - Dynamic -->
        <ul class="nav nav-tabs nav-tabs-custom nav-success mb-3" role="tablist" id="dynamic-tabs-nav">
          <!-- Tabs will be generated dynamically -->
        </ul>

        <!-- Tab Content -->
        <div class="tab-content" id="dynamic-tabs-container">
          <!-- Loading indicator -->
          <div class="text-center py-5">
            <i class="fal fa-spinner fa-spin fa-3x text-muted"></i>
            <p class="mt-3 text-muted">Loading settings...</p>
          </div>
        </div>
      </div>
    </div>
  </div>
</div>

<!-- Hidden file input for import -->
<input type="file" id="import-file-input" accept=".json" style="display: none;">
@endsection

@push('scripts')
<script>
  const route_link = ip_server + 'pengaturan/aplikasi/';
  let settingsMetadata = {};
  let currentSettings = {};
  let groupedSettings = {};

  const defaultGroupIcons = {
    'general': 'fa-info-circle',
    'branding': 'fa-image',
    'seo': 'fa-search',
    'email': 'fa-envelope',
    'theme': 'fa-palette',
    'security': 'fa-shield-alt',
    'logging': 'fa-file-alt',
    'other': 'fa-ellipsis-h'
  };

  function getGroupLabel(groupKey) {
    const labels = {
      'general': 'Umum',
      'branding': 'Logo & Branding',
      'seo': 'SEO',
      'email': 'Email',
      'theme': 'Tema & Tampilan',
      'security': 'Security',
      'logging': 'Logging',
      'other': 'Lainnya'
    };
    return labels[groupKey] || groupKey.charAt(0).toUpperCase() + groupKey.slice(1);
  }

  function getGroupIcon(groupKey) {
    return defaultGroupIcons[groupKey] || 'fa-cog';
  }

  $(document).ready(function() {
    loadSettingsAndBuildForms();

    $(document).on('click', '#btn-export-settings', function() {
      window.location.href = route_link + 'export_settings';
      toast_success('Mengunduh file export...');
    });

    $(document).on('click', '#btn-import-settings', function() {
      $('#import-file-input').click();
    });

    $('#import-file-input').on('change', function() {
      if (this.files && this.files[0]) {
        importSettings(this.files[0]);
      }
    });
  });

  function loadSettingsAndBuildForms() {
    $.ajax({
      url: route_link + 'get_settings',
      type: 'GET',
      dataType: 'json',
      success: function(response) {
        if (response.status) {
          settingsMetadata = response.metadata;
          currentSettings = response.settings;

          groupSettings();
          buildDynamicForms();
          populateFormValues();
          highlightModifiedSettings();
        } else {
          toast_error('Gagal memuat pengaturan');
        }
      },
      error: function() {
        toast_error('Gagal memuat pengaturan');
      }
    });
  }

  function groupSettings() {
    groupedSettings = {};
    for (const [key, meta] of Object.entries(settingsMetadata)) {
      const group = meta.group || 'other';
      if (!groupedSettings[group]) {
        groupedSettings[group] = [];
      }
      groupedSettings[group].push({
        key: key,
        meta: meta,
        value: currentSettings[key] || meta.default_value || ''
      });
    }
  }

  function buildDynamicForms() {
    const tabsNav = $('#dynamic-tabs-nav');
    const tabsContainer = $('#dynamic-tabs-container');

    tabsNav.empty();
    tabsContainer.empty();

    let isFirst = true;
    for (const [groupKey, settings] of Object.entries(groupedSettings)) {
      const activeClass = isFirst ? 'active' : '';
      const tabId = 'tab-' + groupKey;

      tabsNav.append(`
        <li class="nav-item">
          <a class="nav-link ${activeClass}" data-bs-toggle="tab" href="#${tabId}" role="tab">
            <i class="fal ${getGroupIcon(groupKey)} me-1"></i> ${getGroupLabel(groupKey)}
          </a>
        </li>
      `);

      let formFieldsHtml = '';
      settings.forEach(setting => {
        formFieldsHtml += renderSettingField(setting.key, setting.meta);
      });

      tabsContainer.append(`
        <div class="tab-pane ${activeClass}" id="${tabId}" role="tabpanel">
          <form id="form-${groupKey}" class="kms-form needs-validation" novalidate>
            <input type="hidden" name="${csrf_name}" value="${$('#' + csrf_name).val()}">
            <div class="row">
              ${formFieldsHtml}
            </div>
            <div class="d-flex justify-content-end mt-3">
              <button type="button" class="btn btn-success btn-save-group" data-group="${groupKey}">
                <i class="fal fa-save me-1"></i> Simpan ${getGroupLabel(groupKey)}
              </button>
            </div>
          </form>
        </div>
      `);

      isFirst = false;
    }
  }

  function renderSettingField(key, meta) {
    const inputType = meta.input_type || 'text';
    const label = meta.description || key;
    const placeholder = meta.placeholder || '';
    const helpText = meta.help_text || '';

    let inputHtml = '';
    if (inputType === 'textarea') {
      inputHtml = `<textarea class="form-control" name="${key}" id="${key}" rows="3" placeholder="${placeholder}"></textarea>`;
    } else if (inputType === 'switch') {
      inputHtml = `
        <div class="form-check form-switch mt-2">
          <input class="form-check-input" type="checkbox" name="${key}" id="${key}" value="1">
          <label class="form-check-label" for="${key}">Aktif</label>
        </div>
      `;
    } else if (inputType === 'select') {
      let optionsHtml = '';
      if (meta.options && Array.isArray(meta.options)) {
        meta.options.forEach(opt => {
          optionsHtml += `<option value="${opt}">${opt}</option>`;
        });
      }
      inputHtml = `<select class="form-select" name="${key}" id="${key}">${optionsHtml}</select>`;
    } else if (inputType === 'number') {
      inputHtml = `<input type="number" class="form-control" name="${key}" id="${key}" placeholder="${placeholder}">`;
    } else if (inputType === 'password') {
      inputHtml = `<input type="password" class="form-control" name="${key}" id="${key}" placeholder="${placeholder || '••••••••'}">`;
    } else {
      inputHtml = `<input type="text" class="form-control" name="${key}" id="${key}" placeholder="${placeholder}">`;
    }

    return `
      <div class="col-md-6 mb-3" data-setting-key="${key}">
        <div class="fm">
          <div class="d-flex justify-content-between align-items-center mb-1">
            <label for="${key}" class="form-label mb-0">${label}</label>
          </div>
          ${inputHtml}
          ${helpText ? `<small class="text-muted d-block mt-1">${helpText}</small>` : ''}
        </div>
      </div>
    `;
  }

  function populateFormValues() {
    for (const [key, value] of Object.entries(currentSettings)) {
      const field = $('[name="' + key + '"]');
      if (field.length) {
        const meta = settingsMetadata[key];
        if (meta && meta.input_type === 'switch') {
          field.prop('checked', value == '1' || value === true);
        } else if (meta && meta.input_type === 'password') {
          if (value) field.attr('placeholder', '•••••••• (tersimpan)');
        } else {
          field.val(value);
        }
      }
    }
  }

  function highlightModifiedSettings() {
    for (const [key, meta] of Object.entries(settingsMetadata)) {
      const currentValue = currentSettings[key];
      const defaultValue = meta.default_value;

      if (defaultValue !== undefined && currentValue != defaultValue) {
        const fieldContainer = $('[data-setting-key="' + key + '"]');
        if (fieldContainer.length && fieldContainer.find('.btn-reset-setting').length === 0) {
          fieldContainer.addClass('setting-modified');
          const resetBtn = $(`<button type="button" class="btn btn-sm btn-outline-secondary btn-reset-setting float-end" title="Reset ke default"><i class="fal fa-undo"></i></button>`);
          resetBtn.on('click', function() {
            resetSetting(key);
          });
          fieldContainer.find('label').first().after(resetBtn);
        }
      }
    }
  }

  $(document).on('click', '.btn-save-group', function(e) {
    e.preventDefault();
    const groupKey = $(this).data('group');
    const form = $('#form-' + groupKey);
    const btn = $(this);

    btn.prop('disabled', true).html('<i class="fal fa-spinner fa-spin me-1"></i> Menyimpan...');

    $.ajax({
      url: route_link + 'update_settings',
      type: 'POST',
      data: form.serialize(),
      dataType: 'json',
      success: function(response) {
        if (response.status) {
          toast_success(response.msg);
          if (response.csrf_token_name && response.csrf_token_hash) {
            $('input[name="' + response.csrf_token_name + '"]').val(response.csrf_token_hash);
          }
          loadSettingsAndBuildForms();
        } else {
          toast_error(response.msg);
        }
        btn.prop('disabled', false).html('<i class="fal fa-save me-1"></i> Simpan ' + getGroupLabel(groupKey));
      },
      error: function() {
        btn.prop('disabled', false).html('<i class="fal fa-save me-1"></i> Simpan ' + getGroupLabel(groupKey));
        toast_error('Gagal menyimpan pengaturan');
      }
    });
  });

  function resetSetting(key) {
    if (!confirm('Reset setting "' + key + '" ke default?')) return;

    $.ajax({
      url: route_link + 'reset_setting',
      type: 'POST',
      data: {
        key: key,
        [csrf_name]: $('#' + csrf_name).val()
      },
      dataType: 'json',
      success: function(response) {
        if (response.status) {
          toast_success(response.msg);
          loadSettingsAndBuildForms();
        } else {
          toast_error(response.msg);
        }
      },
      error: function() {
        toast_error('Gagal mereset setting');
      }
    });
  }

  function importSettings(file) {
    const formData = new FormData();
    formData.append('file', file);
    formData.append(csrf_name, $('#' + csrf_name).val());

    $.ajax({
      url: route_link + 'import_settings',
      type: 'POST',
      data: formData,
      processData: false,
      contentType: false,
      dataType: 'json',
      success: function(response) {
        if (response.status) {
          toast_success(response.msg);
          loadSettingsAndBuildForms();
        } else {
          toast_error(response.msg);
        }
      },
      error: function() {
        toast_error('Gagal import settings');
      }
    });
  }
</script>
@endpush
