<?php
$with_modal = isset($with_modal) ? !!$with_modal : true;
$btn_selector = $btn_selector ?? '.btn-view-version';
?>

@push('scripts')

@if($with_modal)
<div class="modal fade" id="versionViewModal" tabindex="-1" aria-labelledby="versionViewModalLabel" aria-hidden="true">
  <div class="modal-dialog modal-xl">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title" id="versionViewModalLabel">@lang('admin/apps.changes.version_preview')</h5>
        <button type="button" class="close" data-dismiss="modal" aria-label="@lang('common.close')"><span aria-hidden="true">&times;</span></button>
      </div>
      <div class="modal-body">
        <div class="placeholder-content">
          <h4 class="my-5">loading...</h4>
        </div>
        <div class="error-message alert alert-danger d-none">
          <h5 class="my-5">@lang('admin/apps.changes.cannot_load_version_preview')</h5>
        </div>
      </div>
      <div class="modal-footer text-right">
        <button type="button" class="btn btn-secondary" data-dismiss="modal">@lang('common.close')</button>
      </div>
    </div>
  </div>
</div>
@endif


<script>
jQuery(document).ready(function($) {
  var versionView = function() {
    var $modal = $("#versionViewModal"),
      $modalTitle = $modal.find(".modal-title"),
      $modalBody = $modal.find(".modal-body"),
      $placeholderContent = $modalBody.find(".placeholder-content").remove(),
      $error = $modalBody.find(".error-message").remove().removeClass("d-none");

    function modalTitle(version) {
      $modalTitle.html( @json(__('admin/apps.changes.version_preview')) +': '+ version );
    }

    function modalContent(content) {
      $modalBody.empty().append(content);
    }

    function modalShow(appId, version) {
      if(!appId || !version) {
        modalContent($error);
        $modal.modal("show");
        return;
      }

      modalTitle(version);
      modalContent($placeholderContent);
      $modal.modal("show");

      $.ajax({
        url: @json( route('admin.apps.changes.details') ),
        method: "GET",
        cache: true,
        data: {
          app_id: appId,
          version: version,
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
    }

    $(document).on("click", @json($btn_selector), function(e) {
      e.preventDefault();

      // Show modal containing the item
      var $btn = $(this);
      var appId = $btn.data("appId");
      var version = $btn.data("version");

      modalShow(appId, version);
    });
  }
  versionView();
});
</script>

@endpush