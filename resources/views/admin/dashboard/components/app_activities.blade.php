<?php
$limit = $limit ?? 10;
?>
<div class="card">
	<div class="card-header">
		<h4 class="card-title">
			{{ __('admin/dashboard.app_activities') }}
			@can('view-any', App\Models\App::class)
			<small class="ml-1"><a href="{{ route('admin.app_activities.index') }}">{{ __('admin/dashboard.see_all') }}</a></small>
			@endcan
		</h4>
	</div>
	@if(count($activities) == 0)
	<div class="card-body table-responsive p-0">
		<h5 class="text-center">&ndash; {{ __('admin/apps.no_app_activities_yet') }} &ndash;</h5>
	</div>
	@else
	<div class="card-body p-0 table-responsive">
		<table class="table table-hover table-sm text-nowrap text-090">
			<tbody>
				@foreach($activities as $item)
				<tr>
					<td class="pl-2" style="max-width: 100px;">
						<span class="d-block text-truncate" title="{{ $item->app->name }}">{{ text_truncate($item->app->name, 50) }}</span>
					</td>
					<td class="text-truncate" style="width: 1%;">
						@if($item->concern == 'new')
							<span class="badge badge-soft cursor-default text-090 badge-info">{{ ucfirst(__('admin/common.new')) }}</span>
						@else
							@if($item->concern == 'edit')
								<span class="badge badge-soft cursor-default text-090 badge-primary">{{ ucfirst(__('admin/common.edit')) }}</span>
							@else
								<span class="badge badge-soft cursor-default text-090 badge-{{ $item->status->bg_style }}">{{ $item->status->name }}</span>
							@endif
						@endif
					</td>
					<td class="text-truncate" style="width: 1%;">
						<span class="text-090 text-secondary">@include('components.date-with-tooltip', ['date' => $item->action_at, 'reverse' => true, 'short' => true])</span>
					</td>
					<td class="pr-2" style="width: 1%;">
						@if($item->view_url)
						<a href="{{ $item->view_url }}" class="btn btn-xs btn-default" title="{{ __('common.view') }}" data-toggle="tooltip"><span class="fas fa-search"></span></a>
						@endif
					</td>
				</tr>
				@endforeach
			</tbody>
		</table>
	</div>
	@endif
</div>