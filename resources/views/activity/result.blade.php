<div class="fw-medium fs-14 mb-2">Daftar Work Order Aktifitas Lapangan <span id="tgl_result" class="fw-bold">{{ $tgl }}</span></div>
<ul class="nav nav-tabs nav-border-top nav-border-top-primary" role="tablist">
    <li class="nav-item" role="presentation">
        <a class="nav-link active" data-bs-toggle="tab" href="#nav-border-top-home" role="tab" aria-selected="true">
            Belum di laporkan <span class="badge bg-warning ms-2">{{ number_format(count($open)) }}</span>
        </a>
    </li>
    <li class="nav-item" role="presentation">
        <a class="nav-link" data-bs-toggle="tab" href="#nav-border-top-progress" role="tab" aria-selected="false" tabindex="-1">
            Progress laporkan <span class="badge bg-primary ms-2">{{ number_format(count($progress)) }}</span>
        </a>
    </li>
    <li class="nav-item" role="presentation">
        <a class="nav-link" data-bs-toggle="tab" href="#nav-border-top-done" role="tab" aria-selected="false" tabindex="-1">
            Laporan Selesai <span class="badge bg-success ms-2">{{ number_format(count($done)) }}</span>
        </a>
    </li>
</ul>

<div class="tab-content text-muted p-3 bg-light rounded-bottom border border-top-0">
    <!-- Open (Belum dilaporkan) -->
    <div class="tab-pane active show" id="nav-border-top-home" role="tabpanel">
        <div class="row">
            @if(empty($open))
                <div class="col-12">
                    <div class="alert alert-secondary">Tidak ada work order yang perlu di laporkan</div>
                </div>
            @else
                @foreach($open as $row)
                    <div class="col-xxl-3 col-sm-6 project-card mb-3">
                        <div class="card card-height-100 shadow-sm">
                            <div class="card-body">
                                <div class="d-flex flex-column h-100">
                                    <div class="d-flex mb-2">
                                        <div class="flex-grow-1">
                                            <p class="text-muted mb-0 fs-12">Post: <span class="fw-bold">{{ date('d M Y H:i', strtotime($row->CREATED_AT)) }}</span></p>
                                        </div>
                                        <div class="flex-shrink-0">
                                            <span class="badge bg-{{ $row->total > 0 ? 'success' : 'secondary' }}">
                                                <i class="fal fa-file-alt me-1"></i>{{ number_format($row->total) }} Laporan
                                            </span>
                                        </div>
                                    </div>
                                    <div class="flex-grow-1 mb-3">
                                        <h5 class="mb-1 fs-15"><span class="text-body">{{ $row->NO_WORK_ORDER }}</span></h5>
                                        <p class="text-muted mb-0 fs-13">
                                            <strong>{{ $row->NAMA_KAPAL }}</strong><br>
                                            ({{ $row->NAMA_CLIENT }}) {{ $row->NAMA_DERMAGA }}<br>
                                            {{ $row->NAMA_AKTIFITAS }} ({{ $row->NAMA_SATUAN }})
                                            @if(!empty($row->NAMA_ALAT))
                                                <br><small class="text-info">VIA {{ $row->NAMA_ALAT }}</small>
                                            @endif
                                        </p>
                                    </div>
                                    <div class="d-flex gap-2 mt-auto">
                                        @if($row->total > 0)
                                            <button type="button" class="btn btn-sm btn-outline-info flex-fill btn-view-detail" data-wo-id="{{ $row->ID_WORK_ORDER }}">
                                                <i class="fal fa-eye me-1"></i>Detail
                                            </button>
                                        @endif
                                        <button type="button" class="btn btn-sm btn-primary flex-fill btn-input-laporan" data-wo-id="{{ $row->ID_WORK_ORDER }}">
                                            <i class="fal fa-plus me-1"></i>Input Laporan
                                        </button>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                @endforeach
            @endif
        </div>
    </div>

    <!-- Progress -->
    <div class="tab-pane" id="nav-border-top-progress" role="tabpanel">
        <div class="row">
            @if(empty($progress))
                <div class="col-12">
                    <div class="alert alert-secondary">Tidak ada work order dalam progress</div>
                </div>
            @else
                @foreach($progress as $row)
                    <div class="col-xxl-3 col-sm-6 project-card mb-3">
                        <div class="card card-height-100 shadow-sm">
                            <div class="card-body">
                                <div class="d-flex flex-column h-100">
                                    <div class="d-flex mb-2">
                                        <div class="flex-grow-1">
                                            <p class="text-muted mb-0 fs-12">Post: <span class="fw-bold">{{ date('d M Y H:i', strtotime($row->CREATED_AT)) }}</span></p>
                                        </div>
                                        <div class="flex-shrink-0 d-flex align-items-center gap-2">
                                            <span class="badge bg-{{ $row->total > 0 ? 'success' : 'secondary' }}">
                                                <i class="fal fa-file-alt me-1"></i>{{ number_format($row->total) }} Laporan
                                            </span>
                                            <div class="form-check form-switch form-switch-success mb-0" data-bs-toggle="tooltip" title="Tandai Selesai">
                                                <input class="form-check-input toggle-wo-done" type="checkbox" role="switch"
                                                    data-wo-id="{{ $row->ID_WORK_ORDER }}"
                                                    data-wo-no="{{ $row->NO_WORK_ORDER }}">
                                            </div>
                                        </div>
                                    </div>
                                    <div class="flex-grow-1 mb-3">
                                        <h5 class="mb-1 fs-15"><span class="text-body">{{ $row->NO_WORK_ORDER }}</span></h5>
                                        <p class="text-muted mb-0 fs-13">
                                            <strong>{{ $row->NAMA_KAPAL }}</strong><br>
                                            ({{ $row->NAMA_CLIENT }}) {{ $row->NAMA_DERMAGA }}<br>
                                            @if($row->total > 0)
                                                <span class="badge bg-soft-info text-info">{{ number_format($row->total) }} GG</span>,
                                            @endif
                                            {{ $row->NAMA_AKTIFITAS }} ({{ $row->NAMA_SATUAN }})
                                            @if(!empty($row->NAMA_ALAT))
                                                <br><small class="text-info">VIA {{ $row->NAMA_ALAT }}</small>
                                            @endif
                                        </p>
                                    </div>
                                    <div class="d-flex gap-2 mt-auto">
                                        <button type="button" class="btn btn-sm btn-outline-info flex-fill btn-view-detail" data-wo-id="{{ $row->ID_WORK_ORDER }}">
                                            <i class="fal fa-eye me-1"></i>Detail
                                        </button>
                                        <button type="button" class="btn btn-sm btn-primary flex-fill btn-input-laporan" data-wo-id="{{ $row->ID_WORK_ORDER }}">
                                            <i class="fal fa-plus me-1"></i>Input Laporan
                                        </button>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                @endforeach
            @endif
        </div>
    </div>

    <!-- Done -->
    <div class="tab-pane" id="nav-border-top-done" role="tabpanel">
        <div class="row">
            @if(empty($done))
                <div class="col-12">
                    <div class="alert alert-secondary">Tidak ada work order yang telah selesai</div>
                </div>
            @else
                @foreach($done as $row)
                    <div class="col-xxl-3 col-sm-6 project-card mb-3">
                        <div class="card card-height-100 shadow-sm">
                            <div class="card-body">
                                <div class="d-flex flex-column h-100">
                                    <div class="d-flex mb-2">
                                        <div class="flex-grow-1">
                                            <p class="text-muted mb-0 fs-12">Post: <span class="fw-bold">{{ date('d M Y H:i', strtotime($row->CREATED_AT)) }}</span></p>
                                        </div>
                                        <div class="flex-shrink-0">
                                            <span class="badge bg-{{ $row->total > 0 ? 'success' : 'secondary' }}">
                                                <i class="fal fa-file-alt me-1"></i>{{ number_format($row->total) }} Laporan
                                            </span>
                                        </div>
                                    </div>
                                    <div class="flex-grow-1 mb-3">
                                        <h5 class="mb-1 fs-15"><span class="text-body">{{ $row->NO_WORK_ORDER }}</span></h5>
                                        <p class="text-muted mb-0 fs-13">
                                            <strong>{{ $row->NAMA_KAPAL }}</strong><br>
                                            ({{ $row->NAMA_CLIENT }}) {{ $row->NAMA_DERMAGA }}<br>
                                            {{ $row->NAMA_AKTIFITAS }} ({{ $row->NAMA_SATUAN }})
                                            @if(!empty($row->NAMA_ALAT))
                                                <br><small class="text-info">VIA {{ $row->NAMA_ALAT }}</small>
                                            @endif
                                        </p>
                                    </div>
                                    <div class="d-flex mt-auto">
                                        <button type="button" class="btn btn-sm btn-outline-info flex-fill btn-view-detail" data-wo-id="{{ $row->ID_WORK_ORDER }}">
                                            <i class="fal fa-eye me-1"></i>Detail
                                        </button>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                @endforeach
            @endif
        </div>
    </div>
</div>
