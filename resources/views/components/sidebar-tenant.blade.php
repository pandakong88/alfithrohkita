<div class="sidebar" data-background-color="dark">
    <div class="sidebar-logo">
        <!-- Logo Header -->
        <div class="logo-header" data-background-color="dark">
            <a href="{{ route('tenant.dashboard') }}" class="logo">
                <img src="{{ asset('kaiadmin/assets/img/kaiadmin/logo_light.svg') }}" alt="navbar brand" class="navbar-brand" height="20" />
            </a>
            <div class="nav-toggle">
                <button class="btn btn-toggle toggle-sidebar">
                    <i class="gg-menu-right"></i>
                </button>
                <button class="btn btn-toggle sidenav-toggler">
                    <i class="gg-menu-left"></i>
                </button>
            </div>
            <button class="topbar-toggler more">
                <i class="gg-more-vertical-alt"></i>
            </button>
        </div>
        <!-- End Logo Header -->
    </div>
    <div class="sidebar-wrapper scrollbar scrollbar-inner">
        <div class="sidebar-content">
            <ul class="nav nav-secondary">
                <li class="nav-item {{ request()->routeIs('tenant.dashboard') ? 'active' : '' }}">
                    <a href="{{ route('tenant.dashboard') }}">
                        <i class="fas fa-home"></i>
                        <p>Dashboard</p>
                    </a>
                </li>
                <li class="nav-item {{ request()->routeIs('tenant.user.*') ? 'active' : '' }}">
                    <a href="{{ route('tenant.user.index') }}">
                        <i class="fas fa-users"></i>
                        <p>User</p>
                    </a>
                </li>
                <li class="nav-item {{ request()->routeIs('tenant.role.*') ? 'active' : '' }}">
                    <a href="{{ route('tenant.role.index') }}">
                        <i class="fas fa-user-shield"></i>
                        <p>Role</p>
                    </a>
                </li>
                <li class="nav-item {{ request()->routeIs('tenant.santri.*') ? 'active' : '' }}">
                    <a href="{{ route('tenant.santri.index') }}">
                        <i class="fas fa-user-graduate"></i>
                        <p>Santri</p>
                    </a>
                </li>
                <li class="nav-item {{ request()->routeIs('tenant.wali.*') ? 'active' : '' }}">
                    <a href="{{ route('tenant.wali.index') }}">
                        <i class="fas fa-user-friends"></i>
                        <p>Wali</p>
                    </a>
                </li>
            </ul>
        </div>
    </div>
</div>