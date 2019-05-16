<?php

use Illuminate\Database\Seeder;

class TestSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        // test user
        DB::table('users')->insert([
            'id' => 1,
            'name' => 'Test User',
            'email' => 'e2e@test.com',
            'is_admin' => false,
            'password' => app('hash')->make('pass')
        ]);
        // test admin user
        DB::table('users')->insert([
            'id' => 2,
            'name' => 'Test User',
            'email' => 'e2e.admin@test.com',
            'is_admin' => true,
            'password' => app('hash')->make('pass')
        ]);
    }
}
