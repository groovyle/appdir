@extends('admin.layouts.main')

@section('page-title', __('admin.app_verification.page-title'))

@section('content')
<div class="mb-2">
  <a href="{{ route('admin.app_verifications.index') }}" class="btn btn-sm btn-default">&laquo; {{ __('common.back_to_list') }}</a>
</div>


@if($app->is_unverified_new)
<div class="alert alert-info">
  <p class="mb-0">@lang('admin/app_verifications.messages.this_unverified_item_is_new').</p>
</div>
@endif

<div class="row">
  <div class="col-md-3">
    <div class="card">
      <div class="card-header">
        <h4 class="card-title">@lang('admin/app_verifications.app_versions')</h4>
      </div>
      <ul class="list-group ofy-auto version-select-list" style="max-height: 300px;">
        @foreach($app->changelogs as $cl)
        <?php
        $is_base_version = $base_app->version && $base_app->version_number == $cl->version;
        $base_version = $is_base_version ? 'base-version' : '';
        $is_this_page = $current_version_number == $cl->version;
        $this_page = $is_this_page ? 'active' : ($is_base_version ? 'bg-gray-light' : '');
        ?>
        <a class="list-group-item list-group-item-action {{ $this_page }} {{ $base_version }} version-select-item rounded-0" data-version="{{ $cl->version }}" href="{{ !$is_this_page ? route('admin.app_verifications.advanced_review', ['app' => $app->id, 'version' => $cl->version]) : '#' }}">
          @lang('admin/app_verifications.version_x', ['x' => $cl->version])
          @if($is_base_version)
          <span class="small">(@lang('admin/app_verifications.active'))</span>
          @endif
          @if($cl->is_verified)
          <span class="fas fa-check text-090 mx-1" title="@lang('admin/app_verifications.verified_version')" data-toggle="tooltip"></span>
          @else
          <span class="far fa-clock text-080 mx-1" title="@lang('admin/app_verifications.unverified_version')" data-toggle="tooltip"></span>
          @endif
        </a>
        @endforeach
      </ul>
    </div>
  </div>
  <div class="col-md-9">
    @include('admin.app.changes.list-item', ['app' => $base_app, 'cl' => $version, 'collapsed' => true])

    <div class="card card-primary card-outline card-outline-tabs">
      <div class="card-header p-0 border-bottom-0">
        <ul class="nav nav-tabs" role="tablist" id="aver-tabs-tab">
          <li class="nav-item">
            <a class="nav-link active" role="presentation" data-toggle="pill" href="#aver-tab-history">@lang('admin/app_verifications.verification_history')</a>
          </li>
          <li class="nav-item">
            <a class="nav-link" role="presentation" data-toggle="pill" href="#aver-tab-verify">@lang('admin/app_verifications.verify')</a>
          </li>
        </ul>
      </div>
      <div class="card-body p-0">
        <div class="tab-content" id="aver-tabs-content">
          <div id="aver-tab-history" class="tab-pane fade show active" role="tabpanel">
            <div class="m-4">
              <h5>@lang('admin/app_verifications.verifications')</h5>
            </div>
            @if($version->verifications->count() == 0)
            <h6>&mdash; @lang('admin/app_verifications.there_are_no_verifications_yet') &mdash;</h6>
            @else
            <div class="list-group list-group-flush">
              @foreach($version->verifications as $verif)
              <div class="list-group-item">
                <h6 class="mb-0">
                  <strong>{{ $verif->verifier->name }}</strong>
                  <span class="text-muted text-090 mx-1">@lang('admin/app_verifications.verified')</span>
                  {!! status_badge($verif->status->name, $verif->status->bg_style, 'style="font-weight: normal; font-size: 0.9em;"') !!}
                  <button type="button" class="btn btn-tool btn-view-verif" data-toggle="tooltip" title="@lang('admin/app_verifications.view_this_verification')" data-app-id="{{ $app->id }}" data-verif-id="{{ $verif->id }}"><span class="fas fa-search"></span></button>
                </h6>
                <div class="text-muted small mb-1">@include('components.date-with-tooltip', ['date' => $verif->created_at])</div>
                <div class="text-090">
                  <div>
                    <span class="far fa-copy mx-1"></span>
                    @lang('admin/app_verifications.reviewed_versions'): {{ $verif->changelogs->pluck('version')->reverse()->values()->implode(', ') }}
                  </div>
                  <div class="text-bold">@lang('admin/app_verifications.comments'):</div>
                  <div>
                    @foreach($verif->details as $field => $comment)
                    <div class="d-inline-block mr-5">
                      <span class="fas fa-comment mr-1 text-light-gray text-090"></span>
                      {{ __('admin/apps.fields.'.$field) }}: {{ $comment }}
                    </div>
                    @endforeach
                  </div>
                  <div class=""><strong>@lang('admin/app_verifications.fields.overall_comments'):</strong> {{ $verif->comment }}</div>
                </div>
              </div>
              @endforeach
            </div>
            @endif
          </div>
          <div id="aver-tab-verify" class="tab-pane fade" role="tabpanel">
            <form method="POST" action="{{ route('admin.app_verifications.advanced_verify', ['app' => $app->id]) }}" id="formInputReview" class="app-verification-form">
              <div class="list-group list-group-flush">
                <div class="list-group-item">
                  <h4>{{ __('admin/app_verifications.titles.verification') }}</h4>
                  <div class="callout callout-info py-2">
                    @lang('admin/app_verifications.you_can_add_comments_to_any_related_fields_by_clicking_the_icon')
                  </div>

                  @csrf
                  @method('POST')

                  <input type="hidden" name="version" value="{{ $version->version }}" readonly>

                  @include('components.page-message', ['show_errors' => true])

                  <div class="row gutter-lg">
                    <div class="col-12 col-md-8 col-xl-6">
                      <div class="form-group">
                        <div class="clearfix">
                          <label>{{ __('admin/apps.fields.name') }}</label>
                          <a href="#" class="d-inline-block ml-1 btn-pop-comment" title="{{ __('admin/app_verifications.fields.add_comment_to_this_data') }}" data-target="#inputDetailsName">
                            <span class="fas fa-comment"></span>
                          </a>
                        </div>
                        <p class="value">@voe($app->name)</p>
                        <input type="hidden" name="details[name]" id="inputDetailsName" value="{{ old('details.name') }}">
                      </div>

                      <div class="form-group">
                        <div class="clearfix">
                          <label>{{ __('admin/apps.fields.short_name') }}</label>
                          <a href="#" class="d-inline-block ml-1 btn-pop-comment" title="{{ __('admin/app_verifications.fields.add_comment_to_this_data') }}" data-target="#inputDetailsShortName">
                            <span class="fas fa-comment"></span>
                          </a>
                        </div>
                        <p class="value">@voe($app->short_name)</p>
                        <input type="hidden" name="details[short_name]" id="inputDetailsShortName" value="{{ old('details.short_name') }}">
                      </div>

                      <div class="form-group">
                        <div class="clearfix">
                          <label>{{ __('admin/apps.fields.url') }}</label>
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
                        <input type="hidden" name="details[url]" id="inputDetailsUrl" value="{{ old('details.url') }}">
                      </div>

                    </div>

                    <div class="col-12 col-md-4 col-xl-6">
                      <div class="form-group">
                        <div class="clearfix">
                          <label>
                            {{ __('admin/apps.fields.categories') }}
                            ({{ $app->categories->count() }})
                          </label>
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
                        <input type="hidden" name="details[categories]" id="inputDetailsCategories" value="{{ old('details.categories') }}">
                      </div>

                      <div class="form-group">
                        <div class="clearfix">
                          <label>
                            {{ __('admin/apps.fields.tags') }}
                            ({{ $app->tags->count() }})
                          </label>
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
                        <input type="hidden" name="details[tags]" id="inputDetailsTags" value="{{ old('details.tags') }}">
                      </div>
                    </div>

                    <div class="col-12">
                      <div class="form-group">
                        <div class="clearfix">
                          <label>{{ __('admin/apps.fields.logo') }}</label>
                          <a href="#" class="d-inline-block ml-1 btn-pop-comment" title="{{ __('admin/app_verifications.fields.add_comment_to_this_data') }}" data-target="#inputDetailsLogo">
                            <span class="fas fa-comment"></span>
                          </a>
                        </div>
                        <div class="value">@include('components.app-logo', ['logo' => $app->logo, 'size' => '150x150'])</div>
                        <input type="hidden" name="details[logo]" id="inputDetailsLogo" value="{{ old('details.logo') }}">
                      </div>

                      <div class="form-group">
                        <div class="clearfix">
                          <label>{{ __('admin/apps.fields.description') }}</label>
                          <a href="#" class="d-inline-block ml-1 btn-pop-comment" title="{{ __('admin/app_verifications.fields.add_comment_to_this_data') }}" data-target="#inputDetailsDescription">
                            <span class="fas fa-comment"></span>
                          </a>
                        </div>
                        <p class="value text-pre-wrap">@von($app->description)</p>
                        <input type="hidden" name="details[description]" id="inputDetailsDescription" value="{{ old('details.description') }}">
                      </div>

                      <div class="form-group">
                        <div class="clearfix">
                          <label>
                            {{ __('admin/apps.fields.visuals') }}
                            ({{ $app->visuals->count() }})
                          </label>
                          <a href="#" class="d-inline-block ml-1 btn-pop-comment" title="{{ __('admin/app_verifications.fields.add_comment_to_this_data') }}" data-target="#inputDetailsThumbnails">
                            <span class="fas fa-comment"></span>
                          </a>
                        </div>
                        @include('admin.app.components.detail-visuals-list', ['visuals' => $app->visuals])
                        <input type="hidden" name="details[visuals]" id="inputDetailsThumbnails" value="{{ old('details.visuals') }}">
                      </div>

                      <hr>

                      <div class="form-group">
                        <label for="inputOverallComment">{{ __('admin/app_verifications.fields.overall_comments') }}</label>
                        <textarea name="overall_comment" id="inputOverallComment" class="form-control" placeholder="{{ __('admin/app_verifications.fields.overall_comments_hint') }}" rows="2" maxlength="1000" required>{{ old('overall_comment') }}</textarea>
                      </div>
                    </div>
                  </div>
                </div>
                <div class="list-group-item">
                  <div class="mt-2 mb-4" data-toggle="buttons">
                    <div class="row">
                      <div class="col-12 col-sm-6 col-lg-4">
                        <label class="btn btn-outline-{{ $vstatus['approved']->bg_style }} media media-btn pl-3 py-2">
                          <span class="media-icon mr-3 fa-fw {{ $vstatus['approved']->icon }}"></span>
                          <div class="media-body">
                            <p class="lead">{{ __($vstatus['approved']->name) }}</p>
                            <p>{{ __($vstatus['approved']->description) }}</p>
                            <input type="radio" name="verif_status" value="{{ $vstatus['approved']->id }}" class="btn-group-input vstatus-radio vstatus-radio-{{ $vstatus['approved']->id }}" {!! old_checked('verif_status', NULL, $vstatus['approved']->id) !!} >
                          </div>
                        </label>
                      </div>
                      <div class="col-12 col-sm-6 col-lg-4">
                        <label class="btn btn-outline-{{ $vstatus['rejected']->bg_style }} media media-btn pl-3 py-2">
                          <span class="media-icon mr-3 fa-fw {{ $vstatus['rejected']->icon }}"></span>
                          <div class="media-body">
                            <p class="lead">{{ __($vstatus['rejected']->name) }}</p>
                            <p>{{ __($vstatus['rejected']->description) }}</p>
                            <input type="radio" name="verif_status" value="{{ $vstatus['rejected']->id }}" class="btn-group-input vstatus-radio vstatus-radio-{{ $vstatus['rejected']->id }}" {!! old_checked('verif_status', NULL, $vstatus['rejected']->id) !!} >
                          </div>
                        </label>
                      </div>
                      <div class="col-12 col-sm-6 col-lg-4">
                        <label class="btn btn-outline-{{ $vstatus['revision-needed']->bg_style }} media media-btn pl-3 py-2">
                          <span class="media-icon mr-3 fa-fw {{ $vstatus['revision-needed']->icon }}"></span>
                          <div class="media-body">
                            <p class="lead">{{ __($vstatus['revision-needed']->name) }}</p>
                            <p>{{ __($vstatus['revision-needed']->description) }}</p>
                            <input type="radio" name="verif_status" value="{{ $vstatus['revision-needed']->id }}" class="btn-group-input vstatus-radio vstatus-radio-{{ $vstatus['revision-needed']->id }}" {!! old_checked('verif_status', NULL, $vstatus['revision-needed']->id) !!} >
                          </div>
                        </label>
                      </div>
                    </div>
                  </div>
                  <div class="mt-2">
                    <button type="submit" class="btn btn-primary">{{ __('admin.app_verifications.verify') }}</button>
                  </div>
                  <div class="d-none" id="inputDetailsContent">
                    <div class="input-group">
                      <textarea class="form-control input-comment-text" rows="1" placeholder="{{ __('admin/app_verifications.fields.comment_placeholder') }}" autocomplete="off" maxlength="200"></textarea>
                    </div>
                  </div>
                </div>
              </div>
            </form>
          </div>
        </div>
      </div>
    </div>

  </div>
