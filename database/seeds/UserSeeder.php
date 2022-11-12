<?php

use Illuminate\Database\Seeder;

use App\User;
use App\Models\Prodi;
use Illuminate\Support\Facades\Hash;

class UserSeeder extends Seeder
{
	/**
	 * Run the database seeds.
	 *
	 * @return void
	 */
	public function run()
	{
		config()->set('database.action_logging', false);

		User::query()->withoutGlobalScopes()->toBase()->where('entity', '=', 'user')->delete();
		Prodi::query()->withoutGlobalScopes()->toBase()->delete();


		// Prodi first
		$data_prodi = [
			[
				'name'			=> 'Pend. Teknik Informatika',
				'short_name'	=> 'PTI',
			],
			[
				'name'			=> 'Pend. Teknik Elektronika',
				'short_name'	=> 'PT Elka',
			],
			[
				'name'			=> 'Pend. Teknik Boga',
				'short_name'	=> 'PT Boga',
			],
			[
				'name'			=> 'Pend. Teknik Busana',
				'short_name'	=> 'PT Busana',
			],
			[
				'name'			=> 'Pend. Teknik Mesin',
				'short_name'	=> 'PT Mesin',
			],
			[
				'name'			=> 'Pend. Teknik Otomotif',
				'short_name'	=> 'PT Oto',
			],
			[
				'name'			=> 'Pend. Teknik Sipil dan Perencanaan',
				'short_name'	=> 'PTSP',
			],
			[
				'name'			=> 'Pend. Teknik Elektro',
				'short_name'	=> 'PTE',
			],
			[
				'name'			=> 'Pend. Teknik Mekatronika',
				'short_name'	=> 'PT Meka',
			],
		];
		$prodis = collect();
		foreach($data_prodi as $dp) {
			$prodis[] = Prodi::create($dp + [
				'slug'	=> \Str::slug($dp['name']),
			]);
		}

		$prodis = $prodis->pluck('id')->all();
		$get_prodi = function($i) use($prodis) {
			return $prodis[ $i % count($prodis) ] ?? null;
		};



		// Superuser
		$su = User::create([
			'name' => 'Superuser',
			'email' => 'sadmin@admin.com',
			'password' => Hash::make('sadmin'),
		]);
		Bouncer::assign('superadmin')->to($su);

		// Admin
		$admins = 3;
		// $admins = count($prodis);
		for($i = 1; $i <= $admins; $i++) {
			$prodi_id = $get_prodi($i-1);
			$admin = User::create([
				'name'		=> 'Admin '.$i,
				'email'		=> 'admin'.$i.'@admin.com',
				'password'	=> Hash::make('admin'.$i),
				'prodi_id'	=> $prodi_id,
			]);
			Bouncer::assign('admin')->to($admin);
		}

		// Mahasiswa
		// $mhss = 5;
		$mhss = $admins * 2;
		for($i = 1; $i <= $mhss; $i++) {
			$prodi_id = $get_prodi(floor(($i-1) / 2));
			$mhs = User::create([
				'name'		=> 'Mahasiswa '.$i.' '.random_string(5),
				'email'		=> 'mhs'.$i.'@mhs.com',
				'password'	=> Hash::make('mhs'.$i),
				'prodi_id'	=> $prodi_id,
			]);
			Bouncer::assign('mahasiswa')->to($mhs);
		}
	}
}
