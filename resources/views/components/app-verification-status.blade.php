<?php
// Pass in $app item
$time = !!($time ?? true);
$placement = $placement ?? 'right';
$newline = $newline ?? false;
?>
{!! status_badge($app->verification_status->name, $app->verification_status->bg_style) !!}
@if ($time && $app->verifications()->count() > 0)
@if($newline) <br> @endif
<div class="d-inline-block small ml-1 pr-1" title="{{ $app->last_verification->updated_at->translatedFormat('j F Y, H:i') }}" data-toggle="tooltip" data-placement="{{ $placement }}" data-trigger="hover click">
  <span class="fa-fw far fa-clock"></span>
  {{ $app->last_verification->updated_at->longRelativeToNowDiffForHumans() }}
  <span class="sr-only">{{ $app->last_verification->updated_at->translatedFormat('j F Y, H:i') }}</span>
</div>
@endif