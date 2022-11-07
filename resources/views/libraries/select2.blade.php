@push('load-styles')
<link rel="stylesheet" href="{{ asset('plugins/select2/css/select2.min.css') }}">
<link rel="stylesheet" href="{{ asset('plugins/select2-bootstrap4-theme/select2-bootstrap4.min.css') }}">
@endpush

@push('load-scripts')
<script src="{{ asset('plugins/select2/js/select2.full.min.js') }}"></script>
@if(app()->isLocale('id'))
<script src="{{ asset('plugins/select2/js/i18n/id.js') }}" defer></script>
@endif
@endpush