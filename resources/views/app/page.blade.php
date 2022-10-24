<?php
$is_report_form = old('is_report_form') ?? request()->has('report');
$show_report_form = $is_report_form ? 'show' : '';

$notices_count = 0;
$share_enabled = $app->is_original_version;
$share_description = __('frontend.apps.share_description', ['app' => $app->complete_name, 'owner' => $app->owner->share_name, 'site' => app_name()]);
?>
@extends('layouts.app')

@section('title', text_truncate($app->complete_name, 50).' | '.__('frontend.apps.showcase'))

@section('meta')
@if($share_enabled)
	<meta property="og:title" content="{{ text_truncate($app->complete_name, 70) }}">
	<meta property="og:type" content="article" />
	<meta property="og:image" content="{{ $app->thumbnail_url }}">
	<meta property="og:url" content="{{ route('apps.page', ['slug' => $app->id]) }}">
	<meta name="twitter:card" content="summary_large_image">
	<meta property="og:description" content="{{ $share_description }}">
	<meta property="og:site_name" content="{{ app_name() }}">
	<meta name="twitter:image:alt" content="Thumbnail">
@endif
@endsection

@section('app-notices')
<div class="app-notices collapse show mb-4" id="app-{{ $app->id }}-notices">
	@if($app->is_owned)
	@php $notices_count++; @endphp
	<div class="alert alert-info">
		<span class="icon-text-pair icon-color-reset icon-2x">
			<span class="fas fa-user-check fa-fw icon text-130"></span>
			<span>
				@lang('frontend.apps.notices.owner')
				<br>
				<a href="{{ route('admin.apps.show', ['app' => $ori->id]) }}" class="alert-link">{{ ucfirst(__('frontend.apps.notices.go_to_admin_panel')) }}</a>,
				@lang('common.or')
				<a href="#app-{{ $app->id }}-notices" data-toggle="collapse" class="alert-link">@lang('frontend.apps.notices.hide_these_messages')</a>.
			</span>
		</span>
	</div>
	@endif
	@if($app->owner && $app->owner->is_blocked)
	@php $notices_count++; @endphp
	<div class="alert alert-danger">
		<span class="icon-text-pair icon-color-reset icon-2x">
			<span class="fas fa-user-slash fa-fw icon text-130"></span>
			<span>@lang('frontend.apps.notices.owner_blocked')</span>
		</span>
	</div>
	@endif
	@if(!$app->is_original_version)
		@php $notices_count++; @endphp
		<div class="alert alert-danger">
			<div class="icon-text-pair icon-color-reset icon-2x">
				<span class="fas fa-copy fa-fw icon text-130"></span>
				<span class="d-inline-block">
					@lang('frontend.apps.notices.not_original_version', ['this' => $app->version_number, 'ori' => vo_($app->original_version_number)])
					<br>
					@lang('frontend.apps.notices.version_x_status', ['x' => $app->version_number]):
					@include('components.app-version-status', ['status' => $app->version->status, 'class' => 'text-100 border border-secondary align-middle'])
					<br>
					<span>
						@can('view-changelog', $ori)
						<a href="{{ route('admin.apps.changes', ['app' => $ori->id, 'go_version' => $app->version_number, 'go_flash' => 1]) }}" class="alert-link" target="_blank">{{ ucfirst(__('frontend.apps.notices.check_details_for_this_version')) }}</a>
						@lang('common.or')
						<a href="{{ url()->current() }}" class="alert-link">@lang('frontend.apps.notices.go_back_to_original_version?')</a>
						@else
						<a href="{{ url()->current() }}" class="alert-link">{{ ucfirst(__('frontend.apps.notices.go_back_to_original_version?')) }}</a>
						@endcan
					</span>
				</span>
			</div>
		</div>
	@else
		@if($app->is_unverified_new)
		@php $notices_count++; @endphp
		<div class="alert alert-info">
			<span class="icon-text-pair icon-color-reset icon-2x">
				<span class="fas fa-question fa-fw icon text-130"></span>
				<span>@lang('frontend.apps.notices.unverified_new')</span>
			</span>
		</div>
		@elseif($app->has_floating_changes)
		@php $notices_count++; @endphp
		<div class="alert alert-success">
			<span class="icon-text-pair icon-color-reset icon-2x">
				<span class="fas fa-code-branch fa-fw icon text-130"></span>
				<span>@lang('frontend.apps.notices.has_floating_changes')</span>
			</span>
		</div>
		@endif
		@if(!$app->is_unverified_new && !$app->is_published)
		@php $notices_count++; @endphp
		<div class="alert alert-warning">
			<span class="icon-text-pair icon-color-reset icon-2x">
				<span class="fas fa-eye-slash fa-fw icon text-130"></span>
				<span>@lang('frontend.apps.notices.not_published')</span>
			</span>
		</div>
		@endif
		@if($app->is_private)
		@php $notices_count++; @endphp
		<div class="alert alert-secondary">
			<span class="icon-text-pair icon-color-reset icon-2x">
				<span class="fas fa-lock fa-fw icon text-130"></span>
				<span>@lang('frontend.apps.notices.is_private')</span>
			</span>
		</div>
		@endif
		@if($app->is_reported)
		@php $notices_count++; @endphp
		<div class="alert alert-danger">
			<span class="icon-text-pair icon-color-reset icon-2x">
				<span class="fas fa-exclamation-circle fa-fw icon text-130"></span>
				<span>@lang('frontend.apps.notices.is_reported')</span>
			</span>
		</div>
		@endif
	@endif
