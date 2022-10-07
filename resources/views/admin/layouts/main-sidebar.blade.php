<?php

use App\Http\Controllers\Admin as Ctl;

$app_verif_count = get_count_from_list_query(Ctl\AppVerificationController::listQuery()[0], 'a.id');
$app_reports_count = get_count_from_list_query(Ctl\AppReportController::listQuery()[0], 'rur.id');

$menu_list = [
	'dashboard'		=> [
		'text'	=> __('admin/menus.dashboard'),
		'icon'	=> 'fas fa-tachometer-alt',
		'route'	=> 'admin.home',
		'match'	=> ['admin.home', true],
	],
	'apps_header'	=> [
		'text'	=> __('admin/menus.app_management'),
		'type'	=> 'header',
	],
	'app_list'	=> [
		'text'	=> __('admin/menus.app_list'),
		'icon'	=> 'fas fa-cloud',
		'route'	=> 'admin.apps.index',
		'match'	=> 'admin.apps.',
	],
	'app_verif'	=> [
		'text'	=> __('admin/menus.app_verifications'),
		'icon'	=> 'fas fa-clipboard-check',
		'route'	=> 'admin.app_verifications.index',
		'match'	=> 'admin.app_verifications.',
		'extra'	=> '<span class="badge badge-danger ml-2 text-090">'.badge_number($app_verif_count).'</span>',
	],
	'app_report'	=> [
		'text'	=> __('admin/menus.app_moderation'),
		'icon'	=> 'fas fa-exclamation-triangle',
		'route'	=> 'admin.app_reports.index',
		'match'	=> 'admin.app_reports.',
		'extra'	=> '<span class="badge badge-danger ml-2 text-090">'.badge_number($app_reports_count).'</span>',
	],

	'app_categories'	=> [
		'text'	=> __('admin/menus.app_categories'),
		'icon'	=> 'fas fa-list-ul',
		'route'	=> 'admin.app_categories.index',
		'match'	=> 'admin.app_categories.',
	],
	'app_tags'	=> [
		'text'	=> __('admin/menus.app_tags'),
		'icon'	=> 'fas fa-tags',
		'route'	=> 'admin.app_tags.index',
		'match'	=> 'admin.app_tags.',
	],
];

// TODO: different menus based on role
$menus = [
	'dashboard',

	// 'apps_header',
	'header:admin/menus.app_management',
	'app_list',
	'app_verif',
	'app_report',

	'header:admin/menus.base_data',
	'app_categories',
	'app_tags',
];
?>
	<!-- Main Sidebar Container -->
	<aside class="main-sidebar sidebar-dark-primary elevation-4">
		<!-- Brand Logo -->
		<a href="{{ route('admin.home') }}" class="brand-link">
			<img src="../../dist/img/AdminLTELogo.png"
					 alt="logo"
					 class="brand-image img-circle elevation-3"
					 style="opacity: .8">
			<span class="brand-text font-weight-light">{{ config('app.short_name') }}</span>
		</a>

		<!-- Sidebar -->
		<div class="sidebar">
			<!-- Sidebar user (optional) -->
			<div class="user-panel mt-3 pb-3 mb-3 d-flex">
				<div class="image">
					<img src="../../dist/img/user2-160x160.jpg" class="img-circle elevation-2" alt="User Image">
				</div>
				<div class="info">
					<a href="#" class="d-block">{{ Auth::user()->name }}</a>
				</div>
			</div>

			<!-- Sidebar Menu -->
			<nav class="mt-2">
				<ul class="nav nav-pills nav-sidebar flex-column" data-widget="treeview" role="menu" data-accordion="false">
					@foreach($menus as $key)
					<?php
					list($type, $text) = optional(explode(':', $key, 2));
					if(isset($menu_list[$key])) {
						$item = optional($menu_list[$key]);
						$type = $item['type'] ?: 'item';
						$text = $item['text'];
					} elseif($type == 'header') {
						$text = __($text);
					}
					?>
					@if($type == 'item')
					<?php
					$active = menu_active(${'menu_active_'.$key} ?? active_menu_by_route((array) $item['match']));
					$url = $item['url'] ?: ($item['route'] ? route($item['route']) : '#');
					?>
					<li class="nav-item">
						<a href="{{ $url }}" class="nav-link {{ $active }}">
							<i class="nav-icon {{ $item['icon'] }}"></i>
							<p>
								{{ $text }}
								{!! $item['extra'] !!}
							</p>
						</a>
					</li>
					@elseif($type == 'header')
					<li class="nav-header text-uppercase">{!! $text !!}</li>
					@endif
					@endforeach
				</ul>
			</nav>
			<!-- /.sidebar-menu -->
		</div>
		<!-- /.sidebar -->
	</aside>