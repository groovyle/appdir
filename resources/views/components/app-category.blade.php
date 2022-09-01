<?php
// Pass in $category item
$size = $size ?? 'sm';
$color = $color ?? 'default';
$trigger = $trigger ?? 'focus';
$placement = $placement ?? 'top';
$popup_content = (string) $category; // TODO
?>
<span class="btn btn-{{ $size }} btn-{{ $color }} rounded-pill" data-toggle="popover" data-content="{{ $popup_content }}" data-trigger="{{ $trigger }}" data-placement="{{ $placement }}" data-original-title="" title="" tabindex="0">{{ (string) $category }}</span>
