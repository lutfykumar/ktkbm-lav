@extends('layouts.app')

@section('title', $title ?? 'Logs Dashboard')
@section('title_header', 'Logs & Analytics Dashboard')
@section('title_parent', $title_parent ?? 'Logs')

@section('content')
<div class="row">
    <div class="col-12">
        <!-- Top Title Bar & Manual Refresh -->
        <div class="d-flex flex-wrap align-items-center justify-content-between mb-3 gap-2">
            <div>
                <h5 class="fs-16 mb-0 text-dark fw-bold">Pusat Pemantauan & Analisis Seluruh Log</h5>
                <p class="text-muted small mb-0">Rangkuman terpadu aktivitas pengguna, request API, email, error database, dan file log aplikasi.</p>
            </div>
            <div class="d-flex align-items-center gap-2">
                <span class="text-muted small">Pembaruan data: <strong id="last-refresh-time">{{ now()->translatedFormat('H:i:s') }}</strong></span>
                <button type="button" class="btn btn-sm btn-primary" id="btn-manual-refresh">
                    <i class="fal fa-sync-alt me-1" id="refresh-icon"></i> Refresh Data
                </button>
            </div>
        </div>

        <!-- 4 Top Stat Cards -->
        <div class="row mb-3">
            <div class="col-xl-3 col-md-6">
                <div class="card card-animate">
                    <div class="card-body">
                        <div class="d-flex align-items-center">
                            <div class="flex-grow-1">
                                <p class="text-uppercase fw-medium text-muted mb-0">Aktivitas Hari Ini</p>
                                <h4 class="fs-22 fw-semibold ff-secondary mb-0" id="stat-activity-today">{{ number_format($metrics['activity_today']) }}</h4>
                                <small class="text-muted">Total: <span id="stat-activity-total">{{ number_format($metrics['total_activities']) }}</span> logs</small>
                            </div>
                            <div class="avatar-sm flex-shrink-0">
                                <span class="avatar-title bg-info-subtle text-info rounded-circle fs-3">
                                    <i class="fal fa-user-clock"></i>
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
                                <p class="text-uppercase fw-medium text-muted mb-0">API Request Hari Ini</p>
                                <h4 class="fs-22 fw-semibold ff-secondary mb-0" id="stat-api-today">{{ number_format($metrics['api_today']) }}</h4>
                                <small class="text-success"><i class="fal fa-check-circle me-1"></i><span id="stat-api-rate">{{ $metrics['api_success_rate'] }}%</span> Success Rate</small>
                            </div>
                            <div class="avatar-sm flex-shrink-0">
                                <span class="avatar-title bg-primary-subtle text-primary rounded-circle fs-3">
                                    <i class="fal fa-network-wired"></i>
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
                                <p class="text-uppercase fw-medium text-muted mb-0">Email Terkirim</p>
                                <h4 class="fs-22 fw-semibold ff-secondary mb-0 text-success" id="stat-email-sent">{{ number_format($metrics['email_sent']) }}</h4>
                                <small class="text-muted">Gagal: <span id="stat-email-failed" class="text-danger">{{ number_format($metrics['email_failed']) }}</span></small>
                            </div>
                            <div class="avatar-sm flex-shrink-0">
                                <span class="avatar-title bg-success-subtle text-success rounded-circle fs-3">
                                    <i class="fal fa-paper-plane"></i>
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
                                <p class="text-uppercase fw-medium text-muted mb-0">Unresolved Errors</p>
                                <h4 class="fs-22 fw-semibold ff-secondary mb-0 text-danger" id="stat-unresolved-errors">{{ number_format($metrics['unresolved_errors']) }}</h4>
                                <small class="text-muted">File Log: <span id="stat-log-size" class="fw-semibold">{{ $metrics['log_size_formatted'] }}</span></small>
                            </div>
                            <div class="avatar-sm flex-shrink-0">
                                <span class="avatar-title bg-danger-subtle text-danger rounded-circle fs-3">
                                    <i class="fal fa-bug"></i>
                                </span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Charts Row -->
        <div class="row mb-3">
            <!-- 7-Day Trend Chart -->
            <div class="col-xl-8">
                <div class="card">
                    <div class="card-header border-0 align-items-center d-flex justify-content-between">
                        <h4 class="card-title mb-0 flex-grow-1">Tren Aktivitas, API, & Error (7 Hari Terakhir)</h4>
                        <div class="text-muted small">Visualisasi Harian</div>
                    </div>
                    <div class="card-body">
                        <div id="trend_chart" style="min-height: 330px;"></div>
                    </div>
                </div>
            </div>

            <!-- Distribution Donut Chart -->
            <div class="col-xl-4">
                <div class="card">
                    <div class="card-header border-0 align-items-center d-flex justify-content-between">
                        <h4 class="card-title mb-0 flex-grow-1">Komposisi Volume Log</h4>
                    </div>
                    <div class="card-body">
                        <div id="distribution_chart" style="min-height: 330px;"></div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Subsystems Quick Navigation Cards -->
        <div class="row mb-3">
            <div class="col-12">
                <div class="card">
                    <div class="card-header border-0">
                        <h5 class="card-title mb-0">Navigasi Langsung ke Subsistem Log</h5>
                    </div>
                    <div class="card-body pt-0">
                        <div class="row g-3">
                            <div class="col-md-2 col-sm-4 col-6">
                                <a href="{{ route('logs.aktifitas.index') }}" class="card text-center border shadow-none mb-0 p-3 text-decoration-none hover-shadow transition">
                                    <div class="avatar-xs mx-auto mb-2">
                                        <span class="avatar-title bg-info-subtle text-info rounded-circle fs-4">
                                            <i class="fal fa-user-shield"></i>
                                        </span>
                                    </div>
                                    <h6 class="fs-13 text-dark mb-1">Aktivitas</h6>
                                    <span class="badge bg-light text-muted border">Buka Modul</span>
                                </a>
                            </div>

                            <div class="col-md-2 col-sm-4 col-6">
                                <a href="{{ route('logs.api.index') }}" class="card text-center border shadow-none mb-0 p-3 text-decoration-none hover-shadow transition">
                                    <div class="avatar-xs mx-auto mb-2">
                                        <span class="avatar-title bg-primary-subtle text-primary rounded-circle fs-4">
                                            <i class="fal fa-plug"></i>
                                        </span>
                                    </div>
                                    <h6 class="fs-13 text-dark mb-1">API Requests</h6>
                                    <span class="badge bg-light text-muted border">Buka Modul</span>
                                </a>
                            </div>

                            <div class="col-md-2 col-sm-4 col-6">
                                <a href="{{ route('logs.email.index') }}" class="card text-center border shadow-none mb-0 p-3 text-decoration-none hover-shadow transition">
                                    <div class="avatar-xs mx-auto mb-2">
                                        <span class="avatar-title bg-success-subtle text-success rounded-circle fs-4">
                                            <i class="fal fa-envelope"></i>
                                        </span>
                                    </div>
                                    <h6 class="fs-13 text-dark mb-1">Email Logs</h6>
                                    <span class="badge bg-light text-muted border">Buka Modul</span>
                                </a>
                            </div>

                            <div class="col-md-2 col-sm-4 col-6">
                                <a href="{{ route('logs.errors.index') }}" class="card text-center border shadow-none mb-0 p-3 text-decoration-none hover-shadow transition">
                                    <div class="avatar-xs mx-auto mb-2">
                                        <span class="avatar-title bg-danger-subtle text-danger rounded-circle fs-4">
                                            <i class="fal fa-exclamation-triangle"></i>
                                        </span>
                                    </div>
                                    <h6 class="fs-13 text-dark mb-1">Error Tracking</h6>
                                    <span class="badge bg-light text-muted border">Buka Modul</span>
                                </a>
                            </div>

                            <div class="col-md-2 col-sm-4 col-6">
                                <a href="{{ route('logs.aplikasi.index') }}" class="card text-center border shadow-none mb-0 p-3 text-decoration-none hover-shadow transition">
                                    <div class="avatar-xs mx-auto mb-2">
                                        <span class="avatar-title bg-dark-subtle text-dark rounded-circle fs-4">
                                            <i class="fal fa-file-code"></i>
                                        </span>
                                    </div>
                                    <h6 class="fs-13 text-dark mb-1">Log Aplikasi</h6>
                                    <span class="badge bg-light text-muted border">Buka Modul</span>
                                </a>
                            </div>

                            <div class="col-md-2 col-sm-4 col-6">
                                <a href="{{ route('pengaturan.cron.index') }}" class="card text-center border shadow-none mb-0 p-3 text-decoration-none hover-shadow transition">
                                    <div class="avatar-xs mx-auto mb-2">
                                        <span class="avatar-title bg-secondary-subtle text-secondary rounded-circle fs-4">
                                            <i class="fal fa-clock"></i>
                                        </span>
                                    </div>
                                    <h6 class="fs-13 text-dark mb-1">Cron Logs</h6>
                                    <span class="badge bg-light text-muted border">Buka Modul</span>
                                </a>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Live Feeds Row -->
        <div class="row">
            <!-- Latest Activities -->
            <div class="col-xl-6">
                <div class="card">
                    <div class="card-header border-0 align-items-center d-flex justify-content-between">
                        <h4 class="card-title mb-0 flex-grow-1">Aktivitas Pengguna Terkini</h4>
                        <a href="{{ route('logs.aktifitas.index') }}" class="text-muted small">Lihat Semua <i class="fal fa-chevron-right ms-1"></i></a>
                    </div>
                    <div class="card-body pt-0">
                        <div class="table-responsive">
                            <table class="table table-hover align-middle table-nowrap mb-0">
                                <thead class="table-light">
                                    <tr>
                                        <th>Pengguna</th>
                                        <th>Aktivitas</th>
                                        <th>Modul</th>
                                        <th>Waktu</th>
                                    </tr>
                                </thead>
                                <tbody id="feed-activities-body">
                                    @forelse($recentData['activities'] as $act)
                                    <tr>
                                        <td>
                                            <span class="fw-semibold text-dark">{{ $act->username ?? 'System' }}</span>
                                        </td>
                                        <td class="text-truncate" style="max-width: 200px;" title="{{ $act->activity_description ?? '' }}">
                                            {{ $act->activity_description ?? '-' }}
                                        </td>
                                        <td><span class="badge bg-light text-secondary border">{{ $act->activity_type ?? 'general' }}</span></td>
                                        <td class="text-muted small font-monospace">{{ $act->created_at }}</td>
                                    </tr>
                                    @empty
                                    <tr>
                                        <td colspan="4" class="text-center text-muted py-3">Belum ada aktivitas.</td>
                                    </tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Latest Unresolved / Recent Errors -->
            <div class="col-xl-6">
                <div class="card">
                    <div class="card-header border-0 align-items-center d-flex justify-content-between">
                        <h4 class="card-title mb-0 flex-grow-1">Error Database Terkini</h4>
                        <a href="{{ route('logs.errors.index') }}" class="text-muted small">Lihat Semua <i class="fal fa-chevron-right ms-1"></i></a>
                    </div>
                    <div class="card-body pt-0">
                        <div class="table-responsive">
                            <table class="table table-hover align-middle table-nowrap mb-0">
                                <thead class="table-light">
                                    <tr>
                                        <th>Level</th>
                                        <th>Pesan Error</th>
                                        <th>File</th>
                                        <th>Waktu</th>
                                    </tr>
                                </thead>
                                <tbody id="feed-errors-body">
                                    @forelse($recentData['errors'] as $err)
                                    <tr>
                                        <td>
                                            <span class="badge {{ $err->ERROR_LEVEL === 'critical' ? 'bg-danger' : 'bg-warning text-dark' }}">
                                                {{ strtoupper($err->ERROR_LEVEL ?? 'ERROR') }}
                                            </span>
                                        </td>
                                        <td class="text-truncate" style="max-width: 220px;" title="{{ $err->ERROR_MESSAGE ?? '' }}">
                                            {{ $err->ERROR_MESSAGE ?? '-' }}
                                        </td>
                                        <td class="text-muted small font-monospace">{{ basename($err->FILE_PATH ?? 'unknown') }}</td>
                                        <td class="text-muted small font-monospace">{{ $err->LAST_OCCURRED_AT ?? '-' }}</td>
                                    </tr>
                                    @empty
                                    <tr>
                                        <td colspan="4" class="text-center text-success py-3"><i class="fal fa-check-circle me-1"></i> Tidak ada error terkini.</td>
                                    </tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<!-- ApexCharts Library -->
