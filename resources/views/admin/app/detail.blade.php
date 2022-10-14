<?php
$append_breadcrumb = [
	[
		'text'    => $app->name,
	]
];
?>

@extends('admin.layouts.main')

@section('title')
{{ __('admin/apps.tab_title.detail', ['x' => text_truncate($app->name, 20)]) }} - @parent
@endsection

@section('page-title', __('admin/apps.page_title.detail'))

@include('admin.app.detail-inner', ['hide_status' => false])
@include('admin.app.changes.pending')

@section('content')
<div class="d-flex flex-wrap text-nowrap mb-1">
	<div class="details-nav-left mr-auto mb-1">
		@can('view-any', $app)
		<a href="{{ route('admin.apps.index') }}" class="btn btn-sm btn-default">&laquo; {{ __('common.back_to_list') }}</a>
		@endcan
		@can('update', $app)
		<a href="{{ route('admin.apps.edit', ['app' => $app->id]) }}" class="btn btn-sm btn-primary">
			<span class="fas fa-edit"></span>
			{{ __('admin/apps.edit_app_info') }}
		</a>
		<a href="{{ route('admin.apps.visuals', ['app' => $app->id]) }}" class="btn btn-sm btn-info">
			<span class="fas fa-photo-video"></span>
			{{ __('admin/apps.edit_visuals') }}
		</a>
		@endcan
	</div>
	<div class="details-nav-right ml-auto mb-1">
		@can('delete', $app)
		<a href="{{ route('admin.apps.destroy', ['app' => $app->id]) }}" class="btn btn-danger btn-sm text-nowrap btn-ays-modal" data-method="DELETE" data-prompt="_delete" data-description="{{ sprintf('<strong>%s</strong>: %s (%s: %s)', __('admin/apps._self'), $app->complete_name, __('admin/common.fields.id'), $app->id) }}">
			<span class="fas fa-trash mr-1"></span>
			{{ __('common.delete') }}
		</a>
		@endcan
	</div>
