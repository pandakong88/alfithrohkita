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