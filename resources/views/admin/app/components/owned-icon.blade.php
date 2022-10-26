<?php
$size = $size ?? 'text-080';
$is_owned = $is_owned ?? $app->is_owned ?? false;
$margin = $margin ?? 'ml-2';
?>
@if($is_owned)
<span class="fas fa-house-user text-primary {{ $size }} {{ $margin }}" title="{{ __('admin/apps.you_own_this_app') }}" data-toggle="tooltip"></span>
@endif