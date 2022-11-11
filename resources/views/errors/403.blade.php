@extends('layouts.error')

@section('title', __('errors.forbidden_title'))
@section('code', '403')
@section('code-title', __('errors.forbidden_title'))
@section('message', __($exception->getMessage()) ?: __('errors.forbidden_description'))
