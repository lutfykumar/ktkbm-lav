@extends('layouts.app')

@section('title', 'Dashboard Pengawas')

@section('content')
<div class="row">
  <div class="col">
    <div class="h-100">
      <!-- Greeting (auto-hide after 5s) -->
      <div id="greeting-header" style="max-height: 120px; overflow: hidden; transition: max-height 0.6s ease, opacity 0.5s ease, margin 0.6s ease; opacity: 1;">
        <div class="row mb-3 pb-1">
          <div class="col-12">
            <div class="d-flex align-items-lg-center flex-lg-row flex-column">
              <div class="flex-grow-1">
                <h4 class="fs-16 mb-1">Selamat Datang, {{ $username ?? 'Pengawas' }} 👋</h4>
                <p class="text-muted mb-0">Ringkasan Work Order Anda &mdash; {{ date('d F Y') }}</p>
              </div>
            </div>
          </div>
        </div>
      </div>

      <!-- Stat Cards -->
      <div class="row">
        <div class="col-xl-3 col-md-6">
          <div class="card card-animate border-0 overflow-hidden">
            <div class="position-absolute top-0 end-0 mt-n2 me-n2">
              <div class="avatar-sm">
                <span class="avatar-title bg-info-subtle rounded-circle fs-1">
                  <i class="fal fa-clipboard-list text-info"></i>
                </span>
              </div>
            </div>
            <div class="card-body">
              <div>
                <p class="text-uppercase fw-medium text-muted text-truncate mb-2">WO Hari Ini</p>
                <h4 class="fs-22 fw-semibold ff-secondary mb-0">
                  <span class="counter-value">{{ $counts->today ?? 0 }}</span>
                </h4>
              </div>
              <div class="mt-3">
                <span class="badge bg-info-subtle text-info">Total: {{ $counts->total ?? 0 }} WO</span>
              </div>
            </div>
          </div>
        </div>
        <div class="col-xl-3 col-md-6">
          <div class="card card-animate border-0 overflow-hidden">
            <div class="position-absolute top-0 end-0 mt-n2 me-n2">
              <div class="avatar-sm">
                <span class="avatar-title bg-warning-subtle rounded-circle fs-1">
                  <i class="fal fa-hourglass-half text-warning"></i>
                </span>
              </div>
            </div>
            <div class="card-body">
              <div>
                <p class="text-uppercase fw-medium text-muted text-truncate mb-2">Open</p>
                <h4 class="fs-22 fw-semibold ff-secondary mb-0">
                  <span class="counter-value">{{ $counts->open_count ?? 0 }}</span>
                </h4>
              </div>
              <div class="mt-3">
                <span class="badge bg-warning-subtle text-warning">Menunggu Laporan</span>
              </div>
            </div>
          </div>
        </div>
        <div class="col-xl-3 col-md-6">
          <div class="card card-animate border-0 overflow-hidden">
            <div class="position-absolute top-0 end-0 mt-n2 me-n2">
              <div class="avatar-sm">
                <span class="avatar-title bg-primary-subtle rounded-circle fs-1">
                  <i class="fal fa-sync text-primary"></i>
                </span>
              </div>
            </div>
            <div class="card-body">
              <div>
                <p class="text-uppercase fw-medium text-muted text-truncate mb-2">Progress</p>
                <h4 class="fs-22 fw-semibold ff-secondary mb-0">
                  <span class="counter-value">{{ $counts->progress_count ?? 0 }}</span>
                </h4>
              </div>
              <div class="mt-3">
                <span class="badge bg-primary-subtle text-primary">Sedang Dikerjakan</span>
              </div>
            </div>
          </div>
        </div>
        <div class="col-xl-3 col-md-6">
          <div class="card card-animate border-0 overflow-hidden">
            <div class="position-absolute top-0 end-0 mt-n2 me-n2">
              <div class="avatar-sm">
                <span class="avatar-title bg-success-subtle rounded-circle fs-1">
                  <i class="fal fa-check-circle text-success"></i>
                </span>
              </div>
            </div>
            <div class="card-body">
              <div>
                <p class="text-uppercase fw-medium text-muted text-truncate mb-2">Selesai</p>
                <h4 class="fs-22 fw-semibold ff-secondary mb-0">
                  <span class="counter-value">{{ $counts->done_count ?? 0 }}</span>
                </h4>
              </div>
              <div class="mt-3">
                <span class="badge bg-success-subtle text-success">Work Order Done</span>
              </div>
            </div>
          </div>
        </div>
      </div>

      <!-- WO Table + Donut Chart -->
      <div class="row">
        <!-- Recent WO Table -->
        <div class="col-xl-8 col-lg-7">
          <div class="card card-height-100">
            <div class="card-header align-items-center d-flex">
              <h4 class="card-title mb-0 flex-grow-1">
                <i class="fal fa-clipboard-list me-2 text-primary"></i>Work Order Terbaru
              </h4>
              <a href="{{ url('activity') }}" class="btn btn-sm btn-soft-primary">
                Lihat Semua <i class="fal fa-arrow-right ms-1"></i>
              </a>
            </div>
            <div class="card-body p-0">
              <div class="table-responsive">
                <table class="table table-hover table-nowrap align-middle mb-0">
                  <thead class="table-light">
                    <tr>
                      <th>No WO</th>
                      <th>Client / Kapal</th>
                      <th>Aktifitas</th>
                      <th class="text-center">Laporan</th>
                      <th class="text-center">Status</th>
                      <th>Tanggal</th>
                    </tr>
                  </thead>
                  <tbody>
                    @forelse($recent_wo as $row)
                      <tr>
                        <td class="fw-semibold">{{ $row->NO_WORK_ORDER }}</td>
                        <td>
                          <span class="text-muted d-block" style="font-size: 11px;">{{ $row->NAMA_CLIENT ?: '-' }}</span>
                          {{ $row->NAMA_KAPAL ?: '-' }}
                        </td>
                        <td>
                          {{ $row->NAMA_AKTIFITAS ?: '-' }}
                          @if(!empty($row->NAMA_SATUAN))
                            <span class="text-muted">({{ $row->NAMA_SATUAN }})</span>
                          @endif
                        </td>
                        <td class="text-center">
                          <span class="badge bg-{{ $row->total_laporan > 0 ? 'success' : 'secondary' }}-subtle text-{{ $row->total_laporan > 0 ? 'success' : 'secondary' }}">
                            {{ $row->total_laporan }}
                          </span>
                        </td>
                        <td class="text-center">
                          @php
                            $badgeClass = 'secondary';
                            if ($row->STATUS == 'open') $badgeClass = 'warning';
                            elseif ($row->STATUS == 'progress') $badgeClass = 'primary';
                            elseif ($row->STATUS == 'done') $badgeClass = 'success';
                            elseif ($row->STATUS == 'hide') $badgeClass = 'dark';
                          @endphp
                          <span class="badge bg-{{ $badgeClass }}-subtle text-{{ $badgeClass }} text-uppercase">{{ $row->STATUS }}</span>
                        </td>
                        <td>
                          <span class="text-muted">{{ date('d/m/Y', strtotime($row->CREATED_AT)) }}</span>
                        </td>
                      </tr>
                    @empty
                      <tr>
                        <td colspan="6" class="text-center text-muted py-4">
                          <i class="fal fa-inbox fa-2x d-block mb-2"></i>
                          Belum ada work order
                        </td>
                      </tr>
                    @endforelse
                  </tbody>
                </table>
              </div>
            </div>
          </div>
        </div>

        <!-- Donut Chart -->
        <div class="col-xl-4 col-lg-5">
          <div class="card card-height-100">
            <div class="card-header align-items-center d-flex">
              <h4 class="card-title mb-0 flex-grow-1">
                <i class="fal fa-chart-pie me-2 text-primary"></i>Ringkasan {{ date('F Y') }}
              </h4>
            </div>
            <div class="card-body d-flex flex-column align-items-center justify-content-center">
              @php
                $m_open = (int)($monthly->open_count ?? 0);
                $m_progress = (int)($monthly->progress_count ?? 0);
                $m_done = (int)($monthly->done_count ?? 0);
                $m_total = $m_open + $m_progress + $m_done;
              @endphp
              @if($m_total > 0)
                <div id="donut_chart" style="min-height: 240px; width: 100%;"></div>
                <div class="mt-3 text-center">
                  <p class="text-muted mb-0">Total <strong>{{ $m_total }}</strong> Work Order bulan ini</p>
                </div>
              @else
                <div class="text-center text-muted py-4">
                  <i class="fal fa-chart-pie fa-3x d-block mb-3 text-muted opacity-50"></i>
                  <p class="mb-0">Belum ada data bulan ini</p>
                </div>
              @endif
            </div>
          </div>
        </div>
      </div>

      <!-- Weekly Bar Chart -->
      <div class="row">
        <div class="col-12">
          <div class="card">
            <div class="card-header align-items-center d-flex">
              <h4 class="card-title mb-0 flex-grow-1">
                <i class="fal fa-chart-bar me-2 text-primary"></i>Aktifitas Laporan 7 Hari Terakhir
              </h4>
            </div>
            <div class="card-body">
              <div id="weekly_chart" style="min-height: 320px;"></div>
            </div>
          </div>
        </div>
      </div>

    </div>
  </div>
