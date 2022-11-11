@extends('layouts.error')

@section('title', __('errors.unauthorized_title'))
@section('code', '401')
@section('code-title', __('errors.unauthorized_title'))
@section('message', __('errors.unauthorized_description'))
