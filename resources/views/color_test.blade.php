@extends('layouts.app')

@section('content')
<div class="container">
	<div class="card shadow-sm">
		<div class="card-header">
			<div class="float-right">
				<span class="mr-1">Test text color:</span>
				<button type="button" class="btn btn-dark btn-sm btn-test-text-color" data-target-chroma="dark">Light</button>
				<button type="button" class="btn btn-light bordered btn-sm btn-test-text-color" data-target-chroma="light">Dark</button>
				<button type="button" class="btn btn-link btn-sm btn-test-text-color" data-target-chroma="reset">Reset</button>
			</div>
			<div>Color Schemes Test ({{ $schemes->count() }})</div>
			<div>
				<form method="GET" action="{{ route('color_test') }}" id="formSearch">
					<div class="form-inline">
						<input type="text" name="search" value="{{ request()->input('search') }}" placeholder="search keyword" class="form-control form-control-sm" style="width: 200px; max-width: 100%;">
						<button type="submit" class="btn btn-sm btn-primary ml-2">submit</button>
					</div>
					<div>
						<div class="form-check form-check-inline">
							<input type="radio" class="form-check-input" name="theme" value="light" id="searchChromaLight" @if(request()->input('theme') == 'light') checked @endif>
							<label class="form-check-label" for="searchChromaLight">light</label>
						</div>
						<div class="form-check form-check-inline">
							<input type="radio" class="form-check-input" name="theme" value="dark" id="searchChromaDark" @if(request()->input('theme') == 'dark') checked @endif>
							<label class="form-check-label" for="searchChromaDark">dark</label>
						</div>
						<div class="form-check form-check-inline">
							<input type="radio" class="form-check-input" name="theme" value="flexible" id="searchChromaFlexible" @if(request()->input('theme') == 'flexible') checked @endif>
							<label class="form-check-label" for="searchChromaFlexible">flexible</label>
						</div>
						<div class="form-check form-check-inline">
							<input type="radio" class="form-check-input" name="theme" value="" id="searchChromaAll" @if(request()->input('theme') == null) checked @endif>
							<label class="form-check-label" for="searchChromaAll">all</label>
						</div>
					</div>
				</form>
			</div>
		</div>
		<div class="card-body p-0">
			@if($schemes->count() == 0)
			<h4>Nothing here yet...</h4>
			@else
			<div id="colorsCarousel" class="carousel slide" data-interval="false">
				<div class="carousel-inner">
					@foreach($schemes as $i => $s)
					@php
					$bg = $s->chroma == 'both' ? 'gray' : ($s->chroma == 'light' ? 'gray-dark' : 'light');
					@endphp
					<div class="carousel-item @if($i == 0) active @endif py-3 px-5 text-center" data-itemdata="{{ json_encode($s) }}">
						<div class="d-inline-block text-left">
							<h5>
								<samp class="mr-1">#{{ $i + 1 }}</samp>
								<span class="px-1 bg-{{ $bg }}" style="color: {{ substr($s->color, 0, 7) }};">{{ $s->name }}</span>
								<span>(ID: {{ $s->id }})</span>
								<br>
								<small class="">Chroma: <span class="@if($s->chroma == 'both') font-weight-bold @endif">{{ ucwords($s->chroma) }}</span></small>
								<br>
								<small>Text color:</small>
								@if(in_array($s->chroma, ['light', 'both']))
								<button type="button" class="btn btn-light btn-sm py-0 px-1 border-secondary btn-test-text-color btn-test-text-color-light" data-target-chroma="light">Dark on light</button>
								@endif
								@if(in_array($s->chroma, ['dark', 'both']))
								<button type="button" class="btn btn-dark btn-sm py-0 px-1 btn-test-text-color btn-test-text-color-dark" data-target-chroma="dark">Light on dark</button>
								@endif
							</h5>
							<p>{{ $s->description }}</p>
							<p>
								Colors:
								<span class="d-inline-flex" style="column-gap: 0.25rem;">
								@foreach($s->colors as $color)
								@php
								$color_only = substr($color, 0, 7);
								@endphp
								<samp class="px-1 border border-secondary" style="color: {{ $color_only }};">{{ $color }}</samp>
								@endforeach
								</span>
								<br>
								Gradient angle: <samp>@vo_($s->gradient_angle)</samp>
							</p>
							<p class="text-muted">Notes: @vo_($s->notes)</p>
						</div>
					</div>
					@endforeach
				</div>
				<a class="carousel-control-prev" role="button" href="#colorsCarousel" data-slide="prev">
					<span class="carousel-control-prev-icon" aria-hidden="true"></span>
					<span class="sr-only">Prev</span>
				</a>
				<a class="carousel-control-next" role="button" href="#colorsCarousel" data-slide="next">
					<span class="carousel-control-next-icon" aria-hidden="true"></span>
					<span class="sr-only">Next</span>
				</a>
			</div>
			@endif
		</div>
	</div>
</div>
@endsection

@push('styles')
<style>
	#navbar, #navbar .nav-link, #navbar .navbar-brand,
	#footer {
		transition: background 0.75s, color 0.75s;
	}
	.carousel-control-prev,
	.carousel-control-next {
		background-color: rgba(0,0,0,0.25);
	}
</style>
@endpush

@push('scripts')
<script>
jQuery(document).ready(function($) {
	var $navbar = $("#navbar"),
			$footer = $("#footer")
	;
	var $carousel = $("#colorsCarousel");

	function activateItemColors(index) {
		if(typeof index === "undefined")
			index = $carousel.find(".carousel-inner > .carousel-item.active").index();

		var $item = $carousel.find(".carousel-inner > .carousel-item").eq(index);
		var data = $item.data("itemdata");
		if(!data)
			return;

		var gradientAngle = data.gradient_angle || "45deg";
		var gradient = `linear-gradient(${gradientAngle}, ${data.colors})`;

		$navbar.add($footer).css({
			backgroundImage: gradient,
			backgroundColor: data.colors[0],
		});
		activateTextColor(data.chroma);
	}

	function activateTextColor(color) {
		$navbar.removeClass("navbar-light navbar-dark");
		$navbar.addClass(color == "dark" ? "navbar-dark" : "navbar-light");

		$footer.removeClass("text-light text-dark");
		$footer.addClass(color == "dark" ? "text-light" : "text-dark");
	}

	$carousel.on("slid.bs.carousel", function(e) {
		activateItemColors(e.to);
	});

	$(document).on("click", ".btn-test-text-color", function(e) {
		var targetChroma = $(this).data("targetChroma");
		if(!targetChroma)
			return;

		if(targetChroma != "reset")
			activateTextColor(targetChroma);
		else
			activateItemColors();
	});

	// On load, trigger event immediately
	activateItemColors();
});
</script>
@endpush
