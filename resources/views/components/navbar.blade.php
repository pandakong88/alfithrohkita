<nav class="navbar navbar-header navbar-expand-lg border-bottom">
    <div class="container-fluid">

        <ul class="navbar-nav ms-md-auto align-items-center">

            <li class="nav-item topbar-user dropdown hidden-caret">
                <a class="dropdown-toggle profile-pic" data-bs-toggle="dropdown" href="#">
                    <div class="avatar-sm">
                        <img src="{{ asset('kaiadmin/assets/img/profile.jpg') }}"
                             class="avatar-img rounded-circle" />
                    </div>
                    <span class="profile-username">
                        <span class="op-7">Hi,</span>
                        <span class="fw-bold">{{ auth()->user()->name ?? 'User' }}</span>
                    </span>
                </a>

                <ul class="dropdown-menu dropdown-user">
                    <li>
                        <form method="POST" action="{{ route('logout') }}">
                            @csrf
                            <button class="dropdown-item">Logout</button>
                        </form>
                    </li>
                </ul>
            </li>

        </ul>
    </div>
</nav>