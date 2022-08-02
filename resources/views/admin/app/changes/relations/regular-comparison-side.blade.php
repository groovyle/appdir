
	<div class="title">{{ $title }}</div>
	@if(!empty($rel))
	<ol class="value">
	@foreach($rel as $relitem)
		<li>{{ $relitem }}</li>
	@endforeach
	</ol>
	@else
	<span class="value">{!! $getval($rel) !!}</span>
	@endif