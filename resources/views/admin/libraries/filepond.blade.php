@push('load-styles')
<link rel="stylesheet" href="{{ asset('plugins/filepond/filepond.min.css') }}">
<link rel="stylesheet" href="{{ asset('plugins/filepond/plugins/filepond-plugin-image-preview.min.css') }}">
@endpush

@push('load-scripts')
<!-- Filepond -->
<script src="{{ asset('plugins/filepond/filepond.min.js') }}"></script>
<script src="{{ asset('plugins/filepond/filepond.jquery.js') }}"></script>

{{--
use locale? but there's no way to match the locale with the file name
<script src="{{ asset('plugins/filepond/locale/en-en.js') }}"></script>
<script src="{{ asset('plugins/filepond/locale/id-id.js') }}"></script>
--}}

<!-- Filepond plugins -->
<script src="{{ asset('plugins/filepond/plugins/filepond-plugin-image-exif-orientation.min.js') }}"></script>
<script src="{{ asset('plugins/filepond/plugins/filepond-plugin-image-preview.min.js') }}"></script>
<script src="{{ asset('plugins/filepond/plugins/filepond-plugin-file-validate-size.min.js') }}"></script>
<script src="{{ asset('plugins/filepond/plugins/filepond-plugin-file-validate-type.min.js') }}"></script>

<!-- Filepond init -->
<script>
jQuery(document).ready(function($) {
  // Set default FilePond options
  $.fn.filepond.setOptions({
    server: {
      url: "{{ url(config('filepond.server.url', '')) }}",
      restore: "/restore/",
      headers: {
        'X-CSRF-TOKEN': @json(csrf_token()),
      },
    },
  });

  // Register plugins
  $.fn.filepond.registerPlugin(
    FilePondPluginImageExifOrientation,
    FilePondPluginImagePreview,
    FilePondPluginFileValidateSize,
    FilePondPluginFileValidateType
  );
});
</script>

<!-- END Filepond -->
@endpush