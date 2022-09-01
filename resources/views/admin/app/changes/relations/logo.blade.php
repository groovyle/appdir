<div class="item field-group col-lg-4 col-md-6 col-12">
	<div class="title">{{ __('admin/apps.fields.logo') }}</div>
	<div class="logo comparison">
		@include('components.app-logo', ['logo' => $rel['old'], 'size' => '100x100', 'attributes' => ['class' => 'value old-value', 'title' => __('admin/apps.changes.old_logo'), 'data-toggle' => 'tooltip']])

		<span class="fas fa-arrow-right arrow text-primary mx-3"></span>

		@include('components.app-logo', ['logo' => $rel['new'], 'size' => '100x100', 'attributes' => ['class' => 'value new-value', 'title' => __('admin/apps.changes.new_logo'), 'data-toggle' => 'tooltip']])
	</div>
</div>