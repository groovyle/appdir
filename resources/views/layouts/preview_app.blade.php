<!doctype html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="preview-page">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">

    <!-- CSRF Token -->
    <meta name="csrf-token" content="{{ csrf_token() }}">

    @section('title', config('app.name'))
    <title>{{ trim(View::yieldContent('title')) }}</title>

    @section('styles')
    <link rel="stylesheet" href="{{ asset('plugins/toastr/toastr.min.css') }}">
    <link href="{{ asset('plugins/ekko-lightbox/ekko-lightbox.css') }}" rel="stylesheet">
    @show

    <!-- Fonts -->
    <link rel="stylesheet" href="{{ asset('plugins/fontawesome-free/css/all.min.css') }}">
    <link rel="dns-prefetch" href="//fonts.gstatic.com">
    <link href="https://fonts.googleapis.com/css?family=Nunito" rel="stylesheet">

    <!-- Styles -->
    <link href="{{ asset('css/app.css') }}" rel="stylesheet">
    <link href="{{ asset('css/frontend.css') }}" rel="stylesheet">

    @section('head-additional')
    @show
</head>
<body>
    <div id="app">
        <nav class="navbar navbar-expand-md navbar-light fixed-top bg-white shadow-sm py-0">
            <div class="container">
                <a class="navbar-brand" href="{{ url('/') }}">
                    {{ config('app.name', 'Laravel') }}
                </a>
                <button class="navbar-toggler" type="button" data-toggle="collapse" data-target="#navbarSupportedContent" aria-controls="navbarSupportedContent" aria-expanded="false" aria-label="{{ __('Toggle navigation') }}">
                    <span class="navbar-toggler-icon"></span>
                </button>

                <div class="collapse navbar-collapse" id="navbarSupportedContent">
                    <ul class="navbar-nav ml-auto mr-auto">
                        <li><h4 class="m-0">{{ $app->name }}</h4></li>
                    </ul>

                    <!-- Right Side Of Navbar -->
                    <ul class="navbar-nav">
                        <li>
                        </li>
                        <li>
                            <a href="{{ route('apps.page', [$app->slug]) }}" class="btn btn-sm btn-secondary">Back to app page</a>
                            <a href="{{ $app->full_url ?? '#' }}" class="btn btn-sm btn-success" id="btn-go-to-live" data-content="Click here to go to the live site">
                                Go to live site
                                <span class="fa-fw fas fa-external-link-alt"></span>
                            </a>
                        </li>
                    </ul>
                </div>
            </div>
        </nav>

        <main>
            @yield('content')
        </main>
    </div>

    <!-- Scripts -->
    <script src="{{ asset('js/app.js') }}"></script>

    @section('scripts')
    <script src="{{ asset('plugins/toastr/toastr.min.js') }}"></script>
    <script src="{{ asset('plugins/ekko-lightbox/ekko-lightbox.min.js') }}"></script>

    <script>
    jQuery(document).ready(function($) {
        $("#btn-go-to-live").popover({
            placement: "bottom",
            trigger: "hover"
        }).on("shown.bs.popover", function(e) {
            var $this = $(this),
                pop = $this.data("bs.popover");
            if(pop && pop.tip) {
                var $tip = $(pop.tip);
                $tip.addClass("popover-bounce-slow");
            }
            setTimeout(function() {
                $this.popover("hide");
            }, 4000);
        }).popover("show");
    });
    </script>
    @show

</body>
</html>
