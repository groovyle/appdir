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
	if(isset($status)) {
		if(is_string($status)) {
			$status_class = $status;
		} else {
			$status_class = $status == 1 ? 'success' : 'danger';
		}
	} elseif(isset($show_errors) && $show_errors) {
		$status_class = 'danger';
	} else {
		$status_class = 'info';
	}
} else {
	$status_class = $status;
}
$dismiss = $dismiss ?? false;
@endphp
<div class="alert alert-{{ $status_class }}" role="alert">
@if($dismiss)
<button type="button" class="close" data-dismiss="alert" aria-label="Close">
	<span aria-hidden="true">&times;</span>
</button>
@endif
@foreach($messages as $message)
	@if(!is_array($message))
	<p>{{ $message }}</p>
	@else
	<ul class="pl-4 my-1">
		@foreach($message as $submessage)
		<li>{{ $submessage }}</li>
		@endforeach
	</ul>
	@endif
@endforeach
</div>
@endif