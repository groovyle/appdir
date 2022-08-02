
@include('admin.app.detail-inner', ['is_snippet' => true])

@stack('head-additional')

@yield('content')

@stack('scripts')