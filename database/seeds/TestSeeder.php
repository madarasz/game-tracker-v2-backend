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
            'id' => 9001,
            'name' => 'Test User',
            'email' => 'e2e@test.com',
            'is_admin' => false,
            'password' => app('hash')->make('pass')
        ]);
        // test admin user
        DB::table('users')->insert([
            'id' => 9002,
            'name' => 'Test Admin',
            'email' => 'e2e.admin@test.com',
            'is_admin' => true,
            'password' => app('hash')->make('pass')
        ]);
        // test groups
        DB::table('groups')->insert([
            'id' => 9001,
            'name' => 'Public Group A',
            'is_public' => true,
            'created_by' => 9001
        ]);
        DB::table('groups')->insert([
            'id' => 9002,
            'name' => 'Public Group B',
            'is_public' => true,
            'created_by' => 9002
        ]);
        DB::table('groups')->insert([
            'id' => 9003,
            'name' => 'Private Group A',
            'is_public' => false,
            'created_by' => 9001
        ]);
        DB::table('groups')->insert([
            'id' => 9004,
            'name' => 'Private Group B',
            'is_public' => false,
            'created_by' => 9002
        ]);
        // group memberships
        DB::table('group_user')->insert([
            'user_id' => 9001,
            'group_id' => 9001,
            'is_group_admin' => true
        ]);
        DB::table('group_user')->insert([
            'user_id' => 9002,
            'group_id' => 9001,
            'is_group_admin' => false
        ]);
        DB::table('group_user')->insert([
            'user_id' => 9001,
            'group_id' => 9003,
            'is_group_admin' => false
        ]);
        // games
        DB::table('game')->insert([
            'id' => 31260,
            'name' => 'Agricola',
            'designers' => 'Uwe Rosenberg',
            'thumbnail' => 'https://cf.geekdo-images.com/thumb/img/zl48oz7IeKlgWJVBLYd0nFJumdA=/fit-in/200x150/pic259085.jpg',
            'year' => 2007,
            'created_by' => 9001,
            'type' => 1
        ]);
        DB::table('group_game')->insert([
            'group_id' => 9001,
            'game_id' => 31260, // Agricola
            'created_by' => 9001
        ]);

    }
}
