<?php

use Illuminate\Database\Seeder;
use App\TargetList;
use App\TargetUser;

class TestData extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $t1 = TargetUser::create(['first_name' => 'Fred', 'last_name' => 'Jones', 'email' => 'fredjones@gmail.com']);
        $t2 = TargetUser::create(['first_name' => 'John', 'last_name' => 'Fyfe', 'email' => 'johnfyfe@gmail.com']);
        $t3 = TargetUser::create(['first_name' => 'Bob', 'last_name' => 'Anderson', 'email' => 'bobanderson@gmail.com']);
        $l1 = TargetList::create(['name' => 'Test List 1']);
        $l2 = TargetList::create(['name' => 'Another Test List 2']);
        
        $l1->users()->attach($t1);
        $l1->users()->attach($t3);
        
        $l2->users()->attach($t2);
    }
}
