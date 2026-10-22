<header id="page-topbar">
  <div class="layout-width">
    <div class="navbar-header">
      <div class="d-flex">
        <div class="navbar-brand-box horizontal-logo">
          <a href="{{ url('dashboard') }}" class="logo logo-dark">
            <span class="logo-sm">
              <img src="{{ asset($logo_sm) }}" class="rounded-circle avatar-sm img-thumbnail" alt="" height="45">
            </span>
            <span class="logo-lg">
              <h3 class="text-white my-3">{{ $site_name }}</h3>
            </span>
          </a>

          <a href="{{ url('dashboard') }}" class="logo logo-light">
            <span class="logo-sm">
              <img src="{{ asset($logo_sm) }}" class="rounded-circle avatar-sm img-thumbnail" alt="" height="45">
            </span>
            <span class="logo-lg">
              <h3 class="text-dark my-3">{{ $site_name }}</h3>
            </span>
          </a>
        </div>

        <button type="button" class="btn btn-sm px-3 fs-16 header-item vertical-menu-btn topnav-hamburger" id="topnav-hamburger-icon">
          <span class="hamburger-icon">
            <span></span>
            <span></span>
            <span></span>
          </span>
        </button>

        <form class="app-search d-none d-md-block">
          <div class="position-relative">
            <input type="text" class="form-control" placeholder="Search..." autocomplete="off" id="search-options" value="">
            <span class="mdi mdi-magnify"><i class="fal fa-search"></i></span>
            <span class="mdi mdi-close-circle search-widget-icon search-widget-icon-close d-none" id="search-close-options"><i class="fal fa-times-circle"></i></span>
          </div>
          <div class="dropdown-menu dropdown-menu-lg" id="search-dropdown">
            <div data-simplebar style="max-height: 320px;">
              <div class="dropdown-header mt-2">
                <h6 class="text-overflow text-muted mb-1 text-uppercase">Menu</h6>
              </div>
              <div id="area_search_menu">
                <div class="dropdown-item notify-item">
                  <div class="text-center my-3">
                    <i class="fad fa-circle-notch fa-spin fa-2x"></i>
                  </div>
                </div>
              </div>
            </div>
          </div>
        </form>
      </div>

      <div class="d-flex align-items-center">
        <div class="dropdown d-md-none topbar-head-dropdown header-item">
          <button type="button" class="btn btn-icon btn-topbar btn-ghost-secondary rounded-circle" id="page-header-search-dropdown" data-bs-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
            <i class="bx bx-search fs-22"></i>
          </button>
          <div class="dropdown-menu dropdown-menu-lg dropdown-menu-end p-0" aria-labelledby="page-header-search-dropdown">
            <form class="p-3">
              <div class="form-group m-0">
                <div class="input-group">
                  <input type="text" class="form-control" placeholder="Search ..." aria-label="Recipient's username">
                  <button class="btn btn-primary" type="submit"><i class="mdi mdi-magnify"></i></button>
                </div>
              </div>
            </form>
          </div>
        </div>

        <div class="ms-1 header-item d-none d-sm-flex">
          <button type="button" class="btn btn-icon btn-topbar btn-ghost-secondary rounded-circle" data-toggle="fullscreen">
            <i class="fal fa-expand-wide fs-22"></i>
          </button>
        </div>
        <div class="ms-1 header-item d-none d-sm-flex">
          <button type="button" class="btn btn-icon btn-topbar btn-ghost-secondary rounded-circle light-dark-mode">
            <i class="fal fa-moon fs-22"></i>
          </button>
        </div>

        @if($user)
        <div class="dropdown ms-sm-3 header-item topbar-user">
          <button type="button" class="btn" id="page-header-user-dropdown" data-bs-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
            <span class="d-flex align-items-center">
              <img class="rounded-circle header-profile-user" style="object-position: center; object-fit: cover;" src="{{ $user->avatar_url }}" alt="Header Avatar">
              <span class="text-start ms-xl-2">
                <span class="d-none d-xl-inline-block ms-1 fw-medium user-name-text">{{ $user->username }}</span>
                <span class="d-none d-xl-block ms-1 fs-12 text-muted user-name-sub-text">{{ ucfirst($user->group_name ?? 'User') }}</span>
              </span>
            </span>
          </button>
          <div class="dropdown-menu dropdown-menu-end">
            <h6 class="dropdown-header">Hi, {{ $user->username }}!</h6>
            <a class="dropdown-item" href="{{ url('profile') }}"><i class="fal fa-user text-muted fs-16 align-middle me-1"></i> <span class="align-middle">Profile</span></a>
            <div class="dropdown-divider"></div>
            <a class="dropdown-item" href="{{ url('profile/edit') }}"><i class="fal fa-cog text-muted fs-16 align-middle me-1"></i> <span class="align-middle">Edit Profile</span></a>
            <form method="POST" action="{{ route('logout') }}" id="logout-form">
              @csrf
              <a class="dropdown-item" href="{{ route('logout') }}" onclick="event.preventDefault(); document.getElementById('logout-form').submit();">
                <i class="fal fa-sign-out text-muted fs-16 align-middle me-1"></i> <span class="align-middle" data-key="t-logout">Logout</span>
              </a>
            </form>
          </div>
        </div>
        @endif
      </div>
    </div>
  </div>
