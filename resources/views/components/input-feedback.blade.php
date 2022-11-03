@if(isset($name) && $errors->has($name))
<?php
$all = $all ?? false;
$force = is_string($force ?? false) ? $force : 'd-block';
$wrap = $wrap ?? false;
$wrap_class = $wrap_class ?? (is_string($wrap) ? $wrap : 'mt-1');
$msgs = $all ? $errors->get($name) : [$errors->first($name)];
?>
@if($wrap) <div class="{{ $wrap_class }}"> @endif
@foreach($msgs as $msg)
<div class="invalid-feedback {{ $force }}">{{ $msg[0] }}</div>
@endforeach
@if($wrap) </div> @endif
@endif