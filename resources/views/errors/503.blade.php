@extends('layouts.error')

@section('title', __('errors.service_unavailable_title'))
@section('code', '503')
@section('code-title', __('errors.service_unavailable_title'))
@section('message', __($exception->getMessage()) ?: __('errors.service_unavailable_description'))
