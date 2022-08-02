@extends('admin.layouts.main')

@section('title')
{{ __('admin.app.tab_title') }} - @parent
@endsection

@section('page-title', __('admin.app.page_title.detail'))

@include('admin.app.detail-inner')