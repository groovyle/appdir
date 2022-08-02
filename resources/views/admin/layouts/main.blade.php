<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
  <meta charset="utf-8">
  <meta http-equiv="X-UA-Compatible" content="IE=edge">
  <meta name="viewport" content="width=device-width, initial-scale=1">

  <!-- CSRF Token -->
  <meta name="csrf-token" content="{{ csrf_token() }}">

  @section('title', config('app.name'))
  <title>@yield('title')</title>

  <!-- Fonts -->
  <!-- Font Awesome -->
  <link rel="stylesheet" href="{{ asset('plugins/fontawesome-free/css/all.min.css') }}">
  <!-- Ionicons -->
  <link rel="stylesheet" href="https://code.ionicframework.com/ionicons/2.0.1/css/ionicons.min.css">

  <link rel="stylesheet" href="{{ asset('plugins/toastr/toastr.min.css') }}">

  @stack('load-styles')

  <!-- Theme style -->
  <link rel="stylesheet" href="{{ asset('css/adminlte.min.css') }}">
  <link rel="stylesheet" href="{{ asset('css/admin.css') }}">
  <!-- Google Font: Source Sans Pro -->
  <link href="https://fonts.googleapis.com/css?family=Source+Sans+Pro:300,400,400i,700" rel="stylesheet">

  @stack('styles')

  @stack('head-additional')
</head>
<body class="hold-transition sidebar-mini">
<div class="wrapper" id="app">
  @include('admin.layouts.main-navbar')

  @include('admin.layouts.main-sidebar')

  <main class="content-wrapper pb-3">
    @section('content-header')
    <!-- Content Header (Page header) -->
    <section class="content-header">
      <div class="container-fluid">
        <div class="row mb-2">
          <div class="col-12">
            @include('admin.layouts.main-breadcrumb')
          </div>
          <div class="col-12">
            <h1 class="mt-3 mt-md-2">@yield('page-title')</h1>
          </div>
        </div>
      </div><!-- /.container-fluid -->
    </section>
    @show

    @section('content-outer')
    <!-- Main content -->
    <section class="content">
      <div class="container-fluid">
        @yield('content')
      </div>
    </section>
    <!-- /.content -->
    @show
  </main>

  <footer class="main-footer">
    <div class="float-right d-none d-sm-block">
      <b>Version</b> 3.0.4
    </div>
    <strong>Copyright &copy; 2014-2019 <a href="http://adminlte.io">AdminLTE.io</a>.</strong> All rights
    reserved.
  </footer>

</div>

<!-- Scripts -->
<script>
window.AppGlobals = {
  lang: @json(app()->getLocale()),
}
</script>
<script src="{{ asset('js/app.js') }}"></script>

<!-- AdminLTE App -->
<script src="{{ asset('js/adminlte.min.js') }}"></script>
@include('admin.layouts.toast-notification')

@stack('load-scripts')

<script src="{{ asset('js/helpers.js') }}"></script>
<script src="{{ asset('js/admin.js') }}"></script>

@stack('scripts')

</body>
</html>
