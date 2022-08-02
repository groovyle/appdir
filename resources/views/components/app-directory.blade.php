<?php
if(isset($user)) {
	if(! ($user instanceof \App\User) ) {
		$user = \App\User::firstOrNew($user);
	}
} else {
	$user = NULL;
}
?>
@if($user)
<samp><abbr title="{{ __('admin.app.hint.directory_hint_changes_auto') }}">{{ $user->home_directory }}</abbr>{{ $directory }}</samp>
@else
<samp>{{ $directory }}</samp>
@endif