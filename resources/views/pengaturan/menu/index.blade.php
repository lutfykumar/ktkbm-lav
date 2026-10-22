@extends('layouts.app')

@section('title', $title ?? 'Menu')
@section('title_header', 'Menu')
@section('title_parent', $title_parent ?? 'Pengaturan')

@push('styles')
<style>
    .nested-sortable-handle .handle {
        width: 15px;
        height: 15px;
        left: 15px;
        top: 13px;
        cursor: grab;
        margin-right: 8px;
    }

    .nested-data {
        font-size: 15px;
        margin-bottom: 4px;
        border-radius: 4px !important;
    }
</style>
@endpush

@section('content')
@php
$headersHtml = '';
foreach ($sidebar as $v) {
    if (!empty($v['nama'])) {
        $menus = '';
        if (!empty($v['menus'])) {
            foreach ($v['menus'] as $mm) {
                if (!empty($mm['menu_nama'])) {
                    $menu_nama = $mm['menu_nama'];
                    $sub_menus = '';
                    if (!empty($mm['sub_menus'])) {
                        foreach ($mm['sub_menus'] as $ss) {
                            if (!empty($ss['sub_menu_nama'])) {
                                $sub_menu_nama = $ss['sub_menu_nama'];
                                $sub_menus .= '<div class="list-group-item nested-data" data-id="' . $ss['sub_menu_id'] . '"><i class="fal fa-arrows align-bottom handle"></i>' . e($sub_menu_nama) . '</div>';
                            }
                        }
                    }
                    if (!empty($sub_menus)) {
                        $menus .= '<div class="list-group-item nested-data" data-id="' . $mm['menu_id'] . '"><i class="fal fa-arrows align-bottom handle"></i>' . e($menu_nama) . '<div class="list-group nested-list nested-sortable-handle mt-2">' . $sub_menus . '</div></div>';
                    } else {
                        $menus .= '<div class="list-group-item nested-data" data-id="' . $mm['menu_id'] . '"><i class="fal fa-arrows align-bottom handle"></i>' . e($menu_nama) . '</div>';
                    }
                }
            }
        }
        $nama = $v['nama'];
        if (!empty($menus)) {
            $headersHtml .= '<div class="list-group-item nested-data" data-id="' . $v['id'] . '"><i class="fal fa-arrows align-bottom handle"></i><strong>' . e($nama) . '</strong><div class="list-group nested-list nested-sortable-handle mt-2">' . $menus . '</div></div>';
        } else {
            $headersHtml .= '<div class="list-group-item nested-data" data-id="' . $v['id'] . '"><i class="fal fa-arrows align-bottom handle"></i><strong>' . e($nama) . '</strong></div>';
        }
    }
}
@endphp

<div class="row">
    <div class="col-12">
        <div class="card">
            <div class="card-header">
                <h4 class="card-title mb-0">Atur Menu Sidebar</h4>
            </div>

            <div class="card-body">
                <p class="text-muted">Drag & Drop menu sesuai urutan yang Anda inginkan.</p>
                <form id="form_update" class="kms-form needs-validation" novalidate>
                    <input type="hidden" name="{{ $csrf_name }}" id="{{ $csrf_name }}" value="{{ $csrf_token }}">
                    <div class="list-group col nested-list nested-sortable-handle">
                        {!! !empty($headersHtml) ? $headersHtml : '' !!}
                    </div>
                </form>
            </div>
            <div class="card-footer">
                <div class="d-flex justify-content-center">
                    <button type="button" class="btn btn-success w-sm waves-effect waves-light btn-update">Simpan</button>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script src="{{ asset('assets/libs/sortablejs/Sortable.min.js') }}"></script>
<script>
    const route_link = ip_server + 'pengaturan/menu/';

    var nestedSortablesHandles = [].slice.call(document.querySelectorAll('.nested-sortable-handle'));
    if (nestedSortablesHandles) {
        nestedSortablesHandles.forEach(function(nestedSortHandle) {
            new Sortable(nestedSortHandle, {
                handle: '.handle',
                group: 'nested',
                animation: 150,
                fallbackOnBody: true,
                swapThreshold: 0.65
            });
        });
    }

    $(document).ready(function() {
        $('.btn-update').click(function(e) {
            e.preventDefault();
            var btn = $(this);
            var action_url = route_link + 'update';
            var action_form = $('#form_update');
            var nested = $('.nested-data');
            var menus = [];
            var no = 1;
            nested.each(function() {
                var menu_id = $(this).data('id');
                var data_menu = {
                    'urut': no,
                    'menu_id': menu_id
                };
                menus.push(data_menu);
                no++;
            });

            var dataForm = new FormData(action_form[0]);
            dataForm.append('menus', JSON.stringify(menus));
            btn.addClass('disabled').text('Mohon tunggu...');

            $.ajax({
                url: action_url,
                type: "POST",
                dataType: "JSON",
                data: dataForm,
                contentType: false,
                cache: false,
                processData: false,
                success: function(res) {
                    if (res.csrf_token_name && res.csrf_token_hash) {
                        $('input[name="' + res.csrf_token_name + '"]').val(res.csrf_token_hash);
                    }
                    if (res.status == true) {
                        toast_success(res.msg);
                    } else {
                        toast_error(res.msg);
                    }
                    btn.removeClass('disabled').text('Simpan');
                },
                error: function() {
                    btn.removeClass('disabled').text('Simpan');
                    toast_error('Terjadi kesalahan koneksi');
                }
            });
        });
    });
</script>
@endpush
