<!doctype html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no, maximum-scale=1.0, user-scalable=0">
    <title>{{ config('app.name', 'Live') }}</title>
    <link href="{{ asset('/css/app.css') }}" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/open-iconic/1.1.1/font/css/open-iconic-bootstrap.min.css" rel="stylesheet">
    @yield('header')
</head>
<body>
    <header id="nav-main" class="navbar navbar-expand-lg navbar-dark d-none d-md-block">
        <div class="container py-1">
            <a class="navbar-brand" href="/" style="font-size:1.3rem;"><img src="{{ config('app.logo') }}" /></a>
            <ul class="navbar-nav mr-auto">
                <li class="nav-item px-2">
                <a class="nav-link" href="#"><span class="oi oi-data-transfer-download"></span>&nbsp;&nbsp;{{ __('home.app_download') }}</a>
                </li>
            </ul>
            <span class="navbar-text small d-none d-md-block">
                Ctrl + D
            </span>
        </div>
    </header>
    <main>
        @yield('main')
    </main>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jquery/3.4.1/jquery.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/popper.js/1.15.0/umd/popper.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/twitter-bootstrap/4.3.1/js/bootstrap.min.js"></script>
    @yield('footer')
</body>
</html>