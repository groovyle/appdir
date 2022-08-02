@extends('layouts.app')

@section('content')
<div class="container">
    <div class="card">
        <h2 class="card-header">{{ $app->name }}</h2>

        <div class="card-body">
            <div class="row justify-content-center">
                <div class="col col-sm-8 border-right">
                    {!! description_text($app->description) !!}
                    <div class="app-tags">
                        <small class="font-weight-bold">Tags</small>
                        <br>
                        @forelse ($app->tags as $tag)
                        <a href="#" class="btn btn-sm btn-light border rounded-pill" data-toggle="popover" data-content="{{ $tag->name }}" data-trigger="focus" data-placement="top">{{ $tag->name }}</a>
                        @empty
                        &ndash;
                        @endforelse
                    </div>
                    <hr>
                    <div>
                        URL: <a href="{{ $app->full_url }}" target="_blank">{{ $app->full_url }} <span class="fa-fw fas fa-external-link-alt"></span></a>
                    </div>
                    <div>
                        <a href="{{ route('apps.preview', [$app->slug]) }}">Preview</a>
                    </div>
                    @if ($app->visuals_count)
                    <div class="row mx-n1">
                        @foreach ($app->visuals as $visual)
                        @php
                        $i = $loop->iteration;
                        @endphp
                        <div class="col w-auto px-1 mr-1 mb-1 flex-grow-0">
                            <div class="border bg-white d-flex justify-content-center align-items-stretch" style="width: 8rem; height: 8rem;">
                                <a href="{{ $visual->url }}" class="d-flex justify-content-center align-items-stretch overflow-hidden" data-toggle="lightbox" data-gallery="visuals">
                                    <img src="{{ $visual->url }}" class="mh-100" alt="visual {{ $i }}">
                                </a>
                            </div>
                        </div>
                        @endforeach
                    </div>
                    @endif
                </div>
                <div class="col col-sm-4">
                    <h4>Author</h4>
                    <p>{{ $app->owner->name }}</p>
                    <dl>
                        <dt>Date Published</dt>
                        <dd>{{ $app->last_verification->updated_at->translatedFormat('j F Y, H:i') }}</dd>

                        <dt>Categories</dt>
                        <dd>
                            <ul class="pl-3">
                                @foreach ($app->categories as $category)
                                <li>{{ $category->name }}</li>
                                @endforeach
                            </ul>
                        </dd>
                    </dl>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@section('scripts')
@parent
<script>
jQuery(document).ready(function($) {
    $(document).on('click', '[data-toggle="lightbox"]', function(e) {
        e.preventDefault();
        $(this).ekkoLightbox({
            // alwaysShowClose: true
        });
    });
    $('[data-toggle="popover"]').popover({
        container: "body",
    });
});
</script>
@endsection
