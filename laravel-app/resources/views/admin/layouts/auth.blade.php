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
<body class="hold-transition bg-light" data-bs-theme="light">
    <style>
        html, body {
            min-height: 100vh;
            width: 100%;
            margin: 0;
            padding: 0;
            overflow-x: hidden;
        }

        .auth-fullscreen {
            min-height: 100vh;
            width: 100%;
        }

        .auth-fullscreen > * {
            width: 100%;
            margin: 0;
        }
    </style>

    <div class="auth-fullscreen">
        @yield('content')
    </div>

    <script src="{{ asset('adminlte/js/adminlte.min.js') }}"></script>
    @stack('scripts')
</body>
</html>
