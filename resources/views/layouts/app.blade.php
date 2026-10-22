<!DOCTYPE html>
<html lang="id"
  data-layout="{{ $theme_config['data-layout'] ?? 'vertical' }}"
  data-topbar="{{ $theme_config['data-topbar'] ?? 'light' }}"
  data-sidebar="{{ $theme_config['data-sidebar'] ?? 'dark' }}"
  data-sidebar-size="{{ $theme_config['data-sidebar-size'] ?? 'lg' }}"
  data-sidebar-image="{{ $theme_config['data-sidebar-image'] ?? 'none' }}"
  data-preloader="{{ $theme_config['data-preloader'] ?? 'enable' }}"
  data-theme="{{ $theme_config['data-theme'] ?? 'default' }}"
  data-theme-colors="{{ $theme_config['data-theme-colors'] ?? 'default' }}"
  data-bs-theme="{{ $theme_config['data-bs-theme'] ?? 'light' }}"
  data-layout-width="{{ $theme_config['data-layout-width'] ?? 'fluid' }}"
  data-layout-position="{{ $theme_config['data-layout-position'] ?? 'fixed' }}"
  data-layout-style="{{ $theme_config['data-layout-style'] ?? 'default' }}"
  data-body-image="{{ $theme_config['data-body-image'] ?? 'none' }}"
  data-sidebar-visibility="{{ $theme_config['data-sidebar-visibility'] ?? 'show' }}">

