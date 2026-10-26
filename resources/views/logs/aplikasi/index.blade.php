@extends('layouts.app')

@section('title', $title ?? 'Application Logs')
@section('title_header', 'Application Logs')
@section('title_parent', $title_parent ?? 'Logs')

@section('content')
<div class="row">
    <div class="col-12">
        <!-- Stats Widgets -->
        <div class="row mb-3">
            <div class="col-xl-3 col-md-6">
                <div class="card card-animate">
                    <div class="card-body">
                        <div class="d-flex align-items-center">
                            <div class="flex-grow-1">
                                <p class="text-uppercase fw-medium text-muted mb-0">Ukuran File Log</p>
                                <h4 class="fs-22 fw-semibold ff-secondary mb-0" id="stat-file-size">{{ $stats['size_formatted'] }}</h4>
                            </div>
                            <div class="avatar-sm flex-shrink-0">
                                <span class="avatar-title bg-primary-subtle text-primary rounded-circle fs-3">
                                    <i class="fal fa-file-alt"></i>
                                </span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-xl-3 col-md-6">
                <div class="card card-animate">
                    <div class="card-body">
                        <div class="d-flex align-items-center">
                            <div class="flex-grow-1">
                                <p class="text-uppercase fw-medium text-muted mb-0">Total Entri (Tail 1000)</p>
                                <h4 class="fs-22 fw-semibold ff-secondary mb-0" id="stat-total-entries">{{ number_format($stats['total_entries']) }}</h4>
                            </div>
                            <div class="avatar-sm flex-shrink-0">
                                <span class="avatar-title bg-info-subtle text-info rounded-circle fs-3">
                                    <i class="fal fa-list-ol"></i>
                                </span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-xl-3 col-md-6">
                <div class="card card-animate">
                    <div class="card-body">
                        <div class="d-flex align-items-center">
                            <div class="flex-grow-1">
                                <p class="text-uppercase fw-medium text-muted mb-0">Error / Critical</p>
                                <h4 class="fs-22 fw-semibold ff-secondary mb-0 text-danger" id="stat-errors">{{ number_format($stats['errors_count']) }}</h4>
                            </div>
                            <div class="avatar-sm flex-shrink-0">
                                <span class="avatar-title bg-danger-subtle text-danger rounded-circle fs-3">
                                    <i class="fal fa-exclamation-circle"></i>
                                </span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-xl-3 col-md-6">
                <div class="card card-animate">
                    <div class="card-body">
                        <div class="d-flex align-items-center">
                            <div class="flex-grow-1">
                                <p class="text-uppercase fw-medium text-muted mb-0">Warnings / Notice</p>
                                <h4 class="fs-22 fw-semibold ff-secondary mb-0 text-warning" id="stat-warnings">{{ number_format($stats['warnings_count']) }}</h4>
                            </div>
                            <div class="avatar-sm flex-shrink-0">
                                <span class="avatar-title bg-warning-subtle text-warning rounded-circle fs-3">
                                    <i class="fal fa-exclamation-triangle"></i>
                                </span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Main Card -->
        <div class="card">
            <div class="card-header border-0">
                <div class="d-flex flex-wrap align-items-center justify-content-between gap-2">
                    <div>
                        <h5 class="card-title mb-0">Laravel System Logs (<code>storage/logs/laravel.log</code>)</h5>
                        <p class="text-muted small mb-0">Terakhir diperbarui: <span id="stat-last-modified">{{ $stats['last_modified'] }}</span></p>
                    </div>
                    <div class="d-flex flex-wrap align-items-center gap-2">
                        <!-- Filter Level -->
                        <div style="min-width: 140px;">
                            <select id="filter-level" class="form-select form-select-sm">
                                <option value="all">Semua Level</option>
                                <option value="error">ERROR / CRITICAL</option>
                                <option value="warning">WARNING</option>
                                <option value="info">INFO</option>
                                <option value="debug">DEBUG</option>
                            </select>
                        </div>

                        <!-- Search -->
                        <div class="search-box">
                            <input type="text" id="search-log" class="form-control form-control-sm" placeholder="Cari pesan log...">
                        </div>

                        <button type="button" class="btn btn-sm btn-soft-secondary" id="btn-refresh" title="Muat Ulang">
                            <i class="fal fa-sync-alt me-1"></i> Refresh
                        </button>

                        @if($can_export)
                        <a href="{{ route('logs.aplikasi.download') }}" class="btn btn-sm btn-soft-primary" id="btn-download" title="Download File Log">
                            <i class="fal fa-download me-1"></i> Unduh Log
                        </a>
                        @endif

                        @if($can_delete)
                        <button type="button" class="btn btn-sm btn-soft-danger" id="btn-clear" title="Kosongkan File Log">
                            <i class="fal fa-trash-alt me-1"></i> Kosongkan
                        </button>
                        @endif
                    </div>
                </div>
            </div>

            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-hover align-middle table-nowrap mb-0" id="table-logs">
                        <thead class="table-light">
                            <tr>
                                <th style="width: 50px;">No</th>
                                <th style="width: 180px;">Waktu</th>
                                <th style="width: 100px;">Level</th>
                                <th style="width: 90px;">Env</th>
                                <th>Pesan Singkat</th>
                                <th style="width: 100px;" class="text-center">Aksi</th>
                            </tr>
                        </thead>
                        <tbody id="log-tbody">
                            <tr>
                                <td colspan="6" class="text-center py-4">
                                    <div class="spinner-border spinner-border-sm text-primary" role="status"></div>
                                    <span class="ms-2">Memuat data log...</span>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>

                <div class="d-flex justify-content-between align-items-center mt-3">
                    <div class="text-muted small" id="log-info">Menampilkan 0 entri</div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Modal Detail Log -->
