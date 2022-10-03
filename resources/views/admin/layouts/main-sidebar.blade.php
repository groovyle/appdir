<?php
list($app_verif_query, ) = \App\Http\Controllers\Admin\AppVerificationController::listQuery();
$app_verif_query = $app_verif_query->getQuery()->cloneWithout(['groups', 'columns']);
$app_verif_count = $app_verif_query->selectRaw('count(distinct a.id) as vcount')->value('vcount');
$app_verif_count = badge_number($app_verif_count);

$app_reports_count = \App\Models\AppReport::unresolved()->count();
$app_reports_count = badge_number($app_reports_count);
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
          <!-- Add icons to the links using the .nav-icon class
               with font-awesome or any other icon font library -->
          <li class="nav-item">
            <a href="{{ route('admin.home') }}" class="nav-link">
              <i class="nav-icon fas fa-tachometer-alt"></i>
              <p>
                @lang('admin/menus.dashboard')
              </p>
            </a>
          </li>
          <li class="nav-item">
            <a href="{{ route('admin.apps.index') }}" class="nav-link">
              <i class="nav-icon fas fa-cloud"></i>
              <p>
                @lang('admin/menus.app_list')
              </p>
            </a>
          </li>
          <li class="nav-item">
            <a href="{{ route('admin.app_verifications.index') }}" class="nav-link">
              <i class="nav-icon fas fa-tasks"></i>
              <p>
                @lang('admin/menus.app_verifications')
                <span class="badge badge-danger ml-2 text-090">{{ $app_verif_count }}</span>
              </p>
            </a>
          </li>
          <li class="nav-item">
            <a href="{{ route('admin.app_reports.index') }}" class="nav-link">
              <i class="nav-icon fas fa-exclamation-triangle"></i>
              <p>
                @lang('admin/menus.app_reports')
                <span class="badge badge-danger ml-2 text-090">{{ $app_reports_count }}</span>
              </p>
            </a>
          </li>
        </ul>
      </nav>
      <!-- /.sidebar-menu -->
    </div>
    <!-- /.sidebar -->
  </aside>