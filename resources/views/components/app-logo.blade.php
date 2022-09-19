<?php
$width = null;
$height = null;
$style = '';
if(isset($size)) {
	$tmp = explode('x', $size);
	if(count($tmp) == 1)
		$width = $height = $tmp[0];
	else
		list($width, $height) = $tmp;
	$style = sprintf('style="max-width: %spx; max-height: %spx;"', $width, $height);
}
if(isset($exact)) {
	$tmp = explode('x', $exact);
	if(count($tmp) == 1)
		$width = $height = $tmp[0];
	else
		list($width, $height) = $tmp;
	$style = sprintf('style="width: %spx; height: %spx;"', $width, $height);
}

$none = isset($none) ? !!$none : true;

$attrs = '';
if(isset($attributes)) {
	$attrs = html_attributes($attributes);
}

$img_class = $img_class ?? '';
$default = $default ?? false;
$logo_url = null;
if($logo) {
	$logo_url = $logo->url;
} elseif($default) {
	$logo_url = asset('img/default-product-logo-bw.png');
}

$as_link = $as_link ?? true;
$rand = 'logo-'.random_alpha(5);
?>
@section($rand)
<img rel="logo" src="{{ $logo_url }}" class="maxw-100 {{ $img_class }}" {!! $style !!}>
@endsection
@if($logo_url)
@if($as_link)
<a href="{{ $logo_url }}" target="_blank" {!! $attrs !!}>@yield($rand)</a>
@else
@yield($rand)
@endif
@elseif($none)
<span {!! $attrs !!}>@von(null)</span>
@endif