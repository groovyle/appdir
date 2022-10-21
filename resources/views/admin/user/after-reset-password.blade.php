<?php
$append_breadcrumb = [
  [
    'text'    => $user->name,
  ]
];
$rand = random_string(5);
?>

@extends('admin.layouts.main')

@section('title')
{{ __('admin/users.tab_title.reset_password', ['x' => text_truncate($user->name, 20)]) }} - @parent
@endsection

@section('page-title', __('admin/users.page_title.reset_password'))

@section('content')
<div class="mb-2">
  @if($back)
  <a href="{{ $back }}" class="btn btn-sm btn-default">&laquo; {{ __('common.back') }}</a>
  @endif
</div>

<div class="card main-content scroll-to-me" id="afterResetPassword">
  <div class="card-body">
    <div class="row gutter-lg">
      <div class="col-12 col-md-6 my-5 mx-auto text-center">
        <div class="text-bold text-primary">@lang('admin/users.password_has_been_reset_for_user')</div>
        <div class="text-120 mb-3">
          {{ $user->name_email }}
          @if($user->prodi)
          <br><span class="text-080 text-secondary">{{ $user->prodi->complete_name }}</span>
          @endif
        </div>
        <div class="text-bold text-110 text-primary mb-1">@lang('admin/users.the_new_password_is'):</div>
        @component('admin.slots.box-copy-info')
        {{ $new_pass }}
        @endcomponent
        <div class="mt-3">@lang('admin/users.take_note_of_new_password')</div>

        <hr>

        @if($back)
        <a href="{{ $back }}" class="btn btn-primary btn-min-100">{{ __('admin/common.done') }}</a>
        @endif
      </div>
    </div>
  </div>
</div>

@endsection

@push('scripts')
<script type="text/javascript">
jQuery(document).ready(function($) {
  Helpers.scrollTo("#afterResetPassword", {
    offset: 50,
  });
});
</script>
@endpush
