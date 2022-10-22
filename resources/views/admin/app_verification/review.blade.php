<?php

$old_attributes = optional($version->display_diffs['attributes']['old'] ?? null);
$diff_relations = optional($version->display_diffs['relations'] ?? null);

$is_edit = !!$verif->id;

$old_exists = old_input_exists();
$goto_form = !!($goto_form ?? false);
$goto_history = !!($goto_history ?? false);
$post_verif_status = $post_verif_status ?? null;
$post_edit = request()->has('post_edit');

$tab_review_active = '';
$tabpanel_review_active = '';
$tab_history_active = '';
$tabpanel_history_active = '';
$review_form_show = '';

if($goto_history
  || (!$is_edit && !$goto_form && !$post_edit && !$ori->has_pending_changes && !$old_exists && !$post_verif_status)
) {
  $tab_history_active = 'active';
  $tabpanel_history_active = 'active show';
} else {
  $tab_review_active = 'active';
  $tabpanel_review_active = 'active show';
}

if($tab_review_active
  && !$post_edit
  && (!$post_verif_status || $post_verif_status == 'revision-needed')
  && ($ori->has_pending_changes || $is_edit)) {
  $review_form_show = 'show';
}

$lverif = $ori->last_verification;
$lverif_callout = $lverif->is_reported_guilty ? 'callout-danger' : 'callout-info';

$rand = random_alpha(5);
?>

@extends('admin.layouts.main')

@section('page-title', __('admin.app_verification.page-title'))

@section('content')
<div class="mb-2">
  @can('view-any', App\Models\AppVerification::class)
  <a href="{{ route('admin.app_verifications.index') }}" class="btn btn-sm btn-default">&laquo; {{ __('common.back_to_list') }}</a>
  @endcan
</div>


@if($app->is_unverified_new)
<div class="alert alert-info">
  <p class="mb-0">@lang('admin/app_verifications.messages.this_unverified_item_is_new').</p>
</div>
@endif

@include('admin.app.detail-card', ['app' => $ori, 'hide_changes' => true, 'is_snippet' => true])

