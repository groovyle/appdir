<?php
$linked_based_on = $linked_based_on ?? false;
$new_attrs = $cl->display_diffs['attributes']['new'] ?? $cl->display_diffs['attributes'] ?? [];
$old_attrs = $cl->display_diffs['attributes']['old'] ?? [];
$long_attrs = ['description'];
$relations = $cl->display_diffs['relations'] ?? [];
$regular_relations = ['categories', 'tags'];
?>
@if($cl->based_on)
<div class="mb-1">
	@if($cl->is_switch)
	<div class="text-secondary text-bold text-italic mt-n2 mb-2">
		@lang('admin/apps.changes.this_version_was_a_result_of_version_switch')
	</div>
	@endif
	@lang('admin/apps.changes.this_version_is_based_on'):
	@if($linked_based_on)
	<a href="{{ route('admin.apps.changes', ['app' => $app->id, 'go_version' => $cl->based_on->version]) }}" class="btn-goto-version" data-goto-version="{{ $cl->based_on->version }}">@lang('admin/apps.changes.version_x', ['x' => $cl->based_on->version])</a>
	@else
	@lang('admin/apps.changes.version_x', ['x' => $cl->based_on->version])
	@endif
</div>
@endif
@if(!isset($cl->display_diffs['is_new']) || !$cl->display_diffs['is_new'])
<p class="mb-1">@lang('admin/apps.changes.there_are_x_changes', ['x' => count($new_attrs) + count($relations)])</p>
@else
<p class="mb-1">@lang('admin/apps.changes.new_item')</p>
@endif

@if(!empty($new_attrs))
<div class="changes-attributes">
	<div class="row">
	@foreach($new_attrs as $k => $v)
	@if(!in_array($k, $long_attrs) && strlen($v) < 100)
	<div class="item field-group short-attribute col-lg-4 col-sm-6 col-12">
		<div class="title">@lang('admin/apps.fields.'. $k)</div>
		<div class="comparison">
			<span class="value old-value text-secondary" title="@lang('admin/apps.changes.old_value')">@voe($old_attrs[$k])</span>
			<span class="d-inline-block">
				<span class="fas fa-arrow-right arrow text-primary"></span>
				<span class="value new-value text-primary" title="@lang('admin/apps.changes.new_value')">@voe($v)</span>
			</span>
		</div>
	</div>
	@else
	<div class="item field-group long-attribute col-12">
		<div class="title">@lang('admin/apps.fields.'. $k)</div>
		<div class="comparison">
			<div class="value old-value text-secondary" title="@lang('admin/apps.changes.old_value')"><span class="init-readmore">@voe($old_attrs[$k])</span></div>
			<span class="fas fa-arrow-down arrow text-primary"></span>
			<div class="value new-value text-primary" title="@lang('admin/apps.changes.new_value')"><span class="init-readmore">@voe($v)</span></div>
		</div>
	</div>
	@endif
	@endforeach
	</div>
</div>
@endif

@if(!empty($relations))
<div class="changes-relations row">
	@if(isset($relations['visuals']))
		@include('admin.app.changes.relations.visuals', ['rel' => $relations['visuals'], 'cl' => $cl])
	@endif
	@if(isset($relations['logo']))
		@include('admin.app.changes.relations.logo', ['rel' => $relations['logo'], 'cl' => $cl])
	@endif
	@foreach($regular_relations as $relname)
		@if(isset($relations[$relname]))
		@include('admin.app.changes.relations.regular', ['title' => __('admin/apps.fields.'.$relname), 'rel' => $relations[$relname]])
		@endif
	@endforeach
</div>
@endif