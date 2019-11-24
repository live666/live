<!doctype html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no, maximum-scale=1.0, user-scalable=0">
    <title>@yield('title', config('html.title'))</title>

    <meta name="description" content="{{ config('html.description') }}">
    <meta name="keywords" content="{{ config('html.keywords') }}">

    <meta name="apple-mobile-web-app-title" content="{{ config('app.name', 'Live') }}">
    <meta name="application-name" content="{{ config('app.name', 'Live') }}">
    <meta name="apple-mobile-web-app-capable" content="yes">
    <link rel="apple-touch-icon" href="{{ config('html.apple_touch_icon') }}">

    <link href="/css/app.min.css" rel="stylesheet">
    <link href="https://cdn.staticfile.org/open-iconic/1.1.1/font/css/open-iconic-bootstrap.min.css" rel="stylesheet">
    @yield('header')
</head>
<body>
    <header id="nav-main" class="navbar navbar-expand-lg navbar-dark d-none d-md-block">
        <div class="container py-1">
            <a class="navbar-brand" href="{{ route('home', [], false) }}" style="font-size:1.3rem;"><img src="{{ config('app.logo') }}" /></a>
            <ul class="navbar-nav mr-auto">
                <li class="nav-item px-2" style="display:none;">
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
    <script src="https://cdn.staticfile.org/jquery/3.4.1/jquery.min.js"></script>
    <script src="https://cdn.staticfile.org/popper.js/1.15.0/umd/popper.min.js"></script>
    <script src="https://cdn.staticfile.org/twitter-bootstrap/4.3.1/js/bootstrap.min.js"></script>
    @yield('footer')

    @if (config('html.baidu_tongji_id'))
    <script>
        var _hmt = _hmt || [];
        (function() {
            var hm = document.createElement("script");
            hm.src = "https://hm.baidu.com/hm.js?{{ config('html.baidu_tongji_id') }}";
            var s = document.getElementsByTagName("script")[0]; 
            s.parentNode.insertBefore(hm, s);
        })();
    </script>
    @endif
</body>
</html>