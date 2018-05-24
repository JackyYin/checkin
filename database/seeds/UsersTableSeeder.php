<?php

use Illuminate\Database\Seeder;
use App\Models\Staff;
use App\Models\Profile;

class UsersTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $jacky = Staff::create([
            'name'     => '殷豪',
            'email'    => 'jjyyg1123@gmail.com',
            'active'   => 0,
        ]);
        Profile::create([
            'staff_id'  => $jacky->id,
        ]);
        DB::table('admin')->insert([
            'staff_id' => $jacky->id,
            'name' => 'root',
            'email' => 'jjyyg1123@gmail.com',
            'password' => bcrypt('12345678'),
        ]);
        $joe = Staff::create([
            'name'     => '江承諭',
            'email'    => 't9590345@gmail.com',
            'active'   => 0,
        ]);
        Profile::create([
            'staff_id'  => $joe->id,
        ]);
    }
}
