<div class="item field-group col-lg-4 col-md-6 col-12">
	<div class="title">{{ __('admin/apps.fields.visuals') }}</div>
	<div class="visuals comparison">
		<span class="value old-value text-secondary" title="@lang('admin.app.old_value')">@lang('common.x_items', ['x' => count($rel['old'])])</span>
		<span class="fas fa-arrow-right arrow text-primary mx-2"></span>
		<span class="value new-value text-primary" title="@lang('admin.app.new_value')">@lang('common.x_items', ['x' => count($rel['new'])])</span>
		<a href="{{ route('admin.apps.changes.visuals', ['app' => $cl->app_id]) }}" class="fas fa-search arrow text-gray ml-2 btn-compare-visuals" title="@lang('admin/apps.visuals.visual_comparison_detail')" data-toggle="tooltip" data-version="{{ $cl->version }}" data-visuals-old="{{ implode(',', $rel['old']->keys()->all()) }}" data-visuals-new="{{ implode(',', $rel['new']->keys()->all()) }}"></a>
	</div>
</div>
