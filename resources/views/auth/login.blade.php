<!DOCTYPE html>
<html lang="id" data-layout="vertical" data-topbar="light" data-sidebar="dark" data-sidebar-size="sm-hover">

<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <meta name="csrf-token" content="{{ csrf_token() }}">
  <title>Login - {{ $site_name }}</title>
  <meta name="description" content="{{ $meta_description }}">
  <meta name="keywords" content="{{ $meta_keywords }}">
  <meta name="author" content="{{ $meta_author }}">
  <meta name="robots" content="{{ $meta_robots_content }}">

  <link rel="apple-touch-icon" sizes="200x200" href="{{ asset('assets/favicon/apple-touch-icon.png') }}">
  <link rel="icon" type="image/png" sizes="32x32" href="{{ asset('assets/favicon/favicon-32x32.png') }}">
  <link rel="icon" type="image/png" sizes="16x16" href="{{ asset('assets/favicon/favicon-16x16.png') }}">
  <link rel="manifest" href="{{ asset('assets/favicon/site.webmanifest') }}">
  <link rel="mask-icon" href="{{ asset('assets/favicon/safari-pinned-tab.svg') }}" color="#5bbad5">
  <meta name="msapplication-TileColor" content="#da532c">
  <meta name="theme-color" content="#ffffff">

  <link rel="stylesheet" href="https://fonts.googleapis.com/css?family=Source+Sans+Pro:300,400,400i,700&display=fallback">
  <script src="{{ asset('assets/js/layout.js') }}"></script>
  <link rel="stylesheet" type="text/css" href="{{ asset('assets/css/bootstrap.min.css') }}">
  <link rel="stylesheet" type="text/css" href="{{ asset('assets/css/icons.min.css') }}">
  <link rel="stylesheet" type="text/css" href="{{ asset('assets/css/app.min.css') }}">
  <link rel="stylesheet" type="text/css" href="{{ asset('assets/css/custom.min.css') }}">
  <link rel="stylesheet" type="text/css" href="{{ asset('assets/css/auth.css') }}">
  <link rel="stylesheet" type="text/css" href="{{ asset('assets/libs/sweetalert2/sweetalert2.min.css') }}">

  <script src="{{ asset('assets/libs/jquery/jquery-3.6.0.min.js') }}"></script>
  <script src="{{ asset('assets/libs/bootstrap/js/bootstrap.bundle.min.js') }}"></script>
  <script src="{{ asset('assets/libs/jquery-validation/jquery.validate.min.js') }}"></script>
  <script src="{{ asset('assets/libs/jquery-validation/additional-methods.min.js') }}"></script>
  <script src="{{ asset('assets/libs/jquery-validation/localization/messages_id.min.js') }}"></script>
  <script src="{{ asset('assets/libs/sweetalert2/sweetalert2.all.min.js') }}"></script>
  <script src="{{ asset('assets/js/plugins.js') }}"></script>
  <script src="{{ asset('assets/js/custom.js') }}"></script>

  <script>
    const ip_server = '{{ url("/") }}/';
  </script>
</head>

