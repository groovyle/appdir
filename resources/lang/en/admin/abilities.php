<?php

return [

'page_title'	=> [
	'index'			=> 'Abilities & Permissions Management',
	'detail'		=> 'View Ability',
	'add'			=> 'Add New Ability',
	'edit'			=> 'Edit Ability',
],

'tab_title'		=> [
	'detail'		=> 'View Ability :x',
	'edit'			=> 'Edit Ability :x',
],

'_self'					=> 'Ability',
'add_new_ability'		=> 'Add a New Ability',
'abilities_list'		=> 'Abilities List',
'no_abilities_yet'		=> 'There are no abilities yet',
'no_abilities_matches'	=> 'No abilities match your search',
'edit_ability'			=> 'Edit ability',
'see_users_with_this_ability'	=> 'See users with this ability',
'add_ability'			=> 'Add ability',
'ability_data'			=> 'Ability data',
'ability_roles'			=> 'Roles having this ability',
'ability_users'			=> 'Users with this ability',

'management_warning'	=> '<strong>Please take care</strong> when editing/managing around abilities, which are used internally by the system. Any modifications might result in breaking the system.<br>This management interface is primarily intended to be used by <strong>system administrators and developers</strong>.<br>Do not be here unless you know what you are doing!',

'details'	=> [
	'mode_allow'		=> 'Allow',
	'mode_allowed'		=> 'Allowed',
	'mode_forbid'		=> 'Forbid',
	'mode_forbidden'	=> 'Forbidden',
	'only_owned'		=> 'Only on owned items',
	'alias_from'		=> 'alias from',
	'morphed_from'		=> 'morphed from',
],

'fields'	=> [
	'name'						=> 'Name',
	'name_placeholder'			=> 'Ability name',
	'name_hint'					=> 'Lowercase letters and dashes instead of spaces, e.g edit-non-owned-items.',
	'name_hint_edit'			=> 'Changing ability name is not supported because the name is used internally by the system.',
	'attribute_hint_no_edit'	=> 'Changing/editing this field is not supported because it is used internally by the system.',
	'title'						=> 'Title',
	'title_placeholder'			=> 'Ability title',
	'title_hint'				=> 'An ability title that is nice to read. If not supplied, title will be automatically generated based on the name.',
	'roles'						=> 'Roles',
	'roles_placeholder'			=> 'Roles having this ability',
	'role'						=> 'Role',
	'role_mode'					=> 'Role mode',
	'users'						=> 'Users',
	'users_placeholder'			=> 'Users with this ability',
	'user'						=> 'User',
	'users_hint'				=> 'Generally, abilities shouldn\'t be granted through direct assignments, but through role abilities instead.',
	'entity_id'					=> 'Entity ID',
	'entity_type'				=> 'Entity Type',
	'entity_type_hint'			=> 'Entity type is the model class name for the rule. This may be morphed automatically if the system has a morph map defined for that entity/model class.',
	'only_owned'				=> 'Only Owned?',
	'scope'						=> 'Scope',
	'options'					=> 'Options',
	'some_attributes_cannot_be_edited'	=> 'Some attributes of the ability cannot be edited because those attributes are used internally within the system.',
],


];
