<?php

use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        // Prepopulated data:
        $this->call(ConfigSeeder::class);
        
        
        
        
        // Test Data:
        $this->call(UsersTableSeeder::class);
        $this->call(TestData::class);
    }
}
