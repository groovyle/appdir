<?php
$view_only = !!($view_only ?? false);
$show_version_status = !$view_only && !!($show_version_status ?? false);
?>
@include('admin.app.detail-inner', ['is_snippet' => true])

@stack('head-additional')

@section('content')
@if($show_version_status)
<div class="mb-2">
  @lang('admin/apps.changes.version_x_status', ['x' => $version->version]):
  @include('components.app-version-status', ['status' => $version->status])
</div>
@endif

@yield('detail-content')
@show

@stack('scripts')