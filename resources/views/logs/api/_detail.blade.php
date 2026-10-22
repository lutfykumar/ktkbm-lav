<div class="row">
    <div class="col-md-6">
        <h6 class="text-primary mb-3"><i class="fal fa-info-circle me-1"></i> Request Information</h6>
        <table class="table table-sm table-bordered">
            <tr>
                <th width="140">Method:</th>
                <td><span class="badge bg-primary">{{ $log->HTTP_METHOD }}</span></td>
            </tr>
            <tr>
                <th>Endpoint:</th>
                <td><code>{{ $log->ENDPOINT ?: '-' }}</code></td>
            </tr>
            <tr>
                <th>Full URL:</th>
                <td><small class="text-break">{{ $log->FULL_URL ?: '-' }}</small></td>
            </tr>
            <tr>
                <th>Status Code:</th>
                <td>
                    <span class="badge bg-{{ ($log->HTTP_STATUS_CODE >= 400) ? 'danger' : 'success' }}">
                        {{ $log->HTTP_STATUS_CODE }}
                    </span>
                </td>
            </tr>
            <tr>
                <th>Timestamp:</th>
                <td>{{ date('d/m/Y H:i:s', strtotime($log->CREATED_AT)) }}</td>
            </tr>
            <tr>
                <th>Execution Time:</th>
                <td>{{ $log->RESPONSE_TIME_MS ?? 0 }} ms</td>
            </tr>
            <tr>
                <th>Memory Usage:</th>
                <td>{{ $log->MEMORY_USAGE_MB ?? '-' }} MB</td>
            </tr>
        </table>
    </div>

    <div class="col-md-6">
        <h6 class="text-primary mb-3"><i class="fal fa-user me-1"></i> Client Information</h6>
        <table class="table table-sm table-bordered">
            <tr>
                <th width="140">User:</th>
                <td><strong>{{ $log->user_name ?: '-' }}</strong> (ID: {{ $log->USER_ID ?: '-' }})</td>
            </tr>
            <tr>
                <th>IP Address:</th>
                <td><code>{{ $log->IP_ADDRESS ?: '-' }}</code></td>
            </tr>
            <tr>
                <th>Request ID:</th>
                <td><small class="text-muted">{{ $log->REQUEST_ID ?: '-' }}</small></td>
            </tr>
            <tr>
                <th>User Agent:</th>
                <td><small class="text-muted">{{ $log->USER_AGENT ?: '-' }}</small></td>
            </tr>
            <tr>
                <th>Referer:</th>
                <td><small class="text-muted">{{ $log->REFERER ?: '-' }}</small></td>
            </tr>
        </table>
    </div>

    @if(!empty($log->REQUEST_BODY) || !empty($log->RESPONSE_BODY))
        <div class="col-12 mt-3">
            <h6 class="text-primary mb-2"><i class="fal fa-code me-1"></i> Payload & Response</h6>
            <div class="row">
                @if(!empty($log->REQUEST_BODY))
                    <div class="col-md-6">
                        <small class="fw-bold">Request Body:</small>
                        <pre class="bg-light p-2 rounded small" style="max-height: 250px; overflow-y: auto;">{{ is_string($log->REQUEST_BODY) ? $log->REQUEST_BODY : json_encode($log->REQUEST_BODY, JSON_PRETTY_PRINT) }}</pre>
                    </div>
                @endif
                @if(!empty($log->RESPONSE_BODY))
                    <div class="col-md-6">
                        <small class="fw-bold">Response Body:</small>
                        <pre class="bg-light p-2 rounded small" style="max-height: 250px; overflow-y: auto;">{{ is_string($log->RESPONSE_BODY) ? $log->RESPONSE_BODY : json_encode($log->RESPONSE_BODY, JSON_PRETTY_PRINT) }}</pre>
                    </div>
                @endif
            </div>
        </div>
    @endif
</div>
