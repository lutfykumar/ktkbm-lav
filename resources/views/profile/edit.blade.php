@extends('layouts.app')

@section('title', $title ?? 'Profile Edit')
@section('title_header', 'Profile Edit')
@section('title_parent', $title_parent ?? 'Account')

@php
$avatarUrl = $user->avatar && $user->avatar !== 'default.jpg'
    ? asset('filemanager/avatar/' . $user->avatar)
    : asset('assets/images/users/user-dummy-img.jpg');
@endphp

@section('content')
<div class="position-relative mx-n4 mt-n4">
    <div class="profile-wid-bg profile-setting-img" style="height: 220px; background: linear-gradient(135deg, #405189 0%, #0ab39c 100%);">
    </div>
</div>

<div class="row">
    <div class="col-12">
        <div class="card mt-xxl-n5" style="margin-top: -100px;">
            <div class="card-header">
                <ul class="nav nav-tabs-custom rounded card-header-tabs border-bottom-0" role="tablist">
                    <li class="nav-item">
                        <a class="nav-link active" data-bs-toggle="tab" href="#personalDetails" role="tab">
                            <i class="fal fa-user me-1"></i> Personal Details
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" data-bs-toggle="tab" href="#changePassword" role="tab">
                            <i class="fal fa-key me-1"></i> Change Password
                        </a>
                    </li>
                </ul>
            </div>
            <div class="card-body p-4">
                <div class="tab-content">
                    <!-- Personal Details -->
                    <div class="tab-pane active" id="personalDetails" role="tabpanel">
                        <form id="form_info" method="post" enctype="multipart/form-data">
                            @csrf
                            <input type="hidden" name="model_id" value="{{ $user->id }}">

                            <div class="row">
                                <div class="col-lg-12">
                                    <div class="text-center mb-4">
                                        <div class="profile-user position-relative d-inline-block mx-auto">
                                            <img id="avatar-preview" src="{{ $avatarUrl }}"
                                                class="rounded-circle img-thumbnail shadow"
                                                alt="user-profile-image"
                                                style="height: 8rem; width: 8rem; object-fit: cover;"
                                                onerror="this.src='{{ asset('assets/images/users/user-dummy-img.jpg') }}'">
                                            <div class="position-absolute bottom-0 end-0">
                                                <input id="avatar-input" type="file" name="avatar" class="d-none" accept="image/*">
                                                <label for="avatar-input" class="btn btn-sm btn-light rounded-circle shadow p-2" style="cursor: pointer;">
                                                    <i class="fal fa-camera"></i>
                                                </label>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <div class="col-lg-6">
                                    <div class="mb-3">
                                        <label for="name" class="form-label required">Nama Lengkap</label>
                                        <input type="text" class="form-control" id="name" name="name"
                                            value="{{ $user->username }}" required>
                                    </div>
                                </div>

                                <div class="col-lg-6">
                                    <div class="mb-3">
                                        <label for="phone" class="form-label">No. Telepon / WhatsApp</label>
                                        <input type="text" class="form-control" id="phone" name="phone"
                                            value="{{ $user->phone }}" placeholder="08xxxxxxxxxx">
                                    </div>
                                </div>

                                <div class="col-lg-6">
                                    <div class="mb-3">
                                        <label class="form-label">Email (Read Only)</label>
                                        <input type="email" class="form-control bg-light" value="{{ $user->email }}" disabled>
                                    </div>
                                </div>

                                <div class="col-lg-6">
                                    <div class="mb-3">
                                        <label class="form-label">Role</label>
                                        <input type="text" class="form-control bg-light" value="{{ ucfirst($user->group->name ?? 'User') }}" disabled>
                                    </div>
                                </div>

                                <div class="col-12 mt-3">
                                    <div class="d-flex justify-content-end gap-2">
                                        <a href="{{ url('profile') }}" class="btn btn-light waves-effect">Kembali</a>
                                        <button type="submit" class="btn btn-primary waves-effect" id="btn_save_info">
                                            <i class="fal fa-save me-1"></i> Simpan Perubahan
                                        </button>
                                    </div>
                                </div>
                            </div>
                        </form>
                    </div>

                    <!-- Change Password -->
                    <div class="tab-pane" id="changePassword" role="tabpanel">
                        <form id="form_password" method="post">
                            @csrf
                            <input type="hidden" name="model_id" value="{{ $user->id }}">

                            <div class="row g-2">
                                <div class="col-lg-4">
                                    <div>
                                        <label for="oldpasswordInput" class="form-label required">Password Lama*</label>
                                        <input type="password" class="form-control" id="oldpasswordInput"
                                            name="oldpasswordInput" placeholder="Password lama..." required>
                                    </div>
                                </div>
                                <div class="col-lg-4">
                                    <div>
                                        <label for="newpasswordInput" class="form-label required">Password Baru*</label>
                                        <input type="password" class="form-control" id="newpasswordInput"
                                            name="newpasswordInput" placeholder="Password baru..." required>
                                    </div>
                                </div>
                                <div class="col-lg-4">
                                    <div>
                                        <label for="confirmpasswordInput" class="form-label required">Konfirmasi Password Baru*</label>
                                        <input type="password" class="form-control" id="confirmpasswordInput"
                                            name="confirmpasswordInput" placeholder="Ulangi password baru..." required>
                                    </div>
                                </div>

                                <div class="col-12 mt-4">
                                    <div class="d-flex justify-content-end gap-2">
                                        <a href="{{ url('profile') }}" class="btn btn-light waves-effect">Kembali</a>
                                        <button type="submit" class="btn btn-success waves-effect" id="btn_save_password">
                                            <i class="fal fa-key me-1"></i> Update Password
                                        </button>
                                    </div>
                                </div>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
    $(document).ready(function() {
        // Preview avatar before upload
        $('#avatar-input').on('change', function(e) {
            if (e.target.files && e.target.files[0]) {
                let reader = new FileReader();
                reader.onload = function(evt) {
                    $('#avatar-preview').attr('src', evt.target.result);
                }
                reader.readAsDataURL(e.target.files[0]);
            }
        });

        // Submit personal info
        $('#form_info').on('submit', function(e) {
            e.preventDefault();
            let formData = new FormData(this);
            let btn = $('#btn_save_info');
            btn.prop('disabled', true).html('<i class="fal fa-spinner fa-spin me-1"></i> Menyimpan...');

            $.ajax({
                url: "{{ url('profile/store_info') }}",
                type: 'POST',
                data: formData,
                processData: false,
                contentType: false,
                success: function(res) {
                    btn.prop('disabled', false).html('<i class="fal fa-save me-1"></i> Simpan Perubahan');
                    if (res.status) {
                        Swal.fire('Berhasil!', res.msg, 'success');
                    } else {
                        Swal.fire('Gagal!', res.msg, 'error');
                    }
                },
                error: function(xhr) {
                    btn.prop('disabled', false).html('<i class="fal fa-save me-1"></i> Simpan Perubahan');
                    let err = xhr.responseJSON?.message || 'Terjadi kesalahan sistem';
                    Swal.fire('Error!', err, 'error');
                }
            });
        });

        // Submit password
        $('#form_password').on('submit', function(e) {
            e.preventDefault();
            let btn = $('#btn_save_password');
            btn.prop('disabled', true).html('<i class="fal fa-spinner fa-spin me-1"></i> Mengupdate...');

            $.ajax({
                url: "{{ url('profile/store_password') }}",
                type: 'POST',
                data: $(this).serialize(),
                success: function(res) {
                    btn.prop('disabled', false).html('<i class="fal fa-key me-1"></i> Update Password');
                    if (res.status) {
                        Swal.fire('Berhasil!', res.msg, 'success');
                        $('#form_password')[0].reset();
                    } else {
                        Swal.fire('Gagal!', res.msg, 'error');
                    }
                },
                error: function(xhr) {
                    btn.prop('disabled', false).html('<i class="fal fa-key me-1"></i> Update Password');
                    let err = xhr.responseJSON?.message || 'Terjadi kesalahan sistem';
                    Swal.fire('Error!', err, 'error');
                }
            });
        });
    });
</script>
@endpush
