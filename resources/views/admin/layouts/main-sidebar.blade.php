<?php

use App\Http\Controllers\Admin as Ctl;
use Illuminate\Support\Facades\Gate;

$app_verif_count = get_count_from_list_query(Ctl\AppVerificationController::listQuery()['query'], 'a.id');
$app_reports_count = get_count_from_list_query(Ctl\AppReportController::listQuery()['query'], 'rur.id');

$menu_list = [
	'dashboard'		=> [
		'text'	=> __('admin/menus.dashboard'),
		'icon'	=> 'fas fa-tachometer-alt',
		'route'	=> 'admin.home',
		'match'	=> ['admin.home', true],
		'check'	=> true,
	],

	'app_list'	=> [
		'text'	=> __('admin/menus.app_list'),
		'icon'	=> 'fas fa-cloud',
		'route'	=> 'admin.apps.index',
		'match'	=> 'admin.apps.',
		'check'	=> Gate::any(['view-any', 'view-any-in-prodi', 'view-all'], App\Models\App::class),
	],
	'app_verif'	=> [
		'text'	=> __('admin/menus.app_verifications'),
		'icon'	=> 'fas fa-clipboard-check',
		'route'	=> 'admin.app_verifications.index',
		'match'	=> 'admin.app_verifications.',
		'extra'	=> '<span class="badge badge-info ml-2 text-090">'.badge_number($app_verif_count).'</span>',
		'check'	=> Gate::allows('view-any', App\Models\AppVerification::class),
	],
	'app_report'	=> [
		'text'	=> __('admin/menus.app_moderation'),
		'icon'	=> 'fas fa-exclamation-triangle',
		'route'	=> 'admin.app_reports.index',
		'match'	=> 'admin.app_reports.',
		'extra'	=> '<span class="badge badge-danger ml-2 text-090">'.badge_number($app_reports_count).'</span>',
		'check'	=> Gate::allows('view-any', App\Models\AppVerdict::class),
	],

	'app_categories'	=> [
		'text'	=> __('admin/menus.app_categories'),
		'icon'	=> 'fas fa-list-ul',
		'route'	=> 'admin.app_categories.index',
		'match'	=> 'admin.app_categories.',
		'check'	=> Gate::allows('view-any', App\Models\AppCategory::class),
	],
	'app_tags'	=> [
		'text'	=> __('admin/menus.app_tags'),
		'icon'	=> 'fas fa-tags',
		'route'	=> 'admin.app_tags.index',
		'match'	=> 'admin.app_tags.',
		'check'	=> Gate::allows('view-any', App\Models\AppTag::class),
	],

	'prodi'	=> [
		'text'	=> __('admin/menus.prodi'),
		'icon'	=> 'fas fa-sitemap',
		'route'	=> 'admin.prodi.index',
		'match'	=> 'admin.prodi.',
		'check'	=> Gate::allows('view-any', App\Models\Prodi::class),
	],
	'users'	=> [
		'text'	=> __('admin/menus.users'),
		'icon'	=> 'fas fa-users',
		'route'	=> 'admin.users.index',
		'match'	=> 'admin.users.',
		'check'	=> Gate::allows('view-any', 'App\User'),
	],
	'user_roles'	=> [
		'text'	=> __('admin/menus.user_roles'),
		'icon'	=> 'fas fa-users-cog',
		'route'	=> 'admin.roles.index',
		'match'	=> 'admin.roles.',
		'check'	=> Gate::allows('view-any', App\Models\Role::class),
	],
	'system_abilities'	=> [
		'text'	=> __('admin/menus.system_abilities'),
		'icon'	=> 'fas fa-user-cog',
		'route'	=> 'admin.abilities.index',
		'match'	=> 'admin.abilities.',
		'check'	=> Gate::allows('view-any', App\Models\Ability::class),
	],
	'system_settings'	=> [
		'text'	=> __('admin/menus.system_settings'),
		'icon'	=> 'fas fa-wrench',
		'route'	=> 'admin.settings.index',
		'match'	=> 'admin.settings.',
		'check'	=> Gate::allows('view-any', App\Models\Setting::class),
	],
	'log_actions'	=> [
		'text'	=> __('admin/menus.log_actions'),
		'icon'	=> 'fas fa-stream',
		'route'	=> 'admin.log_actions.index',
		'match'	=> 'admin.log_actions.',
		'check'	=> Gate::allows('view-any', App\Models\LogAction::class),
	],
];

