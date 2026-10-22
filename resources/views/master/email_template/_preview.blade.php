@php
$variables = is_array($template->VARIABLES) ? $template->VARIABLES : json_decode($template->VARIABLES, true);
@endphp

<div class="row">
    <!-- Template Information -->
    <div class="col-md-6">
        <div class="card mb-3">
            <div class="card-header bg-light">
                <h6 class="mb-0"><i class="fal fa-info-circle me-1"></i> Template Information</h6>
            </div>
            <div class="card-body">
                <table class="table table-sm table-borderless mb-0">
                    <tr>
                        <td width="35%" class="text-muted">Template Name</td>
                        <td><strong>{{ $template->TEMPLATE_NAME }}</strong></td>
                    </tr>
                    <tr>
                        <td class="text-muted">Slug</td>
                        <td><code>{{ $template->TEMPLATE_SLUG }}</code></td>
                    </tr>
                    <tr>
                        <td class="text-muted">Category</td>
                        <td>
                            @if(!empty($template->CATEGORY))
                                <span class="badge bg-info">{{ ucfirst($template->CATEGORY) }}</span>
                            @else
                                -
                            @endif
                        </td>
                    </tr>
                    <tr>
                        <td class="text-muted">Status</td>
                        <td>
                            @if($template->IS_ACTIVE == 1)
                                <span class="badge bg-success">Active</span>
                            @else
                                <span class="badge bg-secondary">Inactive</span>
                            @endif
                        </td>
                    </tr>
                    <tr>
                        <td class="text-muted">Description</td>
                        <td>{{ $template->DESCRIPTION ?: '-' }}</td>
                    </tr>
                </table>
            </div>
        </div>

        <!-- Variables -->
        @if(!empty($variables))
            <div class="card mb-3">
                <div class="card-header bg-light">
                    <h6 class="mb-0"><i class="fal fa-code me-1"></i> Available Variables</h6>
                </div>
                <div class="card-body">
                    <div class="d-flex flex-wrap gap-2">
                        @foreach($variables as $var)
                            <code class="badge bg-secondary">&#123;&#123;{{ $var }}&#125;&#125;</code>
                        @endforeach
                    </div>
                </div>
            </div>
        @endif
    </div>

    <!-- Preview -->
    <div class="col-md-6">
        <div class="card mb-3">
            <div class="card-header bg-light">
                <h6 class="mb-0"><i class="fal fa-eye me-1"></i> Subject</h6>
            </div>
            <div class="card-body">
                <h6>{{ $template->SUBJECT }}</h6>
            </div>
        </div>

        <div class="card mb-3">
            <div class="card-header bg-light">
                <h6 class="mb-0"><i class="fal fa-envelope-open-text me-1"></i> Body Preview</h6>
            </div>
            <div class="card-body border rounded bg-white p-3" style="max-height: 400px; overflow-y: auto;">
                {!! $template->EMAIL_BODY !!}
            </div>
        </div>
    </div>
</div>
