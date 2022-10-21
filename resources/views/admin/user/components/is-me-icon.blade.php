<?php
$size = $size ?? 'text-080';
$is_me = $is_me ?? $user->is_me ?? false;
?>
@if($is_me)
<span class="fas fa-user text-primary {{ $size }} ml-2" title="{{ __('admin/users.this_is_you') }}" data-toggle="tooltip"></span>
@endif