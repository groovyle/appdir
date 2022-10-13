<?php
$size = $size ?? 'text-080';
$is_owned = $is_owned ?? $app->is_owned ?? false;
?>
@if($is_owned)
<span class="fas fa-house-user text-primary {{ $size }} ml-2" title="{{ __('admin/apps.you_own_this_app') }}" data-toggle="tooltip"></span>
@endif