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
        // test teams
        DB::table('groups')->insert([
            'id' => 1,
            'name' => 'Public Group A',
            'is_public' => true
        ]);
        DB::table('groups')->insert([
            'id' => 2,
            'name' => 'Public Group B',
            'is_public' => true
        ]);
        DB::table('groups')->insert([
            'id' => 3,
            'name' => 'Private Group A',
            'is_public' => false
        ]);
        DB::table('groups')->insert([
            'id' => 4,
            'name' => 'Private Group B',
            'is_public' => false
        ]);
        // team memberships
        DB::table('group_user')->insert([
            'user_id' => 1,
            'group_id' => 1,
            'is_admin' => true
        ]);
        DB::table('group_user')->insert([
            'user_id' => 1,
            'group_id' => 3,
            'is_admin' => false
        ]);
    }
}
