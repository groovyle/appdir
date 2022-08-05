<?php
$rand = random_string(5, array_merge(range('A', 'Z'), range('a', 'z')) );
?>
@push('scripts')
<div class="modal fade" id="pendingChangesModal" tabindex="-1" aria-labelledby="pendingChangesModalLabel" aria-hidden="true">
  <div class="modal-dialog modal-xl">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title" id="pendingChangesModalLabel">@lang('admin/apps.changes.version_preview')</h5>
        <button type="button" class="close" data-dismiss="modal" aria-label="@lang('common.close')"><span aria-hidden="true">&times;</span></button>
      </div>
      <div class="modal-body">
        <div class="d-flex justify-content-center align-items-center mb-3" id="pending-changes-selector-wrapper">
          <label for="pending-changes-selector" class="mr-2">@lang('admin/apps.changes.pending_changes')</label>
          <select class="custom-select w-auto" id="pending-changes-selector" style="min-width: 150px;"></select>
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

  var pendingChangesView = function() {
    var $modal = $("#pendingChangesModal"),
      $modalTitle = $modal.find(".modal-title"),
      $modalContent = $modal.find(".modal-body #pending-changes-content"),
      $placeholderContent = $modalContent.find(".placeholder-content").remove(),
      $error = $modalContent.find(".error-message").remove().removeClass("d-none")
      $select = $("#pending-changes-selector");

    var $trigger, appId;

    function modalTitle(version) {
      $modalTitle.html( @json(__('admin/apps.changes.version_preview')) +': '+ version );
    }

    function modalContent(content) {
      $modalContent.empty().append(content);
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
        var $none = $('<option value="">&mdash; '+ @json(__('admin/apps.changes.no_pending_changes')) +' &mdash;</option>');
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
  }
  pendingChangesView();

});
</script>
@endpush