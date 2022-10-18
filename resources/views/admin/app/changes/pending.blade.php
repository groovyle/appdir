<?php
$rand = random_alpha(5);
?>
@push('scripts')
<div class="modal fade" id="pendingChangesModal" tabindex="-1" aria-labelledby="pendingChangesModalLabel" aria-hidden="true">
  <div class="modal-dialog modal-dialog-scrollable modal-xl">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title" id="pendingChangesModalLabel">@lang('admin/apps.changes.pending_changes_view')</h5>
        <button type="button" class="close" data-dismiss="modal" aria-label="@lang('common.close')"><span aria-hidden="true">&times;</span></button>
      </div>
      <div class="modal-body">
        <div class="d-flex justify-content-center align-items-center mb-3" id="pending-changes-selector-wrapper">
          <label for="pending-changes-selector" class="mr-2">@lang('admin/apps.changes.pending_changes')</label>
          <select class="custom-select w-auto" id="pending-changes-selector" style="min-width: 150px;"></select>
          <label for="pending-changes-selector" class="current-version-text ml-2">@lang('admin/apps.changes._compared_to_current_version_x')</label>
        </div>
        <div id="pending-changes-content">
          <div class="placeholder-content">
            <h4 class="my-5">loading...</h4>
          </div>
          <div class="error-message alert alert-danger d-none">
            <h5 class="my-5">@lang('admin/apps.changes.cannot_load_version_preview')</h5>
          </div>
        </div>
      </div>
      <div class="modal-footer text-right">
        <button type="button" class="btn btn-secondary" data-dismiss="modal">@lang('common.close')</button>
      </div>
    </div>
  </div>
</div>

<script>
jQuery(document).ready(function($) {

  AppGlobals.init("apps.PendingChangesView", function() {
    var $modal = $("#pendingChangesModal"),
      $modalTitle = $modal.find(".modal-title"),
      $modalContent = $modal.find(".modal-body #pending-changes-content"),
      $placeholderContent = $modalContent.find(".placeholder-content").remove(),
      $error = $modalContent.find(".error-message").remove().removeClass("d-none"),
      $select = $("#pending-changes-selector"),
      $curverText = $(".current-version-text").remove(),
      curverTemplate = $curverText.text(),
      accumulateChanges;

    var $trigger, appId;

    function modalTitle(version) {
      $modalTitle.html( @json(__('admin/apps.changes.version_preview')) +': '+ version );
    }

    function modalContent(content) {
      // Trigger hide for tooltips/popovers
      $modalContent.find("[data-toggle]").popover("hide").tooltip("hide");

      $modalContent.empty().append(content);
      if(window.initDefaultClasses) window.initDefaultClasses($modalContent);
      $modalContent.modal("handleUpdate");
    }

    function replaceSelections(data) {
      $select.empty();

      if(Array.isArray(data) && data.length > 0) {
        data.forEach(function(v, k) {
          var $option = $('<option value="'+ v.version +'">'+ @json(__('admin/apps.changes.version')) +' '+ v.version +'</option>');
          $select.append($option);
        });

        $select.trigger("change");
        return;
      } else {
        var $none = $('<option value="">&mdash; '+ @json(__('admin/apps.changes.no_versions_found')) +' &mdash;</option>');
        $select.append($none)
      }

      $select.trigger("change");
    }

    $select.on("change", function(e) {
      var value = $(this).val();

      if(!value) {
        modalContent(null);
        return;
      }

      modalContent($placeholderContent);

      $.ajax({
        url: @json( route('admin.apps.changes.details') ),
        method: "GET",
        cache: true,
        data: {
          app_id: appId,
          version: value,
          accumulate_changes: accumulateChanges ? 1 : 0,
        },
        dataType: "html",
        success: function(response, status, xhr) {
          modalContent(response);
        },
        error: function(xhr, status, message) {
          console.error("Error occurred while trying to load content. "+ status +": "+ message);
          modalContent($error);
        },
      });
    });

    function modalShow(id) {
      // Init modal variables and contents
      var accumulate = !!$trigger.data("accumulateChanges");
      var curver = $trigger.data("currentVersion");
      if(accumulate && curver) {
        curver = curverTemplate.replace(":x", curver);
        $curverText.text(curver).insertAfter($select);
      } else {
        $curverText.remove();
      }

      // Reset
      if(!id) {
        $trigger = null;
        appId = null;
        modalContent(null);
        return;
      }

      if(id == appId) {
        // Just show the modal with the previous content
        $modal.modal("show");
        return;
      }
      appId = id;

      modalContent($placeholderContent);
      $modal.modal("show");

      // Get the version selection
      $.ajax({
        url: @json( route('admin.apps.changes.pending_versions') ),
        method: "GET",
        cache: false,
        data: {
          app_id: appId,
        },
        dataType: "json",
        success: function(response, status, xhr) {
          replaceSelections(response);
        },
        error: function(xhr, status, message) {
          console.error("Error occurred while trying to load content. "+ status +": "+ message);
          modalContent($error);
        },
      });
    }

    $(document).on("click", ".btn-pending-changes-show", function(e) {
      // Show modal containing the item
      var $item = $(this);
      var id = $(this).data("appId");
      if(!id)
        return;

      e.preventDefault();

      $trigger = $item;
      modalShow(id);
    });
  });

});
</script>
@endpush