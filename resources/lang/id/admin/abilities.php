<?php

return [

'page_title'	=> [
	'index'			=> 'Manajemen Ability & Perizinan',
	'detail'		=> 'Rincian Ability',
	'add'			=> 'Tambah Ability Baru',
	'edit'			=> 'Edit Ability',
],

'tab_title'		=> [
	'detail'		=> 'Rincian Ability :x',
	'edit'			=> 'Edit Ability :x',
],

'_self'					=> 'Ability',
'add_new_ability'		=> 'Tambahkan Ability Baru',
'abilities_list'		=> 'Daftar Ability',
'no_abilities_yet'		=> 'Belum ada ability',
'no_abilities_matches'	=> 'Ability yang dicari tidak ditemukan',
'edit_ability'			=> 'Edit ability',
'see_users_with_this_ability'	=> 'Lihat pengguna dengan ability ini',
'add_ability'			=> 'Tambah ability',
'ability_data'			=> 'Data ability',
'ability_roles'			=> 'Roles dengan ability ini',
'ability_users'			=> 'Pengguna dengan ability ini',

'management_warning'	=> '<strong>Tolong berhati-hati</strong> ketika mengelola/edit ability, karena digunakan secara internal oleh sistem. Modifikasi tertentu dapat berujung pada kerusakan sistem.<br>Antarmuka pengelolaan ini utamanya ditujukan untuk penggunaan oleh <strong>admin dan pengembang sistem</strong>.<br>Jangan kesini jika Anda tidak 100% tahu pasti akan melakukan apa!',

'details'	=> [
	'mode_allow'		=> 'Boleh',
	'mode_allowed'		=> 'Dibolehkan',
	'mode_forbid'		=> 'Larang',
	'mode_forbidden'	=> 'Dilarang',
	'only_owned'		=> 'Hanya item yang dipunyai',
	'alias_from'		=> 'alias dari',
	'morphed_from'		=> 'berubah (morph) dari',
],

'fields'	=> [
	'name'						=> 'Nama',
	'name_placeholder'			=> 'Nama ability',
	'name_hint'					=> 'Huruf kecil dan setrip sebagai ganti spasi, misal edit-non-owned-items.',
	'name_hint_edit'			=> 'Tidak dapat mengubah nama ability karena digunakan secara internal oleh sistem.',
	'attribute_hint_no_edit'	=> 'Tidak dapat mengubah kolom ini karena digunakan secara internal oleh sistem.',
	'title'						=> 'Judul',
	'title_placeholder'			=> 'Judul ability',
	'title_hint'				=> 'Judul ability adalah nama tampil yang enak dibaca. Jika dikosongkan, akan dibuat otomatis berdasarkan nama.',
	'roles'						=> 'Role',
	'roles_placeholder'			=> 'Role dengan ability ini',
	'role'						=> 'Role',
	'role_mode'					=> 'Mode role',
	'users'						=> 'Pengguna',
	'users_placeholder'			=> 'Pengguna dengan ability ini',
	'user'						=> 'Pengguna',
	'users_hint'				=> 'Pada dasarnya, ability harusnya tidak diberikan secara langsung kepada pengguna, melainkan melalui role.',
	'entity_id'					=> 'Entity ID',
	'entity_type'				=> 'Entity Type',
	'entity_type_hint'			=> 'Entity type adalah nama kelas model untuk aturan/ability ini. Atribut ini dapat diubah (morph) otomatis jika sistem mempunyai morph map untuk kelas entitas/model tersebut.',
	'only_owned'				=> 'Hanya yang Dipunyai?',
	'scope'						=> 'Scope',
	'options'					=> 'Options',
	'some_attributes_cannot_be_edited'	=> 'Beberapa atribut ability tidak dapat diubah karena digunakan secara internal oleh sistem.',
],


];