// TODO: different menus based on role
// ?? or just divide it by the checks??? p good huh?
$menus = [
	'dashboard',

	'header:admin/menus.header_app_management' => [
		'app_list',
		'app_verif',
		'app_report',
	],

	'header:admin/menus.header_base_data' => [
		'app_categories',
		'app_tags',
	],

	'header:admin/menus.header_system' => [
		'prodi',
		'users',
		'user_roles',
		'system_abilities',
		'system_settings',
		'log_actions',
	],
];

$generate_menus = function($menus) use(&$generate_menus, $menu_list) {
	foreach($menus as $key => $value):
		$menu_is_active = function($item) {
			return !isset($item['match']) ? false : ( is_bool($item['match'])
				? $item['match']
				: active_menu_by_route((array) $item['match']) )
			;
		};
		$has_subs = is_array($value);
		$menu_key = $has_subs ? $key : $value;
		$flattened = collect($value)->flatten();
		$subs_filled = $has_subs && $flattened->contains(function($item) use($menu_list) {
			return $menu_list[$item]['check'] ?? false;
		});
		$treeview_open = $has_subs && $flattened->contains(function($item) use($menu_list, $menu_is_active) {
			return $menu_is_active($menu_list[$item] ?? null);
		});
		list($type, $text) = optional(explode(':', $menu_key, 2));
		if(isset($menu_list[$menu_key])) {
			$item = optional($menu_list[$menu_key]);
			$type = $item['type'] ?: 'item';
			$text = $item['text'];
		} elseif($type == 'header') {
			$text = __($text);
		}
		?>
		@if($type == 'item')
		<?php
		if(!$item['check']) continue;
		$active = menu_active( ${'menu_active_'.$menu_key}
			?? ( $menu_is_active($item) || $treeview_open )
		);
		$url = $item['url'] ?: ($item['route'] ? route($item['route']) : '#');
		?>
		<li class="nav-item @if($subs_filled) has-treeview @endif @if($treeview_open) menu-open @endif">
			<a href="{{ !$subs_filled ? $url : '#' }}" class="nav-link {{ $active }}">
				<i class="nav-icon {{ $item['icon'] }}"></i>
				<p>
					{{ $text }}
					{!! $item['extra'] !!}
					@if($subs_filled)
					<i class="fas fa-angle-left right"></i>
					@endif
				</p>
			</a>
			@if($subs_filled)
			<ul class="nav nav-treeview" @if($treeview_open) style="display: block;" @endif>
				<?php $generate_menus($value); ?>
			</ul>
			@endif
		</li>
		@elseif($type == 'header')
		@if(!$has_subs || $subs_filled)
		<li class="nav-header tight text-uppercase">{!! $text !!}</li>
			@if($subs_filled)
			<?php $generate_menus($value); ?>
			@endif
		@endif
		@endif
<?php
	endforeach;
}
?>
	<!-- Main Sidebar Container -->
	<aside class="main-sidebar sidebar-dark-primary elevation-4">
		<!-- Brand Logo -->
		<!-- TODO: logo -->
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
				<ul class="nav nav-pills nav-sidebar nav-treeview-lined flex-column pb-5" data-widget="treeview" role="menu" data-accordion="false">
					<?php $generate_menus($menus); ?>
				</ul>
			</nav>
			<!-- /.sidebar-menu -->
		</div>
		<!-- /.sidebar -->
	</aside>