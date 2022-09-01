<?php
$use_mock = isset($ori);
$titler = $use_mock ? $ori : $app;
$append_breadcrumb = [
  [
    'text'    => text_truncate($titler->name, 50),
    'url'     => route('admin.apps.show', ['app' => $app->id]),
    'active'  => false,
  ],
  [
    'text'    => __('admin/apps.page_title.visuals'),
  ]
];
?>

@extends('admin.layouts.main')

@section('title')
{{ __('admin.app.tab_title') }} - @parent
@endsection

@section('page-title', __('admin/apps.page_title.visuals'))

@section('content')
<div class="mb-2">
  <a href="{{ route('admin.apps.show', ['app' => $app->id]) }}" class="btn btn-sm btn-default">&laquo; {{ __('common.back') }}</a>
</div>

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

<form id="visuals-form" class="visuals-form" action="{{ route('admin.apps.visuals.save', ['app' => $app->id]) }}" method="POST" enctype="multipart/form-data">
  @csrf
  @method('POST')

  <input type="hidden" name="visuals_count" value="{{ $app->visuals->count() }}" readonly>
  <input type="hidden" id="inputBackAfterSave" name="back_after_save" value="0" >

  <!-- Card -->
  <div class="card">
    <div class="card-body">
      <h4 class="mb-3">
        @lang('admin/apps.fields.visuals')
        <span class="text-primary">{{ $app->name }} </span>
      </h4>
      <p class="mt-n2 mb-3">
        <span class="fas fa-info-circle ml-1 mr-2"></span> @lang('admin/apps.max_visuals'): {{ $max_visuals }}
      </p>

      @include('components.alert-box', ['show_errors' => true, 'errors' => $cerrors])

      <div class="row">
      <div class="col-lg-6 col-md-6 col-12">
        @if($app->visuals->count() > 0)
        <ul class="list-unstyled visuals-list">
          @foreach($app->visuals as $i => $vis)
          <li class="media visuals-item visuals-item-{{ $vis->id }}" data-visuals-id="{{ $vis->id }}">
            <div class="visuals-nav">
              <div class="visuals-handle" tabindex="0" title="@lang('admin/apps.visuals_handle_hint')">
                <span class="fas fa-sort handle-icon"></span>
              </div>
              <div class="visuals-head">
                <div class="visuals-media image @if($vis->type != 'image') white @endif">
                  <a href="{{ $vis->url }}" target="_blank" title="{{ $vis->type_text }}" data-toggle="tooltip">
                    <img src="{{ $vis->thumbnail_url }}">
                  </a>
                </div>
                <div class="visuals-nav-additional">
                  <div class="visuals-delete form-check text-danger">
                    <input type="checkbox" class="form-check-input input-delete" name="visuals[{{ $vis->id }}][delete]" value="1" id="inputVisualDelete{{ $vis->id }}" {{ old_checked('visuals.'.$vis->id.'.delete') }}>
                    <label class="form-check-label" for="inputVisualDelete{{ $vis->id }}">@lang('common.remove')?</label>
                  </div>
                </div>
              </div>
            </div>
            <div class="media-body visuals-body">
              <div class="visuals-title">
                @lang('admin/apps.fields.order'):
                <input type="text" name="visuals[{{ $vis->id }}][order]" class="form-control input-order" value="{{ old('visuals.'.$vis->id.'.order', $vis->order ?: ($i + 1)) }}" maxlength="3">
                <input type="hidden" name="visuals[{{ $vis->id }}][id]" value="{{ $vis->id }}" >
              </div>
              <div class="visuals-content">
                <small class="text-muted">@lang('admin/apps.fields.caption'):</small>
                <div class="textarea-length-container textarea-length-top-right">
                  <textarea class="form-control form-control-sm input-caption" name="visuals[{{ $vis->id }}][caption]" placeholder="@lang('admin/apps.fields.caption_placeholder')" rows="1" maxlength="{{ $caption_limit }}">{{ old('visuals.'.$vis->id.'.caption', $vis->caption) }}</textarea>
                </div>
                @include('components.input-feedback', ['name' => 'visuals.'.$vis->id.'.caption'])
              </div>
              <div class="visuals-meta">{!! implode(' | ', $vis->meta_text) !!}</div>
            </div>
          </li>
          @endforeach
        </ul>
        @else
          @empty_text
        @endif
      </div>
      <div class="col-lg-5 offset-lg-1 col-md-5 offset-md-1 col-12 offset-0 mt-4 mt-md-0">
        <h5 class="mb-3">
          @lang('admin/apps.add_more_visuals')
        </h5>
        <div class="form-group">
          <label for="input-file-visuals">
            @lang('admin/apps.fields.upload_image')
            @component('admin.slots.label-hint')
            @lang('admin/apps.fields.upload_image_hint')
            @endcomponent
          </label>
          <input type="file" name="new_images[]" class="visuals-filepond" id="input-file-visuals" multiple>
        </div>
        <div class="form-group">
          <label>
            @lang('admin/apps.fields.or_add_other_visual_types')
            <a href="#" class="d-inline-block ml-2 btn-viso-add" title="@lang('common.add')" data-toggle="tooltip" data-trigger="hover focus">
              <span class="fas fa-plus"></span>
            </a>
          </label>
          <input type="hidden" name="_viso_payload" id="viso-payload" value="{{ json_encode($viso_payload) }}" disabled>
          <ol class="pl-4 viso-wrapper">
            <li class="viso-item mb-1 d-none" id="viso-item-template">
              <div class="d-flex flex-row align-items-start">
                <div class="flex-grow-1 flex-shrink-0 ml-2">
                  <select name="viso[__I__][type]" id="input-viso-__I__-type" class="custom-select custom-select-sm d-inline-block mb-1 viso-data-value" data-field="type">
                    <option value="" class="text-muted">&mdash; @lang('admin/apps.fields.choose_other_visuals_type') &mdash;</option>
                    <optgroup label="@lang('admin/apps.visuals.types.video')">
                      <option value="video.youtube">@lang('admin/apps.visuals.types.video_youtube')</option>
                    </optgroup>
                  </select>
                  <br>
                  <input type="text" name="viso[__I__][value]" id="input-viso-__I__-type" class="form-control form-control-sm d-inline-block viso-data-value" placeholder="@lang('admin/apps.fields.visuals_other_value_placeholder')" data-field="value">
                  <div class="invalid-feedback viso-data-html" data-field="message"></div>
                </div>
                <div class="flex-shrink-1 ml-2">
                  <a href="#" class="text-danger ml-2 align-top btn-viso-delete" title="@lang('common.remove')" data-toggle="tooltip"><span class="fas fa-trash"></span></a>
                </div>
              </div>
            </li>
          </ol>
        </div>
      </div>
      </div>
      <div class="row mt-3">
        <div class="col-12 text-center">
          <button type="submit" class="btn btn-primary btn-min-100">@lang('common.save')</button>
          <br>
          <button type="submit" class="btn btn-default btn-min-100 btn-sm mt-2 btn-save-back">@lang('common.save_and_go_back')</button>
          <br>
          <a href="{{ route('admin.apps.show', ['app' => $app->id]) }}" class="btn btn-sm btn-warning mt-3">{{ __('common.cancel') }}</a>
        </div>
      </div>
    </div>
    <!-- /.card-body -->
  </div>
  <!-- /.card -->
