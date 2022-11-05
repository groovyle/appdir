<?php
$collapse = $collapse ?? true;
$collapse_class = $collapse ? 'collapsed-card' : '';
?>
<div class="card {{ $collapse_class }} user-block-item mb-2">
	<div class="card-header border-bottom-0">
		<div class="card-title">
			<div class="cursor-pointer" data-card-widget="collapse" tabindex="0">
				@if(!$ub->trashed())
				<span class="fas fa-ban fa-fw text-090 mr-1 text-danger" title="{{ __('admin/users.block_is_active') }}" data-toggle="tooltip"></span>
				@else
				<span class="fas fa-ban fa-fw text-090 mr-1 text-success" title="{{ __('admin/users.block_is_inactive') }}" data-toggle="tooltip"></span>
				@endif
				@lang('admin/users.block_by_x', ['x' => pretty_username(vo_($ub->createdBy))])
			</div>
			<div class="text-080 d-flex">
				<span class="text-secondary">@include('components.date-with-tooltip', ['date' => $ub->created_at])</span>
			</div>
		</div>
		<div class="card-tools m-0 mr-n2">
			<button type="button" class="btn btn-tool" data-card-widget="collapse" data-toggle="tooltip" title="@lang('common.show/hide')"><i class="fas @if(!$collapse) fa-minus @else fa-plus @endif"></i></button>
		</div>
	</div>
	<div class="card-body mt-n2 pt-1 lh-130">
		<div>
			<div class="text-bold">{{ __('admin/users.fields.block_reason') }}:</div>
			<span class="text-pre-wrap reason-text init-readmore">@voe($ub->reason)</span>
		</div>
		@if($ub->trashed())
		<div class="mt-2">
			<span class="text-success text-bold">@lang('admin/users.unblock_by_x', ['x' => pretty_username(vo_($ub->deletedBy))])</span>
			<span class="text-080 text-secondary ml-2">@include('components.date-with-tooltip', ['date' => $ub->deleted_at])</span>
		</div>
		@endif
	</div>
</div>