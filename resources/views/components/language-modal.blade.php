<?php
$list = \App\DataManagers\LanguageManager::getTranslatedList();
$curlang = app()->getLocale();
?>
<!-- Modal for changing app language -->
<div class="modal fade" id="chLangModal" tabindex="-1" role="dialog" aria-labelledby="chLangModalLabel" aria-hidden="true">
	<div class="modal-dialog modal-dialog-centered" role="document">
		<div class="modal-content">
			<div class="modal-header">
				<h5 class="modal-title" id="chLangModalLabel">{{ __('frontend.lang.change_language') }}</h5>
				<button type="button" class="close" data-dismiss="modal" aria-label="Close">
					<span aria-hidden="true">&times;</span>
				</button>
			</div>
			<div class="modal-body text-center">
				<form method="POST" action="{{ route('change_language') }}" autocomplete="off">
					@csrf
					@method('PATCH')

					<div class="text-center">
						<div class="d-inline-block text-left mx-auto w-auto">
							@foreach($list as $l => $text)
							<div class="form-check text-110">
								<input type="radio" name="language" value="{{ $l }}" class="form-check-input ch-lang-input" id="ch-lang-{{ $l }}" {!! old_checked('', $curlang, $l) !!}>
								<label for="ch-lang-{{ $l }}" class="form-check-label cursor-pointer">{{ $text }} ({{ $l }})</label>
							</div>
							@endforeach
						</div>
					</div>
					<button type="submit" class="btn btn-success btn-min-100 mt-4">{{ __('common.save') }}</button>
					<br>
					<button type="button" class="btn btn-secondary btn-sm mt-2" data-dismiss="modal">{{ __('common.close') }}</button>
				</form>
			</div>
		</div>
	</div>
</div>