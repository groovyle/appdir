
@push('scripts')
<div class="modal fade" id="changesVisualsModal" tabindex="-1" aria-labelledby="changesVisualsModalLabel" aria-hidden="true">
  <div class="modal-dialog modal-lg">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title" id="changesVisualsModalLabel">@lang('admin/apps.changes.visuals_comparison')</h5>
        <button type="button" class="close" data-dismiss="modal" aria-label="@lang('common.close')"><span aria-hidden="true">&times;</span></button>
      </div>
      <div class="modal-body">
        <div class="placeholder-content">
          <h4 class="my-5">loading...</h4>
        </div>
        <div class="error-message alert alert-danger d-none">
          <h5 class="my-5">@lang('admin/apps.changes.cannot_load_visuals_comparison')</h5>
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
  AppGlobals.init("apps.ChangesVisuals", function() {
    var $modal = $("#changesVisualsModal"),
      $modalBody = $modal.find(".modal-body"),
      $placeholderContent = $modalBody.find(".placeholder-content").remove(),
      $error = $modalBody.find(".error-message").remove().removeClass("d-none");

    function modalContent(content) {
      $modalBody.empty().append(content);
    }

    $(document).on("click", ".btn-compare-visuals", function(e) {
      e.preventDefault();

      // Show modal containing the difference
      var $btn = $(this);
      var version = $btn.data("version") || "";
      var newIds = $btn.data("visualsNew") || "";
      var oldIds = $btn.data("visualsOld") || "";
      var simple = $btn.data("simple") || "";
      simple = simple == "true" || simple == 1;

      modalContent($placeholderContent);
      $modal.modal("show");

      $.ajax({
        url: @json( route('admin.apps.changes.visuals', ['app' => $app->id]) ),
        method: "GET",
        cache: true,
        data: {
          new: newIds,
          old: oldIds,
          version: version,
          simple: simple ? 1 : 0,
          // autoplay: 1,
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
  });
});
</script>
@endpush