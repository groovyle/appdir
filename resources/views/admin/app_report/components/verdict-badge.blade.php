<?php
$badge_color_class = '';
if($verdict->is_innocent == 'innocent') {
	$badge_color_class = 'badge-success';
} else {
	$badge_color_class = 'badge-danger';
}
?>
<span class="badge badge-soft {{ $badge_color_class }} cursor-default" data-toggle="tooltip" title="{{ lang_or_raw($verdict->status.'_explanation', 'admin/app_reports.verdicts_past.') }}" data-custom-class="tooltip-wider">
	<span class="icon-text-pair icon-color-reset">
		<span class="fas fa-gavel icon text-090"></span>
		<span>@langraw($verdict->status, 'admin/app_reports.verdicts_past.')</span>
	</span>
</span>