<body>
  <div class="auth-page-wrapper auth-bg-cover py-5 d-flex justify-content-center align-items-center min-vh-100">
    <div class="bg-overlay"></div>
    <div class="auth-page-content overflow-hidden pt-lg-5">
      <div class="container">
        <div class="row">
          <div class="col-lg-12">
            <div class="card overflow-hidden">
              <div class="row g-0">
                <div class="col-lg-6">
                  <div class="p-lg-5 p-4 auth-one-bg h-100">
                    <div class="bg-overlay"></div>
                    <div class="position-relative h-100 d-flex flex-column">
                      <div class="mb-4">
                        <a href="{{ url('/') }}" class="d-block">
                          <h3 class="text-light">{{ $site_name }}</h3>
                        </a>
                      </div>
                      <div class="mt-auto">
                        <div class="mb-3">
                          <i class="ri-double-quotes-l display-4 text-success"></i>
                        </div>

                        <div id="qoutescarouselIndicators" class="carousel slide" data-bs-ride="carousel">
                          <div class="carousel-indicators">
                            <button type="button" data-bs-target="#qoutescarouselIndicators" data-bs-slide-to="0" class="active" aria-current="true" aria-label="Slide 1"></button>
                            <button type="button" data-bs-target="#qoutescarouselIndicators" data-bs-slide-to="1" aria-label="Slide 2"></button>
                            <button type="button" data-bs-target="#qoutescarouselIndicators" data-bs-slide-to="2" aria-label="Slide 3"></button>
                          </div>
                          <div class="carousel-inner text-center text-white-50 pb-5">
                            <div class="carousel-item active">
                              <p class="fs-15 fst-italic">" Sistem manajemen bongkar muat yang terintegrasi, memudahkan pencatatan dan pelacakan setiap aktivitas operasional di lapangan. "</p>
                            </div>
                            <div class="carousel-item">
                              <p class="fs-15 fst-italic">" Kelola work order, laporan harian, dan kinerja tenaga kerja bongkar muat secara efisien dalam satu platform. "</p>
                            </div>
                            <div class="carousel-item">
                              <p class="fs-15 fst-italic">" Monitoring real-time aktivitas bongkar muat untuk meningkatkan produktivitas dan akuntabilitas kerja. "</p>
                            </div>
                          </div>
                        </div>
                      </div>
                    </div>
                  </div>
                </div>

                <div class="col-lg-6">
                  <div class="p-lg-5 p-4">
                    <div>
                      <h5 class="text-primary">Selamat Datang !!</h5>
                      <p class="text-muted">Silahkan Log In untuk akses ke dashboard.</p>
                    </div>

                    @if(session('error'))
                      <div class="alert alert-danger alert-dismissible fade show" role="alert">
                        {{ session('error') }}
                        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                      </div>
                    @endif

                    <div class="mt-4">
                      <form class="login-form needs-validation" action="{{ route('login') }}" method="POST" novalidate>
                        @csrf
                        <div class="mb-3">
                          <label for="identity" class="form-label required">Alamat Email</label>
                          <input type="text" class="form-control @error('identity') is-invalid @enderror" id="identity" name="identity" placeholder="Enter Alamat Email" value="{{ old('identity') }}" autofocus required>
                          @error('identity')
                            <div class="invalid-feedback">{{ $message }}</div>
                          @enderror
                        </div>

                        <div class="mb-3">
                          <div class="float-end">
                            <a href="{{ url('auth/forgot_password') }}" class="text-muted">Lupa password?</a>
                          </div>
                          <label class="form-label required" for="password-input">Password</label>
                          <div class="position-relative auth-pass-inputgroup mb-3">
                            <input type="password" class="form-control pe-5 password-input @error('password') is-invalid @enderror" placeholder="Enter password" id="password-input" name="password" required>
                            <button class="btn btn-link position-absolute end-0 top-0 text-decoration-none text-muted password-addon" type="button" id="password-addon"><i class="ri-eye-fill align-middle"></i></button>
                            @error('password')
                              <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                          </div>
                        </div>

                        <div class="form-check">
                          <input class="form-check-input" type="checkbox" id="auth-remember-check" name="remember" value="1">
                          <label class="form-check-label" for="auth-remember-check">Ingat Saya</label>
                        </div>

                        <div class="mt-4">
                          <button class="btn btn-success w-100" type="submit" id="btn_login">Masuk</button>
                        </div>
                      </form>
                    </div>
                  </div>
                </div>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>

    <footer class="footer">
      <div class="container">
        <div class="row">
          <div class="col-lg-12">
            <div class="text-center">
              <p class="mb-0">&copy;
                <script>document.write(new Date().getFullYear())</script> {{ $site_name }}. Crafted with by Bangchan
              </p>
            </div>
          </div>
        </div>
      </div>
    </footer>
  </div>
</body>

<script src="{{ asset('assets/js/pages/password-addon.init.js') }}"></script>
<script>
  $(document).ready(function() {
    $('.login-form').on('submit', function(e) {
      e.preventDefault();
      let btn = $('#btn_login');
      let form = $(this);
      let url = form.attr('action');
      var valid = form.validate();

      if (form.valid()) {
        btn.addClass('disabled').html('<i class="fal fa-spinner fa-spin me-1"></i> Mohon tunggu...');
        $.ajax({
          url: url,
          type: "POST",
          dataType: "JSON",
          data: form.serialize(),
          success: function(res) {
            if (res.status == true) {
              if (typeof toast_success === 'function') {
                toast_success('Berhasil masuk. Mohon tunggu...');
              }
              setTimeout(() => {
                window.location.href = res.link_tujuan || '{{ url("dashboard") }}';
              }, 1000);
            } else {
              if (typeof toast_error === 'function') {
                toast_error(res.msg);
              } else {
                alert(res.msg);
              }
              btn.removeClass('disabled').text('Masuk');
            }
          },
          error: function(xhr) {
            btn.removeClass('disabled').text('Masuk');
            let msg = 'Terjadi kesalahan koneksi, silakan coba lagi';
            if (xhr.responseJSON && xhr.responseJSON.message) {
              msg = xhr.responseJSON.message;
            }
            if (typeof toast_error === 'function') {
              toast_error(msg);
            } else {
              alert(msg);
            }
          }
        });
      } else {
        btn.removeClass('disabled').text('Masuk');
        valid.focusInvalid();
        return false;
      }
    });
  });
</script>
</html>
