<?php

use App\User;
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
        $a = new User([
            'name' => 'admin',
            'email' => 'test@test.com',
            'password' => bcrypt('test'),
            ]);
        $a->save();
    }
}
