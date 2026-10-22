@extends('layouts.app')

@section('title', $title ?? 'Aktifitas Lapangan')
@section('title_header', $title_header ?? 'Aktifitas Lapangan')
@section('title_parent', $title_parent ?? 'Tasks')

@push('styles')
<style>
    .project-card .card {
        transition: transform 0.15s ease, box-shadow 0.15s ease;
    }
    .project-card .card:hover {
        transform: translateY(-2px);
        box-shadow: 0 4px 12px rgba(0, 0, 0, 0.15);
    }
</style>
@endpush

@section('content')
<div class="row">
    <div class="col-12">
        <div class="card">
            <div class="card-body">
                <h5>Filter</h5>
                <div class="row">
                    <div class="col-sm-12 col-lg-4">
                        <div class="fm row mb-2">
                            <label for="filter_tgl" class="col-sm-5 col-form-label required">Tanggal :</label>
                            <div class="col-lg-7">
                                <input type="date" class="form-control" id="filter_tgl" name="filter_tgl"
                                    value="{{ date('Y-m-d') }}" required>
                            </div>
                        </div>
                    </div>
                    <div class="col-sm-12 col-lg-4">
                        <div class="fm row mb-2">
                            <label for="filter_pelabuhan" class="col-sm-5 col-form-label">Pilih Pelabuhan :</label>
                            <div class="col-lg-7">
                                <select class="form-control select2 filter_pelabuhan" id="filter_pelabuhan" name="filter_pelabuhan" style="width: 100%;">
                                    <option value="">Semua Pelabuhan</option>
                                </select>
                            </div>
                        </div>
                    </div>
                    <div class="col-sm-12 col-lg-4">
                        <div class="d-flex align-items-center pt-2 mb-2">
                            <button type="button" class="btn btn-success btn-sm w-sm waves-effect waves-light me-3" id="btn_search">
                                <i class="fal fa-search me-1"></i> Cari
                            </button>
                            <button type="button" class="btn btn-danger btn-sm w-sm waves-effect waves-light" id="btn_reset">
                                <i class="fal fa-sync me-1"></i> Reset
                            </button>
                        </div>
                    </div>
                </div>
                <hr>
                <div id="result_area">
                    <!-- Dynamic content loaded via AJAX -->
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
    const route_link = "{{ url('activity') }}/";
    const resultArea = $('#result_area');

    $(document).ready(function() {
        // Load pelabuhan dropdown
        $.post("{{ url('ajax/get_all_pelabuhan') }}", {
            _token: $('meta[name="csrf-token"]').attr('content')
        }, function(data) {
            let options = '<option value="">Semua Pelabuhan</option>';
            if (Array.isArray(data)) {
                data.forEach(function(item) {
                    options += `<option value="${item.ID_PELABUHAN || item.id}">${item.NAMA || item.text}</option>`;
                });
            }
            $('#filter_pelabuhan').html(options);
        });

        // Auto load on init
        load_data($('#filter_tgl').val(), '');
    });

    $("#btn_search").click(function(e) {
        e.preventDefault();
        let filter_tgl = $('#filter_tgl').val() || '';
        let filter_pelabuhan = $('#filter_pelabuhan').val() || '';
        load_data(filter_tgl, filter_pelabuhan);
    });

    $("#btn_reset").click(function(e) {
        e.preventDefault();
        $('#filter_tgl').val("{{ date('Y-m-d') }}");
        $('#filter_pelabuhan').val('').trigger('change');
        load_data("{{ date('Y-m-d') }}", '');
    });

    function load_data(tgl = '', pelabuhan = '') {
        resultArea.html('<div class="text-center py-4"><i class="fal fa-spinner fa-spin fa-2x text-muted"></i><p class="mt-2 text-muted">Memuat data...</p></div>');
        $.ajax({
            type: "POST",
            url: route_link + 'result',
            data: {
                _token: $('meta[name="csrf-token"]').attr('content'),
                tgl: tgl,
                pelabuhan: pelabuhan
            },
            success: function(res) {
                resultArea.html(res);
            },
            error: function() {
                resultArea.html('<div class="text-center py-4"><i class="fal fa-exclamation-triangle fa-2x text-danger"></i><p class="mt-2 text-danger">Gagal memuat data. Silakan coba lagi.</p></div>');
            }
        });
    }

    // Button "Input Laporan" -> Open form
    $(document).on('click', '.btn-input-laporan', function() {
        let wo_id = $(this).data('wo-id');
        load_form(wo_id);
    });

    // Button "Detail" -> Open report list
    $(document).on('click', '.btn-view-detail', function() {
        let wo_id = $(this).data('wo-id');
        load_detail(wo_id);
    });

    function load_form(wo_id) {
        resultArea.html('<div class="text-center py-4"><i class="fal fa-spinner fa-spin fa-2x text-muted"></i><p class="mt-2 text-muted">Memuat form...</p></div>');
        $.ajax({
            type: "GET",
            url: route_link + 'form_detail/' + wo_id,
            success: function(res) {
                resultArea.html(res);
            },
            error: function() {
                resultArea.html('<div class="alert alert-danger">Gagal memuat form, coba lagi</div>');
            }
        });
    }

    function load_detail(wo_id) {
        resultArea.html('<div class="text-center py-4"><i class="fal fa-spinner fa-spin fa-2x text-muted"></i><p class="mt-2 text-muted">Memuat data...</p></div>');
        $.ajax({
            type: "GET",
            url: route_link + 'list_detail/' + wo_id,
            success: function(res) {
                resultArea.html(res);
            },
            error: function() {
                resultArea.html('<div class="alert alert-danger">Gagal memuat data, coba lagi</div>');
            }
        });
    }

    $(document).on('click', '.btn-back-to-cards', function() {
        let filter_tgl = $('#filter_tgl').val() || '';
        let filter_pelabuhan = $('#filter_pelabuhan').val() || '';
        load_data(filter_tgl, filter_pelabuhan);
    });

    $(document).on('change', '.toggle-wo-done', function() {
        let toggle = $(this);
        let woId = toggle.data('wo-id');
        let woNo = toggle.data('wo-no');

        Swal.fire({
            title: 'Tandai Selesai?',
            text: `Tandai Work Order ${woNo} sebagai SELESAI?`,
            icon: 'question',
            showCancelButton: true,
            confirmButtonText: 'Ya, Tandai Selesai!',
            cancelButtonText: 'Batal'
        }).then((result) => {
            if (result.isConfirmed) {
                toggle.prop('disabled', true);
                $.post(route_link + 'update_status', {
                    _token: $('meta[name="csrf-token"]').attr('content'),
                    id: woId,
                    status: 'done'
                }, function(res) {
                    if (res.status) {
                        Swal.fire('Berhasil!', res.msg, 'success');
                        let filter_tgl = $('#filter_tgl').val() || '';
                        let filter_pelabuhan = $('#filter_pelabuhan').val() || '';
                        load_data(filter_tgl, filter_pelabuhan);
                    } else {
                        Swal.fire('Gagal!', res.msg, 'error');
                        toggle.prop('checked', false);
                    }
                    toggle.prop('disabled', false);
                });
            } else {
                toggle.prop('checked', false);
            }
        });
    });
</script>
@endpush
