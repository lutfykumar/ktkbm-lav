@extends('layouts.app')

@section('title', $title ?? 'User Activity')
@section('title_header', 'User Activity')
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
                                <p class="text-uppercase fw-medium text-muted mb-0">Total Activities</p>
                                <h4 class="fs-22 fw-semibold ff-secondary mb-0">{{ number_format($statistics['total_activities']) }}</h4>
                            </div>
                            <div class="avatar-sm flex-shrink-0">
                                <span class="avatar-title bg-primary-subtle text-primary rounded-circle fs-3">
                                    <i class="fal fa-list"></i>
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
                                <p class="text-uppercase fw-medium text-muted mb-0">Today's Activities</p>
                                <h4 class="fs-22 fw-semibold ff-secondary mb-0">{{ number_format($statistics['today_activities']) }}</h4>
                            </div>
                            <div class="avatar-sm flex-shrink-0">
                                <span class="avatar-title bg-success-subtle text-success rounded-circle fs-3">
                                    <i class="fal fa-calendar-check"></i>
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
                                <p class="text-uppercase fw-medium text-muted mb-0">Failed Logins</p>
                                <h4 class="fs-22 fw-semibold ff-secondary mb-0">{{ number_format($statistics['failed_logins']) }}</h4>
                            </div>
                            <div class="avatar-sm flex-shrink-0">
                                <span class="avatar-title bg-danger-subtle text-danger rounded-circle fs-3">
                                    <i class="fal fa-exclamation-triangle"></i>
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
                                <p class="text-uppercase fw-medium text-muted mb-0">Active Users</p>
                                <h4 class="fs-22 fw-semibold ff-secondary mb-0">{{ number_format($statistics['unique_users']) }}</h4>
                            </div>
                            <div class="avatar-sm flex-shrink-0">
                                <span class="avatar-title bg-info-subtle text-info rounded-circle fs-3">
                                    <i class="fal fa-users"></i>
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
                <h5 class="card-title mb-3"><i class="fal fa-filter me-1"></i> Filter Activity Logs</h5>
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
                        <label class="form-label">User</label>
                        <select class="form-select" id="filter_user">
                            <option value="">Semua User</option>
                            @foreach($users as $u)
                                <option value="{{ $u->id }}">{{ $u->username }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-3 mb-2">
                        <label class="form-label">Tipe Aktifitas</label>
                        <select class="form-select" id="filter_type">
                            <option value="">Semua Tipe</option>
                            @foreach($activity_types as $type)
                                <option value="{{ $type }}">{{ ucfirst($type) }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-3 mb-2">
                        <label class="form-label">Kategori</label>
                        <select class="form-select" id="filter_category">
                            <option value="">Semua Kategori</option>
                            @foreach($activity_categories as $cat)
                                <option value="{{ $cat }}">{{ ucfirst($cat) }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-3 mb-2">
                        <label class="form-label">Status</label>
                        <select class="form-select" id="filter_status">
                            <option value="">Semua Status</option>
                            <option value="success">Success</option>
                            <option value="failed">Failed</option>
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
                                <th>User</th>
                                <th>Deskripsi</th>
                                <th>Tipe</th>
                                <th>Kategori</th>
                                <th>Status</th>
                                <th>IP Address</th>
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
                <h5 class="modal-title">Detail User Activity</h5>
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
    const route_link = "{{ url('logs/aktifitas') }}/";

    $(document).ready(function() {
        'use strict';

        let table = $('#table_logs').DataTable({
            processing: true,
            serverSide: false,
            ajax: {
                url: route_link + 'table',
                type: 'POST',
                data: function(d) {
                    d._token = $('meta[name="csrf-token"]').attr('content');
                    d.date_start = $('#date_start').val();
                    d.date_end = $('#date_end').val();
                    d.user_id = $('#filter_user').val();
                    d.activity_type = $('#filter_type').val();
                    d.activity_category = $('#filter_category').val();
                    d.activity_status = $('#filter_status').val();
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
            $('#filter_user').val('');
            $('#filter_type').val('');
            $('#filter_category').val('');
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