</header>

<div class="app-menu navbar-menu">
  <div class="navbar-brand-box">
    <a href="{{ url('dashboard') }}" class="logo logo-dark">
      <span class="logo-sm">
        <img src="{{ asset($logo_sm) }}" class="rounded-circle avatar-sm img-thumbnail" alt="" height="45">
      </span>
      <span class="logo-lg">
        <h3 class="text-dark my-3">{{ $site_name }}</h3>
      </span>
    </a>

    <a href="{{ url('dashboard') }}" class="logo logo-light">
      <span class="logo-sm">
        <img src="{{ asset($logo_sm) }}" class="rounded-circle avatar-sm img-thumbnail" alt="" height="45">
      </span>
      <span class="logo-lg">
        <h3 class="text-light my-3">{{ $site_name }}</h3>
      </span>
    </a>
    <button type="button" class="btn btn-sm p-0 fs-20 header-item float-end btn-vertical-sm-hover" id="vertical-hover">
      <i class="ri-record-circle-line"></i>
    </button>
  </div>

  <div id="scrollbar">
    <div class="container-fluid">
      <div id="two-column-menu"></div>
      <ul class="navbar-nav" id="navbar-nav">
        @php
          $class_font_type = 'fal';
          $currentPath = request()->path();
        @endphp

        @foreach($sidebar as $head)
          @if(!empty($head['nama']))
            <li class="menu-title"><span data-key="t-{{ $head['slug'] ?? '' }}">{{ $head['nama'] }}</span></li>

            @if(!empty($head['menus']))
              @foreach($head['menus'] as $menu)
                @php
                  $menuNama = $menu['menu_nama'] ?? '';
                  $menuSlug = $menu['menu_slug'] ?? '';
                  $menuIcon = $menu['menu_icon'] ?? '';
                  $menuLink = $menu['menu_link'] ?? '';
                  $isMenuActive = ($menuLink == $currentPath);
                  $hasSubMenus = !empty($menu['sub_menus']);
                  $isAnySubActive = false;

                  if ($hasSubMenus) {
                    foreach ($menu['sub_menus'] as $sub) {
                      if (($sub['sub_menu_link'] ?? '') == $currentPath) {
                        $isAnySubActive = true;
                        break;
                      }
                    }
                  }
                @endphp

                @if($hasSubMenus)
                  <li class="nav-item">
                    <a class="nav-link menu-link {{ $isAnySubActive ? 'collapsed active' : '' }}" href="#sidebar-{{ $menuSlug }}" data-bs-toggle="collapse" role="button" aria-expanded="{{ $isAnySubActive ? 'true' : 'false' }}" aria-controls="sidebar-{{ $menuSlug }}">
                      <i class="{{ $class_font_type }} {{ $menuIcon }}"></i> <span data-key="t-{{ $menuSlug }}">{{ $menuNama }}</span>
                    </a>
                    <div class="collapse menu-dropdown {{ $isAnySubActive ? 'show' : '' }}" id="sidebar-{{ $menuSlug }}">
                      <ul class="nav nav-sm flex-column">
                        @foreach($menu['sub_menus'] as $sub)
                          @php
                            $subLink = $sub['sub_menu_link'] ?? '';
                            $isSubActive = ($subLink == $currentPath);
                          @endphp
                          <li class="nav-item">
                            <a href="{{ url($subLink) }}" class="nav-link {{ $isSubActive ? 'active' : '' }}" data-key="t-{{ $sub['sub_menu_slug'] ?? '' }}">
                              {{ $sub['sub_menu_nama'] ?? '' }}
                            </a>
                          </li>
                        @endforeach
                      </ul>
                    </div>
                  </li>
                @else
                  <li class="nav-item">
                    <a class="nav-link menu-link {{ $isMenuActive ? 'active' : '' }}" href="{{ url($menuLink) }}">
                      <i class="{{ $class_font_type }} {{ $menuIcon }}"></i> <span data-key="t-{{ $menuSlug }}">{{ $menuNama }}</span>
                    </a>
                  </li>
                @endif
              @endforeach
            @endif
          @endif
        @endforeach

        <li class="menu-title"><span data-key="t-panduan">DOKUMENTASI</span></li>
        <li class="nav-item">
          <a class="nav-link menu-link {{ request()->is('panduan*') ? 'active' : '' }}" href="{{ url('panduan') }}">
            <i class="fal fa-book-open"></i> <span data-key="t-panduan-guide">Panduan Pengguna</span>
          </a>
        </li>
      </ul>
    </div>
  </div>
</div>
<div class="vertical-overlay"></div>
