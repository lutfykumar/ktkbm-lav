<div class="row">
    <div class="col-md-6">
        <h6 class="text-primary mb-3"><i class="fal fa-bug me-1"></i> Informasi Error</h6>
        <table class="table table-sm table-bordered">
            <tr>
                <th width="140">Error Message:</th>
                <td class="text-danger fw-bold">{{ $log->ERROR_MESSAGE ?? '-' }}</td>
            </tr>
            <tr>
                <th>Error Type:</th>
                <td><code>{{ $log->ERROR_TYPE ?? '-' }}</code></td>
            </tr>
            <tr>
                <th>Severity Level:</th>
                <td><span class="badge bg-danger">{{ ucfirst($log->ERROR_LEVEL ?? 'Error') }}</span></td>
            </tr>
            <tr>
                <th>Error Code:</th>
                <td>{{ $log->ERROR_CODE ?? '-' }}</td>
            </tr>
            <tr>
                <th>Lokasi File:</th>
                <td><small class="text-break"><code>{{ $log->ERROR_FILE ?? '-' }}:{{ $log->ERROR_LINE ?? '-' }}</code></small></td>
            </tr>
            <tr>
                <th>Endpoint/URI:</th>
                <td><code>{{ $log->ENDPOINT ?? '-' }}</code></td>
            </tr>
            <tr>
                <th>Status:</th>
                <td>
                    @if($log->IS_RESOLVED)
                        <span class="badge bg-success">Resolved</span>
                    @else
                        <span class="badge bg-danger">Open</span>
                    @endif
                </td>
            </tr>
        </table>
    </div>

    <div class="col-md-6">
        <h6 class="text-primary mb-3"><i class="fal fa-history me-1"></i> Frekuensi & Penyelesaian</h6>
        <table class="table table-sm table-bordered">
            <tr>
                <th width="150">Jumlah Terjadi:</th>
                <td><span class="badge bg-warning text-dark fs-13">{{ $log->OCCURRENCE_COUNT ?? 1 }} kali</span></td>
            </tr>
            <tr>
                <th>Pertama Kali:</th>
                <td>{{ !empty($log->FIRST_OCCURRED_AT) ? date('d/m/Y H:i:s', strtotime($log->FIRST_OCCURRED_AT)) : '-' }}</td>
            </tr>
            <tr>
                <th>Terakhir Kali:</th>
                <td>{{ !empty($log->LAST_OCCURRED_AT) ? date('d/m/Y H:i:s', strtotime($log->LAST_OCCURRED_AT)) : '-' }}</td>
            </tr>
            <tr>
                <th>User Terkait:</th>
                <td>{{ $log->user_name ?: '-' }} (ID: {{ $log->USER_ID ?: '-' }})</td>
            </tr>
            <tr>
                <th>Request ID:</th>
                <td><small class="text-muted">{{ $log->REQUEST_ID ?: '-' }}</small></td>
            </tr>
            <tr>
                <th>Diselesaikan Pada:</th>
                <td>{{ !empty($log->RESOLVED_AT) ? date('d/m/Y H:i:s', strtotime($log->RESOLVED_AT)) : '-' }}</td>
            </tr>
            <tr>
                <th>Catatan Solusi:</th>
                <td>{{ $log->RESOLUTION_NOTES ?: '-' }}</td>
            </tr>
        </table>
    </div>

    @if(!empty($log->STACK_TRACE))
        <div class="col-12 mt-3">
            <h6 class="text-primary mb-2"><i class="fal fa-layer-group me-1"></i> Stack Trace</h6>
            <pre class="bg-dark text-white p-3 rounded small" style="max-height: 350px; overflow-y: auto;">{{ $log->STACK_TRACE }}</pre>
        </div>
    @endif
</div>
