@extends('layouts.app')

@section('title', $title ?? 'Email Templates')
@section('title_header', 'Email Templates')
@section('title_parent', $title_parent ?? 'Master')

@section('content')
<div class="row">
    <div class="col-12">
        <!-- Header with Add Button -->
        <div class="row mb-3">
            <div class="col-12">
                <div class="d-flex justify-content-between align-items-center">
                    <h4 class="mb-0">Email Templates</h4>
                    @if(!empty($can_create))
                        <button type="button" class="btn btn-primary waves-effect" id="btn_create">
                            <i class="fal fa-plus me-1"></i> Create Template
                        </button>
                    @endif
                </div>
            </div>
        </div>

        <!-- Filter Panel -->
        <div class="row mb-3">
            <div class="col-12">
                <div class="card">
                    <div class="card-body">
                        <div class="d-flex justify-content-between align-items-center mb-3">
                            <h5 class="card-title mb-0">
                                <i class="fal fa-filter me-1"></i> Filters
                            </h5>
                            <button type="button" class="btn btn-sm btn-soft-secondary" id="btn_toggle_filter">
                                <i class="fal fa-chevron-up" id="icon_toggle"></i> Toggle
                            </button>
                        </div>

                        <div id="filter_panel">
                            <div class="row">
                                <div class="col-md-3">
                                    <div class="mb-3">
                                        <label class="form-label">Category</label>
                                        <select class="form-select" id="filter_category">
                                            <option value="">All Categories</option>
                                            @foreach($categories as $cat)
                                                <option value="{{ $cat }}">{{ ucfirst($cat) }}</option>
                                            @endforeach
                                        </select>
                                    </div>
                                </div>

                                <div class="col-md-3">
                                    <div class="mb-3">
                                        <label class="form-label">Status</label>
                                        <select class="form-select" id="filter_status">
                                            <option value="">All Status</option>
                                            <option value="1">Active</option>
                                            <option value="0">Inactive</option>
                                        </select>
                                    </div>
                                </div>

                                <div class="col-md-6">
                                    <div class="mb-3">
                                        <label class="form-label">&nbsp;</label>
                                        <div class="d-flex gap-2">
                                            <button type="button" class="btn btn-primary waves-effect" id="btn_apply_filter">
                                                <i class="fal fa-filter me-1"></i> Apply Filter
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
                    </div>
                </div>
            </div>
        </div>

        <!-- DataTable -->
        <div class="row">
            <div class="col-12">
                <div class="card">
                    <div class="card-body">
                        <div class="table-responsive">
                            <table id="table_templates" class="table table-bordered table-striped dt-responsive nowrap w-100">
                                <thead>
                                    <tr>
                                        <th width="50px">No</th>
                                        <th>Template Name</th>
                                        <th>Slug</th>
                                        <th>Subject</th>
                                        <th width="100px">Category</th>
                                        <th width="80px">Status</th>
                                        <th width="180px">Actions</th>
                                    </tr>
                                </thead>
                                <tbody></tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Modals -->
<div class="modal fade" id="modal_form" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-xl modal-dialog-scrollable">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="modal_form_title">Template Form</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body" id="modal_form_content">
                <!-- Content loaded via AJAX -->
            </div>
        </div>
    </div>
</div>

<div class="modal fade" id="modal_preview" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-xl modal-dialog-scrollable">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Template Preview</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body" id="modal_preview_content">
                <!-- Content loaded via AJAX -->
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
    const route_link = "{{ url('master/email') }}/";
    let csrf_token = "{{ csrf_token() }}";

    $(document).ready(function() {
        'use strict';

        let table = $('#table_templates').DataTable({
            processing: true,
            serverSide: false,
            ajax: {
                url: route_link + 'table',
                type: 'POST',
                data: function(d) {
                    d._token = $('meta[name="csrf-token"]').attr('content') || csrf_token;
                    d.category = $('#filter_category').val();
                    d.is_active = $('#filter_status').val();
                },
                dataSrc: function(json) {
                    if (json.csrf_token_hash) {
                        csrf_token = json.csrf_token_hash;
                        $('meta[name="csrf-token"]').attr('content', csrf_token);
                    }
                    return json.dataTables || [];
                }
            },
            columns: [
                { data: 0, width: '50px' },
                { data: 1 },
                { data: 2 },
                { data: 3 },
                { data: 4, width: '100px' },
                { data: 5, width: '80px' },
                { data: 6, width: '180px', orderable: false }
            ]
        });

        $('#btn_apply_filter').on('click', function() {
            table.ajax.reload();
        });

        $('#btn_reset_filter').on('click', function() {
            $('#filter_category').val('');
            $('#filter_status').val('');
            table.ajax.reload();
        });

        $('#btn_refresh').on('click', function() {
            table.ajax.reload();
        });

        $('#btn_toggle_filter').on('click', function() {
            $('#filter_panel').slideToggle();
            $('#icon_toggle').toggleClass('fa-chevron-up fa-chevron-down');
        });

        // Create
        $('#btn_create').on('click', function() {
            $('#modal_form_title').text('Create Email Template');
            $.get(route_link + 'create', function(html) {
                $('#modal_form_content').html(html);
                $('#modal_form').modal('show');
            });
        });

        // Edit
        $(document).on('click', '.btn-edit', function() {
            let id = $(this).data('id');
            $('#modal_form_title').text('Edit Email Template');
            $.get(route_link + 'edit', { id: id }, function(html) {
                $('#modal_form_content').html(html);
                $('#modal_form').modal('show');
            });
        });

        // Preview
        $(document).on('click', '.btn-preview', function() {
            let id = $(this).data('id');
            $.get(route_link + 'preview', { id: id }, function(html) {
                $('#modal_preview_content').html(html);
                $('#modal_preview').modal('show');
            });
        });

        // Duplicate
        $(document).on('click', '.btn-duplicate', function() {
            let id = $(this).data('id');
            Swal.fire({
                title: 'Duplikasi Template?',
                text: 'Template baru akan dibuat sebagai salinan',
                icon: 'question',
                showCancelButton: true,
                confirmButtonColor: '#3085d6',
                cancelButtonColor: '#d33',
                confirmButtonText: 'Ya, Duplikasi!'
            }).then((result) => {
                if (result.isConfirmed) {
                    $.post(route_link + 'duplicate', {
                        _token: $('meta[name="csrf-token"]').attr('content') || csrf_token,
                        id: id
                    }, function(res) {
                        if (res.status) {
                            Swal.fire('Berhasil!', res.msg, 'success');
                            table.ajax.reload();
                        } else {
                            Swal.fire('Gagal!', res.msg, 'error');
                        }
                    });
                }
            });
        });

        // Delete
        $(document).on('click', '.btn-delete', function() {
            let id = $(this).data('id');
            Swal.fire({
                title: 'Apakah anda yakin?',
                text: 'Data yang dihapus tidak dapat dikembalikan!',
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#d33',
                cancelButtonColor: '#3085d6',
                confirmButtonText: 'Ya, Hapus!'
            }).then((result) => {
                if (result.isConfirmed) {
                    $.post(route_link + 'delete', {
                        _token: $('meta[name="csrf-token"]').attr('content') || csrf_token,
                        id: id
                    }, function(res) {
                        if (res.status) {
                            Swal.fire('Terhapus!', res.msg, 'success');
                            table.ajax.reload();
                        } else {
                            Swal.fire('Gagal!', res.msg, 'error');
                        }
                    });
                }
            });
        });
    });
</script>
@endpush
