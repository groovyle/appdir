<?php
$home_path = [
	'text'		=> sprintf('<span class="fas fa-home" title="%s"></span>', __('admin/common.home_title')),
	'url'			=> route('admin.home'),
	'active'	=> false,
];
if(!isset($breadcrumb_paths)) {
	// Dynamically determine breadcrumb based on route path if param is not passed in.
	$paths = array();
	$route = Route::currentRouteName();
	if($route) {
		$route = explode('.', $route);
		if($route[0] === 'admin') {
			$home_path['active'] = count($route) == 1;
			$paths[] = $home_path;

			$parts = array_slice($route, 1);
			$lastkey = array_key_last($parts);
			$cumulative_path = $route[0];
			foreach($parts as $i => $part) {
				if($part === 'index') {
					continue;
				}
				if($i === 0 && $part === 'home') {
					break;
				}

				$cumulative_path .= '.'.$part;

				$page_title = __($route[0].'/'.$parts[0].'.page_title.'.($i == 0 ? 'index' : $part));
				$path = array(
					'text'		=> $page_title,
					'url'			=> FALSE,
					'active'	=> FALSE,
				);

				if($i != $lastkey) {
					$path_route = $cumulative_path;
					if(!Route::has($path_route)) {
						$path_route .= '.index';
					}
					$path['url'] = Route::has($path_route) ? route($path_route) : false;
				} else {
					if(isset($append_breadcrumb)) {
						$paths = array_merge($paths, $append_breadcrumb);
						break;
					}

					if(isset($last_breadcrumb)) {
						$path['text'] = $last_breadcrumb;
					} else {
						$path['text'] = __('common.'.$part);
					}
				}

				$paths[] = $path;
			}
			$lastpathkey = array_key_last($paths);
			$paths[$lastpathkey]['url'] = FALSE;
			$paths[$lastpathkey]['active'] = TRUE;

			if(isset($last_breadcrumb)) {
				$paths[$lastpathkey]['text'] = $last_breadcrumb;
			}
		}
	}
} else {
	$paths = array_merge([$home_path], $breadcrumb_paths);
}
?>
@if (!empty($paths))
<ol class="breadcrumb float-sm-right">
@foreach ($paths as $path)
	<?php
	$contents = $path['text'];
	$classes = ($path['active'] ?? false) ? 'active' : '';
	if(!empty($path['url'])) {
		$contents = sprintf('<a href="%s">%s</a>', $path['url'], $contents);
	}
	?>
	<li class="breadcrumb-item {{ $classes }}">{!! $contents !!}</li>
@endforeach
</ol>
@else
@endif