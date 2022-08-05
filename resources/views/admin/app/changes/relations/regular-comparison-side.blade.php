
	<div class="title">{{ $title }}</div>
	@if(!empty($rel) && count($rel) > 0)
	<ol class="value">
	@foreach($rel as $relitem)
		<li>{{ $relitem }}</li>
	@endforeach
	</ol>
	@else
	<span class="value">@empty_text()</span>
	@endif