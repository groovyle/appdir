@if(trim($slot))
<a href="#" class="d-inline-block ml-2 text-warning text-090 old-value-pop" title="{{ __('admin/apps.changes.old_value') }}" data-content="{{ e($slot) }}" data-toggle="popover" data-trigger="focus" data-html="true"><span class="fas fa-history"></span></a>
@endif