</div>

@endsection

@push('head-additional')
<style>
  textarea.input-comment-text {
    /*height: 60px;*/
    max-height: 100px;
    font-size: 0.9rem;
    line-height: 1.2;
  }
  .close.other-icon {
    font-size: 1rem;
    line-height: 1.5;
  }
</style>
@endpush

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
  var $popTrashBtn = '<button type="button" class="close other-icon text-danger text-sm mx-2"><span class="fas fa-trash"></span></button>';
  $popTrashBtn = $($popTrashBtn);
  $popTrashBtn.on("click", function(e) {
    var $tip = $(this).closest(".popover");
    if($tip.length) {
      $tip.find(".input-comment-text").val(null);
      $tip.popover("hide");
    }
  });

  var $inputDetailsContent = $("#inputDetailsContent").remove().children();
  $("#formInputReview").popover({
    selector: ".btn-pop-comment",
    container: "body",
    html: true,
    sanitize: false,
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


  // Scroll to the active version item in list
  var $versionList = $(".version-select-list");
  var $activeVersion = $versionList.children(".version-select-item.active");
  if($activeVersion.length == 0)
    $activeVersion = $versionList.children(".version-select-item.base-version");
  Helpers.parentScrollTo($activeVersion, {
    percentageOffset: 20,
    animate: false,
  });

  @if(old_input_exists())
  // Activate tab containing form and scroll to it
  var tabPaneWithForm = "aver-tab-verify",
      tabPaneWithFormId = "#"+ tabPaneWithForm;
  var $tabPaneWithForm = $(tabPaneWithFormId);
  var $tabWithForm = $('a[href="'+ tabPaneWithFormId +'"]');
  $tabWithForm.one("shown.bs.tab", function(e) {
    Helpers.scrollTo($tabPaneWithForm);
  }).tab("show");
  @endif

  $("#formInputReview").noEnterSubmit();

});
</script>

@endpush
