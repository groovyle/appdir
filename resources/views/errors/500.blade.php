@extends('layouts.error')

@section('title', __('errors.internal_server_error_title'))
@section('code', '500')
@section('code-title', __('errors.internal_server_error_title'))
@section('message', __($exception->getMessage()) ?: __('errors.internal_server_error_description'))
