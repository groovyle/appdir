@if(isset($verdict->details['block_user']) && $verdict->details['block_user'])
<span class="badge badge-soft badge-warning cursor-default" data-toggle="tooltip" title="{{ __('admin/app_reports.verdicts_past.guilty_block_user').': '.__('admin/app_reports.verdicts_past.guilty_block_user_explanation') }}" data-custom-class="tooltip-wider"><span class="fas fa-user-times text-090"></span></span>
@endif