@php
if(isset($show_errors))
	$messages = array_unique($errors->all());
elseif(isset($message))
	$messages = $message;
elseif(!isset($messages))
	$messages = session($keyname ?? 'messages');
@endphp
@if($messages)
<!-- Messages -->
@php
$messages = (array) $messages;
if(!isset($status)) {
	$status = session('status');
	if(is_string($status)) {
		$status_class = $status;
	} else {
		$status_class = $status == 1 ? 'success' : 'danger';
	}
} else {
	$status_class = $status;
}
@endphp
<div class="alert alert-{{ $status_class }}" role="alert">
@foreach($messages as $message)
	@if(!is_array($message))
	<p class="mb-1">{{ $message }}</p>
	@else
	<ul class="m-0 pl-3">
		@foreach($message as $submessage)
		<li>{{ $submessage }}</li>
		@endforeach
	</ul>
	@endif
@endforeach
</div>
@endif