<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <meta name="csrf-token" content="{{ csrf_token() }}">
  <title>@yield('title', 'Dashboard') - {{ $site_name }}</title>
  <meta name="description" content="{{ $meta_description }}">
  <meta name="keywords" content="{{ $meta_keywords }}">
  <meta name="author" content="{{ $meta_author }}">
  <meta name="robots" content="{{ $meta_robots_content }}">

  <link rel="apple-touch-icon" sizes="180x180" href="{{ asset('assets/favicon/apple-touch-icon.png') }}">
  <link rel="icon" type="image/png" sizes="32x32" href="{{ asset('assets/favicon/favicon-32x32.png') }}">
  <link rel="icon" type="image/png" sizes="16x16" href="{{ asset('assets/favicon/favicon-16x16.png') }}">
  <link rel="manifest" href="{{ asset('assets/favicon/site.webmanifest') }}">

  <link rel="stylesheet" href="https://fonts.googleapis.com/css?family=Source+Sans+Pro:300,400,400i,700&display=fallback">
  <script src="{{ asset('assets/js/layout.js') }}"></script>
  <link rel="stylesheet" type="text/css" href="{{ asset('assets/css/bootstrap.min.css') }}">
  <link rel="stylesheet" type="text/css" href="{{ asset('assets/libs/fontawesome/css/all.min.css') }}">
  <link rel="stylesheet" type="text/css" href="{{ asset('assets/libs/datatables/DataTables-1.12.1/css/jquery.dataTables.min.css') }}">
  <link rel="stylesheet" type="text/css" href="{{ asset('assets/libs/datatables/DataTables-1.12.1/css/dataTables.bootstrap5.min.css') }}">
  <link rel="stylesheet" type="text/css" href="{{ asset('assets/libs/datatables/Select-1.4.0/css/select.bootstrap5.min.css') }}">
  <link rel="stylesheet" type="text/css" href="{{ asset('assets/libs/datatables/Responsive-2.3.0/css/responsive.bootstrap5.min.css') }}">
  <link rel="stylesheet" type="text/css" href="{{ asset('assets/libs/datatables/RowGroup-1.2.0/css/rowGroup.bootstrap5.min.css') }}">
  <link rel="stylesheet" type="text/css" href="{{ asset('assets/libs/datatables/Buttons-2.2.3/css/buttons.bootstrap5.min.css') }}">
  <link rel="stylesheet" type="text/css" href="{{ asset('assets/libs/select2/css/select2.min.css') }}">
  <link rel="stylesheet" type="text/css" href="{{ asset('assets/libs/date-picker/css/bootstrap-datepicker.min.css') }}">
  <link rel="stylesheet" type="text/css" href="{{ asset('assets/libs/daterangepicker/daterangepicker.css') }}">
  <link rel="stylesheet" type="text/css" href="{{ asset('assets/css/app.min.css') }}">
  <link rel="stylesheet" type="text/css" href="{{ asset('assets/css/custom.min.css') }}">
  <link rel="stylesheet" type="text/css" href="{{ asset('assets/libs/filepond/filepond.min.css') }}" />
  <link rel="stylesheet" type="text/css" href="{{ asset('assets/libs/filepond-plugin-image-preview/filepond-plugin-image-preview.min.css') }}">

  <style>
    .footer {
      height: 40px;
      padding: 10px calc(1.5rem / 2);
    }
    [pointer-events="bounding-box"] {
      display: none;
    }
    .navbar-menu .navbar-nav .nav-link[data-bs-toggle=collapse]:after {
      font-family: "Font Awesome 5 Pro";
      font-weight: 400;
      content: "\f053";
      font-size: 0.7rem;
    }
    .navbar-menu .navbar-nav .nav-link[data-bs-toggle=collapse][aria-expanded=true]:after {
      -webkit-transform: rotate(-90deg);
      transform: rotate(-90deg);
    }
    .navbar-menu .navbar-nav .nav-link .badge {
      margin-right: 4px;
    }
    .breadcrumb-item+.breadcrumb-item::before {
      font-family: "Font Awesome 5 Pro";
      font-weight: 400;
      content: "\f054";
      font-size: 0.5rem;
    }
    .kms-form .required::after {
      content: " *";
      color: red;
    }
    div.dataTables_wrapper div.dataTables_length select {
      width: 50px;
    }
    div.dataTables_wrapper .form-select {
      background-position: right 2px center;
    }
    div.dataTables_wrapper .dataTables_paginate .paginate_button {
      padding: 0;
      margin-left: 0;
    }
    div.dataTables_wrapper div.dataTables_filter input {
      width: 200px;
      background-color: #f3f6f9;
      border-color: #e2e2e2;
      color: #333;
    }
    table.dataTable tbody th,
    table.dataTable tbody td {
      vertical-align: middle;
      border-color: #d9d7d7;
    }
    .text_dark {
      color: var(--vz-heading-color);
      font-family: Montserrat, sans-serif;
    }
    .app-search span.mdi-magnify {
      position: absolute;
      z-index: 10;
      font-size: 18px;
      line-height: 38px;
      left: 13px;
      top: 0;
      color: #878a99;
    }
    .app-search span.mdi-close-circle {
      position: absolute;
      z-index: 10;
      font-size: 18px;
      line-height: 38px;
      left: 13px;
      top: 0;
      color: #878a99;
      cursor: pointer;
    }
    .app-search {
      width: 320px;
    }
    .spanner {
      position: absolute;
      top: 50%;
      left: 0;
      background: #2a2a2a55;
      width: 100%;
      display: block;
      text-align: center;
      height: 100%;
      color: #FFF;
      transform: translateY(-50%);
      z-index: 9999999;
      visibility: hidden;
      font-size: 20px;
    }
    .overlay {
      position: fixed;
      width: 100%;
      height: 100%;
      background: rgba(0, 0, 0, 0.5);
      visibility: hidden;
    }
    .loader,
    .loader:before,
    .loader:after {
      border-radius: 50%;
      width: 2.5em;
      height: 2.5em;
      -webkit-animation-fill-mode: both;
      animation-fill-mode: both;
      -webkit-animation: load7 1.8s infinite ease-in-out;
      animation: load7 1.8s infinite ease-in-out;
    }
    .loader {
      color: #ffffff;
      font-size: 10px;
      margin: 100px auto;
      position: relative;
      text-indent: -9999em;
      -webkit-transform: translateZ(0);
      -ms-transform: translateZ(0);
      transform: translateZ(0);
      -webkit-animation-delay: -0.16s;
      animation-delay: -0.16s;
    }
    .loader:before,
    .loader:after {
      content: '';
      position: absolute;
      top: 0;
    }
    .loader:before {
      left: -3.5em;
      -webkit-animation-delay: -0.32s;
      animation-delay: -0.32s;
    }
    .loader:after {
      left: 3.5em;
    }
    @keyframes load7 {
      0%, 80%, 100% { box-shadow: 0 2.5em 0 -1.3em; }
      40% { box-shadow: 0 2.5em 0 0; }
    }
    .spanner.show, .overlay.show { opacity: 1; visibility: visible; }
  </style>

  <script>
    const ip_server = '{{ url("/") }}/';
    const type_auth = parseInt('{{ $type_auth }}');
    const csrf_name = '{{ $csrf_name }}';
    const loading_card = '<div class="text-center py-5" id="loading_card"> <div class="spinner-border border-5" role="status"> <span class="visually-hidden">Loading...</span> </div> <div class="fs-11 mt-2">Loading...</div> </div>';
  </script>

  <script src="{{ asset('assets/libs/jquery/jquery-3.6.0.min.js') }}"></script>
  <script src="{{ asset('assets/libs/bootstrap/js/bootstrap.bundle.min.js') }}"></script>
  <script src="{{ asset('assets/libs/fontawesome/js/all.min.js') }}"></script>
  <script src="{{ asset('assets/libs/jquery-validation/jquery.validate.min.js') }}"></script>
  <script src="{{ asset('assets/libs/jquery-validation/additional-methods.min.js') }}"></script>
  <script src="{{ asset('assets/libs/jquery-validation/localization/messages_id.min.js') }}"></script>
  <script src="{{ asset('assets/libs/datatables/DataTables-1.12.1/js/jquery.dataTables.min.js') }}"></script>
  <script src="{{ asset('assets/libs/datatables/DataTables-1.12.1/js/dataTables.bootstrap5.min.js') }}"></script>
  <script src="{{ asset('assets/libs/datatables/Select-1.4.0/js/dataTables.select.min.js') }}"></script>
  <script src="{{ asset('assets/libs/datatables/Select-1.4.0/js/select.bootstrap5.min.js') }}"></script>
  <script src="{{ asset('assets/libs/datatables/Responsive-2.3.0/js/dataTables.responsive.min.js') }}"></script>
  <script src="{{ asset('assets/libs/datatables/Responsive-2.3.0/js/responsive.bootstrap5.min.js') }}"></script>
  <script src="{{ asset('assets/libs/datatables/RowGroup-1.2.0/js/rowGroup.bootstrap5.min.js') }}"></script>
  <script src="{{ asset('assets/libs/datatables/RowGroup-1.2.0/js/dataTables.rowGroup.min.js') }}"></script>
  <script src="{{ asset('assets/libs/datatables/Buttons-2.2.3/js/dataTables.buttons.min.js') }}"></script>
  <script src="{{ asset('assets/libs/datatables/Buttons-2.2.3/js/buttons.bootstrap5.min.js') }}"></script>
  <script src="{{ asset('assets/libs/node-waves/waves.min.js') }}"></script>
  <script src="{{ asset('assets/libs/select2/js/select2.full.min.js') }}"></script>
  <script src="{{ asset('assets/libs/moment/moment.min.js') }}"></script>
  <script src="{{ asset('assets/libs/date-picker/js/bootstrap-datepicker.min.js') }}"></script>
  <script src="{{ asset('assets/libs/daterangepicker/daterangepicker.js') }}"></script>
  <script src="{{ asset('assets/libs/cleave.js/cleave.min.js') }}"></script>
  <script src="{{ asset('assets/libs/sweetalert2/sweetalert2.all.min.js') }}"></script>
  <script src="{{ asset('assets/js/plugins.js') }}"></script>
  <script src="{{ asset('assets/js/custom.js') }}"></script>

  <script>
    // Setup jQuery AJAX to automatically include Laravel CSRF token header
    $.ajaxSetup({
      headers: {
        'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
      }
    });
  </script>

  @stack('styles')
