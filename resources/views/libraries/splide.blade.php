@push('load-styles')
<link rel="stylesheet" href="{{ asset('plugins/splide/css/themes/splide-skyblue.min.css') }}">
<link rel="stylesheet" href="{{ asset('plugins/splide/extensions/video/splide-extension-video.min.css') }}">
@endpush

@push('load-scripts')
<!-- Splide carousel -->
<script src="{{ asset('plugins/splide/js/splide.min.js') }}"></script>
<script src="{{ asset('plugins/splide/extensions/intersection/splide-extension-intersection.min.js') }}"></script>
<script src="{{ asset('plugins/splide/extensions/video/splide-extension-video.min.js') }}"></script>
@endpush