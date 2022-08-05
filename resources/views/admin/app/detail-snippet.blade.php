
@include('admin.app.detail-inner', ['is_snippet' => true])

@stack('head-additional')

@section('content')
  @yield('detail-content')
@show

@stack('scripts')