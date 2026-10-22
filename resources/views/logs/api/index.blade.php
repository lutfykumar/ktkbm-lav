@extends('layouts.app')

@section('title', $title ?? 'API Request Logs')
@section('title_header', 'API Request Logs')
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
                                <p class="text-uppercase fw-medium text-muted mb-0">Total Requests</p>
                                <h4 class="fs-22 fw-semibold ff-secondary mb-0">{{ number_format($statistics['total_requests']) }}</h4>
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
                                <p class="text-uppercase fw-medium text-muted mb-0">Today's Requests</p>
                                <h4 class="fs-22 fw-semibold ff-secondary mb-0">{{ number_format($statistics['today_requests']) }}</h4>
                            </div>
                            <div class="avatar-sm flex-shrink-0">
                                <span class="avatar-title bg-success-subtle text-success rounded-circle fs-3">
                                    <i class="fal fa-calendar-alt"></i>
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
                                <p class="text-uppercase fw-medium text-muted mb-0">Avg Response Time</p>
                                <h4 class="fs-22 fw-semibold ff-secondary mb-0">{{ $statistics['avg_response_time'] }} ms</h4>
                            </div>
                            <div class="avatar-sm flex-shrink-0">
                                <span class="avatar-title bg-info-subtle text-info rounded-circle fs-3">
                                    <i class="fal fa-stopwatch"></i>
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
                                <p class="text-uppercase fw-medium text-muted mb-0">Errors (>=400)</p>
                                <h4 class="fs-22 fw-semibold ff-secondary mb-0">{{ number_format($statistics['error_requests']) }}</h4>
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
        </div>

        <!-- Filter Panel -->
        <div class="card mb-3">
            <div class="card-body">
                <h5 class="card-title mb-3"><i class="fal fa-filter me-1"></i> Filter API Request Logs</h5>
                <div class="row">
                    <div class="col-md-3 mb-2">
                        <label class="form-label">Tanggal Mulai</label>
                        <input type="date" class="form-control" id="date_start">
                    </div>
                    <div class="col-md-3 mb-2">
                        <label class="form-label">Tanggal Akhir</label>
                        <input type="date" class="form-control" id="date_end">
                    </div>
                    <div class="col-md-3 mb-2">
                        <label class="form-label">HTTP Method</label>
                        <select class="form-select" id="filter_method">
                            <option value="">Semua Method</option>
                            <option value="GET">GET</option>
                            <option value="POST">POST</option>
                            <option value="PUT">PUT</option>
                            <option value="PATCH">PATCH</option>
                            <option value="DELETE">DELETE</option>
                        </select>
                    </div>
                    <div class="col-md-3 mb-2">
                        <label class="form-label">Status Code</label>
                        <select class="form-select" id="filter_status">
                            <option value="">Semua Status</option>
                            <option value="200">200 OK</option>
                            <option value="201">201 Created</option>
                            <option value="400">400 Bad Request</option>
                            <option value="401">401 Unauthorized</option>
                            <option value="403">403 Forbidden</option>
                            <option value="404">404 Not Found</option>
                            <option value="500">500 Server Error</option>
                        </select>
                    </div>
                    <div class="col-md-6 mb-2">
                        <label class="form-label">&nbsp;</label>
                        <div class="d-flex gap-2">
                            <button type="button" class="btn btn-primary waves-effect" id="btn_apply_filter">
                                <i class="fal fa-filter me-1"></i> Filter
                            </button>
                            <button type="button" class="btn btn-secondary waves-effect" id="btn_reset_filter">
                                <i class="fal fa-undo me-1"></i> Reset
                            </button>
                            <button type="button" class="btn btn-info waves-effect" id="btn_refresh">
                                <i class="fal fa-sync me-1"></i> Refresh
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- DataTable -->
        <div class="card">
            <div class="card-body">
                <div class="table-responsive">
                    <table id="table_logs" class="table table-bordered table-striped dt-responsive nowrap w-100">
                        <thead>
                            <tr>
                                <th width="50px">ID</th>
                                <th>Waktu</th>
                                <th>Method</th>
                                <th>Endpoint</th>
                                <th>Status</th>
                                <th>Response Time</th>
                                <th>IP Address</th>
                                <th>User</th>
                                <th width="70px">Aksi</th>
                            </tr>
                        </thead>
                        <tbody></tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Modal Detail -->
<div class="modal fade" id="modal_detail" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-scrollable">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Detail API Request</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body" id="modal_detail_content">
                <!-- Loaded via AJAX -->
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
    const route_link = "{{ url('logs/api') }}/";

    $(document).ready(function() {
        'use strict';

        let table = $('#table_logs').DataTable({
            processing: true,
            serverSide: false,
            ajax: {
                url: route_link + 'get_data',
                type: 'POST',
                data: function(d) {
                    d._token = $('meta[name="csrf-token"]').attr('content');
                    d.date_start = $('#date_start').val();
                    d.date_end = $('#date_end').val();
                    d.method = $('#filter_method').val();
                    d.status_code = $('#filter_status').val();
                },
                dataSrc: function(json) {
                    return json.data || [];
                }
            },
            columns: [
                { data: 0, width: '50px' },
                { data: 1 },
                { data: 2 },
                { data: 3 },
                { data: 4 },
                { data: 5 },
                { data: 6 },
                { data: 7 },
                { data: 8, orderable: false, width: '70px' }
            ],
            order: [[0, 'desc']]
        });

        $('#btn_apply_filter').on('click', function() {
            table.ajax.reload();
        });

        $('#btn_reset_filter').on('click', function() {
            $('#date_start').val('');
            $('#date_end').val('');
            $('#filter_method').val('');
            $('#filter_status').val('');
            table.ajax.reload();
        });

        $('#btn_refresh').on('click', function() {
            table.ajax.reload();
        });

        $(document).on('click', '.btn-detail', function() {
            let id = $(this).data('id');
            $.post(route_link + 'detail', {
                _token: $('meta[name="csrf-token"]').attr('content'),
                id: id
            }, function(html) {
                $('#modal_detail_content').html(html);
                $('#modal_detail').modal('show');
            });
        });
    });
</script>
@endpush
