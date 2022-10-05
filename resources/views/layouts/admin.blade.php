<!DOCTYPE html>
<html>

<head>
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
    <meta name="description" content="Start your development with a Dashboard for Bootstrap 4.">
    <meta name="author" content="Creative Tim">

    <!-- Scripts -->
    <script src="{{ asset('js/app.js') }}" defer></script>
    <!-- Styles -->
    <link href="{{ asset('css/app.css') }}" rel="stylesheet">

    <script src="{{ asset('assets/libs/fontawesome.js') }}" crossorigin="anonymous"></script>

    <title>Administrador</title>
    <link rel="stylesheet" href="{{ asset('css/admin/style.css') }}">
    <link rel="stylesheet" href="{{ asset('assets/css/styles.css') }}">

</head>

<body class="sb-nav-fixed">
    <!-- Scripts -->
    <script src="{{ asset('assets/libs/jquery-3.6.1.min.js') }}"></script>
    <script src="{{ asset('assets/js/scripts.js') }}"></script>
    <div id="app">
        @yield('content')
        @yield('script')
    </div>
</body>



</html>
