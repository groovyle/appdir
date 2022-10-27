@push('load-styles')
<link rel="stylesheet" href="{{ asset('plugins/chart.js/Chart.min.css') }}">
@endpush

@push('load-scripts')
<script src="{{ asset('plugins/chart.js/Chart.min.js') }}"></script>
<script src="{{ asset('plugins/chart.js/chartjs-plugin-datalabels.min.js') }}"></script>
<script src="{{ asset('plugins/chart.js/chartjs-plugin-colorschemes.min.js') }}"></script>
@endpush