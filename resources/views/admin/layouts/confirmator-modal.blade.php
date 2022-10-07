<!-- Modal for general "are-you-sure" (AYS) POST confirmation -->
<div class="modal fade aysmodal" id="aysModal" data-backdrop="static" tabindex="-1" role="dialog" aria-labelledby="aysModalLabel" aria-hidden="true">
	<div class="modal-dialog" role="document">
		<div class="modal-content">
			<div class="modal-header">
				<h4 class="modal-title" id="aysModalLabel">{{ __('admin/common.aysmodal.default_title') }}</h4>
				<button type="button" class="close" data-dismiss="modal" aria-label="Close">
					<span aria-hidden="true">&times;</span>
				</button>
			</div>
			<div class="modal-body">
				<div class="prompt-wrapper text-center">
					<h5 class="prompt">@lang('admin/common.aysmodal.default_prompt')</h5>
					<div class="template-prompt d-none" data-type="edit">@lang('admin/common.aysmodal.prompt_edit')</div>
					<div class="template-prompt d-none" data-type="delete">@lang('admin/common.aysmodal.prompt_delete')</div>
					<p class="description"></p>
				</div>
				<div class="error-wrapper text-center text-danger mt-4">
					<h5 class="error-header">{{ __('admin/common.aysmodal.error_text') }}</h5>
					<div class="error-description"></div>
				</div>
			</div>
			<div class="modal-footer justify-content-center">
				<button type="button" class="btn btn-sm btn-approve"><span class="fas fa-check mr-1"></span> {{ __('admin/common.yes_im_sure') }}</button>
				<button type="button" class="btn btn-sm btn-cancel"><span class="fas fa-times mr-1"></span> {{ __('common.cancel') }}</button>
			</div>
		</div>
	</div>
</div>