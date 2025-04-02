<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class UsersTableSeeder extends Seeder
{

    /**
     * Auto generated seed file
     *
     * @return void
     */
    public function run()
    {
        DB::table('users')->delete(); 
        DB::table('users')->insert(array (
            0 => 
            array (
                'id' => 1,
                'name' => 'teguh',
                'email' => 'teguh@gmail.com',
                'email_verified_at' => NULL,
                'password' => '$2a$12$dc/KEIJAeDQOm4P4dl7OxeQhCEuAauf1ed1dXt1RVCQhPhknUhHwK',
                'role' => 'user',
                'status' => 0,
                'remember_token' => NULL,
                'created_at' => '2025-04-02 16:24:54',
                'updated_at' => '2025-04-02 16:24:54',
            ),
            1 => 
            array (
                'id' => 3,
                'name' => 'samsul',
                'email' => 'samsul@gmail.com',
                'email_verified_at' => NULL,
                'password' => '$2a$12$dc/KEIJAeDQOm4P4dl7OxeQhCEuAauf1ed1dXt1RVCQhPhknUhHwK',
                'role' => 'user',
                'status' => 0,
                'remember_token' => NULL,
                'created_at' => '2025-04-02 16:34:00',
                'updated_at' => '2025-04-02 16:34:00',
            ),
            2 => 
            array (
                'id' => 4,
                'name' => 'farhan',
                'email' => 'farhan@gmail.com',
                'email_verified_at' => NULL,
                'password' => '$2a$12$dc/KEIJAeDQOm4P4dl7OxeQhCEuAauf1ed1dXt1RVCQhPhknUhHwK',
                'role' => 'user',
                'status' => 0,
                'remember_token' => NULL,
                'created_at' => '2025-04-02 16:39:31',
                'updated_at' => '2025-04-02 16:39:31',
            ),
        ));
        
        
    }
}