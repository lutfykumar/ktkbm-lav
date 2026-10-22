<div class="row">
    <div class="col-md-6">
        <h6 class="text-primary mb-3"><i class="fal fa-info-circle me-1"></i> Informasi Email</h6>
        <table class="table table-sm table-bordered">
            <tr>
                <th width="140">Subject:</th>
                <td><strong>{{ $log->SUBJECT ?? '-' }}</strong></td>
            </tr>
            <tr>
                <th>Dari (From):</th>
                <td>{{ $log->EMAIL_FROM_NAME ?? '' }} &lt;{{ $log->EMAIL_FROM ?? '-' }}&gt;</td>
            </tr>
            <tr>
                <th>Kepada (To):</th>
                <td><code>{{ $log->EMAIL_TO ?? '-' }}</code></td>
            </tr>
            <tr>
                <th>Kategori:</th>
                <td>{{ ucfirst($log->EMAIL_CATEGORY ?? '-') }}</td>
            </tr>
            <tr>
                <th>Template:</th>
                <td>{{ $log->TEMPLATE_USED ?? '-' }}</td>
            </tr>
            <tr>
                <th>Status:</th>
                <td>
                    <span class="badge bg-{{ ($log->STATUS === 'sent') ? 'success' : (($log->STATUS === 'failed') ? 'danger' : 'info') }}">
                        {{ ucfirst($log->STATUS ?? '-') }}
                    </span>
                </td>
            </tr>
            <tr>
                <th>Waktu Kirim:</th>
                <td>{{ date('d/m/Y H:i:s', strtotime($log->CREATED_AT)) }}</td>
            </tr>
            <tr>
                <th>Durasi/Response:</th>
                <td>{{ $log->RESPONSE_TIME ?? 0 }} ms</td>
            </tr>
        </table>
    </div>

    <div class="col-md-6">
        <h6 class="text-primary mb-3"><i class="fal fa-server me-1"></i> Detail Teknis & Error</h6>
        <table class="table table-sm table-bordered">
            <tr>
                <th width="140">SMTP Host:</th>
                <td><code>{{ $log->SMTP_HOST ?? '-' }}:{{ $log->SMTP_PORT ?? '-' }}</code></td>
            </tr>
            <tr>
                <th>Retry Count:</th>
                <td>{{ $log->RETRY_COUNT ?? 0 }} kali</td>
            </tr>
            <tr>
                <th>User ID:</th>
                <td>{{ $log->SENT_BY_USER_ID ?? '-' }}</td>
            </tr>
            <tr>
                <th>IP Address:</th>
                <td><code>{{ $log->IP_ADDRESS ?? '-' }}</code></td>
            </tr>
            <tr>
                <th>Request ID:</th>
                <td><small class="text-muted">{{ $log->REQUEST_ID ?? '-' }}</small></td>
            </tr>
            <tr>
                <th>Error Message:</th>
                <td>
                    @if(!empty($log->ERROR_MESSAGE))
                        <span class="text-danger small">{{ $log->ERROR_MESSAGE }}</span>
                    @else
                        <span class="text-muted">-</span>
                    @endif
                </td>
            </tr>
        </table>
    </div>

    @if(!empty($log->EMAIL_BODY))
        <div class="col-12 mt-3">
            <h6 class="text-primary mb-2"><i class="fal fa-envelope-open-text me-1"></i> Isi Email (Body)</h6>
            <div class="border rounded p-3 bg-white" style="max-height: 350px; overflow-y: auto;">
                {!! $log->EMAIL_BODY !!}
            </div>
        </div>
    @endif
</div>
