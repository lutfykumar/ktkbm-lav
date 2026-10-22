<?php
$variables = !empty($template->VARIABLES) ? json_decode($template->VARIABLES, true) : [];
?>

<div class="row">
  <!-- Template Information -->
  <div class="col-md-6">
    <div class="card mb-3">
      <div class="card-header bg-light">
        <h6 class="mb-0"><i class="fa fa-info-circle me-1"></i> Template Information</h6>
      </div>
      <div class="card-body">
        <table class="table table-sm table-borderless mb-0">
          <tr>
            <td width="35%" class="text-muted">Template Name</td>
            <td><strong><?= htmlspecialchars($template->TEMPLATE_NAME) ?></strong></td>
          </tr>
          <tr>
            <td class="text-muted">Slug</td>
            <td><code><?= htmlspecialchars($template->TEMPLATE_SLUG) ?></code></td>
          </tr>
          <tr>
            <td class="text-muted">Category</td>
            <td>
              <?php if (!empty($template->CATEGORY)): ?>
                <span class="badge bg-info"><?= ucfirst($template->CATEGORY) ?></span>
              <?php else: ?>
                -
              <?php endif; ?>
            </td>
          </tr>
          <tr>
            <td class="text-muted">Status</td>
            <td>
              <?php if ($template->IS_ACTIVE == 1): ?>
                <span class="badge bg-success">Active</span>
              <?php else: ?>
                <span class="badge bg-secondary">Inactive</span>
              <?php endif; ?>
            </td>
          </tr>
          <tr>
            <td class="text-muted">Description</td>
            <td><?= !empty($template->DESCRIPTION) ? htmlspecialchars($template->DESCRIPTION) : '-' ?></td>
          </tr>
        </table>
      </div>
    </div>

    <!-- Variables -->
    <?php if (!empty($variables)): ?>
      <div class="card mb-3">
        <div class="card-header bg-light">
          <h6 class="mb-0"><i class="fa fa-code me-1"></i> Available Variables</h6>
        </div>
        <div class="card-body">
          <div class="d-flex flex-wrap gap-2">
            <?php foreach ($variables as $var): ?>
              <code class="badge bg-secondary">{{<?= htmlspecialchars($var) ?>}}</code>
            <?php endforeach; ?>
          </div>
        </div>
      </div>
    <?php endif; ?>
  </div>

  <!-- Subject Preview -->
  <div class="col-md-6">
    <div class="card mb-3">
      <div class="card-header bg-light">
        <h6 class="mb-0"><i class="fa fa-envelope me-1"></i> Email Subject</h6>
      </div>
      <div class="card-body">
        <p class="mb-0"><strong><?= htmlspecialchars($template->SUBJECT) ?></strong></p>
      </div>
    </div>
  </div>
</div>

<!-- Email Body Preview -->
<div class="row">
  <div class="col-12">
    <div class="card">
      <div class="card-header bg-light d-flex justify-content-between align-items-center">
        <h6 class="mb-0"><i class="fa fa-envelope-open me-1"></i> Email Body Preview</h6>
        <div class="btn-group btn-group-sm" role="group">
          <button type="button" class="btn btn-outline-primary active" id="btn_preview_rendered">Rendered</button>
          <button type="button" class="btn btn-outline-primary" id="btn_preview_source">Source</button>
        </div>
      </div>
      <div class="card-body">
        <!-- Rendered Preview -->
        <div id="preview_rendered" class="border rounded p-3" style="max-height: 600px; overflow-y: auto; background-color: #f8f9fa;">
          <?= $template->EMAIL_BODY ?>
        </div>

        <!-- Source Code -->
        <div id="preview_source" class="d-none">
          <pre class="border rounded p-3" style="max-height: 600px; overflow-y: auto; background-color: #f8f9fa;"><code><?= htmlspecialchars($template->EMAIL_BODY) ?></code></pre>
        </div>
      </div>
    </div>
  </div>
</div>

<!-- Sample Data for Testing -->
<div class="row mt-3">
  <div class="col-12">
    <div class="card">
      <div class="card-header bg-light">
        <h6 class="mb-0"><i class="fa fa-vials me-1"></i> Test with Sample Data</h6>
      </div>
      <div class="card-body">
        <form id="form_test_template">
          <div class="row">
            <?php if (!empty($variables)): ?>
              <?php foreach ($variables as $var): ?>
                <div class="col-md-4 mb-3">
                  <label class="form-label">{{<?= htmlspecialchars($var) ?>}}</label>
                  <input type="text" class="form-control form-control-sm" name="test_<?= htmlspecialchars($var) ?>"
                    placeholder="Enter value for <?= htmlspecialchars($var) ?>">
                </div>
              <?php endforeach; ?>
            <?php else: ?>
              <div class="col-12">
                <p class="text-muted mb-0">No variables defined for this template.</p>
              </div>
            <?php endif; ?>
          </div>
          <?php if (!empty($variables)): ?>
            <button type="button" class="btn btn-sm btn-primary" id="btn_test_render">
              <i class="fa fa-play me-1"></i> Test Render
            </button>
          <?php endif; ?>
        </form>

        <div id="test_result" class="mt-3 d-none">
          <hr>
          <h6>Test Result:</h6>
          <div class="border rounded p-3" style="background-color: #fff;" id="test_result_content"></div>
        </div>
      </div>
    </div>
  </div>
</div>

<script>
  $(document).ready(function() {
    // Toggle preview mode
    $('#btn_preview_rendered').on('click', function() {
      $(this).addClass('active');
      $('#btn_preview_source').removeClass('active');
      $('#preview_rendered').removeClass('d-none');
      $('#preview_source').addClass('d-none');
    });

    $('#btn_preview_source').on('click', function() {
      $(this).addClass('active');
      $('#btn_preview_rendered').removeClass('active');
      $('#preview_source').removeClass('d-none');
      $('#preview_rendered').addClass('d-none');
    });

    // Test render with sample data
    $('#btn_test_render').on('click', function() {
      var templateBody = <?= json_encode($template->EMAIL_BODY) ?>;
      var templateSubject = <?= json_encode($template->SUBJECT) ?>;

      // Get all test values
      $('#form_test_template input').each(function() {
        var name = $(this).attr('name').replace('test_', '');
        var value = $(this).val() || '[' + name + ']';

        // Replace in body and subject
        var regex = new RegExp('{{' + name + '}}', 'g');
        templateBody = templateBody.replace(regex, value);
        templateSubject = templateSubject.replace(regex, value);
      });

      // Show result
      $('#test_result_content').html(
        '<strong>Subject:</strong> ' + templateSubject + '<hr>' + templateBody
      );
      $('#test_result').removeClass('d-none');
    });
  });
</script>
