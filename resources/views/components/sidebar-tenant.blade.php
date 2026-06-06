<div class="sidebar" data-background-color="dark">
    <div class="sidebar-logo">
        <div class="logo-header" data-background-color="dark">
            <a href="{{ route('tenant.dashboard') }}" class="logo">
                <img src="{{ asset('kaiadmin/assets/img/kaiadmin/logo_light.svg') }}" 
                     alt="navbar brand" 
                     class="navbar-brand" 
                     height="20" />
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
                
                {{-- Utama --}}
                <li class="nav-item {{ request()->routeIs('tenant.dashboard') ? 'active' : '' }}">
                    <a href="{{ route('tenant.dashboard') }}">
                        <i class="fas fa-home"></i>
                        <p>Dashboard Utama</p>
                    </a>
                </li>

                {{-- Section Kesiswaan --}}
                @if(auth()->user()->can('manage_santri') || auth()->user()->can('manage_wali'))
                <li class="nav-section">
                    <span class="sidebar-mini-icon"><i class="fa fa-users"></i></span>
                    <h4 class="text-section">Data Santri & Wali</h4>
                </li>
                @endif

                {{-- Data Santri (Direct Link - No collapse needed since it has one child) --}}
                @can('manage_santri')
                <li class="nav-item {{ request()->routeIs('tenant.santri.*') && !request()->routeIs('tenant.santri.handbook.*') ? 'active' : '' }}">
                    <a href="{{ route('tenant.santri.index') }}">
                        <i class="fas fa-user-graduate"></i>
                        <p>Data Santri</p>
                    </a>
                </li>
                @endcan

                {{-- Data Wali --}}
                @can('manage_wali')
                <li class="nav-item {{ request()->routeIs('tenant.wali.*') ? 'active' : '' }}">
                    <a href="{{ route('tenant.wali.index') }}">
                        <i class="fas fa-user-friends"></i>
                        <p>Data Wali Murid</p>
                    </a>
                </li>
                @endcan

                {{-- Pedoman Santri --}}
                @can('manage_cms')
                <li class="nav-item {{ request()->routeIs('tenant.santri.handbook.*') ? 'active' : '' }}">
                    <a href="{{ route('tenant.santri.handbook.index') }}">
                        <i class="fas fa-book-open"></i>
                        <p>Buku Pedoman Santri</p>
                    </a>
                </li>
                @endcan

                {{-- Section Aktivitas Harian --}}
                @if(auth()->user()->can('manage_perizinan') || auth()->user()->can('manage_absensi') || auth()->user()->can('manage_pelanggaran'))
                <li class="nav-section">
                    <span class="sidebar-mini-icon"><i class="fa fa-calendar-alt"></i></span>
                    <h4 class="text-section">Aktivitas & Kedisiplinan</h4>
                </li>
                @endif

                {{-- Perizinan Keluar Masuk --}}
                @can('manage_perizinan')
                <li class="nav-item {{ request()->routeIs('tenant.template-perizinan.*') || request()->routeIs('tenant.perizinan.*') ? 'active submenu' : '' }}">
                    <a data-bs-toggle="collapse" href="#menuPerizinan">
                        <i class="fas fa-file-contract"></i>
                        <p>Izin Keluar-Masuk</p>
                        <span class="caret"></span>
                    </a>
                    <div class="collapse {{ request()->routeIs('tenant.template-perizinan.*') || request()->routeIs('tenant.perizinan.*') ? 'show' : '' }}" id="menuPerizinan">
                        <ul class="nav nav-collapse">
                            <li class="{{ request()->routeIs('tenant.perizinan.*') ? 'active' : '' }}">
                                <a href="{{ route('tenant.perizinan.index') }}">
                                    <span class="sub-item">Catatan Izin</span>
                                </a>
                            </li>
                            <li class="{{ request()->routeIs('tenant.template-perizinan.*') ? 'active' : '' }}">
                                <a href="{{ route('tenant.template-perizinan.index') }}">
                                    <span class="sub-item">Template Surat Izin</span>
                                </a>
                            </li>
                        </ul>
                    </div>
                </li>
                @endcan

                {{-- Presensi / Kehadiran --}}
                @can('manage_absensi')
                <li class="nav-item {{ request()->routeIs('tenant.absensi.*') || request()->routeIs('tenant.absensi-sesi.*') ? 'active submenu' : '' }}">
                    <a data-bs-toggle="collapse" href="#menuAbsensi">
                        <i class="fas fa-calendar-check"></i>
                        <p>Kehadiran (Absensi)</p>
                        <span class="caret"></span>
                    </a>
                    <div class="collapse {{ request()->routeIs('tenant.absensi.*') || request()->routeIs('tenant.absensi-sesi.*') ? 'show' : '' }}" id="menuAbsensi">
                        <ul class="nav nav-collapse">
                            <li class="{{ request()->routeIs('tenant.absensi.pilih-sesi') || request()->routeIs('tenant.absensi.index') ? 'active' : '' }}">
                                <a href="{{ route('tenant.absensi.pilih-sesi') }}">
                                    <span class="sub-item">Pencatatan Hadir</span>
                                </a>
                            </li>
                            <li class="{{ request()->routeIs('tenant.absensi-sesi.*') ? 'active' : '' }}">
                                <a href="{{ route('tenant.absensi-sesi.index') }}">
                                    <span class="sub-item">Sesi Absensi</span>
                                </a>
                            </li>
                        </ul>
                    </div>
                </li>
                @endcan

                {{-- Pencatatan Pelanggaran --}}
                @can('manage_pelanggaran')
                <li class="nav-item {{ request()->routeIs('tenant.pelanggaran.*') || request()->routeIs('tenant.kategori-pelanggaran.*') ? 'active submenu' : '' }}">
                    <a data-bs-toggle="collapse" href="#menuPelanggaran">
                        <i class="fas fa-exclamation-triangle"></i>
                        <p>Catatan Pelanggaran</p>
                        <span class="caret"></span>
                    </a>
                    <div class="collapse {{ request()->routeIs('tenant.pelanggaran.*') || request()->routeIs('tenant.kategori-pelanggaran.*') ? 'show' : '' }}" id="menuPelanggaran">
                        <ul class="nav nav-collapse">
                            <li class="{{ request()->routeIs('tenant.pelanggaran.index') ? 'active' : '' }}">
                                <a href="{{ route('tenant.pelanggaran.index') }}">
                                    <span class="sub-item">Laporan Pelanggaran</span>
                                </a>
                            </li>
                            <li class="{{ request()->routeIs('tenant.kategori-pelanggaran.index') ? 'active' : '' }}">
                                <a href="{{ route('tenant.kategori-pelanggaran.index') }}">
                                    <span class="sub-item">Kategori Hukuman</span>
                                </a>
                            </li>
                        </ul>
                    </div>
                </li>
                @endcan

                {{-- Section Utilitas --}}
                @if(auth()->user()->can('manage_santri') || auth()->user()->can('manage_users'))
                <li class="nav-section">
                    <span class="sidebar-mini-icon"><i class="fa fa-cog"></i></span>
                    <h4 class="text-section">Sistem & Pengaturan</h4>
                </li>
                @endif

                {{-- Fitur Import --}}
                @can('manage_santri')
                <li class="nav-item {{ request()->routeIs('tenant.import.*') || request()->routeIs('tenant.import-templates.*') ? 'active submenu' : '' }}">
                    <a data-bs-toggle="collapse" href="#menuImport">
                        <i class="fas fa-file-import"></i>
                        <p>Import Excel</p>
                        <span class="caret"></span>
                    </a>
                    <div class="collapse {{ request()->routeIs('tenant.import.*') || request()->routeIs('tenant.import-templates.*') ? 'show' : '' }}" id="menuImport">
                        <ul class="nav nav-collapse">
                            <li class="{{ request()->routeIs('tenant.import.upload') ? 'active' : '' }}">
                                <a href="{{ route('tenant.import.upload') }}">
                                    <span class="sub-item">Upload File</span>
                                </a>
                            </li>
                            <li class="{{ request()->routeIs('tenant.import.history') || request()->routeIs('tenant.import.detail') ? 'active' : '' }}">
                                <a href="{{ route('tenant.import.history') }}">
                                    <span class="sub-item">Riwayat Import</span>
                                </a>
                            </li>
                            <li class="{{ request()->routeIs('tenant.import-templates.index') ? 'active' : '' }}">
                                <a href="{{ route('tenant.import-templates.index') }}">
                                    <span class="sub-item">Setting Kolom Template</span>
                                </a>
                            </li>
                        </ul>
                    </div>
                </li>
                @endcan

                {{-- Manajemen Akses --}}
                @can('manage_users')
                <li class="nav-item {{ request()->routeIs('tenant.user.*') || request()->routeIs('tenant.role.*') ? 'active submenu' : '' }}">
                    <a data-bs-toggle="collapse" href="#pengaturanAkses">
                        <i class="fas fa-user-lock"></i>
                        <p>Manajemen Pengguna</p>
                        <span class="caret"></span>
                    </a>
                    <div class="collapse {{ request()->routeIs('tenant.user.*') || request()->routeIs('tenant.role.*') ? 'show' : '' }}" id="pengaturanAkses">
                        <ul class="nav nav-collapse">
                            <li class="{{ request()->routeIs('tenant.user.*') ? 'active' : '' }}">
                                <a href="{{ route('tenant.user.index') }}">
                                    <span class="sub-item">User & Staf</span>
                                </a>
                            </li>
                            <li class="{{ request()->routeIs('tenant.role.*') ? 'active' : '' }}">
                                <a href="{{ route('tenant.role.index') }}">
                                    <span class="sub-item">Hak Akses (Role)</span>
                                </a>
                            </li>
                        </ul>
                    </div>
                </li>
                @endcan

                {{-- Profil Pondok --}}
                <li class="nav-item">
                    <a href="#">
                        <i class="fas fa-mosque"></i>
                        <p>Profil Pondok</p>
                    </a>
                </li>

            </ul>
        </div>
    </div>
</div>