<?php
$use_mock = isset($ori);
$titler = $use_mock ? $ori : $app;
if(!$is_edit) {
  $append_breadcrumb = [
    [
      'text'    => __('common.add'),
    ]
  ];
} else {
  $append_breadcrumb = [
    [
      'text'    => text_truncate($titler->name, 50),
      'url'     => route('admin.apps.show', ['app' => $app->id]),
      'active'  => false,
    ],
    [
      'text'    => __('common.edit'),
    ]
  ];
}
?>

@extends('admin.layouts.main')

@section('title')
{{ __('admin/apps.tab_title') }} - @parent
@endsection

@section('page-title', __('admin/apps.page_title.'. ($is_edit ? 'edit' : 'add')) )

@section('content')

<div class="mb-2">
  @if($is_edit)
  <a href="{{ route('admin.apps.show', ['app' => $app->id]) }}" class="btn btn-sm btn-default">&laquo; {{ __('common.back') }}</a>
  @else
  <a href="{{ route('admin.apps.index') }}" class="btn btn-sm btn-default">&laquo; {{ __('common.back_to_list') }}</a>
  @endif
</div>

<form method="POST" action="{{ $action }}" enctype="multipart/form-data" id="formInputApp">
  @csrf
  @method($method)

  @if($is_edit && $pending_edits)
  <div class="alert alert-warning">
    <h5>{{ __('common.attention') }}!</h5>
    <p class="mb-0">{{ __('admin/apps.messages.edits_will_be_staged') }}.</p>
  </div>
  @elseif(!$is_edit && $pending_add)
  <div class="alert alert-warning">
    <h5>{{ __('common.attention') }}!</h5>
    <p class="mb-0">{{ __('admin/apps.messages.new_items_will_be_staged') }}.</p>
  </div>
  @endif

  @if($use_mock)
  <div class="alert alert-info">
    <p class="mb-0">
      @lang('admin/apps.messages.form_showing_pending_changes') (<strong>@lang('admin/apps.changes.version_x', ['x' => $app->version_number])</strong>).
      <br>
      <a href="#" class="text-reset btn-view-version" data-app-id="{{ $ori->id }}" data-version="{{ $ori->version_number }}">
        @lang('admin/apps.changes.show_current_version') (@lang('admin/apps.changes.version_x', ['x' => $ori->version_number]))
        <span class="fas fa-search ml-1"></span>
      </a>
    </p>
  </div>
  @endif

  @if($errors->any())
  <div class="alert alert-danger">
    <ul>
      @foreach($errors->all() as $errmsg)
      <li>{{ $errmsg }}</li>
      @endforeach
    </ul>
  </div>
  @endif

  <!-- Card -->
  <div class="card">
    <ul class="list-group list-group-flush">
      <li class="list-group-item">
        <h4>{{ __('admin/apps.titles.app_info') }}</h4>
        <div class="row gutter-lg">
          <div class="col-12 col-md-8 col-xl-6">
            <div class="form-group">
              <label for="inputAppName">{{ __('admin/apps.fields.name') }}</label>
              <input type="text" name="app_name" class="form-control" id="inputAppName" placeholder="{{ __('admin/apps.fields.name_placeholder') }}" value="{{ old('app_name', $app->name) }}" maxlength="100" required>
            </div>

            <div class="form-group mt-n3 ml-1">
              <div class="form-check">
                <input type="checkbox" name="app_has_short_name" class="form-check-input" id="hasShortName" value="1" {{ old_checked('app_has_short_name', !empty($app->short_name)) }} >
                <label class="form-check-label text-sm" for="hasShortName">{{ __('admin/apps.fields.has_short_name?') }}</label>
              </div>
              <div class="mt-0 ml-4 d-none" id="wrapperShortName">
                <input type="text" name="app_short_name" class="form-control form-control-sm maxw-100" id="inputAppShortName" placeholder="{{ __('admin/apps.fields.short_name_placeholder') }}" value="{{ old('app_short_name', $app->short_name) }}" maxlength="20" style="width: 40ch;">
              </div>
            </div>

            <div class="form-group">
              <label for="inputAppUrl">{{ __('admin/apps.fields.url') }}</label>
              <a href="#" class="d-inline-block ml-2" data-toggle="popover" data-content="{{ __('admin/apps.fields.url_hint') }}" data-html="true" data-trigger="click">
                <span class="far fa-question-circle text-muted"></span>
              </a>
              <input type="text" name="app_url" class="form-control" id="inputAppUrl" placeholder="{{ __('admin/apps.fields.url_placeholder') }}" value="{{ old('app_url', $app->url) }}" maxlength="200">
            </div>

            {{--
            <div class="form-group">
              <label for="inputAppType">{{ __('admin/apps.fields.type') }}</label>
              <select name="type" class="custom-select d-block w-auto" id="inputAppType" style="min-width: 100px;">
                <option value="">&ndash; {{ __('admin/apps.fields.type_placeholder') }} &ndash;</option>
                @if (!empty($types))
                {!! generate_options($types, old('type', $app->type_id)) !!}
                @endif
              </select>
            </div>
            --}}
          </div>

          <div class="col-12 col-md-4 col-xl-6">
            <div class="form-group">
              <label for="inputAppCategories">{{ __('admin/apps.fields.categories') }}</label>
              <div class="select2-dark">
                <select name="categories[]" class="form-control" id="inputAppCategories" multiple="multiple" data-placeholder="&ndash; {{ __('admin/apps.fields.categories_placeholder') }} &ndash;" data-dropdown-css-class="select2-dark" style="width: 100%;" required>
                  @if (!empty($categories))
                  {!! generate_options($categories, old('categories', $app->categories->pluck('id')->toArray() )) !!}
                  @endif
                </select>
              </div>
            </div>

            <div class="form-group">
              <label for="inputAppTags">{{ __('admin/apps.fields.tags') }}</label>
              <a href="#" class="d-inline-block ml-2" data-toggle="popover" data-content="{{ __('admin/apps.fields.tags_hint') }}" data-trigger="focus">
                <span class="far fa-question-circle text-muted"></span>
              </a>
              <div class="select2-light">
                <select name="tags[]" class="form-control w-100" id="inputAppTags" multiple="multiple" data-placeholder="&ndash; {{ __('admin/apps.fields.tags_placeholder') }} &ndash;" data-dropdown-css-class="select2-light">
                  @if (!empty($tags))
                  {!! generate_options($tags, old('tags', $app->tags->pluck('name')->toArray()), 'name', 'name') !!}
                  @endif
                </select>
              </div>
            </div>
          </div>

          <div class="col-12">
            <div class="form-group">
              <label>{{ __('admin/apps.fields.logo') }}</label>
              <a href="#" class="d-inline-block ml-2" data-toggle="popover" data-content="{{ __('admin/apps.fields.logo_hint') }}" data-html="true" data-trigger="click">
                <span class="far fa-question-circle text-muted"></span>
              </a>
              <div class="row gutter-lg">
                @if($is_edit && $app->logo)
                <div class="col flex-grow-0">
                  <div class="text-bold">@lang('admin/apps.fields.current_logo')</div>
                  <div>
                    @include('components.app-logo', ['logo' => $app->logo, 'size' => '150x150'])
                  </div>
                  <div class="form-check">
                    <input type="checkbox" name="app_logo_delete" class="form-check-input" id="logoDelete" value="1" {{ old_checked('app_logo_delete') }} >
                    <label class="form-check-label text-nowrap" for="logoDelete">{{ __('admin/apps.fields.remove_logo?') }}</label>
                  </div>
                </div>
                @endif
                <div class="col">
                  @if($is_edit && $app->logo)
                  <label for="inputAppLogo">@lang('admin/apps.fields.change_logo'):</label>
                  @endif
                  <div class="app-upload-wrapper" style="max-width: 400px;">
                    <input type="file" name="app_logo" class="" id="inputAppLogo">
                    {{--
                    <button type="button" class="btn-cancel-logo close float-none text-danger ml-1" aria-label="Cancel" title="@lang('admin/apps.remove_file')" data-toggle="tooltip">
                      <span aria-hidden="true">&times;</span>
                    </button>
                    --}}
                  </div>
                </div>
              </div>
            </div>

            <div class="form-group">
              <label for="inputAppDescription">{{ __('admin/apps.fields.description') }}</label>
              <textarea name="app_description" class="form-control" id="inputAppDescription" placeholder="{{ __('admin/apps.fields.description_placeholder') }}" rows="3" maxlength="{{ settings('app.description_limit', 1000) }}" style="max-height: 300px;">{{ old('app_description', $app->description) }}</textarea>
            </div>
          </div>
        </div>
        {{--
        <div class="form-group">
          <label>
            {{ __('admin/apps.fields.visuals') }}
            <a href="#" class="d-inline-block ml-2" data-toggle="popover" data-content="{{ __('admin/apps.fields.visuals_hint') }}" data-trigger="focus">
              <span class="far fa-question-circle text-muted"></span>
            </a>
          </label>
          @if ($is_edit)
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
                  <div class="form-check">
                    <input type="checkbox" name="visual_delete[]" class="form-check-input" id="inputVisualDelete{{ $i }}" value="{{ $visual->id }}">
                    <label class="form-check-label font-weight-normal" for="inputVisualDelete{{ $i }}">{{ __('common.delete').'?' }}</label>
                  </div>
                </div>
              </div>
            </div>
            @endforeach
          </div>
          @endif
          <input type="file" multiple="multiple" name="visuals[]" class="form-control" placeholder="{{ __('admin/apps.fields.visuals_placeholder') }}" accept="image/*">
        </div>
        --}}
      </li>
      <li class="list-group-item">
        <div class="text-center">
          @if ($is_edit)
          <button type="submit" class="btn btn-primary btn-min-100">{{ __('common.save') }}</button>
          @else
          <button type="submit" class="btn btn-primary btn-min-100">{{ __('admin/apps.submit_app') }}</button>
          @endif
          <br>
          <a href="{{ $is_edit ? route('admin.apps.show', ['app' => $app->id]) : route('admin.apps.index') }}" class="btn btn-default btn-sm mt-2">{{ __('common.cancel') }}</a>
        </div>
      </li>
    </ul>
  </div>
  <!-- /.card -->
