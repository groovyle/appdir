<?php
$is_current = $cl->version == $app->version_number;
$collapsed = $collapsed ?? false;
$color = $is_current ? 'text-success' : '';
$show_current = !!($show_current ?? true);
$show_status = !!($show_status ?? true);
$show_switch = !!($show_switch ?? false);
$show_preview = !!($show_preview ?? true);
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
			<span class="text-090 ml-2">@include('components.app-version-status', ['status' => $cl->status, 'class' => 'align-middle'])</span>
			@endif
			<br>
			<div class="changes-timestamp d-inline-block">@include('components.date-with-tooltip', ['date' => $cl->created_at])</div>
			@if($cl->is_switch)
			<span class="fas fa-recycle text-info text-070 ml-2" title="@lang('admin/apps.changes.this_version_was_a_result_of_version_switch')" data-toggle="tooltip"></span>
			@endif
		</h5>
		<div class="card-tools">
			@if($show_switch)
			@can('switch-to-version', [$app, $cl->version])
			<a href="{{ route('admin.apps.switch_version', ['app' => $app->id, 'version' => $cl->version]) }}" class="btn btn-tool" data-toggle="tooltip" title="@lang('admin/apps.changes.switch_to_this_version')"><span class="fas fa-clone"></span></a>
			@endcan
			@endif
			@if($show_preview)
			<button type="button" class="btn btn-tool btn-view-version" data-toggle="tooltip" title="@lang('admin/apps.changes.view_this_version')" data-app-id="{{ $app->id }}" data-version="{{ $cl->version }}"><span class="fas fa-expand"></span></button>
			@endif
			<button type="button" class="btn btn-tool" data-card-widget="collapse" data-toggle="tooltip" title="@lang('common.show/hide')"><i class="fas {{ !$collapsed ? 'fa-minus' : 'fa-plus' }}"></i></button>
		</div>
	</div>
	<div class="card-body pt-1">
		<div class="card-text changes-content">
			@include('admin.app.changes.list-item-body')
		</div>
	</div>
</div>