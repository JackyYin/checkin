<?php

use Illuminate\Database\Seeder;

class UsersTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
		DB::table('users')->insert([
            'name' => 'jackyyin',
            'email' => 'jjyyg1123@gmail.com',
            'password' => bcrypt('password'),
            'is_admin' => true,
        ]);
    }
}
