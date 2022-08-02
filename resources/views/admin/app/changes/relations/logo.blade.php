<div class="item field-group col-lg-4 col-md-6 col-12">
	<div class="title">{{ __('admin/app.field.logo') }}</div>
	<div class="logo comparison">
		@if($rel['old'])
		<a class="value old-value" href="{{ $rel['old']->url }}" target="_blank" title="@lang('admin/app.changes.old_logo')" data-toggle="tooltip"><img rel="logo" src="{{ $rel['old']->url }}" class="img-responsive" style="max-width: 100px; max-height: 100px;"></a>
		@else
		@von($rel['old'])
		@endif

		<span class="fas fa-arrow-right arrow text-primary mx-2"></span>

		@if($rel['new'])
		<a class="value new-value" href="{{ $rel['new']->url }}" target="_blank" title="@lang('admin/app.changes.new_logo')" data-toggle="tooltip"><img rel="logo" src="{{ $rel['new']->url }}" class="img-responsive" style="max-width: 100px; max-height: 100px;"></a>
		@else
		@von($rel['new'])
		@endif
	</div>
</div>