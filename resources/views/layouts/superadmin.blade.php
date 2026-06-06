<!DOCTYPE html>
<html lang="en">
<head>
    @include('components.head')
</head>
<body>

<div class="wrapper">

    @include('components.sidebar-superadmin')

    <div class="main-panel">

        <div class="main-header">
            <div class="main-header-logo">
                <!-- Logo Header -->
                <div class="logo-header" data-background-color="dark">
                    <a href="{{ route('superadmin.dashboard') }}" class="logo">
                        <img src="{{ asset('kaiadmin/assets/img/kaiadmin/logo_light.svg') }}" 
                             alt="navbar brand" 
                             class="navbar-brand" 
                             height="20" />
                    </a>
                    <div class="nav-toggle">
                        <button class="btn btn-toggle toggle-sidebar"><i class="gg-menu-right"></i></button>
                        <button class="btn btn-toggle sidenav-toggler"><i class="gg-menu-left"></i></button>
                    </div>
                    <button class="topbar-toggler more">
                        <i class="gg-more-vertical-alt"></i>
                    </button>
                </div>
                <!-- End Logo Header -->
            </div>
            @include('components.navbar')
        </div>

        <div class="container">
            <div class="page-inner">
                @yield('content')
            </div>
        </div>

        @include('components.footer')

    </div>

</div>

@include('components.scripts')
@stack('scripts')

</body>
</html>