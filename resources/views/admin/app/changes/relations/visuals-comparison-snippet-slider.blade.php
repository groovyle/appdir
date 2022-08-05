
		<h5>{{ $title }} ({{ count($items) }})</h5>
		@if(count($items) == 0)
		<h3 class="text-center my-4">&mdash; @lang('admin/apps.visuals.no_visuals') &mdash;</h3>
		@else
		<div class="splide mx-auto" id="{{ $rand }}-{{ $name }}" aria-labelledby="{{ $rand }}-{{ $name }}-title">
			<h5 id="{{ $rand }}-{{ $name }}-title" style="display: none;" aria-hidden="yes">{{ $title }} ({{ count($items) }})</h5>
			<div class="splide__track">
				<ul class="splide__list">
					@foreach($items as $item)
					@if($item->type == 'image' || ($image_only_mode ?? false))
					<li class="splide__slide">
						<div class="splide__slide__container">
							<img src="{{ $item->thumbnail_url }}" >
						</div>
						@if($image_only_mode && $item->type == 'video')
						<div class="splide-caption">
							( <a href="{{ $item->url }}" target="_blank">{{ $item->url }}</a> )
							@if($item->caption) <br>{{ $item->caption }} @endif
						</div>
						@else
						<div class="splide-caption">@if($item->caption) {{ $item->caption }} @endif</div>
						@endif
					</li>
					@elseif($item->type == 'video')
					<li class="splide__slide splide-video" data-splide-youtube="{{ $item->embed_url }}">
						<div class="splide__slide__container">
							<img src="{{ $item->thumbnail_url }}" >
						</div>
						<div class="splide-caption">@if($item->caption) {{ $item->caption }} @endif</div>
					</li>
					@endif
					@endforeach
				</ul>
			</div>
		</div>
		@endif