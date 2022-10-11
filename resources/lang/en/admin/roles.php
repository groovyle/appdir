<?php

return [

'page_title'	=> [
	'index'			=> 'User Roles Management',
	'detail'		=> 'View Role',
	'add'			=> 'Add New Role',
	'edit'			=> 'Edit Role',
],

'tab_title'		=> [
	'detail'		=> 'View Role :x',
	'edit'			=> 'Edit Role :x',
],

'_self'					=> 'Role',
'add_new_role'			=> 'Add a New Role',
'roles_list'			=> 'Roles List',
'no_roles_yet'			=> 'There are no roles yet',
'no_roles_matches'		=> 'No roles match your search',
'edit_role'				=> 'Edit role',
'see_users_in_this_role'	=> 'See users in this role',
'add_role'				=> 'Add role',
'role_data'				=> 'Role data',
'role_abilities'		=> 'Role abilities',
'role_users'			=> 'Users with this role',

'role_status_allowed'	=> 'Allowed',
'role_status_forbidden'	=> 'Forbidden',

'management_warning'	=> '<strong>Please take care</strong> when editing/managing around user roles, which are used internally by the system. Any modifications might result in breaking the system.<br>This management interface is primarily intended to be used by <strong>system administrators and developers</strong>.<br>Do not be here unless you know what you are doing!',

'fields'	=> [
	'name'						=> 'Name',
	'name_placeholder'			=> 'Role name',
	'name_hint'					=> 'Lowercase letters and dashes instead of spaces, e.g manages-basic-data.',
	'name_hint_edit'			=> 'Changing role name is not supported because the name is used internally by the system.',
	'title'						=> 'Title',
	'title_placeholder'			=> 'Role title',
	'title_hint'				=> 'A role title that is nice to read. If not supplied, title will be automatically generated based on the name.',
	'abilities'					=> 'Abilities',
	'abilities_placeholder'		=> 'This role\'s capabilities or access rights',
	'users'						=> 'Users',
	'users_placeholder'			=> 'Users with this role',
	'level'						=> 'Level',
	'scope'						=> 'Scope',
],


];
