<?php
$width = null;
$height = null;
if(isset($size)) {
	$tmp = explode('x', $size);
	if(count($tmp) == 1)
		$width = $height = $tmp[0];
	else
		list($width, $height) = $tmp;
}

$style = sprintf('style="max-width: %spx; max-height: %spx;"', $width, $height);
$none = isset($none) ? !!$none : true;

$attrs = '';
if(isset($attributes)) {
	$attrs = html_attributes($attributes);
}
?>
@if($logo)
<a href="{{ $logo->url }}" target="_blank" {!! $attrs !!}><img rel="logo" src="{{ $logo->url }}" class="maxw-100" {!! $style !!}></a>
@elseif($none)
<span {!! $attrs !!}>@von(null)</span>
@endif