<script src="{{ asset('assets/libs/apexcharts/apexcharts.min.js') }}"></script>

<script>
document.addEventListener('DOMContentLoaded', function () {
    const categories = @json($trendData['categories']);
    const activitiesSeries = @json($trendData['activities']);
    const apiSeries = @json($trendData['api']);
    const errorsSeries = @json($trendData['errors']);

    // 1. Trend Area Chart
    const trendOptions = {
        series: [
            { name: 'Aktivitas User', data: activitiesSeries },
            { name: 'Request API', data: apiSeries },
            { name: 'Error Terjadi', data: errorsSeries }
        ],
        chart: {
            type: 'area',
            height: 330,
            toolbar: { show: false },
            zoom: { enabled: false }
        },
        colors: ['#0ab39c', '#405189', '#f06548'],
        dataLabels: { enabled: false },
        stroke: { curve: 'smooth', width: 2 },
        fill: {
            type: 'gradient',
            gradient: {
                shadeIntensity: 1,
                opacityFrom: 0.45,
                opacityTo: 0.05,
                stops: [20, 100, 100, 100]
            }
        },
        xaxis: {
            categories: categories,
            axisBorder: { show: false },
            axisTicks: { show: false }
        },
        yaxis: {
            labels: {
                formatter: function (val) {
                    return Math.floor(val);
                }
            }
        },
        legend: {
            position: 'top',
            horizontalAlign: 'right'
        },
        tooltip: {
            theme: 'light',
            shared: true
        }
    };

    const trendChart = new ApexCharts(document.querySelector("#trend_chart"), trendOptions);
    trendChart.render();

    // 2. Distribution Donut Chart
    const totalAct = {{ $metrics['total_activities'] }};
    const totalApi = {{ $metrics['total_api'] }};
    const totalEmail = {{ $metrics['email_sent'] + $metrics['email_failed'] }};
    const totalErr = {{ $metrics['unresolved_errors'] }};

    const donutOptions = {
        series: [totalAct, totalApi, totalEmail, totalErr],
        labels: ['Aktivitas Pengguna', 'Request API', 'Email Logs', 'Unresolved Error'],
        chart: {
            type: 'donut',
            height: 330
        },
        colors: ['#0ab39c', '#405189', '#299cdb', '#f06548'],
        legend: {
            position: 'bottom'
        },
        dataLabels: {
            enabled: true,
            formatter: function (val) {
                return Math.round(val) + "%";
            }
        },
        plotOptions: {
            pie: {
                donut: {
                    size: '65%'
                }
            }
        }
    };

    const distributionChart = new ApexCharts(document.querySelector("#distribution_chart"), donutOptions);
    distributionChart.render();

    // 3. Manual Refresh Action
    const btnRefresh = document.getElementById('btn-manual-refresh');
    const refreshIcon = document.getElementById('refresh-icon');
    const lastRefreshTime = document.getElementById('last-refresh-time');

    btnRefresh.addEventListener('click', function () {
        refreshIcon.classList.add('fa-spin');
        btnRefresh.disabled = true;

        fetch('{{ route("logs.dashboard.realtime") }}', {
            headers: {
                'Accept': 'application/json',
                'X-Requested-With': 'XMLHttpRequest'
            }
        })
        .then(res => res.json())
        .then(res => {
            refreshIcon.classList.remove('fa-spin');
            btnRefresh.disabled = false;

            if (res.status) {
                lastRefreshTime.textContent = res.timestamp || new Date().toLocaleTimeString();

                // Update counters
                const m = res.metrics;
                document.getElementById('stat-activity-today').textContent = Number(m.activity_today).toLocaleString();
                document.getElementById('stat-activity-total').textContent = Number(m.total_activities).toLocaleString();
                document.getElementById('stat-api-today').textContent = Number(m.api_today).toLocaleString();
                document.getElementById('stat-api-rate').textContent = m.api_success_rate + '%';
                document.getElementById('stat-email-sent').textContent = Number(m.email_sent).toLocaleString();
                document.getElementById('stat-email-failed').textContent = Number(m.email_failed).toLocaleString();
                document.getElementById('stat-unresolved-errors').textContent = Number(m.unresolved_errors).toLocaleString();
                document.getElementById('stat-log-size').textContent = m.log_size_formatted;

                // Update Feeds
                if (res.recentData && res.recentData.activities) {
                    let actHtml = '';
                    res.recentData.activities.forEach(a => {
                        actHtml += `
                            <tr>
                                <td><span class="fw-semibold text-dark">${a.username || 'System'}</span></td>
                                <td class="text-truncate" style="max-width: 200px;" title="${a.activity_description || ''}">${a.activity_description || '-'}</td>
                                <td><span class="badge bg-light text-secondary border">${a.activity_type || 'general'}</span></td>
                                <td class="text-muted small font-monospace">${a.created_at}</td>
                            </tr>
                        `;
                    });
                    document.getElementById('feed-activities-body').innerHTML = actHtml;
                }

                if (res.recentData && res.recentData.errors) {
                    let errHtml = '';
                    if (res.recentData.errors.length === 0) {
                        errHtml = '<tr><td colspan="4" class="text-center text-success py-3"><i class="fal fa-check-circle me-1"></i> Tidak ada error terkini.</td></tr>';
                    } else {
                        res.recentData.errors.forEach(e => {
                            const badgeClass = e.ERROR_LEVEL === 'critical' ? 'bg-danger' : 'bg-warning text-dark';
                            errHtml += `
                                <tr>
                                    <td><span class="badge ${badgeClass}">${(e.ERROR_LEVEL || 'ERROR').toUpperCase()}</span></td>
                                    <td class="text-truncate" style="max-width: 220px;" title="${e.ERROR_MESSAGE || ''}">${e.ERROR_MESSAGE || '-'}</td>
                                    <td class="text-muted small font-monospace">${(e.FILE_PATH || 'unknown').split('/').pop().split('\\\\').pop()}</td>
                                    <td class="text-muted small font-monospace">${e.LAST_OCCURRED_AT || '-'}</td>
                                </tr>
                            `;
                        });
                    }
                    document.getElementById('feed-errors-body').innerHTML = errHtml;
                }
            }
        })
        .catch(err => {
            refreshIcon.classList.remove('fa-spin');
            btnRefresh.disabled = false;
            console.error('Refresh failed:', err);
        });
    });
});
</script>
@endpush