</div>
@endsection

@section('outer-content')
<main class="flex-grow-1 app-page">

	<div class="app-header full-page-tabs">
		<div class="container">
			@if($notices_count > 0 && $view_mode != 'none')
				@yield('app-notices')
			@endif
			<div class="app-with-logo">
				<div class="logo-wrapper">
					@include('components.app-logo', ['logo' => $app->logo, 'exact' => '80x80', 'img_class' => 'app-logo', 'default' => true, 'as_link' => false])
				</div>
				<div class="logo-complement">
					<h1 class="app-title mb-1">
						{{ $app->name }}
						@if($app->short_name)
						<small title="{{ __('frontend.apps.short_name') }}">({{ $app->short_name }})</small>
						@endif
					</h1>
					<div class="app-subtitle segmented">
						<span>@lang('frontend.apps.by') {{ $app->owner }}</span>
						<span>@lang('frontend.apps.x_views', ['x' => vo_($app->page_views)])</span>
						<span>
							<span class="{{ !$app->is_original_version ? 'text-italic' : '' }}">@lang('frontend.apps.version_x', ['x' => vo_($app->version_number)])</span>
							@if($view_mode == 'admin' && count($app->changelogs) > 0)
							<a href="#app-{{ $app->id }}-version-selector" class="text-090 {{ $app->is_original_version ? 'text-primary' : 'text-danger' }} ml-2" data-toggle="modal"><span class="far fa-copy" title="{{ __('frontend.apps.notices.review_other_versions') }}" data-toggle="tooltip"></span></a>
							@endif
						</span>
						<span class="text-muted">
							@cuf('lcfirst', trans('frontend.apps.fields.published_at'))
							@if($app->published_at)
							@include('components/date-with-tooltip', ['date' => $app->published_at, 'reverse' => true])
							@else
							@vo_
							@endif
						</span>
						@if($notices_count > 0 && $view_mode != 'none')
						<span class="d-inline-flex align-middle" data-target="#app-{{ $app->id }}-notices" data-toggle="collapse" style="column-gap: 0.75rem;">
							<a href="#app-{{ $app->id }}-notices" class="text-primary rounded-pill" id="app-{{ $app->id }}-notices-trigger" data-toggle="collapse"><span title="{{ __('frontend.apps.notices.show_app_notices') }}" data-toggle="tooltip" style="opacity: 0.75;"><span class="fas fa-info-circle"></span></span></a>
							@if($app->owner && $app->owner->is_blocked)
							<span class="text-danger" title="{{ __('frontend.apps.notices.owner_blocked_tip') }}" data-toggle="tooltip"><span class="fas fa-user-slash"></span></span>
							@endif
							@if(!$app->is_original_version)
							<span class="text-danger" title="{{ __('frontend.apps.notices.not_original_version_tip') }}" data-toggle="tooltip"><span class="fas fa-copy"></span></span>
							@else
								@if($app->is_unverified_new)
								<span class="text-info" title="{{ __('frontend.apps.notices.unverified_new_tip') }}" data-toggle="tooltip"><span class="fas fa-question"></span></span>
								@elseif($app->has_floating_changes)
								<span class="text-success" title="{{ __('frontend.apps.notices.has_floating_changes_tip') }}" data-toggle="tooltip"><span class="fas fa-code-branch"></span></span>
								@endif
								@if(!$app->is_unverified_new && !$app->is_published)
								<span class="text-dark" title="{{ __('frontend.apps.notices.not_published_tip') }}" data-toggle="tooltip"><span class="fas fa-eye-slash"></span></span>
								@endif
								@if($app->is_private)
								<span class="text-secondary" title="{{ __('frontend.apps.notices.is_private_tip') }}" data-toggle="tooltip"><span class="fas fa-lock"></span></span>
								@endif
								@if($app->is_reported)
								<span class="text-danger" title="{{ __('frontend.apps.notices.is_reported_tip') }}" data-toggle="tooltip"><span class="fas fa-exclamation-circle"></span></span>
								@endif
							@endif
						</span>
						@endif
					</div>
					@if($share_enabled)
					<div class="mt-1">
						<a href="#app-{{ $app->id }}-share-modal" class="text-primary text-r100 text-decoration-none" data-toggle="modal"><span class="fas fa-share-alt mr-1"></span> {{ __('frontend.apps.share_this_app') }}</a>
					</div>
					@endif
				</div>
			</div>
		</div>
				{{--
		<div class="container container-tabs">
			<div class="nav nav-tabs" id="main-page-tabs" role="tablist">
				<a class="nav-item nav-link active" href="#details-tabpane" id="details-tab" data-toggle="tab" role="tab">@lang('frontend.apps.details')</a>
				<a class="nav-item nav-link" href="#comments-tabpane" id="comments-tab" data-toggle="tab" role="tab">@lang('frontend.apps.comments') <span class="badge badge-secondary ml-1">0</span></a>
			</div>
		</div>
				--}}
	</div>

	<div class="app-content tab-content pt-3 px-3 pb-5" id="main-page-tabpanes">
		<div class="tab-pane fade show active" id="details-tabpane" role="tabpanel">
			<div class="container">
				@if($report_message = session('report_message'))
				@include('components.page-message', ['message' => $report_message['message'], 'status' => $report_message['type'].' scroll-to-me', 'dismiss' => true])
				@endif
				<div class="card mb-4 collapse collapse-scrollto {{ $show_report_form }}" id="app-report-section">
					<div class="card-body">
						<div class="row">
							<div class="col-12 col-md-8 col-lg-7 col-xl-6 mx-auto">
								<button type="button" class="close" data-toggle="collapse" data-target="#app-report-section" aria-label="Close">
									<span aria-hidden="true">&times;</span>
								</button>
								<h4 class="card-title text-danger">@lang('frontend.apps.report_app'): <strong class="text-danger">{{ $app->complete_name }}</strong></h4>

								@if($app->is_listed && $app->is_original_version)
								<form class="" id="report-app-form" method="POST" action="{{ route('apps.report.save', ['slug' => $ori->slug]) }}">
									@csrf
									@method('POST')
									<input type="hidden" name="app_id" value="{{ $app->id }}" >
									<input type="hidden" name="report_user" value="" >

									@includeWhen($is_report_form, 'components.page-message', ['show_errors' => true])

									@if(!auth()->check())
									<div class="alert alert-info py-2 mb-1">
										<span class="icon-text-pair icon-color-reset icon-2x">
											<span class="fa fa-info-circle icon"></span>
											<span>@lang('frontend.apps.if_you_have_an_account_logging_in_will_increase_credibility_of_your_report')</span>
										</span>
									</div>
									<div class="form-group mb-1">
										<label for="reportEmail">@lang('frontend.apps.fields.email'):</label>
										<input type="email" name="report_email" id="reportEmail" class="form-control" placeholder="@lang('frontend.apps.fields.reportee_email_placeholder')" value="{{ old('report_email') }}" required>
									</div>
									@else
									<p class="mb-1">@lang('frontend.apps.reporting_as') <strong>{{ auth()->user()->name }}</strong> @if($email = auth()->user()->email) ({{ $email }}) @endif</p>
									@endif
									<div class="form-group mb-1">
										<label class="d-block mb-0">@lang('frontend.apps.fields.report_categories'):</label>
										<div class="d-flex flex-row flex-wrap">
											@foreach($report_categories as $rc)
											<div class="form-check form-check-inline" title="{{ $rc->description }}" data-toggle="tooltip" data-placement="top" data-custom-class="text-r090 tooltip-wider">
												<input type="checkbox" name="report_categories[]" value="{{ $rc->id }}" id="reportCategory-{{ $rc->id }}" class="form-check-input" {!! old_checked('report_categories', NULL, $rc->id) !!}>
												<label class="form-check-label" for="reportCategory-{{ $rc->id }}">{{ $rc->name }}</label>
											</div>
											@endforeach
										</div>
									</div>
									<div class="form-group mb-1">
										<label for="reportReason" class="d-block mb-0">@lang('frontend.apps.fields.report_reason'):</label>
										<textarea name="report_reason" id="reportReason" class="form-control show-resize" placeholder="@lang('frontend.apps.fields.report_reason_placeholder')" rows="2" maxlength="{{ $report_reason_limit }}" required>{{ old('report_reason') }}</textarea>
									</div>
									<div class="text-center mt-3">
										<button type="submit" class="btn btn-sm btn-primary btn-min-100">@lang('frontend.apps.submit_report')</button>
									</div>
								</form>
								@else
								<div class="alert alert-warning mb-0">{{ __('frontend.apps.app_cannot_be_reported_because_it_is_not_publicly_listed') }}</div>
								@endif
							</div>
						</div>
					</div>
				</div>
				<div class="row details-panel text-wrap-word">
					<div class="col-12 mb-4 col-md-8 mb-md-0 col-lg-8 col-xl-9 details-panel-left">
						@if($app->visuals->count() > 0)
						<div>
							<div class="app-visuals-slides">
								<div class="splide img-maxed img-centered" id="app-visuals-slides-big" tabindex="0">
									<div class="splide__track">
										<ul class="splide__list">
											@foreach($app->visuals as $item)
											@if($item->type == 'image')
											<li class="splide__slide">
												<div class="splide__slide__container">
													<img src="{{ $item->thumbnail_url }}" >
												</div>
												<div class="splide-caption has-arrow text-pre-wrap">{{ trim($item->caption) }}</div>
											</li>
											@elseif($item->type == 'video')
											<li class="splide__slide splide-video" data-splide-youtube="{{ $item->embed_url }}">
												<div class="splide__slide__container">
													<img src="{{ $item->thumbnail_url }}" >
												</div>
												<div class="splide-caption has-arrow text-pre-wrap">{{ trim($item->caption) }}</div>
											</li>
											@endif
											@endforeach
										</ul>
									</div>
									<button class="splide__toggle" type="button" title="{{ __('frontend.slideshow.play/pause') }}" data-toggle="tooltip" style="position: absolute; bottom: 0; left: -3rem;">
										<svg class="splide__toggle__play" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg"><path d="m22 12-20 11v-22l10 5.5z"></path></svg>
										<svg class="splide__toggle__pause" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg"><path d="m2 1v22h7v-22zm13 0v22h7v-22z"></path></svg>
									</button>
									<div class="splide__progress mt-1">
										<div class="splide__progress__bar">
										</div>
									</div>
								</div>
							</div>
							<div class="app-visuals-slides mt-2">
								<div class="splide img-cover img-bordered has-arrow-navs" id="app-visuals-slides-small" tabindex="0">
									<div class="splide__track">
										<ul class="splide__list">
											@foreach($app->visuals as $item)
											<li class="splide__slide">
												<div class="splide__slide__container">
													<img src="{{ $item->thumbnail_url }}" >
												</div>
											</li>
											@endforeach
										</ul>
									</div>
								</div>
							</div>
						</div>
						@else
						<div class="placeholder-visuals-empty mock-bg" style="height: 300px;">
							<h5 class="placeholder-text">@lang('frontend.apps.empties.visual_media')</h5>
						</div>
						@endif

						<div class="card mt-3">
							<div class="card-body">
								@if($app->url)
								<div class="mb-2">
									<span class="text-bold">@lang('frontend.apps.fields.app_url'):</span>
									<a target="_blank" class="ml-1" href="{{ $app->url }}">{{ $app->url }} <span class="fas fa-external-link-alt"></span></a>
								</div>
								@endif
								@if(trim($app->description))
								<div class="text-bold">@lang('frontend.apps.fields.description'):</div>
								<span class="text-pre-line text-110">{{ $app->description }}</span>
								@else
								<h5>@lang('frontend.apps.empties.description')</h5>
								@endif
							</div>
						</div>
					</div>
					<div class="col-12 col-md-4 col-lg-4 col-xl-3 details-panel-right">
						<div class="card">
							<div class="card-body">
								<h4>@lang('frontend.apps.author')</h4>
								<div class="user-display vertical mb-2">
									<div class="user-logo-wrapper">
										<img src="{{ $app->owner->profile_picture }}" rel="User image">
									</div>
									<div class="user-text">
										<a href="{{ route('user.profile', ['user' => $app->owner->id]) }}">{{ $app->owner->name }}</a>
										@if($app->owner->prodi)
										<div class="text-secondary text-090 mt-1">{{ $app->owner->prodi->compact_name }}</div>
										@endif
									</div>
								</div>
								<p class="text-center text-090">{{ __('frontend.users.this_user_has_x_apps', ['x' => $app->owner->public_apps()->count()]) }}</p>
								@if($share_enabled)
								<div class="text-center mt-2">
									<a href="#app-{{ $app->id }}-share-modal" class="btn btn-primary btn-sm px-3 btn-flex-row" data-toggle="modal"><span class="fas fa-share-alt mr-2"></span> {{ __('frontend.apps.share_this_app') }}</a>
								</div>
								@endif
								<div class="text-center mt-3">
									<a href="#app-report-section" class="btn btn-danger btn-sm px-3 btn-flex-row" data-toggle="collapse">
										<span class="fas fa-exclamation-triangle mr-2 text-090"></span>
										<span class="text-wrap-word">@lang('frontend.apps.report_this_app')</span>
										<span class="fas fa-exclamation-triangle ml-2 text-090"></span>
									</a>
								</div>
							</div>
						</div>
						<div class="card mt-3">
							<div class="card-body">
								<h5>@lang('frontend.apps.additional_information')</h5>
								<dl class="text-090">
									<dt>@lang('frontend.apps.fields.last_updated')</dt>
									@if($app->last_changes_at)
									<dd>@include('components/date-with-tooltip', ['date' => $app->last_changes_at])</dd>
									@else
									<dd>@vo_</dd>
									@endif

									<dt>@lang('frontend.apps.fields.published_at')</dt>
									@if($app->published_at)
									<dd>@include('components/date-with-tooltip', ['date' => $app->published_at])</dd>
									@else
									<dd>@vo_</dd>
									@endif

									<dt>@lang('frontend.apps.fields.long_name')</dt>
									<dd>{{ $app->name }}</dd>

									<dt>@lang('frontend.apps.fields.short_name')</dt>
									<dd>@vo_($app->short_name)</dd>

									<dt>@lang('frontend.apps.fields.version')</dt>
									<dd>@vo_($app->version_number)</dd>

									<dt>@lang('frontend.apps.fields.app_categories') ({{ count($app->categories) }})</dt>
									<dd>
										@if(count($app->categories) > 0)
										@foreach($app->categories as $category)
										<a href="{{ route('apps', ['c' => $category->id]) }}" class="btn btn-sm btn-light bordered rounded-pill" title="{{ __('frontend.apps.search_by_this_category_x', ['x' => $category->name]) }}" data-toggle="tooltip">{{ $category->name }}</a>
										@endforeach
										@else
										@vo_
										@endif
									</dd>

									<dt>@lang('frontend.apps.fields.tags') ({{ count($app->tags) }})</dt>
									<dd>
										@if(count($app->tags) > 0)
										@foreach($app->tags as $tag)
										<a href="{{ route('apps', ['t' => $tag->name]) }}" class="btn btn-sm btn-light bordered rounded-pill" title="{{ __('frontend.apps.search_by_this_tag_x', ['x' => $tag->name]) }}" data-toggle="tooltip">{{ $tag->name }}</a>
										@endforeach
										@else
										@vo_
										@endif
									</dd>
								</dl>
							</div>
						</div>
					</div>
				</div>
			</div>
		</div>
		{{--
		<div class="tab-pane fade" id="comments-tabpane" role="tabpanel">
			<div class="container">
				asd
			</div>
		</div>
		--}}
	</div>