<div class="card card-primary card-outline card-outline-tabs">
  <div class="card-header p-0 border-bottom-0">
    <ul class="nav nav-tabs" role="tablist" id="aver-tabs-tab">
      <li class="nav-item">
        <a class="nav-link {{ $tab_review_active }}" role="presentation" data-toggle="pill" href="#aver-tab-review">@lang('admin/app_verifications.review_app')</a>
      </li>
      <li class="nav-item">
        <a class="nav-link {{ $tab_history_active }}" role="presentation" data-toggle="pill" href="#aver-tab-history">@lang('admin/app_verifications.verification_history') ({{ $app->verifications->count() }})</a>
      </li>
    </ul>
  </div>
  <div class="card-body p-0">
    <div class="tab-content" id="aver-tabs-content">
      <div id="aver-tab-review" class="tab-pane fade {{ $tabpanel_review_active }}" role="tabpanel">
        <form method="POST" action="{{ route('admin.app_verifications.verify', ['app' => $app->id]) }}" id="formInputReview" class="app-verification-form">
          <div class="list-group list-group-flush">
            <div class="list-group-item border-bottom-0" id="reviewEmptyHeader">
              @if($post_verif_status == 'approved')
              <div class="alert alert-success">
                @lang('admin/app_verifications.messages.app_verification_after_approved', ['base' => $lverif->base_changelog->version, 'final' => $app->version_number])
              </div>
              @elseif($post_verif_status == 'rejected')
              <div class="alert alert-danger">
                @lang('admin/app_verifications.messages.app_verification_after_rejected', ['base' => $lverif->base_changelog->version, 'final' => $app->version_number])
              </div>
              @elseif($post_verif_status == 'revision-needed')
              <div class="alert alert-warning">
                @lang('admin/app_verifications.messages.app_verification_after_revision-needed', ['base' => $lverif->base_changelog->version, 'final' => $app->version_number])
              </div>
              @endif

              <div class="callout {{ $lverif_callout }} last-verif-preview py-2">
                <strong>@lang('admin/app_verifications.last_verification')</strong>
                <div class="text-090">
                  @include('admin.app_verification.components.verif-list-item', ['verif' => $lverif, 'hide_edit' => false, 'other_comments' => true])
                </div>
              </div>

              @if(!($ori->has_pending_changes || $is_edit))
              <div class="callout callout-warning py-2 text-110">
                @lang('admin/app_verifications.this_app_does_not_have_any_pending_changes_to_be_reviewed')
                {{--
                <br>
                <button type="button" class="btn btn-warning" data-toggle="collapse" data-target="#reviewDetails">@lang('admin/app_verifications.review_anyway')</button>
                --}}
              </div>
              @endif
            </div>
            @if($ori->has_pending_changes || $is_edit)
            <div id="reviewDetails" class="collapse {{ $review_form_show }}">
              <div class="list-group-item verif-form-fields">
                <h4>
                  @if(!$is_edit)
                  @lang('admin/app_verifications.titles.verification')
                  @else
                  <span class="text-primary">@lang('admin/app_verifications.titles.editing_last_verification')</span>
                  <a href="{{ route('admin.app_verifications.review', ['app' => $verif->app_id]) }}" class="btn btn-warning btn-sm ml-1">@lang('common.cancel_edit')</a>
                  @endif
                </h4>

                @if($is_edit)
                <div class="alert alert-warning py-2">
                  @lang('admin/app_verifications.you_are_editing_the_last_verification')
                </div>
                @endif
                @if($ori->has_floating_changes)
                <div class="callout callout-info py-2">
                  @lang('admin/app_verifications.this_form_shows_the_app\'s_pending_changes')
                  <br>
                  @lang('admin/app_verifications.related_versions'): {{ $versions_range->rangeText() }}
                </div>
                @else
                <div class="callout callout-warning py-2">
                  @lang('admin/app_verifications.this_form_shows_version_x_the_app\'s_current_version', ['x' => $app->version_number])
                </div>
                @endif

                @if($ori->is_reported)
                <div class="alert alert-danger py-2">
                  <div class="icon-text-pair icon-2x icon-color-reset">
                    <span class="fas fa-exclamation-triangle icon"></span>
                    <div>
                      @lang('admin/app_verifications.this_app_was_recently_found_guilty_for_inappropriate_content')
                      <br>
                      @lang('admin/app_verifications.please_make_sure_the_offending_contents_have_been_removed')
                      @if($verif_report)
                      <br>
                      <a href="#" class="text-white btn-view-verif" data-app-id="{{ $ori->id }}" data-verif-id="{{ $verif_report->id }}">@lang('admin/common.check_details')</a>
                      @endif
                    </div>
                  </div>
                </div>
                @endif

                <div class="callout callout-info py-2">
                  @lang('admin/app_verifications.you_can_add_comments_to_any_related_fields_by_clicking_the_icon')
                </div>

                @csrf
                @method('POST')

                <input type="hidden" name="id" value="{{ $verif->id }}" >
                <input type="hidden" name="base_version" value="{{ $verif->base_version }}" >
                <input type="hidden" name="related_versions" value="{{ $verif->related_versions }}" >

                @include('components.page-message', ['show_errors' => true])

                <div class="row gutter-lg">
                  <div class="col-12 col-md-8 col-xl-6">
                    <div class="form-group">
                      <div class="clearfix">
                        <label>{{ __('admin/apps.fields.name') }}</label>
                        @component('admin.app.components.detail-old-value')
                          @isset($old_attributes['name'])
                            @voe($old_attributes['name'])
                          @endisset
                        @endcomponent
                        <a href="#" class="d-inline-block ml-1 btn-pop-comment" title="{{ __('admin/app_verifications.fields.add_comment_to_this_data') }}" data-target="#inputDetailsName">
                          <span class="fas fa-comment"></span>
                        </a>
                      </div>
                      <p class="value">@voe($app->name)</p>
                      <input type="hidden" name="details[name]" id="inputDetailsName" value="{{ old('details.name', $verif->attrs['name']) }}">
                    </div>

                    <div class="form-group">
                      <div class="clearfix">
                        <label>{{ __('admin/apps.fields.short_name') }}</label>
                        @component('admin.app.components.detail-old-value')
                          @isset($old_attributes['short_name'])
                            @voe($old_attributes['short_name'])
                          @endisset
                        @endcomponent
                        <a href="#" class="d-inline-block ml-1 btn-pop-comment" title="{{ __('admin/app_verifications.fields.add_comment_to_this_data') }}" data-target="#inputDetailsShortName">
                          <span class="fas fa-comment"></span>
                        </a>
                      </div>
                      <p class="value">@voe($app->short_name)</p>
                      <input type="hidden" name="details[short_name]" id="inputDetailsShortName" value="{{ old('details.short_name', $verif->attrs['short_name']) }}">
                    </div>

                    <div class="form-group">
                      <div class="clearfix">
                        <label>{{ __('admin/apps.fields.logo') }}</label>
                        @component('admin.app.components.detail-old-value')
                          @if(is_array($diff_relations['logo']) && array_key_exists('old', $diff_relations['logo']))
                            @include('components.app-logo', ['logo' => $diff_relations['logo']['old'], 'size' => '80x80'])
                          @endif
                        @endcomponent
                        <a href="#" class="d-inline-block ml-1 btn-pop-comment" title="{{ __('admin/app_verifications.fields.add_comment_to_this_data') }}" data-target="#inputDetailsLogo">
                          <span class="fas fa-comment"></span>
                        </a>
                      </div>
                      <div class="value">@include('components.app-logo', ['logo' => $app->logo, 'size' => '150x150'])</div>
                      <input type="hidden" name="details[logo]" id="inputDetailsLogo" value="{{ old('details.logo', $verif->attrs['logo']) }}">
                    </div>

                    <div class="form-group">
                      <div class="clearfix">
                        <label>{{ __('admin/apps.fields.url') }}</label>
                        @component('admin.app.components.detail-old-value')
                          @isset($old_attributes['url'])
                            @if($old_attributes['url'])
                            <a href="{{ $old_attributes['url'] }}" target="_blank">{{ $old_attributes['url'] }} <span class="fas fa-external-link-alt text-080 ml-1"></span></a>
                            @else
                            @von
                            @endif
                          @endisset
                        @endcomponent
                        <a href="#" class="d-inline-block ml-1 btn-pop-comment" title="{{ __('admin/app_verifications.fields.add_comment_to_this_data') }}" data-target="#inputDetailsUrl">
                          <span class="fas fa-comment"></span>
                        </a>
                      </div>
                      <p class="value">
                        @if($app->url)
                        <a href="{{ $app->url }}" target="_blank">{{ $app->url }}</a>
                        @else
                        @von
                        @endif
                      </p>
                      <input type="hidden" name="details[url]" id="inputDetailsUrl" value="{{ old('details.url', $verif->attrs['url']) }}">
                    </div>

                  </div>

                  <div class="col-12 col-md-4 col-xl-6">
                    <div class="form-group">
                      <div class="clearfix">
                        <label>
                          {{ __('admin/apps.fields.categories') }}
                          ({{ $app->categories->count() }})
                        </label>
                        @component('admin.app.components.detail-old-value')
                          @if(is_array($diff_relations['categories']) && array_key_exists('old', $diff_relations['categories']))
                            @if(($count = count($diff_relations['categories']['old'])) > 0)
                            (@lang('common.total_x', ['x' => $count]))
                            @each('components.app-category', $diff_relations['categories']['old'], 'category')
                            @else
                            @voe
                            @endif
                          @endisset
                        @endcomponent
                        <a href="#" class="d-inline-block ml-1 btn-pop-comment" title="{{ __('admin/app_verifications.fields.add_comment_to_this_data') }}" data-target="#inputDetailsCategories">
                          <span class="fas fa-comment"></span>
                        </a>
                      </div>
                      <div class="value">
                        @if($app->categories->isNotEmpty())
                        @each('components.app-category', $app->categories, 'category')
                        @else
                        @voe()
                        @endif
                      </div>
                      <input type="hidden" name="details[categories]" id="inputDetailsCategories" value="{{ old('details.categories', $verif->attrs['categories']) }}">
                    </div>

                    <div class="form-group">
                      <div class="clearfix">
                        <label>
                          {{ __('admin/apps.fields.tags') }}
                          ({{ $app->tags->count() }})
                        </label>
                        @component('admin.app.components.detail-old-value')
                          @if(is_array($diff_relations['tags']) && array_key_exists('old', $diff_relations['tags']))
                            @if(($count = count($diff_relations['tags']['old'])) > 0)
                            (@lang('common.total_x', ['x' => $count]))
                            @each('components.app-tag', $diff_relations['tags']['old'], 'tag')
                            @else
                            @voe
                            @endif
                          @endisset
                        @endcomponent
                        <a href="#" class="d-inline-block ml-1 btn-pop-comment" title="{{ __('admin/app_verifications.fields.add_comment_to_this_data') }}" data-target="#inputDetailsTags">
                          <span class="fas fa-comment"></span>
                        </a>
                      </div>
                      <div class="value">
                        @if($app->tags->isNotEmpty())
                        @each('components.app-tag', $app->tags, 'tag')
                        @else
                        @voe()
                        @endif
                      </div>
                      <input type="hidden" name="details[tags]" id="inputDetailsTags" value="{{ old('details.tags', $verif->attrs['tags']) }}">
                    </div>
                  </div>

                  <div class="col-12">
                    <div class="form-group">
                      <div class="clearfix">
                        <label>{{ __('admin/apps.fields.description') }}</label>
                        @component('admin.app.components.detail-old-value')
                          @isset($old_attributes['description'])
                            <span class="text-pre-wrap">@voe($old_attributes['description'])</span>
                          @endisset
                        @endcomponent
                        <a href="#" class="d-inline-block ml-1 btn-pop-comment" title="{{ __('admin/app_verifications.fields.add_comment_to_this_data') }}" data-target="#inputDetailsDescription">
                          <span class="fas fa-comment"></span>
                        </a>
                      </div>
                      <p class="value text-pre-wrap">@von($app->description)</p>
                      <input type="hidden" name="details[description]" id="inputDetailsDescription" value="{{ old('details.description', $verif->attrs['description']) }}">
                    </div>

                    <div class="form-group">
                      <div class="clearfix">
                        <label>
                          {{ __('admin/apps.fields.visuals') }}
                          ({{ $app->visuals->count() }})
                        </label>
                        @if(is_array($diff_relations['visuals']) && array_key_exists('old', $diff_relations['visuals']))
                          <a href="#visuals-old-{{ $rand }}" class="fas fa-history text-warning text-090 ml-2" title="@lang('admin/apps.visuals.visual_comparison_detail')" data-toggle="collapse" role="button"></a>
                        @endisset
                        <a href="#" class="d-inline-block ml-1 btn-pop-comment" title="{{ __('admin/app_verifications.fields.add_comment_to_this_data') }}" data-target="#inputDetailsThumbnails">
                          <span class="fas fa-comment"></span>
                        </a>
                      </div>
                      @include('admin.app.components.detail-visuals-list', ['visuals' => $app->visuals])
                      @if(is_array($diff_relations['visuals']) && array_key_exists('old', $diff_relations['visuals']))
                      <div class="collapse collapse-scrollto" id="visuals-old-{{ $rand }}">
                        <div class="text-090 text-bold">@lang('admin/apps.visuals.old_visuals') ({{ count($diff_relations['visuals']['old']) }})</div>
                        @include('admin.app.components.detail-visuals-list', ['visuals' => $diff_relations['visuals']['old'], 'smaller' => true])
                      </div>
                      @endisset
                      <input type="hidden" name="details[visuals]" id="inputDetailsThumbnails" value="{{ old('details.visuals', $verif->attrs['visuals']) }}">
                    </div>
                  </div>
                </div>
              </div>
              <div class="list-group-item verif-form-fields">
                <div class="form-group">
                  <label for="inputOverallComment">{{ __('admin/app_verifications.fields.overall_comments') }}</label>
                  <textarea name="overall_comment" id="inputOverallComment" class="form-control" placeholder="{{ __('admin/app_verifications.fields.overall_comments_hint') }}" rows="2" maxlength="1000" required>{{ old('overall_comment', $verif->comment) }}</textarea>
                </div>

                <div class="mt-2 mb-4">
                  <label>{{ __('admin/app_verifications.fields.verification_result') }}</label>
                  <div data-toggle="buttons">
                    <div class="row">
                      <div class="col-12 col-sm-6 col-lg-4">
                        <label class="btn btn-outline-{{ $vstatus['approved']->bg_style }} media media-btn pl-3 py-2">
                          <span class="media-icon mr-3 fa-fw {{ $vstatus['approved']->icon }}"></span>
                          <div class="media-body">
                            <p class="lead">{{ __($vstatus['approved']->name) }}</p>
                            <p>
                              {{ __('admin/app_verifications.status.approved_consequence') }}
                              @if($ori->is_reported)
                              <br>
                              {{ __('admin/app_verifications.status.approved_reported_consequence') }}
                              @endif
                            </p>
                            <input type="radio" name="verif_status" value="{{ $vstatus['approved']->id }}" class="btn-group-input vstatus-radio vstatus-radio-{{ $vstatus['approved']->id }}" {!! old_checked('verif_status', $verif->status_id, $vstatus['approved']->id) !!} >
                          </div>
                        </label>
                      </div>
                      <div class="col-12 col-sm-6 col-lg-4">
                        <label class="btn btn-outline-{{ $vstatus['rejected']->bg_style }} media media-btn pl-3 py-2">
                          <span class="media-icon mr-3 fa-fw {{ $vstatus['rejected']->icon }}"></span>
                          <div class="media-body">
                            <p class="lead">{{ __($vstatus['rejected']->name) }}</p>
                            <p>{{ __('admin/app_verifications.status.rejected_consequence') }}</p>
                            <input type="radio" name="verif_status" value="{{ $vstatus['rejected']->id }}" class="btn-group-input vstatus-radio vstatus-radio-{{ $vstatus['rejected']->id }}" {!! old_checked('verif_status', $verif->status_id, $vstatus['rejected']->id) !!} >
                          </div>
                        </label>
                      </div>
                      <div class="col-12 col-sm-6 col-lg-4">
                        <label class="btn btn-outline-{{ $vstatus['revision-needed']->bg_style }} media media-btn pl-3 py-2">
                          <span class="media-icon mr-3 fa-fw {{ $vstatus['revision-needed']->icon }}"></span>
                          <div class="media-body">
                            <p class="lead">{{ __($vstatus['revision-needed']->name) }}</p>
                            <p>{{ __('admin/app_verifications.status.revision-needed_consequence') }}</p>
                            <input type="radio" name="verif_status" value="{{ $vstatus['revision-needed']->id }}" class="btn-group-input vstatus-radio vstatus-radio-{{ $vstatus['revision-needed']->id }}" {!! old_checked('verif_status', $verif->status_id, $vstatus['revision-needed']->id) !!} >
                          </div>
                        </label>
                      </div>
                    </div>
                  </div>
                </div>
                <div class="mt-2 text-center">
                  @if(!$is_edit)
                  <button type="submit" class="btn btn-primary btn-min-100">{{ __('admin/app_verifications.verify') }}</button>
                  @else
                  <button type="submit" class="btn btn-info btn-min-100">{{ __('common.save') }}</button>
                  <br>
                  <a href="{{ route('admin.app_verifications.review', ['app' => $verif->app_id]) }}" class="btn btn-default btn-sm mt-3">@lang('common.cancel_edit')</a>
                  @endif
                </div>
                <div class="d-none" id="inputDetailsContent">
                  <div class="input-group">
                    <textarea class="form-control input-comment-text text-r090 lh-120" rows="1" placeholder="{{ __('admin/app_verifications.fields.comment_placeholder') }}" autocomplete="off" maxlength="200" style="max-height: 100px;"></textarea>
                  </div>
                </div>
              </div>
            </div>
            @endif
          </div>
        </form>
      </div>
      <div id="aver-tab-history" class="tab-pane fade {{ $tabpanel_history_active }}" role="tabpanel">
        @if($app->verifications->count() == 0)
        <h6>&mdash; @lang('admin/app_verifications.there_are_no_verifications_yet') &mdash;</h6>
        @else
        <div class="pt-3 px-3 pb-1 text-secondary"><em>(@lang('common.sorted_from_newest_to_oldest'))</em></div>
        <div class="verif-list verif-conversation">
          @foreach($app->verifications->reverse() as $verif)
            @include('admin.app_verification.components.verif-list-item', ['hide_edit' => false, 'other_comments' => true])
          @endforeach
        </div>
        @endif
      </div>
    </div>
  </div>
