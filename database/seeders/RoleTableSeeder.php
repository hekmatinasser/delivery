<?php

namespace Database\Seeders;

use App\Models\Role;
use App\Models\RoleUser;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class RoleTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $roles = [
            [
                'id' => '1',
                'name' => 'admin',
                'display_name' => 'مدیر',
                'description' => 'دسترسی مدیریت'
            ],
        ];


        foreach ($roles as $key => $value) {
            Role::create($value);
        }

        RoleUser::create(['user_id' => '1', 'role_id' => '1']);
    }
}
