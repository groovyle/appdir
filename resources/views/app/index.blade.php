@extends('layouts.app')

@section('content')
<div class="container">
	<h1>browse apps</h1>
	<div>filters: none</div>
	<div class="row justify-content-center">
		<div class="col">
			<div class="card">
				<div class="card-header">Apps</div>

				<div class="card-body">
					@if ($apps->isNotEmpty())
					<div class="row">
						@foreach ($apps as $app)
						<div class="card mx-2 mb-2" style="width: 14rem; line-height: 1.2;">
						  <div class="card-img-top" style="background-color: #868e96; min-height: 3rem; max-height: 7rem; overflow: hidden;">
							@if ($app->visuals_count)
							<img class="d-block mw-100 mh-100 m-auto" src="{{ $app->visual->url }}" alt="Thumbnail">
							@else
							<span>{{ __('app.message.no_visuals') }}</span>
							@endif
						  </div>
						  <div class="card-body p-2">
							<h5><a href="{{ route('apps.page', ['slug' => $app->slug]) }}" class="stretched-link">{{ $app->name }}</a></h5>
							<p class="small mb-0">
							  {!! description_text(Str::limit($app->description, 30)) !!}
							</p>
						  </div>
						</div>
						@endforeach
					</div>
					@else
					<h3>{{ __('app.message.no_apps_yet') }}</h3>
					@endif
				</div>
			</div>
		</div>
	</div>
</div>
@endsection
