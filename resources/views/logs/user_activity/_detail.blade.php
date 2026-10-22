<div class="row">
    <div class="col-md-6">
        <h6 class="text-primary mb-3"><i class="fal fa-info-circle me-1"></i> Informasi Aktivitas</h6>
        <table class="table table-sm table-bordered">
            <tr>
                <th width="140">Deskripsi:</th>
                <td>{{ $log->activity_description ?? '-' }}</td>
            </tr>
            <tr>
                <th>Tipe:</th>
                <td><span class="badge bg-primary">{{ ucfirst($log->activity_type ?? '-') }}</span></td>
            </tr>
            <tr>
                <th>Kategori:</th>
                <td>{{ ucfirst($log->activity_category ?? '-') }}</td>
            </tr>
            <tr>
                <th>Status:</th>
                <td>
                    <span class="badge bg-{{ ($log->activity_status === 'failed') ? 'danger' : 'success' }}">
                        {{ ucfirst($log->activity_status ?? 'success') }}
                    </span>
                </td>
            </tr>
            <tr>
                <th>Waktu:</th>
                <td>{{ date('d/m/Y H:i:s', strtotime($log->created_at)) }}</td>
            </tr>
            <tr>
                <th>Endpoint/URI:</th>
                <td><code>{{ $log->request_uri ?? '-' }}</code></td>
            </tr>
            <tr>
                <th>Controller:</th>
                <td>{{ $log->controller ?? '-' }}@<span>{{ $log->method ?? '-' }}</span></td>
            </tr>
        </table>
    </div>

    <div class="col-md-6">
        <h6 class="text-primary mb-3"><i class="fal fa-user me-1"></i> Informasi User & Device</h6>
        <table class="table table-sm table-bordered">
            <tr>
                <th width="140">Username:</th>
                <td><strong>{{ $log->user_name ?: ($log->username ?: '-') }}</strong></td>
            </tr>
            <tr>
                <th>User ID:</th>
                <td>{{ $log->user_id ?: '-' }}</td>
            </tr>
            <tr>
                <th>IP Address:</th>
                <td><code>{{ $log->ip_address ?: '-' }}</code></td>
            </tr>
            <tr>
                <th>Browser:</th>
                <td>{{ $log->browser ?: '-' }} ({{ $log->platform ?: '-' }})</td>
            </tr>
            <tr>
                <th>Device:</th>
                <td>{{ ucfirst($log->device_type ?: 'Desktop') }}</td>
            </tr>
            <tr>
                <th>Session ID:</th>
                <td><small class="text-muted">{{ $log->session_id ?: '-' }}</small></td>
            </tr>
        </table>
    </div>

    @if(!empty($log->old_values) || !empty($log->new_values))
        <div class="col-12 mt-3">
            <h6 class="text-primary mb-2"><i class="fal fa-exchange me-1"></i> Perubahan Data</h6>
            <div class="row">
                @if(!empty($log->old_values))
                    <div class="col-md-6">
                        <small class="fw-bold text-danger">Nilai Lama:</small>
                        <pre class="bg-light p-2 rounded small" style="max-height: 200px; overflow-y: auto;">{{ is_string($log->old_values) ? $log->old_values : json_encode($log->old_values, JSON_PRETTY_PRINT) }}</pre>
                    </div>
                @endif
                @if(!empty($log->new_values))
                    <div class="col-md-6">
                        <small class="fw-bold text-success">Nilai Baru:</small>
                        <pre class="bg-light p-2 rounded small" style="max-height: 200px; overflow-y: auto;">{{ is_string($log->new_values) ? $log->new_values : json_encode($log->new_values, JSON_PRETTY_PRINT) }}</pre>
                    </div>
                @endif
            </div>
        </div>
    @endif
</div>