</form>
@endsection

@include('admin.libraries.select2')
@include('admin.libraries.filepond')
@include('admin.app.changes.btn-view-version')

@push('scripts')

<script>
jQuery(document).ready(function($) {
  var inited = false;
  var $hasShortName = $("#hasShortName"),
      $inputShortName = $("#inputAppShortName"),
      $wrapperShortName = $("#wrapperShortName");


  $("#inputAppDescription").textareaShowLength({
    position: "bottom center",
  }).textareaAutoHeight({
    bypassHeight: false,
  });

  $hasShortName.on("change", function(e) {
    $wrapperShortName.toggleClass("d-none", !this.checked);

    if(inited) {
      if(!this.checked) {
        // $inputShortName.val(null);
      } else {
        $inputShortName.focus();
      }
    }
  }).trigger("change");

  var $inputCategories = $("#inputAppCategories");
  $inputCategories.select2({
    closeOnSelect: false,
    placeholder: @json(__('admin/apps.fields.categories_placeholder')),
    maximumSelectionLength: 5,
  });

  var $inputTags = $("#inputAppTags");
  $inputTags.select2({
    closeOnSelect: false,
    placeholder: @json(__('admin/apps.fields.tags_placeholder')),
    maximumSelectionLength: 10,
    tags: true,
    tokenSeparators: [',', ' ']
  });

  var $logoFile = $("#inputAppLogo");
  var logoFileOnChange = Helpers.debounce(function(isEmpty) {
    var $logoDelete = $("#logoDelete"),
        $btnCancel = $(".btn-cancel-logo")
    ;

    $btnCancel.toggleClass("d-none", isEmpty);

    // Store original user state
    var delstate;
    if(!isEmpty) {
      delstate = true;
      $logoDelete.data("originalChecked", $logoDelete.prop("checked"));
    } else {
      delstate = false;
      var userstate = $logoDelete.data("originalChecked");
      if(typeof userstate !== "undefined") {
        delstate = userstate;
        $logoDelete.removeData("originalChecked");
      }
    }
    $logoDelete.prop("checked", delstate).prop("disabled", !isEmpty);
  }, 10, false);
  $logoFile.filepond({
    //
    dropOnPage: true,
    dropOnElement: false,
    allowImagePreview: true,
    imagePreviewMaxHeight: 150,
    dropValidation: false,
    allowFileTypeValidation: true,
    acceptedFileTypes: ['image/*'],
    fileValidateTypeLabelExpectedTypes: 'Expects {allTypes}',
    fileValidateTypeLabelExpectedTypesMap: {'image/*': 'images'},
    allowFileSizeValidation: true,
    maxFileSize: '2MB',
    files: [
      {{--
      Only use validation returns as old files instead of all files the user ever uploaded
      @foreach($user->fileponds as $fp)
      {
        source: @json(Crypt::encrypt(['id' => $fp->id])),
        options: { type: 'limbo' },
      },
      @endforeach
      --}}
      @foreach(old('app_logo', []) as $filehash)
      {
        source: @json($filehash),
        options: { type: 'limbo' },
      },
      @endforeach
    ],
    onupdatefiles: Helpers.debounce(function(files) {
      logoFileOnChange(files.length == 0);
    }, 10, false),
  });

  $logoFile.on("change", function(e) {
    logoFileOnChange(this.files);
  }).trigger("change");

  $(".btn-cancel-logo").on("click", function(e) {
    e.preventDefault();
    $logoFile.val(null).trigger("change");
    $(this).tooltip("hide");
  });

  $("#inputAppDomain").on("change", function(e) {
    var value = $(this).val();
    var $inputUrl = $("#inputAppUrl");
    var disabled;
    if(value) {
      value = value +"/";
      disabled = false;
    } else {
      value = null;
      disabled = true;
    }
    $inputUrl.prop("disabled", disabled).toggleClass("disabled", disabled).val( value ).trigger("change");
  }).trigger("change");

  $('[data-toggle="popover"]').popover({
    container: "body",
  });

  $("#formInputApp").noEnterSubmit();
  inited = true;
});
</script>

@endpush