</main>
@endsection

@include('libraries.splide')

@if($view_mode == 'admin' && count($app->changelogs) > 0)
@push('scripts')
<div class="modal fade" id="app-{{ $app->id }}-version-selector" tabindex="-1" role="dialog" aria-labelledby="app-{{ $app->id }}-version-selector-title" aria-hidden="true">
	<div class="modal-dialog modal-sm" role="document">
		<div class="modal-content">
			<div class="modal-header">
				<h5 class="modal-title h4" id="app-{{ $app->id }}-version-selector-title">@lang('frontend.apps.notices.version_selector')</h5>
				<button type="button" class="close" data-dismiss="modal" aria-label="Close">
					<span aria-hidden="true">×</span>
				</button>
			</div>
			<div class="modal-body">
				<form method="GET" action="{{ url()->current() }}" class="form-app-version-selector">
					<select class="app-version-selector form-control" name="version" autocomplete="off">
						<option value="">&ndash; {{ __('common.choose') }} &ndash;</option>
						@foreach($app->changelogs as $cl)
						<option value="{{ $cl->version }}" {!! old_selected('', request('version', $app->version_number), $cl->version) !!}>
							{{ __('frontend.apps.version_x', ['x' => $cl->version]) }}
							@if($cl->is_rejected)
							({{ __('frontend.apps.notices.version_is_rejected') }})
							@endif
							@if($ori->version_number == $cl->version)
							({{ __('frontend.apps.notices.app_current_version') }})
							@endif
							@if($cl->version == (request('version', $app->version_number)))
							({{ __('frontend.apps.notices.being_previewed') }})
							@endif
						</option>
						@endforeach
					</select>
				</form>
			</div>
		</div>
	</div>