</div>
<!-- Card -->
<div class="card">
	<div class="card-header">
		<div class="d-flex flex-wrap align-items-center">
			<div class="mr-auto">
				<h4 class="mb-0 d-inline-block">
					{{ $app->complete_name }}
					@include('admin.app.components.owned-icon')
				</h4>
				<br>
				@if($app->is_reported)
					<span class="badge badge-soft badge-danger align-middle text-080">
						<span class="fas fa-exclamation-triangle text-090 mr-1"></span>
						@lang('admin/apps.app_was_reported')
					</span>
				@endif
				@if($app->is_private)
					<span class="badge badge-soft badge-dark align-middle text-080">
						<span class="fas fa-lock text-090 mr-1"></span>
						@lang('admin/apps.app_is_private')
					</span>
				@endif
				<a href="{{ $app->public_url }}" class="btn btn-xs btn-default px-2" target="_blank">
					@lang('admin/apps.view_public_page')
					<span class="fas fa-globe-americas ml-1"></span>
				</a>
			</div>
			<div class="text-right ml-auto">
				@if($app->has_committed)
				<span class="text-bold">
					@lang('admin/apps.changes.version_x', ['x' => $app->version_number])
				</span>
				@else
				<span class="text-bold">
					@lang('admin/apps.this_new_item_is_waiting_verification')
				</span>
				@endif
				@if($app->has_floating_changes)
				@canany(['update', 'view-changelog'], $app)
				<br>
				<button class="btn btn-xs btn-warning btn-pending-changes-show" data-app-id="{{ $app->id }}" data-current-version="{{ $app->version_number }}" data-accumulate-changes="false">
					<span class="fas fa-clock"></span>
					@lang('admin/apps.show_pending_changes')
				</button>
				@endcan
				@endif
			</div>
		</div>
	</div>
	<div class="card-body">
		<div class="row gutter-lg app-detail-panels">
			<div class="col-12 col-md-4 side-panel right">
				<div class="mb-2 text-center">
					@if($app->has_verifications)
					@can('view-verifications', $app)
					<a href="{{ route('admin.apps.verifications', ['app' => $app->id]) }}" class="btn btn-app text-dark">
						<span class="badge badge-primary text-100">{{ count($app->verifications) }}</span>
						<span class="fas fa-clipboard-check"></span>
						{{ __('admin/apps.verifications') }}
					</a>
					@endcan
					@endif
					@if($app->has_history)
					@can('view-changelog', $app)
					<a href="{{ route('admin.apps.changes', ['app' => $app->id, 'current' => '']) }}" class="btn btn-app text-dark">
						<span class="badge badge-primary text-100">{{ count($app->changelogs) }}</span>
						<span class="fas fa-history"></span>
						{{ __('admin/apps.history') }}
					</a>
					@endcan
					@endif
				</div>

				<?php
				$man_panels = [
					'is_private'		=> [
						'value'				=> $app->is_private,
						'check'				=> Auth::user()->can('set-private', $app),
						'action'			=> route('admin.apps.set-private', ['app' => $app->id, 'private' => $app->is_private ? 0 : 1]),
						'simple'			=> true,
						'btn'					=> ['btn-dark'],
						'icon'				=> ['fa-lock', 'fa-unlock'],
						'title'				=> [__('admin/apps.man_pan.app_is_private'), __('admin/apps.man_pan.app_is_not_private')],
						'desc'				=> [
							__('admin/apps.man_pan.make_not_private', ['name' => $app->complete_name]),
							__('admin/apps.man_pan.make_private', ['name' => $app->complete_name])
						],
					],
					'is_published'		=> [
						'value'				=> $app->is_published,
						'check'				=> Auth::user()->can('set-published', $app),
						'action'			=> route('admin.apps.set-published', ['app' => $app->id, 'published' => $app->is_published ? 0 : 1]),
						'icon'				=> ['fa-eye', 'fa-eye-slash'],
						'title'				=> [__('admin/apps.man_pan.app_is_published'), __('admin/apps.man_pan.app_is_not_published')],
						'desc'				=> [
							__('admin/apps.man_pan.make_not_published', ['name' => $app->complete_name]),
							__('admin/apps.man_pan.make_published', ['name' => $app->complete_name])
						],
					],
				];
				// $man_panels = array_filter($man_panels, function($item) { return $item['check'] ?? false; });
				?>
				@if(!empty($man_panels))
				<div class="mb-4 app-manpan-wrapper">
					<h6 class="lead font-weight-normal">{{ __('admin/apps.man_pan.title') }}</h6>
					<div class="mt-2 app-manpan-list">
						<?php
						foreach($man_panels as $mp_key => $mp):
						$mp_check = $mp['check'] ?? false;

						$mp_action = '#';
						if($mp_check)
							$mp_action = $mp['action'] ?? '#';

						$mp_btn = 'btn-light';
						if($mp_check)
							$mp_btn = $mp['value'] ? ($mp['btn'][0] ?? 'btn-primary') : ($mp['btn'][1] ?? 'btn-default');

						$mp_simple = $mp['simple'] ?? true;
						$mp_modal = $mp_simple ? 'btn-ays-modal' : '';
						$mp_icon = $mp['icon'][$mp['value'] ? 0 : 1] ?? '';
						$mp_title = $mp['title'][$mp['value'] ? 0 : 1] ?? '';
						$mp_desc = $mp['desc'][$mp['value'] ? 0 : 1] ?? '';
						?>
						<a href="{{ $mp_action }}" class="btn {{ $mp_btn }} {{ $mp_modal }} btn-manpan-{{ Str::slug($mp_key) }}" title="{{ $mp_title }}" data-toggle="tooltip" data-description="{{ $mp_desc }}"><span class="fas fa-fw {{ $mp_icon }}"></span></a>
						@endforeach
					</div>
				</div>
				@endif

				@if($app->last_verification->status->by == 'verifier')
				<div class="last-verif-info">
					<div class="lead font-weight-normal">@lang('admin/app_verifications.last_verification')</div>
					@include('admin.app_verification.components.verif-list-item', ['verif' => $app->last_verification, 'other_comments' => true, 'item_class' => 'text-090'])
				</div>
				@endif
			</div>

			<div class="col-12 col-md-8 content-panel">
				@if($app->is_reported)
				<div class="alert alert-danger">
					<div class="icon-text-pair icon-2x icon-color-reset">
						<span class="fas fa-exclamation-triangle icon"></span>
						<div>
							{{ __('admin/apps.messages.app_was_unlisted_for_inappropriate_contents') }}
							<br>
							{{ __('admin/apps.messages.to_unblock_app_please_remove_inappropriate_contents') }}
							@if($app->report_verification)
							<br>
							<a href="#" class="text-white btn-view-verif" data-app-id="{{ $app->id }}" data-verif-id="{{ $app->report_verification->id }}">@lang('admin/common.check_details')</a>
							@endif
						</div>
					</div>
				</div>
				@endif
				@if($app->has_approved_changes)
				<div class="callout callout-success py-2">
					@lang('admin/apps.your_app\'s_edits_version_x_has_been_approved', ['x' => $app->approved_changes->last()->version])
					@if($app->is_reported)
					<br>
					<strong>{{ __('common.note') }}: {{ __('admin/apps.messages.app_ban_will_be_lifted_after_publish') }}</strong>
					@endif
					@can('update', $app)
					<br>
					<a href="{{ route('admin.apps.publish', ['app' => $app->id]) }}" class="btn btn-success text-white btn-sm mt-1">@lang('admin/apps.publish_edits') &raquo;</a>
					@endcan
				</div>
				@elseif($app->last_verification->status->by == 'verifier')
				@if($app->last_verification->status_id == 'revision-needed')
				<div class="alert alert-warning">
					<div class="icon-text-pair icon-2x icon-color-reset">
						<span class="fas fa-exclamation-circle icon"></span>
						<div>
							@nl2br(__('admin/apps.messages.last_verification_revision-needed'))
							<br>
							<a href="#" class="text-reset btn-flash-elm" data-flash-target=".last-verif-info">@lang('admin/apps.messages.check_verification_details')</a>
						</div>
					</div>
				</div>
				@elseif($app->last_verification->status_id == 'rejected')
				<div class="alert alert-danger">
					<div class="icon-text-pair icon-2x icon-color-reset">
						<span class="fas fa-exclamation-circle icon"></span>
						<div>
							@nl2br(__('admin/apps.messages.last_verification_rejected'))
							<br>
							<a href="#" class="text-white btn-flash-elm" data-flash-target=".last-verif-info">@lang('admin/apps.messages.check_verification_details')</a>
						</div>
					</div>
				</div>
				@endif
				@endif

				@yield('detail-content')
			</div>
		</div>
	</div>
	<!-- /.card-body -->
</div>
<!-- /.card -->
@endsection

@include('admin.app_verification.btn-view-verif')

@push('scripts')
<script type="text/javascript">
jQuery(document).ready(function($) {
	@if(request()->has('show_verification'))
	// Put in a slight timeout so that everything else finishes first
	setTimeout(function() {
		Helpers.scrollAndFlash($(".last-verif-info"), { animate: true });
	}, 10);
	@elseif(request()->has('show_pending'))
	// Put in a slight timeout so that everything else finishes first
	setTimeout(function() {
		$(".btn-pending-changes-show").trigger("click");
	}, 10);
	@endif
});
</script>
@endpush