</div>
@endsection

@push('scripts')
<script src="{{ asset('assets/libs/apexcharts/apexcharts.min.js') }}"></script>
<script>
  document.addEventListener('DOMContentLoaded', function() {
    // Auto-hide greeting
    var greetEl = document.getElementById('greeting-header');
    if (greetEl) {
      setTimeout(function() {
        greetEl.style.opacity = '0';
        greetEl.style.maxHeight = '0';
        greetEl.style.marginBottom = '0';
        greetEl.addEventListener('transitionend', function handler(e) {
          if (e.propertyName === 'max-height') {
            greetEl.style.display = 'none';
            greetEl.removeEventListener('transitionend', handler);
          }
        });
      }, 5000);
    }

    // Donut Chart
    @if(($m_total ?? 0) > 0)
      var donutOptions = {
        series: [{{ $m_open }}, {{ $m_progress }}, {{ $m_done }}],
        chart: {
          type: 'donut',
          height: 240
        },
        labels: ['Open', 'Progress', 'Done'],
        colors: ['#f7b84b', '#3577f1', '#0ab39c'],
        plotOptions: {
          pie: {
            donut: {
              size: '65%',
              labels: {
                show: true,
                total: {
                  show: true,
                  label: 'Total',
                  fontSize: '14px',
                  fontWeight: 600
                }
              }
            }
          }
        },
        legend: {
          position: 'bottom',
          fontFamily: 'inherit'
        },
        dataLabels: {
          enabled: true,
          dropShadow: {
            enabled: false
          }
        },
        responsive: [{
          breakpoint: 480,
          options: {
            chart: {
              height: 200
            },
            legend: {
              position: 'bottom'
            }
          }
        }]
      };
      new ApexCharts(document.querySelector("#donut_chart"), donutOptions).render();
    @endif

    // Weekly Bar Chart
    var weeklyOptions = {
      series: [{
        name: 'Laporan Dibuat',
        data: {!! $weekly_data !!}
      }],
      chart: {
        type: 'bar',
        height: 320,
        toolbar: {
          show: false
        }
      },
      plotOptions: {
        bar: {
          borderRadius: 6,
          columnWidth: '50%',
          distributed: false,
          dataLabels: {
            position: 'top'
          }
        }
      },
      dataLabels: {
        enabled: true,
        offsetY: -20,
        style: {
          fontSize: '12px',
          fontWeight: 600,
          colors: ['#878a99']
        }
      },
      xaxis: {
        categories: {!! $weekly_labels !!},
        labels: {
          style: {
            fontSize: '12px'
          }
        }
      },
      yaxis: {
        labels: {
          formatter: function(val) {
            return Math.floor(val);
          }
        }
      },
      colors: ['#3577f1'],
      fill: {
        type: 'gradient',
        gradient: {
          shade: 'light',
          type: 'vertical',
          shadeIntensity: 0.25,
          opacityFrom: 0.85,
          opacityTo: 0.95,
          stops: [50, 100]
        }
      },
      grid: {
        borderColor: '#f1f1f1',
        strokeDashArray: 4
      },
      tooltip: {
        y: {
          formatter: function(val) {
            return val + ' laporan';
          }
        }
      }
    };
    new ApexCharts(document.querySelector("#weekly_chart"), weeklyOptions).render();
  });
</script>
@endpush
