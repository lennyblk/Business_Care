<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Business Care - @yield('title')</title>
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Custom CSS -->
    <link href="{{ asset('css/app.css') }}" rel="stylesheet">
    <!-- Page specific styles -->
    @stack('styles')
</head>
<body>
    @include('layouts.header')

    <main class="py-4">
        @yield('content')
    </main>

    <!-- Bootstrap JS Bundle avec Popper -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
    <!-- Custom JS -->
    <script src="{{ asset('js/app.js') }}"></script>
    <!-- Page specific scripts -->
    @stack('scripts')
</body>
</html>
