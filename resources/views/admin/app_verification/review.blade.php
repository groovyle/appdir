@extends('admin.layouts.main')

@section('page-title', __('admin.app_verification.page-title'))

@section('content')
  <!-- Card -->
  <div class="card">
    <div class="card-body">
      @if($errors->any())
      <div class="alert alert-danger">
        <ul>
          @foreach($errors->all() as $errmsg)
          <li>{{ $errmsg }}</li>
          @endforeach
        </ul>
      </div>
      @endif
      <form method="POST" action="{{ route('admin.app_verifications.verify', ['app' => $app->id]) }}" enctype="multipart/form-data" id="formInputReview">
        @csrf
        @method('POST')

        <div class="row">
          <div class="col-12 col-md-8 col-xl-6">
            <div class="form-group">
              <div class="clearfix">
                <label>{{ __('admin.app.field.name') }}</label>
                <a href="#" class="d-inline-block ml-1 btn-pop-comment" title="{{ __('admin.app_verifications.comment_field.name') }}" data-target="#inputDetailsName">
                  <span class="fas fa-comment"></span>
                </a>
              </div>
              <p>{{ $app->name }}</p>
              <input type="hidden" name="details[name]" id="inputDetailsName" value="{{ old('details.name') }}">
            </div>

            <div class="form-group">
              <div class="clearfix">
                <label>{{ __('admin.app.field.description') }}</label>
                <a href="#" class="d-inline-block ml-1 btn-pop-comment" title="{{ __('admin.app_verifications.comment_field.description') }}" data-target="#inputDetailsDescription">
                  <span class="fas fa-comment"></span>
                </a>
              </div>
              <p>{!! description_text($app->description) !!}</p>
              <input type="hidden" name="details[description]" id="inputDetailsDescription" value="{{ old('details.description') }}">
            </div>

            <div class="form-group">
              <div class="clearfix">
                <label>{{ __('admin.app.field.directory') }}</label>
                <a href="#" class="d-inline-block ml-1 btn-pop-comment" title="{{ __('admin.app_verifications.comment_field.directory') }}" data-target="#inputDetailsDirectory">
                  <span class="fas fa-comment"></span>
                </a>
              </div>
              <p>{{ $app->full_directory }}</p>
              <input type="hidden" name="details[directory]" id="inputDetailsDirectory" value="{{ old('details.directory') }}">
            </div>

            <div class="form-group">
              <div class="clearfix">
                <label>{{ __('admin.app.field.url') }}</label>
                <a href="#" class="d-inline-block ml-1 btn-pop-comment" title="{{ __('admin.app_verifications.comment_field.url') }}" data-target="#inputDetailsUrl">
                  <span class="fas fa-comment"></span>
                </a>
              </div>
              <p>
                <a href="{{ $app->full_url }}" target="_blank" class="text-primary">
                  {{ $app->full_url }}
                  <span class="fas fa-xs fa-external-link-alt"></span>
                </a>
              </p>
              <input type="hidden" name="details[url]" id="inputDetailsUrl" value="{{ old('details.url') }}">
            </div>

            <div class="form-group">
              <div class="clearfix">
                <label>{{ __('admin.app.field.categories') }}</label>
                <a href="#" class="d-inline-block ml-1 btn-pop-comment" title="{{ __('admin.app_verifications.comment_field.categories') }}" data-target="#inputDetailsCategories">
                  <span class="fas fa-comment"></span>
                </a>
              </div>
              @if (!empty($app->categories))
              <ul class="pt-0 pb-1">
              @foreach ($app->categories as $category)
              <li>{{ $category->name }}</li>
              @endforeach
              </ul>
              @else
              &ndash;
              @endif
              <input type="hidden" name="details[categories]" id="inputDetailsCategories" value="{{ old('details.categories') }}">
            </div>

            <div class="form-group">
              <div class="clearfix">
                <label>{{ __('admin.app.field.tags') }}</label>
                <a href="#" class="d-inline-block ml-1 btn-pop-comment" title="{{ __('admin.app_verifications.comment_field.tags') }}" data-target="#inputDetailsTags">
                  <span class="fas fa-comment"></span>
                </a>
              </div>
              <div>
              @forelse ($app->tags as $tag)
              <a href="#" class="btn btn-sm btn-default rounded-pill" data-toggle="popover" data-content="{{ $tag->name }}" data-trigger="focus" data-placement="top">{{ $tag->name }}</a>
              @empty
              &ndash;
              @endforelse
              </div>
              <input type="hidden" name="details[tags]" id="inputDetailsTags" value="{{ old('details.tags') }}">
            </div>

            <div class="form-group">
              <div class="clearfix">
                <label>{{ __('admin.app.field.visuals') }}</label>
                <a href="#" class="d-inline-block ml-1 btn-pop-comment" title="{{ __('admin.app_verifications.comment_field.visuals') }}" data-target="#inputDetailsThumbnails">
                  <span class="fas fa-comment"></span>
                </a>
              </div>
              <div class="thumb-cards d-flex justify-content-start align-items-start mb-2">
              @foreach ($app->visuals as $visual)
              @php
              $i = $loop->iteration;
              @endphp
              <div class="thumb-item mr-2 mb-2">
                <div class="card m-0">
                  <div class="card-img-top">
                    <img src="{{ $visual->url }}" alt="{{ __('common.visual').' '.$i }}">
                  </div>
                  <div class="card-body p-1">
                    <p class="card-text text-secondary mb-1">{{ $visual->caption ? $visual->caption : __('common.visual').' '.$i }}</p>
                  </div>
                </div>
              </div>
              @endforeach
              </div>
              <input type="hidden" name="details[visuals]" id="inputDetailsThumbnails" value="{{ old('details.visuals') }}">
            </div>

            <hr>

            <div class="form-group">
              <label for="inputComment">{{ __('admin.app_verification.comment') }}</label>
              <textarea name="comment" id="inputComment" class="form-control" placeholder="{{ __('admin.app_verification.hint.comment') }}" rows="2"></textarea>
            </div>

          </div>
        </div>
        <div class="my-2" data-toggle="buttons">
          <div class="row mb-4">
            <div class="col-12 col-sm-6 col-lg-4">
              <label class="btn btn-outline-{{ $vstatus['revision-needed']->bg_style }} media media-btn pl-3 py-2">
                <span class="media-icon mr-3 fa-fw {{ $vstatus['revision-needed']->icon }}"></span>
                <div class="media-body">
                  <p class="lead">{{ __($vstatus['revision-needed']->name) }}</p>
                  <p>{{ __($vstatus['revision-needed']->description) }}</p>
                  <input type="radio" name="status" value="{{ $vstatus['revision-needed']->code }}" class="vstatus-radio vstatus-radio-{{ $vstatus['revision-needed']->code }} d-none" {!! old_checked('status', NULL, $vstatus['revision-needed']->code) !!} >
                </div>
              </label>
            </div>
            <div class="col-12 col-sm-6 col-lg-4">
              <label class="btn btn-outline-{{ $vstatus['approved']->bg_style }} media media-btn pl-3 py-2">
                <span class="media-icon mr-3 fa-fw {{ $vstatus['approved']->icon }}"></span>
                <div class="media-body">
                  <p class="lead">{{ __($vstatus['approved']->name) }}</p>
                  <p>{{ __($vstatus['approved']->description) }}</p>
                  <input type="radio" name="status" value="{{ $vstatus['approved']->code }}" class="vstatus-radio vstatus-radio-{{ $vstatus['approved']->code }} d-none" {!! old_checked('status', NULL, $vstatus['approved']->code) !!} >
                </div>
              </label>
            </div>
            <div class="col-12 col-sm-6 col-lg-4">
              <label class="btn btn-outline-{{ $vstatus['rejected']->bg_style }} media media-btn pl-3 py-2">
                <span class="media-icon mr-3 fa-fw {{ $vstatus['rejected']->icon }}"></span>
                <div class="media-body">
                  <p class="lead">{{ __($vstatus['rejected']->name) }}</p>
                  <p>{{ __($vstatus['rejected']->description) }}</p>
                  <input type="radio" name="status" value="{{ $vstatus['rejected']->code }}" class="vstatus-radio vstatus-radio-{{ $vstatus['rejected']->code }} d-none" {!! old_checked('status', NULL, $vstatus['rejected']->code) !!} >
                </div>
              </label>
            </div>
          </div>
        </div>
        <div class="mt-2">
          <button type="submit" class="btn btn-primary">{{ __('admin.app_verifications.verify') }}</button>
          <a href="{{ url()->previous() }}" class="btn btn-default">{{ __('common.back') }}</a>
        </div>
        <div class="d-none" id="inputDetailsContent">
          <div class="input-group">
            <textarea class="form-control input-comment-text" rows="1" placeholder="{{ __('common.comment') }}" autocomplete="off"></textarea>
          </div>
        </div>
      </form>
    </div>
    <!-- /.card-body -->
  </div>
  <!-- /.card -->
@endsection

@push('head-additional')
<style>
  .input-comment-text {
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

@push('scripts')
<script>
jQuery(document).ready(function($) {
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
  var $popTrashBtn = '<button type="button" class="close other-icon text-danger text-sm mr-2"><span class="fas fa-trash"></span></button>';
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
      $input.css("height", "").val( $targetInput.val() );
      // $input.one("change", function(e) {
      //   $targetInput.val( $(this).val() );
      // });
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
    // $input.val(null);

    toggleCommentIconColor(e.target);
  }).on("hidden.bs.popover", ".btn-pop-comment", function(e) {
    var $elm = $(e.target),
        $tip = $( $elm.data("bs.popover").tip ),
        $input = $tip.find(".input-comment-text");

    $input.val(null);
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

});
</script>

@endpush
