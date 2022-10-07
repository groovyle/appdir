<!-- Modal for general remote use -->
<div class="modal fade ofamodal" id="ofaModal" tabindex="-1" role="dialog" aria-labelledby="ofaModalLabel" aria-hidden="true">
	<div class="modal-dialog" role="document">
		<div class="modal-content">
			<div class="modal-header">
				<h5 class="modal-title" id="ofaModalLabel">{{ __('admin/common.ofamodal.default_title') }}</h5>
				<button type="button" class="close" data-dismiss="modal" aria-label="Close">
					<span aria-hidden="true">&times;</span>
				</button>
			</div>
			<div class="modal-body">
				<div class="loading-wrapper text-center my-5">
					<div class="spinners mb-2">
						<div class="spinner-grow" role="status"></div>
						<div class="spinner-grow" role="status"></div>
						<div class="spinner-grow" role="status"></div>
					</div>
					<h4 class="loading-title">{{ __('admin/common.ofamodal.loading_text') }}</h4>
				</div>
				<div class="error-wrapper text-center">
					<h5 class="error-header">{{ __('admin/common.ofamodal.error_text') }}</h5>
					<div class="error-description text-danger"></div>
				</div>
			</div>
			<div class="modal-footer">
				<button type="button" class="btn btn-secondary" data-dismiss="modal">{{ __('common.close') }}</button>
			</div>
		</div>
	</div>
</div>