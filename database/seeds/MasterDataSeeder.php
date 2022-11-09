<?php

use Illuminate\Database\Seeder;

use App\Models\AppCategory;
use App\Models\AppTag;

class MasterDataSeeder extends Seeder
{
	/**
	 * Run the database seeds.
	 *
	 * @return void
	 */
	public function run()
	{
		// Categories
		$data_category = [
			[
				'name'	=> 'Sistem Informasi',
			],
			[
				'name'	=> 'Aplikasi Mobile',
			],
			[
				'name'	=> 'Blog',
			],
			[
				'name'	=> 'E-Commerce',
			],
			[
				'name'	=> 'LMS',
			],
			[
				'name'	=> 'CMS',
			],
			[
				'name'	=> 'Produk Fisik',
			],
			[
				'name'	=> 'Makanan',
			],
		];
		foreach($data_category as $dc) {
			AppCategory::create($dc + [
				'slug'	=> \Str::slug($dc['name']),
			]);
		}


		// Tags
		$data_tag = [
			[ 'name'	=> 'android' ],
			[ 'name'	=> 'java' ],
			[ 'name'	=> 'web' ],
			[ 'name'	=> 'php' ],
			[ 'name'	=> 'python' ],
			[ 'name'	=> 'fisik' ],
			[ 'name'	=> 'makanan' ],
			[ 'name'	=> 'sistem' ],
			[ 'name'	=> 'skripsi' ],
			[ 'name'	=> 'personal' ],
		];
		foreach($data_tag as $dt) {
			AppTag::create($dt + [
				'slug'	=> \Str::slug($dt['name']),
			]);
		}

	}
}
