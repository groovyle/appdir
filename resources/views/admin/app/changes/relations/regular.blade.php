<div class="item field-group col-lg-4 col-md-6 col-12">
	<div class="title">{{ $title }}</div>
	<div class="comparison">
		<div class="side old" title="@lang('admin/app.changes.old_value')">
			@include('admin.app.changes.relations.regular-comparison-side', ['title' => __('common.from'), 'rel' => $rel['old']])
		</div>
		<div class="side new" title="@lang('admin/app.changes.new_value')">
			@include('admin.app.changes.relations.regular-comparison-side', ['title' => __('common.to'), 'rel' => $rel['new']])
		</div>
	</div>
</div>