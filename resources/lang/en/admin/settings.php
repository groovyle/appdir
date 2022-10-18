<?php

return [

'page_title'	=> [
	'index'			=> 'System Settings Management',
	'detail'		=> 'View Setting',
	'add'			=> 'Add New Setting',
	'edit'			=> 'Edit Setting',
],

'tab_title'		=> [
	'detail'		=> 'View Setting :x',
	'edit'			=> 'Edit Setting :x',
],

'_self'					=> 'Setting',
'add_new_setting'		=> 'Add a New Setting',
'settings_list'			=> 'Settings List',
'no_settings_yet'		=> 'There are no settings yet',
'no_settings_matches'	=> 'No settings match your search',
'edit_setting'			=> 'Edit setting',
'add_setting'			=> 'Add setting',

'management_warning'	=> '<strong>Please take care</strong> when editing/managing around system settings, which are used internally by the system. Any modifications might result in breaking the system.<br>This management interface is primarily intended to be used by <strong>system administrators and developers</strong>.<br>Do not be here unless you know what you are doing!',

'fields'	=> [
	'key'						=> 'Key',
	'key_hint'					=> 'Key should be unique, and in the form of letters and dots, e.g common.pagination.per_page',
	'key_placeholder'			=> 'Unique key for the setting item',
	'value'						=> 'Value',
	'value_hint'				=> 'Value can be anything, as needed by the system',
	'value_placeholder'			=> 'The actual value for the setting',
	'description_placeholder'	=> 'Type description or hint of the possible values for this setting',
],


];
