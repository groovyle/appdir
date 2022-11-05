<?php
$last_breadcrumb = __('admin/app_reports.page_title.verdicts_x', ['x' => text_truncate($app->name, 50)]);

$verdicts_count = count($verdicts);
$show_recent = $show_recent ?? false;
?>
@extends('admin.layouts.main')

@section('title')
{{ __('admin/app_reports.page_title.verdicts_x', ['x' => text_truncate($app->name, 20)]) }} - @parent
@endsection

@section('page-title', __('admin/app_reports.page_title.verdicts'))

@section('content')

<div class="d-flex flex-wrap text-nowrap mb-1">
	<div class="details-nav-left mr-auto mb-1">
		@can('view-any', App\Models\AppReport::class)
		<a href="{{ route('admin.app_reports.index') }}" class="btn btn-sm btn-default">&laquo; {{ __('common.back_to_list') }}</a>
		@endcan
	</div>
	<div class="details-nav-right ml-auto mb-1">
		@can('create', App\Models\AppReport::class)
		<a href="{{ route('admin.app_reports.review', ['app' => $app->id]) }}" class="btn btn-sm bg-primary">
			<span class="fas fa-clipboard-check mr-1"></span>
			{{ __('admin/app_reports.review_reports') }}
		</a>
		@endcan
	</div>
</div>

@include('admin.app.detail-card', ['app' => $app, 'hide_changes' => true, 'is_snippet' => true, 'show_pending_changes' => false, 'section_id' => 'ori'])

<hr>

@if($verdicts_count > 0)
<h6 class="text-center mb-2">@lang('admin/app_reports.verdicts_history') ({{ $verdicts_count }})</h6>
<div class="mb-3 text-secondary"><em>(@lang('common.sorted_from_newest_to_oldest'))</em></div>

@foreach($verdicts as $vd)
<?php
$card_outline = '';
if($vd->is_innocent)
	$card_outline = 'card-outline card-success';
else
	$card_outline = 'card-outline card-danger';

