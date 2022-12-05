<script>
	function onloadCallback() {
		@foreach($ids as $id)
		@php
		$varkey = 'client'. ucfirst(Str::studly($id));
		@endphp
		document.getElementById(@json($id)).classList.add("g-recaptcha");
		let {{ $varkey }} =  grecaptcha.render(@json($id), {
			'sitekey': @json($publicKey),
			'theme': @json($theme),
			'badge': @json($badge),
			'size': @json($size),
			'hl': @json($language)
		});

		@if($size==='invisible')
		grecaptcha.ready(function () {
			grecaptcha.execute({{ $varkey }});
		});
		@endif
		@endforeach
	}
</script>
<script src="https://www.google.com/recaptcha/api.js?render=explicit&onload=onloadCallback" defer async></script>
