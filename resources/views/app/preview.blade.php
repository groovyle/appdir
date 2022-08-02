@extends('layouts.preview_app')

@section('content')
<iframe id="content" title="App preview" sandbox="" src="{{ $app->full_url }}" frameborder="0" scrolling="auto"></iframe>
@endsection
