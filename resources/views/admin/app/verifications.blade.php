@extends('admin.layouts.main')

@section('title')
{{ __('admin.app.tab_title') }} - @parent
@endsection

@section('page-title', __('admin.app.page_title.verifications'))

@section('content')
  <!-- Card -->
  <div class="card">
    <div class="card-body">
      <div class="mb-2">
        <a href="{{ route('admin.apps.index') }}" class="btn btn-sm btn-default">&laquo; {{ __('common.back_to_list') }}</a>
        <a href="{{ route('admin.apps.edit', ['app' => $app->id]) }}" class="btn btn-sm btn-default">
          <span class="fas fa-edit"></span>
          {{ __('common.edit') }}
        </a>
      </div>

      <h2>{{ $app->name }}</h2>

      @php dd($app->verifications) @endphp
      @if($app->verifications->count())
      <div class="timeline">
        @foreach($app->timeline as $group)
          <div class="time-label">
            <span class="bg-gray">{{ $group->text }}</span>
          </div>
          @foreach($group->items as $v)
          <div>
            <i class="fas k"></i>
          </div>
          @endforeach
        @endforeach
      </div>
      @else
      <p>{{ __('admin.app.message.no_verifications_yet') }}</p>
      @endif
      <!-- TODO: use cols, not table -->
      <table class="table-horizontal w-100">
        <tbody>
          <tr>
            <th scope="row">{{ __('admin.app.field.name') }}</th>
            <td>{{ $app->name }}</td>
          </tr>
          <tr>
            <th scope="row">{{ __('admin.app.field.description') }}</th>
            <td>{!! description_text($app->description) !!}</td>
          </tr>
          <tr>
            <th scope="row">{{ __('admin.app.field.directory') }}</th>
            <td>
              @include('components.app-directory', ['directory' => $app->directory, 'user' => $app->owner])
            </td>
          </tr>
          <tr>
            <th scope="row">{{ __('admin.app.field.url') }}</th>
            <td>
              <a href="{{ $app->full_url }}" target="_blank" class="text-primary">
                {{ $app->full_url }}
                <span class="fas fa-xs fa-external-link-alt"></span>
              </a>
            </td>
          </tr>
          <tr>
            <th scope="row">{{ __('admin.app.field.categories') }}</th>
            <td>
              @forelse ($app->categories as $category)
              <h6 class="font-weight-bolder">{{ $category->name }}</h6>
              @if ($category->description)
              <p class="text-secondary">{!! description_text($category->description) !!}</p>
              @endif
              @empty
              &ndash;
              @endforelse
            </td>
          </tr>
          <tr>
            <th scope="row">{{ __('admin.app.field.tags') }}</th>
            <td>
              @if($app->tags->isNotEmpty())
              @each('components.app-tag', $app->tags, 'tag')
              @else
              &ndash;
              @endforelse
            </td>
          </tr>
          <tr>
            <th scope="row">{{ __('admin.app.field.visuals') }}</th>
            <td>
              <div class="thumb-cards d-flex justify-content-start align-items-start mb-2">
              @foreach ($app->visuals as $visual)
              @php
              $i = $loop->iteration;
              @endphp
              <div class="thumb-item mr-2 mb-2">
                <div class="card m-0">
                  <div class="card-img-top">
                    <img src="{{ $visual->url }}" alt="{{ __('common.visual').' '.$i }}">
                  </div>
                  <div class="card-body p-1">
                    <p class="card-text text-secondary mb-1">{{ $visual->caption ? $visual->caption : __('common.visual').' '.$i }}</p>
                  </div>
                </div>
              </div>
              @endforeach
              </div>
            </td>
          </tr>
          <tr>
            <th scope="row">{{ __('admin.app.field.status') }}</th>
            <td>
              @include('components.app-verification-status', ['app' => $app])
            </td>
          </tr>
        </tbody>
      </table>
    </div>
    <!-- /.card-body -->
  </div>
  <!-- /.card -->
@endsection

@push('scripts')
<script>
jQuery(document).ready(function($) {
  $('[data-toggle="popover"]').popover({
    container: "body",
  });
});
</script>
@endpush