$show_verdict = $loop->index == 0 && $show_recent;
$collapse_class = $show_verdict ? 'scroll-to-me' : 'collapsed-card';
?>
<div class="card {{ $collapse_class }} {{ $card_outline }}">
	<div class="card-header border-bottom-0">
		<h4 class="card-title">
			<span>
				<span class="text-secondary text-right">#{{ $verdicts_count - $loop->index }}</span>
				@lang('admin/app_reports.verdict_by') @puser($vd->updatedBy)
				<span class="text-090 ml-1">
					@include('admin.app_report.components.verdict-badge', ['verdict' => $vd])
					<span class="ml-1 text-090">@include('admin.app_report.components.verdict-additional-badges', ['verdict' => $vd])</span>
				</span>
			</span>
			<div class="text-080 d-flex mt-1">
				<span class="text-secondary mr-2">@include('components.date-with-tooltip', ['date' => $vd->updated_at])</span>
				<span>
					<span class="icon-text-pair" title="{{ __('admin/app_reports.judgement_was_made_when_the_app_was_at_version_x', ['x' => vo_(optional($vd->version)->version)]) }}" data-toggle="tooltip">
						<span class="fas fa-code-branch icon text-090 text-secondary"></span>
						<span>@lang('admin/app_verifications.version_x', ['x' => vo_(optional($vd->version)->version)])</span>
					</span>
					@if($vd->version)
					<button type="button" class="btn btn-tool btn-tool-inline btn-view-version ml-1" data-toggle="tooltip" title="@lang('admin/apps.changes.view_this_version')" data-app-id="{{ $app->id }}" data-version="{{ $vd->version->version }}"><span class="fas fa-expand"></span></button>
					@endif
				</span>
			</div>
		</h4>
		<div class="card-tools">
			<button type="button" class="btn btn-tool" data-card-widget="collapse" data-toggle="tooltip" title="@lang('common.show/hide')"><i class="fas @if($show_verdict) fa-minus @else fa-plus @endif"></i></button>
		</div>
	</div>
	<div class="card-body pt-1 mt-n2">
		<div class="mb-1">
			<strong class="mr-1">@lang('admin/app_reports.fields.violation_types'):</strong>
			@forelse($vd->categories as $rc)
			<span class="btn btn-xs btn-default rounded-pill cursor-default" data-toggle="tooltip" title="{{ $rc->description }}" data-custom-class="tooltip-wider">
				<span class="icon-text-pair mx-1">
					<span>{{ $rc->name }}</span>
					<span class="icon">{{ $rc->reports_count }}</span>
				</span>
			</span>
			@empty
			@vo_
			@endforelse
		</div>
		<div class="mb-1">
			<strong class="mr-1">@lang('admin/app_reports.fields.comments'):</strong>
			@if($vd->comments)
			<div class="lh-130">
				<span class="init-readmore">@voe($vd->comments)</span>
			</div>
			@else
			@vo_
			@endif
		</div>
		<div class="mb-1">
			<a class="text-reset" href="#verdict-item-{{ $vd->id }}-reports" data-toggle="collapse">
				<strong class="mr-1">@lang('admin/app_reports.fields.reports') ({{ count($vd->reports) }})</strong>
				<span class="fas fa-caret-down ml-1"></span>
			</a>
		</div>
		<div class="report-list collapse collapse-scrollto mt-2 px-2 text-090" id="verdict-item-{{ $vd->id }}-reports">
			@forelse($vd->reports as $r)
			<div class="card report-item mb-2">
				<div class="card-header pb-1 px-3 border-bottom-0">
					<div class="card-title text-120">
						@if($r->registered_sender)
						<span class="pr-2" title="{{ __('admin/app_reports.report_from_registered_user') }}" data-toggle="tooltip" data-placement="right">
							<span class="fas fa-user text-lightblue text-080 mr-1"></span>
							@puser($r->user)
						</span>
						@else
						<span class="pr-2" title="{{ __('admin/app_reports.report_from_anonymous_user') }}" data-toggle="tooltip" data-placement="right">
							<span class="fas fa-envelope text-secondary text-080 mr-1"></span>
							{{ $r->email }}
						</span>
						@endif
						<div class="text-080 d-flex">
							<span class="text-secondary mr-2">@include('components.date-with-tooltip', ['date' => $r->updated_at])</span>
							<span class="text-secondary mr-2 d-inline-block">
								@if($r->version->version == optional($vd->version)->version)
								<strong>{{ $r->version->display_name }}</strong>
								@else
								<span>{{ $r->version->display_name }}</span>
								<span class="far fa-clock text-090 text-warning ml-1" title="@lang('admin/app_reports.this_report_was_reported_on_an_older_version')" data-toggle="tooltip" data-custom-class="tooltip-wider"></span>
								@endif
								@if($r->version->version != '__none')
								<button type="button" class="btn btn-tool btn-tool-inline btn-view-version ml-1" data-toggle="tooltip" title="@lang('admin/apps.changes.view_this_version')" data-app-id="{{ $app->id }}" data-version="{{ $r->version->version }}"><span class="fas fa-expand"></span></button>
								@endif
							</span>
						</div>
					</div>
				</div>
				<div class="card-body mt-n2 pt-1 pb-2 px-3 lh-130">
					<div>
						<span class="text-pre-wrap reason-text init-readmore">@voe($r->reason)</span>
					</div>
					<div class="d-flex flex-row flex-wrap justify-content-between align-items-start mt-2" style="gap: 0.5rem 1rem;">
						<div class="">
							<div class="d-inline-block">
								@forelse($r->categories as $rc)
								<span class="btn btn-xs btn-default rounded-pill cursor-default" data-toggle="tooltip" title="{{ $rc->description }}" data-custom-class="tooltip-wider">{{ $rc->name }}</span>
								@empty
								@vo_
								@endforelse
							</div>
						</div>
						<div class="text-120">
							<?php
							$report_badge = '';
							$report_badge_text = '';
							$report_badge_desc = '';
							if($r->is_valid) {
								$report_badge = 'badge-info';
								$report_badge_text = __('admin/app_reports.label_report_is_valid');
								$report_badge_desc = __('admin/app_reports.this_report_was_valid');
							} elseif($r->is_dropped) {
								$report_badge = 'badge-dark';
								$report_badge_text = __('admin/app_reports.label_report_is_invalid');
								$report_badge_desc = __('admin/app_reports.this_report_was_invalid');
							} else {
								$report_badge = 'badge-secondary';
								$report_badge_text = __('admin/app_reports.label_report_is_invalid');
								$report_badge_desc = __('admin/app_reports.this_report_was_invalid');
							}
							?>
							<span class="badge badge-soft {{ $report_badge }} rounded-0 cursor-default" data-toggle="tooltip" title="{{ $report_badge_desc }}">{{ $report_badge_text }}</span>
						</div>
					</div>
				</div>
			</div>
			@empty
			<h5>@lang('admin/app_reports.no_reports_in_this_verdict')</h5>
			@endforelse
		</div>
	</div>
</div>
@endforeach
@else
<h4 class="text-center">&ndash; @lang('admin/app_reports.no_verdicts') &ndash;</h4>
@endif

@endsection

@include('admin.app.changes.btn-view-version')

@push('scripts')
<script>
jQuery(document).ready(function($) {
	var $scrollElm = $(".scroll-to-me");
	if($scrollElm.length > 0) {
		Helpers.scrollTo($scrollElm, { animate: false });
	}
});
</script>

@endpush
