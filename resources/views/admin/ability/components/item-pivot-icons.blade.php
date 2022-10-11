@if(!$pivot->forbidden)
<span class="fas fa-check text-success ml-1" title="{{ __('admin/abilities.details.mode_allowed') }}" data-toggle="tooltip"></span>
@else
<span class="fas fa-times text-danger ml-1" title="{{ __('admin/abilities.details.mode_forbidden') }}" data-toggle="tooltip"></span>
@endif