<div class="modal fade" id="modalDetailLog" tabindex="-1" aria-labelledby="modalDetailLogLabel" aria-hidden="true">
    <div class="modal-dialog modal-xl modal-dialog-centered modal-dialog-scrollable">
        <div class="modal-content">
            <div class="modal-header bg-light">
                <h5 class="modal-title" id="modalDetailLogLabel">
                    <span id="detail-level-badge" class="badge me-2"></span>
                    Detail Log Entri
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <div class="row mb-3">
                    <div class="col-md-6">
                        <p class="mb-1 text-muted small">Waktu Terjadi:</p>
                        <h6 id="detail-date" class="fw-semibold"></h6>
                    </div>
                    <div class="col-md-6">
                        <p class="mb-1 text-muted small">Environment:</p>
                        <h6 id="detail-env" class="fw-semibold"></h6>
                    </div>
                </div>

                <div class="mb-3">
                    <label class="form-label text-muted small">Pesan Keseluruhan:</label>
                    <div class="p-3 bg-light rounded border text-break font-monospace" style="font-size: 13px;" id="detail-message"></div>
                </div>

                <div id="stack-trace-container" style="display: none;">
                    <div class="d-flex justify-content-between align-items-center mb-1">
                        <label class="form-label text-muted small mb-0">Stack Trace:</label>
                        <button type="button" class="btn btn-sm btn-link p-0 text-decoration-none" id="btn-copy-stack">
                            <i class="fal fa-copy me-1"></i> Salin Stack Trace
                        </button>
                    </div>
                    <pre class="p-3 bg-dark text-light rounded border font-monospace overflow-auto" style="max-height: 350px; font-size: 12px;" id="detail-stack"></pre>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary btn-sm" data-bs-dismiss="modal">Tutup</button>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function () {
    let rawLogData = [];

    const tbody = document.getElementById('log-tbody');
    const filterLevel = document.getElementById('filter-level');
    const searchLog = document.getElementById('search-log');
    const btnRefresh = document.getElementById('btn-refresh');
    const btnClear = document.getElementById('btn-clear');
    const logInfo = document.getElementById('log-info');

    // Modal elements
    const modalDetail = new bootstrap.Modal(document.getElementById('modalDetailLog'));
    const detailLevelBadge = document.getElementById('detail-level-badge');
    const detailDate = document.getElementById('detail-date');
    const detailEnv = document.getElementById('detail-env');
    const detailMessage = document.getElementById('detail-message');
    const stackContainer = document.getElementById('stack-trace-container');
    const detailStack = document.getElementById('detail-stack');
    const btnCopyStack = document.getElementById('btn-copy-stack');

    function getLevelBadgeClass(level) {
        level = (level || '').toUpperCase();
        switch (level) {
            case 'EMERGENCY':
            case 'ALERT':
            case 'CRITICAL':
            case 'ERROR':
                return 'bg-danger';
            case 'WARNING':
            case 'NOTICE':
                return 'bg-warning text-dark';
            case 'INFO':
                return 'bg-info';
            case 'DEBUG':
                return 'bg-secondary';
            default:
                return 'bg-light text-dark border';
        }
    }

    function loadLogs() {
        tbody.innerHTML = `
            <tr>
                <td colspan="6" class="text-center py-4">
                    <div class="spinner-border spinner-border-sm text-primary" role="status"></div>
                    <span class="ms-2">Memuat data log...</span>
                </td>
            </tr>
        `;

        const level = filterLevel.value;
        const search = searchLog.value;

        fetch(`{{ route('logs.aplikasi.table') }}?level=${encodeURIComponent(level)}&search=${encodeURIComponent(search)}`, {
            headers: {
                'Accept': 'application/json',
                'X-Requested-With': 'XMLHttpRequest'
            }
        })
        .then(res => res.json())
        .then(res => {
            if (!res.status) {
                tbody.innerHTML = `<tr><td colspan="6" class="text-center text-danger py-4">${res.msg || 'Gagal memuat log.'}</td></tr>`;
                return;
            }

            rawLogData = res.data || [];
            renderTable(rawLogData);
        })
        .catch(err => {
            tbody.innerHTML = `<tr><td colspan="6" class="text-center text-danger py-4">Error: ${err.message}</td></tr>`;
        });
    }

    function renderTable(data) {
        if (!data || data.length === 0) {
            tbody.innerHTML = `<tr><td colspan="6" class="text-center py-4 text-muted">Tidak ada rekaman log yang sesuai.</td></tr>`;
            logInfo.textContent = 'Menampilkan 0 entri';
            return;
        }

        let html = '';
        data.forEach((item, index) => {
            const badgeClass = getLevelBadgeClass(item.level);
            html += `
                <tr>
                    <td class="text-muted">${index + 1}</td>
                    <td class="font-monospace small">${item.date}</td>
                    <td><span class="badge ${badgeClass}">${item.level}</span></td>
                    <td><span class="badge bg-light text-secondary border">${item.env}</span></td>
                    <td class="text-truncate" style="max-width: 500px;" title="${item.short_message.replace(/"/g, '&quot;')}">
                        ${item.short_message}
                    </td>
                    <td class="text-center">
                        <button type="button" class="btn btn-sm btn-soft-primary btn-view-detail" data-index="${index}">
                            <i class="fal fa-eye"></i> Detail
                        </button>
                    </td>
                </tr>
            `;
        });

        tbody.innerHTML = html;
        logInfo.textContent = `Menampilkan ${data.length} entri log`;

        document.querySelectorAll('.btn-view-detail').forEach(btn => {
            btn.addEventListener('click', function () {
                const idx = parseInt(this.getAttribute('data-index'));
                const item = rawLogData[idx];
                if (!item) return;

                detailLevelBadge.className = 'badge me-2 ' + getLevelBadgeClass(item.level);
                detailLevelBadge.textContent = item.level;
                detailDate.textContent = item.date;
                detailEnv.textContent = item.env;
                detailMessage.textContent = item.short_message;

                if (item.stack_trace) {
                    stackContainer.style.display = 'block';
                    detailStack.textContent = item.stack_trace;
                } else {
                    stackContainer.style.display = 'none';
                    detailStack.textContent = '';
                }

                modalDetail.show();
            });
        });
    }

    // Copy Stack Trace
    btnCopyStack.addEventListener('click', function () {
        navigator.clipboard.writeText(detailStack.textContent).then(() => {
            const originalText = btnCopyStack.innerHTML;
            btnCopyStack.innerHTML = '<i class="fal fa-check text-success me-1"></i> Tersalin!';
            setTimeout(() => {
                btnCopyStack.innerHTML = originalText;
            }, 2000);
        });
    });

    // Filters
    filterLevel.addEventListener('change', loadLogs);
    let searchDebounce;
    searchLog.addEventListener('input', function () {
        clearTimeout(searchDebounce);
        searchDebounce = setTimeout(loadLogs, 300);
    });

    btnRefresh.addEventListener('click', loadLogs);

    // Clear Log Action
    if (btnClear) {
        btnClear.addEventListener('click', function () {
            Swal.fire({
                title: 'Kosongkan File Log?',
                text: 'File storage/logs/laravel.log akan dikosongkan. Tindakan ini tidak dapat dibatalkan!',
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#d33',
                cancelButtonColor: '#3085d6',
                confirmButtonText: 'Ya, Kosongkan!',
                cancelButtonText: 'Batal'
            }).then((result) => {
                if (result.isConfirmed) {
                    fetch('{{ route("logs.aplikasi.clear") }}', {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'X-CSRF-TOKEN': '{{ csrf_token() }}',
                            'X-Requested-With': 'XMLHttpRequest'
                        }
                    })
                    .then(res => res.json())
                    .then(res => {
                        if (res.status) {
                            Swal.fire('Berhasil!', res.msg, 'success');
                            loadLogs();
                            // Update stats display
                            document.getElementById('stat-file-size').textContent = '0 KB';
                            document.getElementById('stat-total-entries').textContent = '0';
                            document.getElementById('stat-errors').textContent = '0';
                            document.getElementById('stat-warnings').textContent = '0';
                        } else {
                            Swal.fire('Gagal!', res.msg, 'error');
                        }
                    })
                    .catch(err => {
                        Swal.fire('Error!', err.message, 'error');
                    });
                }
            });
        });
    }

    // Initial Load
    loadLogs();
});
</script>
@endpush
