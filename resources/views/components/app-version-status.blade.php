<?php
$add_class = $class ?? '';
if($status == 'rejected') {
	$class = 'danger';
} elseif($status == 'approved') {
	$class = 'info';
} elseif($status == 'committed') {
	$class = 'success';
} else {
	$class = 'warning';
}
$class .= ' font-weight-normal '. $add_class;
$text = __('admin/apps.changes.statuses.'.$status);
?>
{!! status_badge($text, $class) !!}