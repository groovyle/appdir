<?php
$new_attrs = $cl->display_diffs['attributes']['new'] ?? $cl->display_diffs['attributes'] ?? [];
$old_attrs = $cl->display_diffs['attributes']['old'] ?? [];
$long_attrs = ['description'];
$relations = $cl->display_diffs['relations'] ?? [];
$regular_relations = ['categories', 'tags'];
$getval = function($val) {
	return value_or_empty($val);
};
$is_current = $cl->version == $app->version_number;
?>
<div class="card card-default changes-item" id="changes-item-{{ $cl->id }}" data-version="{{ $cl->version }}">
	<div class="card-header pb-1 border-bottom-0">
		<h5 class="card-title changes-title">
			@if($is_current)
			<span class="text-success">
				@lang('admin/app.changes.version_x', ['x' => $cl->version])
				<span class="fas fa-check ml-2" title="@lang('admin/app.changes.is_current_version')" data-toggle="tooltip"></span>
			</span>
			@else
			@lang('admin/app.changes.version_x', ['x' => $cl->version])
				@if(!$cl->is_verified && $app->version && $cl->created_at > $app->version->created_at)
				<small class="text-muted changes-note">(@lang('admin/app.changes.pending'))</small>
				@endif
			@endif
			<br>
			<div class="changes-timestamp"><span class="pr-2" title="{{ $cl->created_at->longRelativeToNowDiffForHumans() }}" data-toggle="tooltip" data-placement="right" data-trigger="hover click">{{ $cl->created_at->translatedFormat('j F Y, H:i') }}</span></div>
		</h5>
		<div class="card-tools">
			<button type="button" class="btn btn-tool btn-view-version" data-toggle="tooltip" title="@lang('admin/app.changes.view_this_version')"><span class="fas fa-expand"></span></button>
			{{--
			@if(!$is_current)
			<button type="button" class="btn btn-tool btn-go-version" data-toggle="tooltip" title="@lang('admin/app.changes.go_to_version_x', ['x' => $cl->version])"><span class="fas fa-check"></span></button>
			@endif
			--}}
		</div>
	</div>
	<div class="card-body pt-2">
		<div class="card-text changes-content">
			<p class="mb-1">@lang('admin/app.changes.x_changes_in_this_version', ['x' => count($new_attrs) + count($relations)])</p>

			@if(!empty($new_attrs))
			<div class="changes-attributes">
				<div class="row">
				@foreach($new_attrs as $k => $v)
				@if(!in_array($k, $long_attrs) && strlen($v) < 100)
				<div class="item field-group short-attribute col-lg-4 col-sm-6 col-12">
					<div class="title">@lang('admin/app.field.'. $k)</div>
					<div class="comparison">
						<span class="value old-value text-secondary" title="@lang('admin/app.changes.old_value')">{!! $getval($old_attrs[$k]) !!}</span>
						<span class="fas fa-arrow-right arrow text-primary"></span>
						<span class="value new-value text-primary" title="@lang('admin/app.changes.new_value')">{!! $getval($v) !!}</span>
					</div>
				</div>
				@else
				<div class="item field-group long-attribute col-12">
					<div class="title">@lang('admin/app.field.'. $k)</div>
					<div class="comparison">
						<div class="value old-value text-secondary" title="@lang('admin/app.changes.old_value')">{!! $getval($old_attrs[$k]) !!}</div>
						<span class="fas fa-arrow-down arrow text-primary"></span>
						<div class="value new-value text-primary" title="@lang('admin/app.changes.new_value')">{!! $getval($v) !!}</div>
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
					@include('admin/app.changes.relations.visuals', ['rel' => $relations['visuals'], 'cl' => $cl])
				@endif
				@if(isset($relations['logo']))
					@include('admin/app.changes.relations.logo', ['rel' => $relations['logo'], 'cl' => $cl])
				@endif
				@foreach($regular_relations as $relname)
					@if(isset($relations[$relname]))
					@include('admin/app.changes.relations.regular', ['title' => __('admin/app.field.'.$relname), 'rel' => $relations[$relname]])
					@endif
				@endforeach
			</div>
			@endif
		</div>
	</div>
</div>