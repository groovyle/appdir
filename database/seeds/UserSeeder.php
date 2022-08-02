<?php

use Illuminate\Database\Seeder;
use App\User;

class UserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        //
        User::create([
            'name' => 'Superuser',
            'email' => '3kilo.ai@gmail.com',
            'password' => Hash::make('admin'),
        ]);
    }
}
