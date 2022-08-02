@push('load-styles')
<link rel="stylesheet" href="https://unpkg.com/filepond@4/dist/filepond.min.css">
@endpush

@push('load-scripts')
<!-- Filepond -->
<script src="https://unpkg.com/filepond@4/dist/filepond.min.js"></script>
<script src="https://unpkg.com/jquery-filepond@1/filepond.jquery.js"></script>

{{--
use locale? but there's no way to match the locale with the file name
<script src="https://unpkg.com/filepond@4/locale/en-en.js"></script>
<script src="https://unpkg.com/filepond@4/locale/id-id.js"></script>
--}}

<!-- Filepond plugins -->
<script src="https://unpkg.com/filepond-plugin-image-exif-orientation@1/dist/filepond-plugin-image-exif-orientation.min.js"></script>
<link rel="stylesheet" href="https://unpkg.com/filepond-plugin-image-preview@4/dist/filepond-plugin-image-preview.min.css">
<script src="https://unpkg.com/filepond-plugin-image-preview@4/dist/filepond-plugin-image-preview.min.js"></script>
<script src="https://unpkg.com/filepond-plugin-file-validate-size@2/dist/filepond-plugin-file-validate-size.js"></script>
<script src="https://unpkg.com/filepond-plugin-file-validate-type@1/dist/filepond-plugin-file-validate-type.js"></script>

<!-- Filepond init -->
<script>
jQuery(document).ready(function($) {
  // Set default FilePond options
  $.fn.filepond.setOptions({
    server: {
      url: "{{ url(config('filepond.server.url', '')) }}",
      headers: {
        'X-CSRF-TOKEN': @json(csrf_token()),
      }
    }
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