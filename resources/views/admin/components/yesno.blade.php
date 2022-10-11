<?php
$is_negative = falsy($value);
$text = __('admin/common.'. ($is_negative ? 'no' : 'yes'));
$lower = $lower ?? false;
if($lower) $text = strtolower($text);

$colored = $colored ?? true;
$color_no = $color_no ?? 'text-secondary';
$color_yes = $color_yes ?? 'text-success';
$color = $colored ? ($is_negative ? $color_no : $color_yes) : '';
?>
<span class="{{ $color }}">{{ $text }}</span>