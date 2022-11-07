<?php

return [

'page_title'	=> [
	'index'			=> 'Manajemen Pengguna',
	'detail'		=> 'Rincian Pengguna',
	'add'			=> 'Tambah Pengguna Baru',
	'edit'			=> 'Edit Pengguna',
	'reset_password'	=> 'Reset Password Pengguna',
	'block_user'	=> 'Blokir Pengguna',
	'block_history'	=> 'Rekam Blokiran',
],

'tab_title'		=> [
	'detail'		=> 'Rincian Pengguna - :x',
	'edit'			=> 'Edit Pengguna :x',
	'reset_password'	=> 'Reset password untuk :x',
	'block_user'	=> 'Blokir Pengguna :x',
	'block_history'	=> 'Rekam Blokiran :x',
],

'_self'					=> 'Pengguna',
'add_new_user'			=> 'Tambahkan Pengguna Baru',
'users_list'			=> 'Daftar Pengguna',
'no_users_yet'			=> 'Belum ada pengguna',
'no_users_matches'		=> 'Pengguna yang Anda cari tidak ditemukan',
'edit_user'				=> 'Edit pengguna',
'see_apps_by_this_user'	=> 'Lihat karya yang dimiliki pengguna ini',
'add_user'				=> 'Tambah pengguna',
'user_data'				=> 'Data pengguna',
'user_roles'			=> 'Role pengguna',
'user_abilities'		=> 'Ability pengguna',
'user_roles_abilities'	=> 'Ability role pengguna',
'user_direct_abilities'	=> 'Izin khusus - ability yang diberikan langsung ke pengguna',
'this_is_you'			=> 'Ini Anda',

'reset_password'		=> 'Reset Password',
'reset_password_for'	=> 'Reset password untuk pengguna',
'block_user'			=> 'Blokir Pengguna',
'blocks_history'		=> 'Rekam Blokiran',
'password_has_been_reset_for_user'	=> 'Password telah di-reset untuk pengguna',
'the_new_password_is'	=> 'Password barunya adalah',
'take_note_of_new_password'	=> 'Catat password baru tersebut dan/atau berikan ke pengguna yang bersangkutan karena <strong>Anda tidak dapat melihat halaman ini lagi</strong>.',
'active_blocks'			=> 'Blokiran Aktif',
'no_active_blocks'		=> 'Pengguna ini tidak sedang diblokir',
'inactive_blocks'		=> 'Blokiran yang Lalu',
'no_inactive_blocks'	=> 'Pengguna ini belum pernah diblokir',
'block_by_x'			=> 'Diblokir oleh :x',
'unblock_by_x'			=> 'Blokir dicabut oleh :x',
'lift_all_blocks'		=> 'Cabut semua blokiran',
'lift_all_blocks_message'	=> 'Cabut semua blokiran untuk <strong>:x</strong>. Pengguna akan dapat mengakses akun mereka lagi.',
'block_is_active'		=> 'Blokiran aktif',
'block_is_inactive'		=> 'Blokiran telah dicabut (dan menjadi tidak aktif)',

'fields'	=> [
	'name_placeholder'	=> 'Nama pengguna',
	'email'				=> 'Alamat Email',
	'email_placeholder'	=> 'Contoh: john_doe@gmail.com',
	'entity_type'		=> 'Tipe Pengguna',
	'prodi'				=> 'Prodi',
	'prodi_placeholder'	=> 'Pengguna termasuk prodi mana',
	'password'			=> 'Password',
	'password_placeholder'	=> 'Password',
	'password_hint'		=> 'Panjang Password harus antara :min sampai :max karakter.',
	'password_confirmation'	=> 'Konfirmasi Password',
	'password_confirmation_placeholder'	=> 'Ketik ulang password yang sama untuk konfirmasi',
	'roles'				=> 'Role',
	'role'				=> 'Role',
	'profile_picture'	=> 'Foto Profil',
	'user'				=> 'Pengguna',
	'block_reason'		=> 'Alasan pemblokiran',
	'block_reason_placeholder'	=> 'Jelaskan mengapa pengguna diblokir...',
	'language'			=> 'Bahasa',
	'date_created'		=> 'Akun Dibuat pada',
],

'messages'	=> [
	'reset_password_successful'		=> 'Password pengguna telah di-reset.',
	'reset_password_failed'			=> 'Error terjadi ketika memroses reset password pengguna.',
	'user_is_already_blocked'		=> 'Pengguna tersebut telah diblokir.',
	'user_is_not_blocked'			=> 'Pengguna tersebut tidak sedang diblokir.',
	'block_successful'				=> 'Pengguna telah diblokir.',
	'unblock_successful'			=> 'Pengguna telah dicabut blokirannya dan dapat mengakses akunnya lagi.',
],

'statuses'	=> [
	'active'	=> 'Aktif',
	'blocked'	=> 'Diblokir',
],


];
