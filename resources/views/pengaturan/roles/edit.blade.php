@if (!empty($model))
  <form id="form_update" class="kms-form needs-validation" enctype="multipart/form-data" novalidate>
    <input type="hidden" name="{{ $csrf_name }}" id="{{ $csrf_name }}" value="{{ $csrf_token }}">
    <input type="hidden" name="model_id" id="model_id" value="{{ $model->id }}">
    <div class="row g-3">
      <div class="col-xxl-6">
        <div class="fm mb-3">
          <label for="u_name" class="form-label required">Nama {{ $title ?? 'Roles' }}</label>
          <input type="text" class="form-control" id="u_name" name="u_name" placeholder="Enter..." value="{{ $model->name ?? '' }}" required>
        </div>
        <div class="fm">
          <label for="u_body" class="form-label">Keterangan {{ $title ?? 'Roles' }}</label>
          <textarea class="form-control" id="u_body" name="u_body" rows="5" placeholder="Enter...">{{ $model->description ?? '' }}</textarea>
        </div>
      </div>
      <div class="col-xxl-6 d-block d-none-sm">
        <img src="{{ asset('assets/images/interaction_design.svg') }}" class="img-responsive d-block mx-auto" style="height:250px;" alt="roles_image">
      </div>
      <div class="col-lg-12">
        <div class="title mb-2">
          <p class="fs-16"><strong>Permission Menu</strong></p>
        </div>
        @php
        $headers = '';
        foreach ($all_menu_access as $v) {
          $permission_menus = '';
          $count_menu = 0;
          if (!empty($v['nama'])) {
            if (!empty($v['menus'])) {
              foreach ($v['menus'] as $mm) {
                $menu_nama = $mm['menu_nama'] ?? '';
                $menu_slug = $mm['menu_slug'] ?? '';
                $permission_sub_menus = '';
                $count_menu_multi_active_permission = 0;

                if (!empty($mm['sub_menus'])) {
                  foreach ($mm['sub_menus'] as $ss) {
                    $sub_menu_nama = $ss['sub_menu_nama'] ?? '';
                    $sub_menu_slug = $ss['sub_menu_slug'] ?? '';
                    if (!empty($ss['sub_menu_permissions'])) {
                      $sub_menu_ps = '';
                      $count_sub_menu_active_permission = 0;
                      foreach ($ss['sub_menu_permissions'] as $ps) {
                        $sub_menu_ps_nama = $ps['p_nama'] ?? '';
                        $sub_menu_ps_slug = $ps['p_slug'] ?? '';
                        $status_menu = in_array($ps['p_id'], $user_access_permissions) ? 'checked' : '';
                        if ($status_menu) $count_sub_menu_active_permission++;

                        $sub_menu_ps .= '<div class="form-check col-xxl-2"> <input class="form-check-input permission_check" type="checkbox" id="' . $sub_menu_ps_slug . '" name="permissions[]" value="' . $ps['p_id'] . '" ' . $status_menu . '> <label class="form-check-label" for="' . $sub_menu_ps_slug . '"> ' . e($sub_menu_ps_nama) . ' </label> </div>';
                      }
                      $sts_sub_menu = $count_sub_menu_active_permission > 0 ? 'checked' : '';
                      if ($sts_sub_menu) $count_menu_multi_active_permission++;

                      $permission_sub_menus .= '<div class="row sub_menu_permission ps-3"><div class="col"><div class="form-check mb-2"> <input class="form-check-input sub_menu_check" type="checkbox" id="' . $sub_menu_slug . '" name="permission_menus[]" value="' . $ss['sub_menu_id'] . '" ' . $sts_sub_menu . '> <label class="form-check-label" for="' . $sub_menu_slug . '"> ' . e($sub_menu_nama) . ' </label> </div> <div class="row mb-2 ps-4 area_permission">' . $sub_menu_ps . '</div></div></div>';
                    } else {
                      $permission_sub_menus .= '<div class="row sub_menu_permission ps-3"><div class="col"><div class="form-check mb-2"> <input class="form-check-input sub_menu_check" type="checkbox" id="' . $sub_menu_slug . '" name="permission_menus[]" value="' . $ss['sub_menu_id'] . '"> <label class="form-check-label" for="' . $sub_menu_slug . '"> ' . e($sub_menu_nama) . ' </label> </div></div></div>';
                    }
                  }
                }

                if (!empty($permission_sub_menus)) {
                  $sts_menu = $count_menu_multi_active_permission > 0 ? 'checked' : '';
                  if ($sts_menu) $count_menu++;

                  $permission_menus .= '<div class="row menu_permission ps-3"><div class="col"><div class="form-check mb-2"> <input class="form-check-input menu_check" type="checkbox" id="' . $menu_slug . '" name="permission_menus[]" value="' . $mm['menu_id'] . '" ' . $sts_menu . '> <label class="form-check-label" for="' . $menu_slug . '"> ' . e($menu_nama) . ' </label> </div> ' . $permission_sub_menus . '</div></div>';
                } else {
                  if (!empty($mm['menu_permissions'])) {
                    $menu_ps = '';
                    $count_menu_active_permission = 0;
                    foreach ($mm['menu_permissions'] as $psm) {
                      $menu_ps_nama = $psm['p_nama'] ?? '';
                      $menu_ps_slug = $psm['p_slug'] ?? '';
                      $status_menu = in_array($psm['p_id'], $user_access_permissions) ? 'checked' : '';
                      if ($status_menu) $count_menu_active_permission++;

                      $menu_ps .= '<div class="form-check col-xxl-2"> <input class="form-check-input permission_check" type="checkbox" id="' . $menu_ps_slug . '" name="permissions[]" value="' . $psm['p_id'] . '" ' . $status_menu . '> <label class="form-check-label" for="' . $menu_ps_slug . '"> ' . e($menu_ps_nama) . ' </label> </div>';
                    }
                    $sts_menu = $count_menu_active_permission > 0 ? 'checked' : '';
                    if ($sts_menu) $count_menu++;

                    $permission_menus .= '<div class="row menu_permission ps-3"><div class="col"><div class="form-check mb-2"> <input class="form-check-input menu_check" type="checkbox" id="' . $menu_slug . '" name="permission_menus[]" value="' . $mm['menu_id'] . '" ' . $sts_menu . '> <label class="form-check-label" for="' . $menu_slug . '"> ' . e($menu_nama) . ' </label> </div><div class="row mb-2 ps-4 area_permission">' . $menu_ps . '</div></div></div>';
                  } else {
                    $permission_menus .= '<div class="row menu_permission ps-3"><div class="col"><div class="form-check mb-2"> <input class="form-check-input menu_check" type="checkbox" id="' . $menu_slug . '" name="permission_menus[]" value="' . $mm['menu_id'] . '"> <label class="form-check-label" for="' . $menu_slug . '"> ' . e($menu_nama) . ' </label> </div></div></div>';
                  }
                }
              }
            }

            $nama = $v['nama'] ?? '';
            $slug = $v['slug'] ?? '';
            $sts_header = $count_menu > 0 ? 'checked' : '';
            $permission_headers = '<div class="form-check mb-2"> <input class="form-check-input header_check" type="checkbox" id="' . $slug . '" name="permission_menus[]" value="' . $v['id'] . '" ' . $sts_header . '> <label class="form-check-label" for="' . $slug . '"> ' . e($nama) . ' </label> </div>';
            $headers .= '<div class="row header_permission mb-3"><div class="col">' . $permission_headers . ' ' . $permission_menus . '</div></div>';
          }
        }
        @endphp
        {!! $headers !!}
      </div>
      <div class="col-lg-12">
        <div class="hstack gap-2 justify-content-center mt-3">
          <button type="button" class="btn btn-danger w-sm waves-effect waves-light me-5" data-bs-dismiss="modal">Batal</button>
          <button type="button" class="btn btn-success w-sm waves-effect waves-light btn-update">Simpan</button>
        </div>
      </div>
    </div>
  </form>
  <script>
    $(document).ready(function() {
      $('.form-check-input').on('click', function() {
        let header = $(this).hasClass('header_check') ? 1 : 0;
        let menu = $(this).hasClass('menu_check') ? 1 : 0;
        let sub_menu = $(this).hasClass('sub_menu_check') ? 1 : 0;
        let permission = $(this).hasClass('permission_check') ? 1 : 0;
        if (header == 1) {
          let header_check = $(this).is(':checked');
          if (header_check) {
            $(this).closest('.header_permission')
              .find('.form-check-input')
              .each(function() {
                $(this).prop('checked', true);
              });
          } else {
            $(this).closest('.header_permission')
              .find('.form-check-input')
              .each(function() {
                $(this).prop('checked', false);
              });
          }
        }
        if (menu == 1) {
          let menu_check = $(this).is(':checked');
          if (menu_check) {
            $(this).closest('.menu_permission')
              .find('.form-check-input')
              .each(function() {
                $(this).prop('checked', true);
              });
            let header_status = $(this).closest('.header_permission').find('.header_check');
            let header_box = header_status.is(':checked');
            if (!header_box) {
              header_status.prop('checked', true);
            }
          } else {
            $(this).closest('.menu_permission')
              .find('.form-check-input')
              .each(function() {
                $(this).prop('checked', false);
              });
            let cek_header = $(this).closest('.header_permission');
            if (cek_header.length > 0) {
              let menu_cek = 0;
              let get_header = $(this).closest(".header_permission").find('.menu_check');
              let header_status = $(this).closest(".header_permission").find('.header_check');
              get_header.each(function() {
                let cek_box = $(this).is(':checked');
                if (cek_box) {
                  menu_cek += 1;
                }
              });
              if (menu_cek == 0) {
                header_status.prop('checked', false);
              }
            }
          }
        }
        if (sub_menu == 1) {
          let sub_menu_check = $(this).is(':checked');
          if (sub_menu_check) {
            $(this).closest('.sub_menu_permission')
              .find('.form-check-input')
              .each(function() {
                $(this).prop('checked', true);
              });
            let menu_status = $(this).closest('.menu_permission').find('.menu_check');
            let cek_box = menu_status.is(':checked');
            if (!cek_box) {
              menu_status.prop('checked', true);
            }
            let header_status = $(this).closest('.header_permission').find('.header_check');
            let header_box = header_status.is(':checked');
            if (!header_box) {
              header_status.prop('checked', true);
            }
          } else {
            $(this).closest('.sub_menu_permission')
              .find('.form-check-input')
              .each(function() {
                $(this).prop('checked', false);
              });

            let cek_sub_menu = $(this).closest('.sub_menu_permission');
            let cek_menu = $(this).closest('.menu_permission');
            if (cek_sub_menu.length > 0) {
              let sub_menu_cek = 0;
              let get_menu = $(this).closest(".menu_permission").find('.sub_menu_check');
              let menu_status = $(this).closest('.menu_permission').find('.menu_check');
              get_menu.each(function() {
                let cek_box = $(this).is(':checked');
                if (cek_box) {
                  sub_menu_cek += 1;
                }
              });

              if (sub_menu_cek == 0) {
                menu_status.prop('checked', false);

                let menu_cek = 0;
                let get_header = $(this).closest(".header_permission").find('.menu_check');
                let header_status = $(this).closest(".header_permission").find('.header_check');
                get_header.each(function() {
                  let cek_box = $(this).is(':checked');
                  if (cek_box) {
                    menu_cek += 1;
                  }
                });
                if (menu_cek == 0) {
                  header_status.prop('checked', false);
                }
              }
            } else if (cek_menu.length > 0) {
              let menu_status = $(this).closest('.menu_permission').find('.menu_check');
              menu_status.prop('checked', false);

              let menu_cek = 0;
              let get_header = $(this).closest(".header_permission").find('.menu_check');
              let header_status = $(this).closest(".header_permission").find('.header_check');
              get_header.each(function() {
                let cek_box = $(this).is(':checked');
                if (cek_box) {
                  menu_cek += 1;
                }
              });
              if (menu_cek == 0) {
                header_status.prop('checked', false);
              }
            }
          }
        }
        if (permission == 1) {
          let permission_check = $(this).is(':checked');
          if (permission_check) {
            let get_sub_menu = $(this).closest(".sub_menu_permission").find('.sub_menu_check');
            if (!get_sub_menu.is(':checked')) {
              get_sub_menu.prop('checked', true);
            }
            let get_menu = $(this).closest(".menu_permission").find('.menu_check');
            if (!get_menu.is(':checked')) {
              get_menu.prop('checked', true);
            }
            let get_header = $(this).closest(".header_permission").find('.header_check');
            if (!get_header.is(':checked')) {
              get_header.prop('checked', true);
            }
          } else {
            let single_count_cek = 0;
            let get_single_menu = $(this).closest(".area_permission").find('.form-check-input');
            get_single_menu.each(function() {
              let cek_box = $(this).is(':checked');
              if (cek_box) {
                single_count_cek += 1;
              }
            });
            if (single_count_cek == 0) {
              let cek_sub_menu = $(this).closest('.sub_menu_permission');
              let cek_menu = $(this).closest('.menu_permission');
              if (cek_sub_menu.length > 0) {
                let sub_menu_status = $(this).closest('.sub_menu_permission').find('.sub_menu_check');
                sub_menu_status.prop('checked', false);

                let sub_menu_cek = 0;
                let get_menu = $(this).closest(".menu_permission").find('.sub_menu_check');
                let menu_status = $(this).closest('.menu_permission').find('.menu_check');
                get_menu.each(function() {
                  let cek_box = $(this).is(':checked');
                  if (cek_box) {
                    sub_menu_cek += 1;
                  }
                });
                if (sub_menu_cek == 0) {
                  menu_status.prop('checked', false);

                  let menu_cek = 0;
                  let get_header = $(this).closest(".header_permission").find('.menu_check');
                  let header_status = $(this).closest(".header_permission").find('.header_check');
                  get_header.each(function() {
                    let cek_box = $(this).is(':checked');
                    if (cek_box) {
                      menu_cek += 1;
                    }
                  });
                  if (menu_cek == 0) {
                    header_status.prop('checked', false);
                  }
                }
              } else if (cek_menu.length > 0) {
                let menu_status = $(this).closest('.menu_permission').find('.menu_check');
                menu_status.prop('checked', false);

                let menu_cek = 0;
                let get_header = $(this).closest(".header_permission").find('.menu_check');
                let header_status = $(this).closest(".header_permission").find('.header_check');
                get_header.each(function() {
                  let cek_box = $(this).is(':checked');
                  if (cek_box) {
                    menu_cek += 1;
                  }
                });
                if (menu_cek == 0) {
                  header_status.prop('checked', false);
                }
              }
            }
          }
        }
      });
    });
  </script>
@else
  <div class="text-center">
    <h4 class="text-danger">Data tidak ditemukan</h4>
  </div>
@endif