</div>
<script>
jQuery(document).ready(function($) {
	var curVer = @json(request('version', $app->version_number));
	$(".form-app-version-selector").on("change", ".app-version-selector", function(e) {
		var val = $(this).val();
		if(!val || val == curVer) return;

		$(".form-app-version-selector").submit();
	});
});
</script>
@endpush
@endif

@if($share_enabled)
<?php
$share_url = url()->current();
$whatsapp_params = http_build_query([
	'text'			=> $share_url."\n".$share_description,
]);
$facebook_params = http_build_query([
	'u'					=> $share_url,
	'display'		=> 'popup',
	'quote'			=> $share_description,
]);
$twitter_params = http_build_query([
	'url'				=> $share_url,
	'text'			=> $share_description,
]);
$linkedin_params = http_build_query([
	'mini'			=> 'true',
	'url'				=> $share_url,
]);
?>
@push('scripts')
<div class="modal fade" id="app-{{ $app->id }}-share-modal" tabindex="-1" role="dialog" aria-hidden="true">
	<div class="modal-dialog modal-dialog-centered" role="document">
		<div class="modal-content">
			<div class="modal-body pb-1">
				<h5>{{ __('frontend.share.share_on') }}:</h5>
				<div class="share-links justify-content-center align-items-center py-2">
					<a class="share-link-whatsapp" target="_blank" data-toggle="tooltip" title="{{ __('frontend.share.whatsapp') }}" href="https://api.whatsapp.com/send?{{ $whatsapp_params }}"><span class="fab fa-whatsapp"></span></a>
					<a class="share-link-facebook" target="_blank" data-toggle="tooltip" title="{{ __('frontend.share.facebook') }}" href="https://www.facebook.com/sharer/sharer.php?{{ $facebook_params }}"><span class="fab fa-facebook-square"></span></a>
					<a class="share-link-twitter" target="_blank" data-toggle="tooltip" title="{{ __('frontend.share.twitter') }}" href="https://twitter.com/intent/tweet?{{ $twitter_params }}"><span class="fab fa-twitter"></span></a>
					<a class="share-link-linkedin" target="_blank" data-toggle="tooltip" title="{{ __('frontend.share.linkedin') }}" href="https://www.linkedin.com/shareArticle?{{ $twitter_params }}"><span class="fab fa-linkedin"></span></a>
					<a class="share-link-link btn-copy-text" data-toggle="tooltip" title="{{ __('frontend.share.copy_link') }}" data-target="#app-{{ $app->id }}-share-url" href="{{ $share_url }}"><span class="fas fa-link"></span></a>
				</div>
				<div class="mt-2">
					<div class="input-group">
						<input type="text" class="form-control" value="{{ $share_url }}" id="app-{{ $app->id }}-share-url" readonly>
						<div class="input-group-append">
							<button type="button" class="btn btn-primary btn-copy-text" data-target="#app-{{ $app->id }}-share-url">{{ __('frontend.share.copy') }}</button>
						</div>
					</div>
				</div>
			</div>
			<div class="modal-footer py-1">
				<button type="button" class="btn btn-secondary btn-sm btn-block" data-dismiss="modal">{{ __('common.close') }}</button>
			</div>
		</div>
	</div>
