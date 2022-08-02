
<script src="{{ asset('plugins/toastr/toastr.min.js') }}"></script>
@if (session()->has('flash_message'))
@php
	$flash_message = session('flash_message');
	$types = [
		'success'	=> 'success',
		'info'		=> 'info',
		'warning'	=> 'warning',
		'error'		=> 'error',
		'danger'	=> 'error',
	];
	$msg_text = is_string($flash_message) ? $flash_message : $flash_message['message'];
	$msg_type = $types[$flash_message['type']] ?? 'info';
@endphp
<script>
jQuery(document).ready(function($) {
	if(toastr) {
		toastr.options = {
			closeButton: true,
			preventDuplicates: false,
			positionClass: "toast-bottom-right",
			timeout: 8000,
			progressBar: true,
		};
		toastr[@json($msg_type)](@json($msg_text));
	}
});
</script>
@endif
