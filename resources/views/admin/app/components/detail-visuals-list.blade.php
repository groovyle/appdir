<?php
$smaller = !!($smaller ?? false);
// $smaller = $smaller ? 'scale-smaller' : '';
$smaller = $smaller ? 'thumb-cards-sm' : '';
?>
<div class="thumb-cards {{ $smaller }}">
@foreach ($visuals as $visual)
@php
$i = $loop->iteration;
@endphp
<div class="thumb-item">
  <div class="card">
    <a class="card-img-top" href="{{ $visual->url }}" target="_blank">
      <img src="{{ $visual->thumbnail_url }}" alt="{{ __('common.visual').' '.$i }}">
    </a>
    <div class="card-body">
      <p class="card-text text-center text-secondary">
        <a href="#" class="cursor-pointer text-reset d-inline-block px-1" role="button" tabindex="0" title="{{ __('admin/apps.fields.caption') }}" data-content='<span class="text-pre-wrap">{{ voe($visual->caption, false) }}</span>' data-toggle="popover" data-trigger="hover focus" data-placement="right" data-html="true">({{ $i }})</a>
        @if($visual->type == 'image')
        @elseif($visual->type == 'video')
        &nbsp;<a href="{{ $visual->url }}" target="_blank" class="ml-n1">{{ $visual->url }}</a>
        @endif
      </p>
    </div>
  </div>
</div>
@endforeach
</div>