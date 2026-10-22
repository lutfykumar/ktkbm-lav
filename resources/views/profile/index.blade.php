@extends('layouts.app')

@section('title', $title ?? 'Profile')
@section('title_header', 'Profile')
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

<div class="pt-4 mb-4 mb-lg-3 pb-lg-4" style="margin-top: -120px;">
    <div class="row g-4 align-items-end">
        <div class="col-auto">
            <div class="avatar-lg position-relative">
                <img src="{{ $avatarUrl }}" alt="user-img" class="img-thumbnail rounded-circle shadow"
                    style="width: 6.5rem; height: 6.5rem; object-fit: cover; object-position: center;"
                    onerror="this.src='{{ asset('assets/images/users/user-dummy-img.jpg') }}'" />
            </div>
        </div>
        <div class="col">
            <div class="p-2">
                <h3 class="text-white mb-1">{{ $user->username }}</h3>
                <p class="text-white-75 mb-0">{{ ucfirst($user->group->name ?? 'User') }}</p>
            </div>
        </div>
    </div>
</div>

<div class="row">
    <div class="col-lg-12">
        <div class="d-flex">
            <ul class="nav nav-pills animation-nav profile-nav gap-2 gap-lg-3 flex-grow-1" role="tablist">
                <li class="nav-item">
                    <a class="nav-link fs-14 active" data-bs-toggle="tab" href="#overview-tab" role="tab">
                        <i class="fal fa-user me-1"></i> Profile Info
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link fs-14" data-bs-toggle="tab" href="#profile-work-orders" role="tab">
                        <i class="fal fa-clipboard-list me-1"></i> Work Orders
                    </a>
                </li>
            </ul>
            <div class="flex-shrink-0">
                <a href="{{ url('profile/edit') }}" class="btn btn-success waves-effect">
                    <i class="fal fa-edit me-1"></i> Edit Profile
                </a>
            </div>
        </div>

        <div class="tab-content pt-4 text-muted">
            <!-- Overview Tab -->
            <div class="tab-pane fade show active" id="overview-tab" role="tabpanel">
                <div class="row">
                    <div class="col-xxl-4 col-md-5">
                        <div class="card">
                            <div class="card-body">
                                <h5 class="card-title mb-3">Informasi Akun</h5>
                                <div class="table-responsive">
                                    <table class="table table-borderless mb-0">
                                        <tbody>
                                            <tr>
                                                <th class="ps-0" scope="row" width="30%">Nama</th>
                                                <td class="text-muted">: {{ $user->username }}</td>
                                            </tr>
                                            <tr>
                                                <th class="ps-0" scope="row">Kontak</th>
                                                <td class="text-muted">: {{ $user->phone ?: '-' }}</td>
                                            </tr>
                                            <tr>
                                                <th class="ps-0" scope="row">E-mail</th>
                                                <td class="text-muted">: {{ $user->email }}</td>
                                            </tr>
                                            <tr>
                                                <th class="ps-0" scope="row">Role</th>
                                                <td class="text-muted">: <span class="badge bg-primary">{{ ucfirst($user->group->name ?? 'User') }}</span></td>
                                            </tr>
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="col-xxl-8 col-md-7">
                        <div class="card">
                            <div class="card-header align-items-center d-flex">
                                <h4 class="card-title mb-0 me-2">Aktifitas Terakhir</h4>
                            </div>
                            <div class="card-body">
                                @if(count($activities) > 0)
                                    <div class="profile-timeline">
                                        <div class="accordion accordion-flush">
                                            @foreach($activities as $act)
                                                <div class="accordion-item border-0 mb-2">
                                                    <div class="d-flex align-items-start">
                                                        <div class="flex-shrink-0 me-3">
                                                            <span class="avatar-xs d-block text-center mt-1">
                                                                <i class="fad fa-dot-circle fs-5 text-primary"></i>
                                                            </span>
                                                        </div>
                                                        <div class="flex-grow-1">
                                                            <h6 class="fs-14 mb-1">{{ $act->action ?? $act->description ?? 'Activity' }}</h6>
                                                            <small class="text-muted">
                                                                <i class="fal fa-clock me-1"></i>
                                                                {{ date('d M Y H:i', strtotime($act->created_at)) }}
                                                            </small>
                                                        </div>
                                                    </div>
                                                </div>
                                            @endforeach
                                        </div>
                                    </div>
                                @else
                                    <div class="text-center py-4 text-muted">
                                        Belum ada riwayat aktifitas
                                    </div>
                                @endif
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Work Orders Tab -->
            <div class="tab-pane fade" id="profile-work-orders" role="tabpanel">
                <div class="card">
                    <div class="card-body">
                        @if(count($workorders) > 0)
                            <div class="table-responsive">
                                <table class="table table-bordered table-striped dt-responsive nowrap w-100">
                                    <thead>
                                        <tr>
                                            <th>No Work Order</th>
                                            <th>Tanggal</th>
                                            <th>Kapal</th>
                                            <th>Dermaga</th>
                                            <th>Status</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @foreach($workorders as $wo)
                                            <tr>
                                                <td><strong>{{ $wo->NO_WORK_ORDER }}</strong></td>
                                                <td>{{ $wo->CREATED_AT ? date('Y-m-d', strtotime($wo->CREATED_AT)) : '-' }}</td>
                                                <td>{{ $wo->KAPAL_NAMA ?: '-' }}</td>
                                                <td>{{ $wo->DERMAGA_NAMA ?: '-' }}</td>
                                                <td>
                                                    @if($wo->STATUS === 'done')
                                                        <span class="badge bg-success">Done</span>
                                                    @elseif($wo->STATUS === 'progress')
                                                        <span class="badge bg-warning">Progress</span>
                                                    @else
                                                        <span class="badge bg-secondary">Open</span>
                                                    @endif
                                                </td>
                                            </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>
                        @else
                            <div class="text-center py-4 text-muted">
                                Belum ada riwayat work order terkait akun ini
                            </div>
                        @endif
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