</div>

@endsection

@include('admin.app.changes.btn-view-version')
@include('admin.app_verification.btn-view-verif')

@push('scripts')
<script>
jQuery(document).ready(function($) {

  /*$(".version-select-list").on("click", ".version-select-item", function(e) {
    e.preventDefault();
  });*/

  $('[data-toggle="popover"]').popover({
    container: "body",
  });

  var $popCloseBtn = '<button type="button" class="close px-1">&times;</button>';
  $popCloseBtn = $($popCloseBtn);
  $popCloseBtn.on("click", function(e) {
    var $tip = $(this).closest(".popover");
    if($tip.length) {
      $tip.popover("hide");
    }
  });
  var $popTrashBtn = '<button type="button" class="close other-icon text-danger mx-2"><span class="fas fa-trash"></span></button>';
  $popTrashBtn = $($popTrashBtn);
  $popTrashBtn.on("click", function(e) {
    var $tip = $(this).closest(".popover");
    if($tip.length) {
      $tip.find(".input-comment-text").val(null);
      $tip.popover("hide");
    }
  });

  // Reposition popover so it won't conflict with the comment popover
  $("#formInputReview").find(".old-value-pop").popover("dispose").popover({
    placement: "top",
  });

  var $inputDetailsContent = $("#inputDetailsContent").remove().children();
  $("#formInputReview").popover({
    selector: ".btn-pop-comment",
    // container: "body",
    html: true,
    sanitize: false,
    // placement: "top",
    content: function() {
      return $inputDetailsContent.clone(true, true);
    },
  }).on("show.bs.popover", ".btn-pop-comment", function(e) {
    // Hide all the other popovers
    $("#formInputReview .btn-pop-comment").not(e.target).each(function(i, elm) {
      // Only target shown ones
      if($(elm).data("bs.popover")) {
        var $tip = $( $(elm).data("bs.popover").tip );
        if($tip.hasClass("show")) {
          $(elm).popover("hide");
        }
      }
    });

    var $elm = $(e.target),
        $tip = $( $elm.data("bs.popover").tip );

    var target = $elm.data("target"),
        $targetInput = $(target);

    setTimeout(function() {
      $tip.find(".popover-header").prepend($popTrashBtn.clone(true, true));
      $tip.find(".popover-header").prepend($popCloseBtn.clone(true, true));

      $input = $tip.find(".input-comment-text");
      $input.css("height", "").val( $targetInput.val() ).trigger("input");
      $input.on("input change", function(e) {
        $targetInput.val( $(this).val() );
      });
    }, 0);
  }).on("shown.bs.popover", ".btn-pop-comment", function(e) {
    var $elm = $(e.target),
        $tip = $( $elm.data("bs.popover").tip ),
        $input = $tip.find(".input-comment-text");
    $input.focus();
  }).on("hide.bs.popover", ".btn-pop-comment", function(e) {
    var $elm = $(e.target),
        $tip = $( $elm.data("bs.popover").tip ),
        $input = $tip.find(".input-comment-text");

    var target = $elm.data("target"),
        $targetInput = $(target);

    var value = (""+ $input.val()).trim();
    $targetInput.val(value);

    toggleCommentIconColor(e.target);
  }).on("hidden.bs.popover", ".btn-pop-comment", function(e) {
    var $elm = $(e.target),
        $tip = $( $elm.data("bs.popover").tip ),
        $input = $tip.find(".input-comment-text");

    $input.val(null);
  });

  $("#inputOverallComment").textareaShowLength().textareaAutoHeight({
    bypassHeight: false,
  });

  // Delegate
  $(document).textareaAutoHeight({
    selector: ".input-comment-text",
  });

  function toggleCommentIconColor(elm) {
    var $elm = $(elm);
    var target = $elm.data("target");
    if(target) {
      var $targetInput = $(target),
          value = $targetInput.val();

      $elm.toggleClass("has-comment", value != "");
    }
  }
  $("#formInputReview .btn-pop-comment").each(function(i, elm) {
    toggleCommentIconColor(elm);
  });

  $("#reviewDetails").on("show.bs.collapse", function(e) {
    // Check first to make sure the element is itself what we intended, and
    // not some descendants whose event bubbled up
    if(! $(e.target).is("#reviewDetails"))
      return;

    // Need to defer because during "show" event the element is not visible yet,
    // so it doesn't have a scroll offset. To scroll we need to do calculations
    // right after the element is set to be visible.
    setTimeout(function() {
      scrollToForm();
    }, 10);
  });

  var $reviewHeader = $("#reviewEmptyHeader");
  if($reviewHeader.is(":visible") && $reviewHeader.children().length > 1) {
    Helpers.scrollTo($reviewHeader);
  }

  function scrollToForm(animate) {
    if(typeof animate === "undefined")
      animate = true;
    Helpers.scrollTo($("#reviewDetails"), {
      offset: -50,
      animate: animate,
    });
  }

  @if($old_exists || $is_edit)
  // Activate tab containing form and scroll to it
  /*var tabPaneWithForm = "aver-tab-review",
      tabPaneWithFormId = "#"+ tabPaneWithForm;
  var $tabPaneWithForm = $(tabPaneWithFormId);
  var $tabWithForm = $('a[href="'+ tabPaneWithFormId +'"]');
  $tabWithForm.one("shown.bs.tab", function(e) {
  }).tab("show");*/
  scrollToForm(false);
  @endif

  $("#formInputReview").noEnterSubmit();

});
</script>

@endpush
