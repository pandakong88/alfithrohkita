<div class="sidebar" data-background-color="dark">
    <div class="sidebar-logo">
        <div class="logo-header" data-background-color="dark">
            <a href="{{ route('tenant.dashboard') }}" class="logo">
                <img src="{{ asset('kaiadmin/assets/img/kaiadmin/logo_light.svg') }}" alt="navbar brand" class="navbar-brand" height="20" />
            </a>
            <div class="nav-toggle">
                <button class="btn btn-toggle toggle-sidebar"><i class="gg-menu-right"></i></button>
                <button class="btn btn-toggle sidenav-toggler"><i class="gg-menu-left"></i></button>
            </div>
        </div>
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

                <li class="nav-section">
                    <span class="sidebar-mini-icon"><i class="fa fa-ellipsis-h"></i></span>
                    <h4 class="text-section">Manajemen Data</h4>
                </li>
                <li class="nav-item 
                    {{ request()->routeIs('tenant.santri.*') || request()->routeIs('tenant.santri.snapshot.*') ? 'active submenu' : '' }}">
                
                    <a data-bs-toggle="collapse" href="#menuSantri"
                    class="{{ request()->routeIs('tenant.santri.*') || request()->routeIs('tenant.santri.snapshot.*') ? '' : 'collapsed' }}"
                    aria-expanded="{{ request()->routeIs('tenant.santri.*') || request()->routeIs('tenant.santri.snapshot.*') ? 'true' : 'false' }}">
                
                        <i class="fas fa-user-graduate"></i>
                        <p>Data Santri</p>
                        <span class="caret"></span>
                    </a>
                
                    <div class="collapse 
                        {{ request()->routeIs('tenant.santri.*') || request()->routeIs('tenant.santri.snapshot.*') ? 'show' : '' }}"
                        id="menuSantri">
                
                        <ul class="nav nav-collapse">
                
                            {{-- Daftar Santri --}}
                            <li class="{{ request()->routeIs('tenant.santri.index') ? 'active' : '' }}">
                                <a href="{{ route('tenant.santri.index') }}">
                                    <span class="sub-item">Daftar Santri</span>
                                </a>
                            </li>
                
                            {{-- Import Santri --}}
                            <li class="{{ request()->routeIs('tenant.santri.import') ? 'active' : '' }}">
                                <a href="{{ route('tenant.santri.import') }}">
                                    <span class="sub-item">Import Santri</span>
                                </a>
                            </li>
                
                            {{-- Riwayat Import --}}
                            <li class="{{ request()->routeIs('tenant.santri.import.history') ? 'active' : '' }}">
                                <a href="{{ route('tenant.santri.import.history') }}">
                                    <span class="sub-item">Riwayat Import</span>
                                </a>
                            </li>
                
                            {{-- Snapshot --}}
                            <li class="{{ request()->routeIs('tenant.santri.snapshot.*') ? 'active' : '' }}">
                                <a href="{{ route('tenant.santri.snapshot.import') }}">
                                    <span class="sub-item">Snapshot Santri</span>
                                </a>
                            </li>
                
                        </ul>
                    </div>
                </li>

                <li class="nav-item {{ request()->routeIs('tenant.wali.*') ? 'active' : '' }}">
                    <a href="{{ route('tenant.wali.index') }}">
                        <i class="fas fa-user-friends"></i>
                        <p>Data Wali</p>
                    </a>
                </li>

                <li class="nav-section">
                    <span class="sidebar-mini-icon"><i class="fa fa-ellipsis-h"></i></span>
                    <h4 class="text-section">Pengaturan Sistem</h4>
                </li>

                <li class="nav-item {{ request()->routeIs('tenant.user.*') || request()->routeIs('tenant.role.*') ? 'active submenu' : '' }}">
                    <a data-bs-toggle="collapse" href="#pengaturanAkses" class="{{ request()->routeIs('tenant.user.*') || request()->routeIs('tenant.role.*') ? '' : 'collapsed' }}" aria-expanded="{{ request()->routeIs('tenant.user.*') || request()->routeIs('tenant.role.*') ? 'true' : 'false' }}">
                        <i class="fas fa-user-lock"></i>
                        <p>Manajemen Akses</p>
                        <span class="caret"></span>
                    </a>
                    <div class="collapse {{ request()->routeIs('tenant.user.*') || request()->routeIs('tenant.role.*') ? 'show' : '' }}" id="pengaturanAkses">
                        <ul class="nav nav-collapse">
                            <li class="{{ request()->routeIs('tenant.user.*') ? 'active' : '' }}">
                                <a href="{{ route('tenant.user.index') }}">
                                    <span class="sub-item">Pengguna (User)</span>
                                </a>
                            </li>
                            <li class="{{ request()->routeIs('tenant.role.*') ? 'active' : '' }}">
                                <a href="{{ route('tenant.role.index') }}">
                                    <span class="sub-item">Peran (Role)</span>
                                </a>
                            </li>
                        </ul>
                    </div>
                </li>

                <li class="nav-item {{ request()->routeIs('tenant.pondok.*') ? 'active' : '' }}">
                    <a href="#">
                        <i class="fas fa-mosque"></i>
                        <p>Profil Pondok</p>
                    </a>
                </li>

            </ul>
        </div>
    </div>
</div>