</form>
@endsection

@include('admin.libraries.jquery-ui-sortable')
@include('admin.libraries.filepond')
@include('admin.app.changes.btn-view-version')

@push('scripts')

<script>
jQuery(document).ready(function($) {
  var $form = $("#visuals-form");

  var $list = $(".visuals-list");

  $list.onlyNumbers({
    selector: ".input-order",
  });
  $list.textareaAutoHeight({
    selector: ".input-caption",
    bypassHeight: false,
  });
  $list.find(".input-caption").textareaShowLength();

  $list.on("change", ".input-order", function(e) {
    var $order = $(this),
        toOrder = $order.val(),
        $item = $order.closest(".visuals-item"),
        $items = $list.find("> .visuals-item")
    ;

    var current = $item.index() + 1;
    if(toOrder == current) {
      return;
    }

    $order.blur();

    // Do preliminary easy sorts
    if(toOrder <= 1) {
      $item.prependTo($list);
    } else if(toOrder >= $items.length) {
      $item.appendTo($list);
    } else {
      // Insert somewhere

      // If the destination order is bigger than its current index,
      // offset the order because the current item would affect all subsequent items
      if(current < toOrder) {
        $item.insertAfter( $list.find(".visuals-item:nth-child("+ toOrder +")") );
      } else {
        $item.insertBefore( $list.find(".visuals-item:nth-child("+ toOrder +")") );
      }
    }

    normalizeOrders();
  }).on("change", ".input-delete", function(e) {
    var $cbox = $(this),
        $item = $cbox.closest(".visuals-item"),
        $order = $item.find(".input-order"),
        $handle = $item.find(".visuals-handle"),
        toDelete = this.checked
    ;

    $item.toggleClass("item-deleting", toDelete);
    // $handle.toggleClass("invisible pointer-none", toDelete);
    /*if(toDelete) {
      $order.val(99);
    }*/
    $order.prop("readonly", toDelete);
    reorderList();
  });

  var _reorderList = function($changingItem) {
    if(typeof $changingItem === "undefined")
      $changingItem = $();

    // Collect items
    var $items = $list.find("> .visuals-item");
    var $otherItems = $items.not($changingItem);
    var itemsData = [],
        existingOrders = new Set
    ;
    var defaultOrder = $items.length + 1;
    var $order = $changingItem.find(".input-order");
    if($order.length > 0) {
      var toOrder = $order.val();
      defaultOrder = Math.max(defaultOrder, toOrder + 1);
    }
    $changingItem.add($otherItems).each(function(k, item) {
      var order = parseInt($(item).find(".input-order").val()) || defaultOrder;
      while(existingOrders.has(order)) {
        order++;
      }

      var itemData = {
        order: order,
        $element: $(item),
      };
      existingOrders.add(order);

      itemsData.push(itemData);
    });

    // Sort
    Helpers.sortBy(itemsData, "order");
    itemsData.forEach(function(item) {
      item.$element.appendTo($list);
    });

    normalizeOrders();
  }
  var reorderList = Helpers.debounce(_reorderList, 50, false);

  var _normalizeOrders = function() {
    $list.find("> .visuals-item").each(function(i, item) {
      // Normalize order
      $(item).find(".input-order").val( $(item).index() + 1 );
    });
  }
  var normalizeOrders = Helpers.debounce(_normalizeOrders, 50, false);

  // Call reorderList() before the sortable plugin auto-updates the indexes
  // so as to honor the field value at page load.
  reorderList();

  $list.sortable({
    handle: ".visuals-handle",
    // items: ".visuals-item:not(.item-deleting)",
    // cancel: ".item-deleting",
    placeholder: "visuals-sortable-placeholder",
    update: function(event, ui) {
      normalizeOrders();
    },
  });

  var $visualsFile = $("#input-file-visuals");
  $visualsFile.filepond({
    //
    dropOnPage: true,
    dropOnElement: false,
    allowImagePreview: true,
    imagePreviewMaxHeight: 160,
    maxFiles: @json($max_visuals),
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
      @foreach($old_uploads as $filehash)
      {
        source: @json($filehash),
        options: { type: 'limbo' },
      },
      @endforeach
    ],
  });


  // The term viso = visual-others (non image ones)
  var $visoList = $(".viso-wrapper"),
      visoItemTemplate = $("#viso-item-template").remove().removeAttr("id").removeClass("d-none").prop("outerHTML"),
      visoIndex = 0,
      $visoPayload = $("#viso-payload") // used for repopulation
  ;

  function _visoCompile() {
    var $items = $visoList.find("> .viso-item");
    var data = [];
    if($items.length > 0) {
      $items.each(function(i, item) {
        var $inputs = $(item).find("input, select, textarea").filter(".viso-data-value[data-field]");
        var inputData = {};
        $inputs.each(function(j, input) {
          var key = $(input).data("field"),
              value = $(input).val()
          ;
          inputData[key] = value;
        });

        data.push(inputData);
      });
    }

    $visoPayload.val( JSON.stringify(data) );
  }
  var visoCompile = Helpers.debounce(_visoCompile, 50, false);

  function _visoRepopulate() {
    var list;
    try {
      list = JSON.parse( $visoPayload.val() );
    } catch(error) {
      // Abort mission
      console.error(error);
      return;
    }

    if(!Array.isArray(list))
      list = Object.values(list);

    list.forEach(function(item, i) {
      visoItemAdd(item, false);
    });
  }
  var visoRepopulate = Helpers.debounce(_visoRepopulate, 50, false);

  function visoItemAdd(data, update) {
    if(typeof update === "undefined")
      update = true;

    var $list = $visoList,
      template = visoItemTemplate,
      index = visoIndex++;
      uniqid = Helpers.randomString(6)
    ;

    if($list.length > 0 && template) {
      var id = "viso-item-"+ uniqid;
      var $item = $("#"+ id);
      if($item.length == 0) {
        var item = Helpers.fillDataString(template, {
          i: index,
          uniqid: uniqid,
        });
        $item = $(item);

        $item.prop("id", id);
        $item.addClass(id);
        // $item.data("visoId", data.id);

        if(data) {
          Helpers.fillDataClasses($item, data, "viso-data");
          if(data.message) {
            // $item.find("input, select, textarea").addClass("is-invalid");
            $item.find(".invalid-feedback").addClass("d-block");
          }
        }

        $list.removeClass("d-none");
        $item.appendTo($list);

        if(update) {
          visoCompile();
          $item.find("input, select, textarea").first().focus();
        }
      }
    } else {
      console.error("Table, template, and/or list not instantiated");
    }
  }

  function visoItemRemove(item) {
    var $item = $(item),
      $list = $visoList;

    if($item.length == 0 || !$item.is(".viso-item"))
      return;

    // Defer removal to allow event to bubble up first for checks
    setTimeout(function() {
      $item.remove();
      visoCompile();
      if($list.children().length == 0) {
        $list.addClass("d-none");
      }
    }, 10);
  }

  $visoList.on("click", ".btn-viso-delete", function(e) {
    e.preventDefault();
    $(this).tooltip("hide");
    var $item = $(this).closest(".viso-item");
    visoItemRemove($item);
  }).on("change", "input, select, textarea", function(e) {
    visoCompile();
  });

  $form.on("click", ".btn-viso-add", function(e) {
    e.preventDefault();
    $(this).blur();
    visoItemAdd();
  }).on("submit", function(e) {
    // Compile data immediately before submitting.
    // This might be needed for corner cases where the user finished typing on an
    // input box, but instead of losing focus somewhere else (to trigger "change" event),
    // they immediately clicked  the submit button. A html submit action might stop
    // all js processing, so... yeah. Idk if it actually works like that, but just in case.
    _visoCompile();
  });

  $form.on("click", "[type=submit]", function(e) {
    $("#inputBackAfterSave").val( $(this).is(".btn-save-back") ? 1 : 0 );
  });


  $list.find(".input-delete").trigger("change");
  visoRepopulate();

  $form.noEnterSubmit();
});
</script>
@endpush
