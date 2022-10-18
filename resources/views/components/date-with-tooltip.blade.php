@if(isset($date) || isset($text))
<?php
$format = $format ?? 'j F Y, H:i';
$tip_classes = $tip_classes ?? '';
if(isset($date) && !isset($text)) {
	$date_text = $date->translatedFormat($format);
	$text = isset($text) ? sprintf($text, $date_text) : $date_text;
}
$title = $title ?? $date->longRelativeToNowDiffForHumans();
$reverse = $reverse ?? false;
if($reverse) {
	$tmp = $title;
	$title = $text;
	$text = $tmp;
}
?>
<span class="pr-2" title="{{ $title }}" data-toggle="tooltip" data-placement="right" data-trigger="hover click" data-custom-class="{{ $tip_classes }}">{{ $text }}</span>
@elseif(isset($default))
{!! $default !!}
@else
@vo_
@endif