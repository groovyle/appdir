<?php
$is_current = $cl->version == $app->version_number;
$collapsed = $collapsed ?? false;
$color = $is_current ? 'text-success' : '';
$show_current = !!($show_current ?? true);
$show_status = !!($show_status ?? true);
?>
<div class="card card-default @if($collapsed) collapsed-card @endif changes-item changes-item-{{ $cl->id }} changes-item-v{{ $cl->version }}" id="changes-item-{{ $cl->id }}" data-version="{{ $cl->version }}">
	<div class="card-header border-bottom-0">
		<h5 class="card-title changes-title">
			<span class="{{ $color }}">
				@lang('admin/apps.changes.version_x', ['x' => $cl->version])
				@if($is_current)
				<span class="fas fa-check ml-1" title="@lang('admin/apps.changes.is_current_version')" data-toggle="tooltip"></span>
				@else
					@if(!$cl->is_verified && $cl->created_at > optional($app->version)->created_at)
					<small class="text-muted changes-note">(@lang('admin/apps.changes.pending'))</small>
					@endif
				@endif
			</span>
			@if($show_current && $show_status)
			<span class="text-090 ml-2">@include('components.app-version-status', ['status' => $cl->status, 'class' => 'align-text-top'])</span>
			@endif
			<br>
			<div class="changes-timestamp">@include('components.date-with-tooltip', ['date' => $cl->created_at])</div>
		</h5>
		<div class="card-tools">
			<button type="button" class="btn btn-tool btn-view-version" data-toggle="tooltip" title="@lang('admin/apps.changes.view_this_version')" data-app-id="{{ $app->id }}" data-version="{{ $cl->version }}"><span class="fas fa-expand"></span></button>
			<button type="button" class="btn btn-tool" data-card-widget="collapse" data-toggle="tooltip" title="@lang('common.show/hide')"><i class="fas {{ !$collapsed ? 'fa-minus' : 'fa-plus' }}"></i></button>
			{{--
			@if(!$is_current)
			<button type="button" class="btn btn-tool btn-go-version" data-toggle="tooltip" title="@lang('admin/apps.changes.go_to_version_x', ['x' => $cl->version])"><span class="fas fa-check"></span></button>
			@endif
			--}}
		</div>
	</div>
	<div class="card-body pt-1">
		<div class="card-text changes-content">
			@include('admin.app.changes.list-item-body')
		</div>
	</div>
</div>