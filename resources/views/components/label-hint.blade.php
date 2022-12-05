<?php
$icon = $icon ?? 'far fa-question-circle';
$color = $color ?? 'text-muted';
?>
<a href="#" class="d-inline-block ml-1 init-popover {{ $color }}" data-toggle="popover" data-content="{!! $slot !!}" data-trigger="focus" data-html="true"><span class="{{ $icon }}"></span></a>