</head>

<body id="body_page">
  <div class="overlay"></div>
  <div class="spanner">
    <div class="loader"></div>
    <p>Mohon tunggu...</p>
  </div>
  <input type="hidden" id="{{ $csrf_name }}" value="{{ csrf_token() }}">

  <div id="layout-wrapper">
    @include('layouts.menu')

    <div class="main-content">
      <div class="page-content">
        <div class="container-fluid">
          <div class="row">
            <div class="col-12">
              <div class="page-title-box d-sm-flex align-items-center justify-content-between">
                <h4 class="mb-sm-0">@yield('title', 'Dashboard')</h4>

                <div class="page-title-right">
                  <ol class="breadcrumb m-0">
                    @hasSection('title_header')
                      <li class="breadcrumb-item"><a href="javascript: void(0);">@yield('title_header')</a></li>
                    @endif
                    @hasSection('title_parent')
                      <li class="breadcrumb-item"><a href="javascript: void(0);">@yield('title_parent')</a></li>
                    @endif
                    @hasSection('title_parent_action')
                      <li class="breadcrumb-item"><a href="javascript: void(0);">@yield('title_parent_action')</a></li>
                    @endif
                    <li class="breadcrumb-item active">@yield('title', 'Dashboard')</li>
                  </ol>
                </div>
              </div>
            </div>
          </div>

          @yield('content')
        </div>
      </div>

      <footer class="footer">
        <div class="container-fluid">
          <div class="row">
            <div class="col-sm-6">
              <script>document.write(new Date().getFullYear())</script> © PT.PBS - Gresik
            </div>
            <div class="col-sm-6">
              <div class="text-sm-end d-none d-sm-block">
                Crafted with by Bangchan
              </div>
            </div>
          </div>
        </div>
      </footer>
    </div>
  </div>

  <!-- Standard Modals from CI3 template -->
  <div class="modal flip" id="modal_crud">
    <div class="modal-dialog modal-fullscreen modal-dialog-scrollable">
      <div class="modal-content">
        <div class="modal-header border-bottom">
          <h5 class="modal-title" id="m_title">Defaults</h5>
          <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
        </div>
        <div class="modal-body" id="m_body"></div>
      </div>
    </div>
  </div>

  <div class="modal flip" id="modal_center_lg">
    <div class="modal-dialog modal-dialog-centered modal-lg">
      <div class="modal-content">
        <div class="modal-header border-bottom p-3 bg-light">
          <h5 class="modal-title" id="m_title">Defaults</h5>
          <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
        </div>
        <div class="modal-body" id="m_body"></div>
      </div>
    </div>
  </div>

  <div class="modal flip" id="modal_master_md">
    <div class="modal-dialog modal-dialog-centered modal-md">
      <div class="modal-content">
        <div class="modal-header border-bottom p-3">
          <h5 class="modal-title" id="m_title">Defaults</h5>
          <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
        </div>
        <div class="modal-body" id="m_body"></div>
      </div>
    </div>
  </div>

  <button onclick="topFunction()" class="btn btn-danger btn-icon" id="back-to-top">
    <i class="fal fa-chevron-double-up"></i>
  </button>

  <script src="{{ asset('assets/libs/simplebar/simplebar.min.js') }}"></script>
  <script src="{{ asset('assets/js/app.js') }}"></script>
  <script>
    const body_page = $('#body_page');

    $(document).ready(function() {
      $('#search-options').keyup(function() {
        let value = $(this).val();
        let area_menu = $('#area_search_menu');
        let loading = '<div class="dropdown-item notify-item"> <div class="text-center my-3"> <i class="fad fa-circle-notch fa-spin fa-2x"></i> </div> </div>';
        let notFound = '<div class="dropdown-item notify-item"> <div class="text-center my-3"> <p class="text-danger">Data tidak ditemukan</p> </div> </div>';
        area_menu.html('');
        if (value !== '') {
          area_menu.html(loading);
          $.ajax({
            url: ip_server + 'ajax/search_menu',
            type: "POST",
            dataType: 'json',
            data: {
              keywords: value
            },
            success: function(res) {
              if (res.status == true) {
                if (res.menu !== '') {
                  area_menu.html(res.menu);
                } else {
                  area_menu.html(notFound);
                }
              } else {
                area_menu.html(notFound);
              }
            }
          });
        }
      });
    });
  </script>

  @stack('scripts')
</body>
</html>
