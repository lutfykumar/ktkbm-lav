@if (!empty($model))
  @php
    $status = strtolower($model->STATUS ?? '');
    $statusCls = match ($status) {
        'open' => 'bg-info',
        'progress' => 'bg-primary',
        'review' => 'bg-warning text-dark',
        'done' => 'bg-success',
        default => 'bg-secondary',
    };
    $statusLabel = match ($status) {
        'open' => 'Open',
        'progress' => 'Progress',
        'review' => 'Menunggu Verifikasi (Review)',
        'done' => 'Done',
        default => ucfirst($model->STATUS ?? ''),
    };
  @endphp

  <!-- Header Info -->
  <div class="d-flex align-items-center mb-3 pb-3 border-bottom">
    <div class="flex-grow-1">
      <h4 class="fw-bold mb-1">{{ !empty($model->NO_WORK_ORDER) ? $model->NO_WORK_ORDER : '-' }}</h4>
      <div class="hstack gap-2 flex-wrap">
        <span class="badge {{ $statusCls }} fs-12">{{ $statusLabel }}</span>
        <span class="text-muted">|</span>
        <span class="text-muted"><i class="fal fa-calendar-alt me-1"></i> {{ !empty($model->CREATED_AT) ? $model->CREATED_AT : '-' }}</span>
        @if (!empty($model->UPDATED_AT))
          <span class="text-muted">|</span>
          <span class="text-muted"><i class="fal fa-edit me-1"></i> Diupdate: {{ $model->UPDATED_AT }}</span>
        @endif
      </div>
    </div>
  </div>

  <!-- Info Cards -->
  <div class="row">
    <div class="col-xl-8 col-lg-7">
      <div class="card shadow-none border">
        <div class="card-header bg-light">
          <h5 class="card-title mb-0"><i class="fal fa-info-circle me-1"></i> Informasi Work Order</h5>
        </div>
        <div class="card-body">
          <div class="table-responsive">
            <table class="table table-borderless mb-0">
              <tbody>
                <tr>
                  <td class="fw-medium text-muted" style="width:180px;">Client</td>
                  <td>: {{ !empty($model->NAMA_CLIENT) ? $model->NAMA_CLIENT : '-' }}</td>
                </tr>
                <tr>
                  <td class="fw-medium text-muted">Kapal</td>
                  <td>: {{ !empty($model->NAMA_KAPAL) ? $model->NAMA_KAPAL : '-' }}</td>
                </tr>
                <tr>
                  <td class="fw-medium text-muted">Pelabuhan</td>
                  <td>: {{ !empty($model->NAMA_PELABUHAN) ? $model->NAMA_PELABUHAN : '-' }}</td>
                </tr>
                <tr>
                  <td class="fw-medium text-muted">Dermaga</td>
                  <td>: {{ !empty($model->NAMA_DERMAGA) ? $model->NAMA_DERMAGA : '-' }}</td>
                </tr>
                <tr>
                  <td class="fw-medium text-muted">Aktifitas</td>
                  <td>: {{ !empty($model->NAMA_AKTIFITAS) ? $model->NAMA_AKTIFITAS : '-' }}</td>
                </tr>
                <tr>
                  <td class="fw-medium text-muted">Satuan</td>
                  <td>: {{ !empty($model->NAMA_SATUAN) ? $model->NAMA_SATUAN : '-' }}</td>
                </tr>
                <tr>
                  <td class="fw-medium text-muted">Pengawas</td>
                  <td>: {!! !empty($model->PENGAWAS_USER) ? e($model->PENGAWAS_USER) : '<span class="text-warning">Belum ditentukan</span>' !!}</td>
                </tr>
                @if (!empty($model->NAMA_ALAT))
                  <tr>
                    <td class="fw-medium text-muted">Alat Bantu</td>
                    <td>: {{ $model->NAMA_ALAT }}</td>
                  </tr>
                @elseif (!empty($wo_alat))
                  <tr>
                    <td class="fw-medium text-muted">Alat Bantu</td>
                    <td>:
                      @php
                        $alatNames = array_map(fn($a) => $a->NAMA ?? $a->NAMA_ALAT ?? '', $wo_alat);
                        echo implode(', ', array_filter($alatNames));
                      @endphp
                    </td>
                  </tr>
                @endif
              </tbody>
            </table>
          </div>
        </div>
      </div>
    </div>

    <div class="col-xl-4 col-lg-5">
      <div class="card shadow-none border">
        <div class="card-header bg-light">
          <h5 class="card-title mb-0"><i class="fal fa-clipboard-list me-1"></i> Ringkasan</h5>
        </div>
        <div class="card-body">
          <div class="d-flex justify-content-between align-items-center mb-3">
            <span class="text-muted">Status</span>
            <span class="badge {{ $statusCls }} fs-13">{{ $statusLabel }}</span>
          </div>
          <div class="d-flex justify-content-between align-items-center mb-3">
            <span class="text-muted">Total Laporan</span>
            <span class="fw-bold fs-16">{{ !empty($details) ? count($details) . ' GG' : 0 }}</span>
          </div>
          <div class="d-flex justify-content-between align-items-center mb-3">
            <span class="text-muted">Volume</span>
            <span class="fw-bold fs-16">
              @php
                $totalVolume = 0;
                $totalRansum = 0;
                if (!empty($details)) {
                  foreach ($details as $d) {
                    $totalVolume += floatval($d->HASIL ?? 0);
                    $totalRansum += intval($d->RANSUM ?? 0);
                  }
                }
                echo ribuan($totalVolume);
              @endphp
              {{ !empty($model->NAMA_SATUAN) ? $model->NAMA_SATUAN : '' }}
            </span>
          </div>
          @if (!empty($totalRansum) && $totalRansum > 0)
            <div class="d-flex justify-content-between align-items-center mb-3">
              <span class="text-muted">Total Ransum</span>
              <span class="fw-bold fs-16">{{ ribuan($totalRansum) }} Bungkus</span>
            </div>
          @endif
          <div class="d-flex justify-content-between align-items-center mb-3">
            <span class="text-muted">Dibuat Oleh</span>
            <span class="fw-medium">{{ !empty($model->CREATED_USER) ? $model->CREATED_USER : '-' }}</span>
          </div>
          @if (!empty($model->UPDATED_USER))
            <div class="d-flex justify-content-between align-items-center">
              <span class="text-muted">Diupdate Oleh</span>
              <span class="fw-medium">{{ $model->UPDATED_USER }}</span>
            </div>
          @endif
        </div>
      </div>
    </div>
  </div>

  <!-- Laporan Aktivitas -->
  <div class="row">
    <div class="col-12">
      <div class="card shadow-none border">
        <div class="card-header d-flex align-items-center bg-light">
          <h5 class="card-title mb-0 flex-grow-1"><i class="fal fa-file-alt me-1"></i> Laporan Aktivitas</h5>
          <span class="badge bg-soft-primary fs-12">{{ !empty($details) ? count($details) : 0 }} laporan</span>
        </div>
        <div class="card-body">
          @if (!empty($details) && count($details) > 0)
            <div class="table-responsive">
              <table class="table table-hover align-middle mb-0 w-100" id="tbLaporan">
                <thead class="table-light">
                  <tr>
                    <th style="width:40px;">#</th>
                    <th>Tanggal</th>
                    <th>Nama KRK</th>
                    <th>Hasil</th>
                    <th>Ransum</th>
                    <th>Catatan</th>
                    <th>Foto Before</th>
                    <th>Foto After</th>
                  </tr>
                </thead>
                <tbody>
                  @php $no = 1; @endphp
                  @foreach ($details as $d)
                    <tr>
                      <td>{{ $no++ }}</td>
                      <td class="text-nowrap">{{ !empty($d->CREATED_AT) ? date('d M Y H:i', strtotime($d->CREATED_AT)) : '-' }}</td>
                      <td>{{ !empty($d->NAMA_KRK) ? $d->NAMA_KRK : '-' }}</td>
                      <td>
                        @if (!empty($d->HASIL) || (isset($d->HASIL) && $d->HASIL === 0))
                          {{ ribuan($d->HASIL) }} {{ $model->NAMA_SATUAN ?? '' }}
                        @else
                          <span class="text-muted">-</span>
                        @endif
                      </td>
                      <td>
                        @if (!empty($d->RANSUM))
                          {{ ribuan($d->RANSUM) }} Bungkus
                        @else
                          <span class="text-muted">-</span>
                        @endif
                      </td>
                      <td>
                        @if (!empty($d->CATATAN))
                          <span class="d-inline-block" style="max-width:300px; white-space:normal; word-break:break-word;">{{ $d->CATATAN }}</span>
                        @else
                          <span class="text-muted">-</span>
                        @endif
                      </td>
                      <td>
                        @if (!empty($d->FOTO_KEGIATAN_BEFORE))
                          <a href="{{ base_url($d->FOTO_KEGIATAN_BEFORE) }}" target="_blank">
                            <img src="{{ base_url($d->FOTO_KEGIATAN_BEFORE) }}" alt="Before" class="img-thumbnail" style="width:60px;height:60px;object-fit:cover;">
                          </a>
                        @else
                          <span class="text-muted">-</span>
                        @endif
                      </td>
                      <td>
                        @if (!empty($d->FOTO_KEGIATAN_AFTER))
                          <a href="{{ base_url($d->FOTO_KEGIATAN_AFTER) }}" target="_blank">
                            <img src="{{ base_url($d->FOTO_KEGIATAN_AFTER) }}" alt="After" class="img-thumbnail" style="width:60px;height:60px;object-fit:cover;">
                          </a>
                        @else
                          <span class="text-muted">-</span>
                        @endif
                      </td>
                    </tr>
                  @endforeach
                </tbody>
              </table>
            </div>
          @else
            <div class="text-center py-4 d-flex flex-column align-items-center justify-content-center">
              <i class="fal fa-inbox fa-3x text-muted mb-3"></i>
              <p class="text-muted mb-0">Belum ada laporan aktivitas</p>
            </div>
          @endif
        </div>
      </div>
    </div>
  </div>

  <script>
    $(document).ready(function() {
      @if (!empty($details) && count($details) > 0)
        if ($.fn.DataTable.isDataTable('#tbLaporan')) {
          $('#tbLaporan').DataTable().destroy();
        }
        $('#tbLaporan').DataTable({
          responsive: true,
          autoWidth: true,
          paging: true,
          searching: true,
          ordering: true,
          order: [
            [1, 'desc']
          ],
          pageLength: 10,
          columnDefs: [{
            targets: 0,
            responsivePriority: 1,
            width: '40px'
          }, {
            targets: 1,
            responsivePriority: 1
          }, {
            targets: 2,
            responsivePriority: 2
          }, {
            targets: 3,
            responsivePriority: 3
          }, {
            targets: 4,
            responsivePriority: 4
          }, {
            targets: 5,
            responsivePriority: 5
          }, {
            targets: 6,
            responsivePriority: 5
          }],
          language: {
            emptyTable: "Tidak ada data laporan",
            search: "Cari:",
            lengthMenu: "Tampilkan _MENU_ data",
            info: "Menampilkan _START_ - _END_ dari _TOTAL_ data",
            paginate: {
              first: "Awal",
              last: "Akhir",
              next: "Selanjutnya",
              previous: "Sebelumnya"
            }
          },
          drawCallback: function() {
            $('[data-bs-toggle="tooltip"]').tooltip();
          }
        });
      @endif
    });
  </script>

@else
  <div class="text-center py-5 d-flex flex-column align-items-center justify-content-center">
    <i class="fal fa-exclamation-triangle fa-3x text-warning mb-3"></i>
    <h5 class="text-danger mb-0">Work Order tidak ditemukan</h5>
  </div>
@endif
