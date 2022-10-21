<?php
$size = $size ?? 'text-100';
$is_blocked = $is_blocked ?? $user->is_blocked ?? false;
if($is_blocked) {
	$icon = 'fas fa-ban text-danger';
	$title = __('admin/users.statuses.blocked');
} else {
	$icon = 'fas fa-check text-success';
	$title = __('admin/users.statuses.active');
}
$with_text = $with_text ?? false;
$text = $with_text ? $title : '';
?>
@if($with_text)
<span class="icon-text-pair px-1">
	<span class="{{ $icon }} {{ $size }} {{ $classes ?? '' }}" title="{{ $title }}" data-toggle="tooltip"></span>
	<span>{{ $text }}</span>
</span>
@else
<span class="{{ $icon }} {{ $size }} {{ $classes ?? '' }} px-1" title="{{ $title }}" data-toggle="tooltip"></span>
@endif