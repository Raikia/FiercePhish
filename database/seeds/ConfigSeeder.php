<?php

use Illuminate\Database\Seeder;

use App\Config;

class ConfigSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        (new Config(['key' => 'asdf', 'value' => 'fdsa', 'description' => 'This is a description']))->save();
        
    }
}
