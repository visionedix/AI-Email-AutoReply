<!DOCTYPE html>
<html lang="en" data-bs-theme="light">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ config('app.name', 'Laravel Admin') }} - @yield('title')</title>
    <link rel="stylesheet" href="{{ asset('adminlte/plugins/fontawesome-free/css/all.min.css') }}">
    <link rel="stylesheet" href="{{ asset('adminlte/css/adminlte.min.css') }}">
    @stack('styles')
</head>
<body class="hold-transition login-page bg-light" data-bs-theme="light">
    <div class="login-box">
        @yield('content')
    </div>

    <script src="{{ asset('adminlte/js/adminlte.min.js') }}"></script>
    @stack('scripts')
</body>
</html>
