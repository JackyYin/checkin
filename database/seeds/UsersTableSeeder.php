<?php

use Illuminate\Database\Seeder;
use App\Models\Staff;

class UsersTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $staff = Staff::create([
            'name'     => 'jacky',
            'email'    => 'jjyyg1123@gmail.com',
            'active'   => 0,
        ]);
        DB::table('admin')->insert([
            'staff_id' => $staff->id,
            'name' => 'root',
            'email' => 'jjyyg1123@gmail.com',
            'password' => bcrypt('12345678'),
        ]);
        Staff::create([
            'name'     => 'joe',
            'email'    => 't9590345@gmail.com',
            'active'   => 0,
        ]);
    }
}
