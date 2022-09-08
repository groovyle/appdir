<?php
$format = $format ?? 'j F Y, H:i';
$tip_classes = $tip_classes ?? '';
$date_text = $date->translatedFormat($format);
$text = isset($text) ? sprintf($text, $date_text) : $date_text;
$reverse = $reverse ?? false;
if($reverse) {
	$tmp = $date_text;
	$date_text = $text;
	$text = $tmp;
}
?>
<span class="pr-2" title="{{ $date->longRelativeToNowDiffForHumans() }}" data-toggle="tooltip" data-placement="right" data-trigger="hover click" data-custom-class="{{ $tip_classes }}">{{ $text }}</span>