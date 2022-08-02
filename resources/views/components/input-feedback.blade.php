@if(isset($name) && $errors->has($name))
<div class="invalid-feedback">{!! implode('. ', $errors->get($name)) !!}</div>
@endif