</div>
@endpush
@endif

@push('scripts')
<script>
jQuery(document).ready(function($) {
	$('[data-toggle="popover"]').popover({
		container: "body",
	});

	$("#app-{{ $app->id }}-notices").on("hidden.bs.collapse", function(e) {
		Helpers.flashElement($("#app-{{ $app->id }}-notices-trigger"), {
			variant: "blue",
		});
	});


	$("#reportReason").textareaShowLength({
		position: "top right",
	}).textareaAutoHeight({
		bypassHeight: false,
	});

	@if($is_report_form)
	Helpers.scrollTo($("#report-app-form"));
	@endif


	@if($app->visuals->count() > 0)
	var splideOptionsBig = {
		type: "fade",
		gap: "1rem",
		rewind: true,
		width: "600px",
		height: "350px",
		autoHeight: true,
		heightRatio: 9/16,
		arrows: false,
		pagination: false,
		drag: true,
		keyboard: true,
		autoplay: 'pause',
		interval: 8000,
		intersection: {
			once: true,
			inView: {
				autoplay: true,
				video: true,
			},
			outView: {
				autoplay: false,
				video: false,
			},
		},
		video: {
			autoplay: true,
			loop: false,
			// HTML standards dictate that videos that autoplay should start muted
			mute: true,
			volume: 0.4,
		},
	};

	var splideOptionsSmall = {
		type: "slide",
		rewind: true,
		fixedWidth: 100,
		fixedHeight: 65,
		heightRatio: 9/16,
		arrows: true,
		pagination: false,
		isNavigation: true,
		keyboard: true,
		breakpoints: {
			767: {
				fixedWidth: 75,
				fixedHeight: 50,
			},
		},
	};

	var splideSlidesSmall = new Splide("#app-visuals-slides-small", splideOptionsSmall);
	var splideSlidesBig = new Splide("#app-visuals-slides-big", splideOptionsBig);

	splideSlidesSmall.mount( window.splide.Extensions );
	splideSlidesBig.mount( window.splide.Extensions );
	splideSlidesBig.sync(splideSlidesSmall);
	splideAutoplayWithVideo(splideSlidesBig);

	@endif

	var $scrollToElm = $(".scroll-to-me");
	if($scrollToElm.length > 0) {
			Helpers.scrollTo($scrollToElm.first(), {
				animate: false,
			});
	}

});
</script>
@endpush
