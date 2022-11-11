@extends('layouts.error')

@section('title', __('errors.too_many_requests_title'))
@section('code', '429')
@section('code-title', __('errors.too_many_requests_title'))
@section('message', __('errors.too_many_requests_description'))
