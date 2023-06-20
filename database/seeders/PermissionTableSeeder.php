<?php

namespace Database\Seeders;

use App\Models\Permission;
use App\Models\PermissionRole;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class PermissionTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $permission = [
            [
                'id' => '1',
                'name' => 'user-modify',
                'display_name' => 'مدیریت کاربران',
                'description' => 'مدیریت کاربران'
            ],
            [
                'id' => '2',
                'name' => 'store-modify',
                'display_name' => 'مدیریت مغازه ها',
                'description' => 'مدیریت مغازه ها '
            ],
            [
                'id' => '3',
                'name' => 'vehicle-modify',
                'display_name' => 'مدیریت پیک ها',
                'description' => 'مدیریت پیک ها'
            ],
            [
                'id' => '4',
                'name' => 'neighborhood-modify',
                'display_name' => 'مدیریت محله ها',
                'description' => 'مدیریت محله ها'
            ],
        ];


        foreach ($permission as $key => $value) {

            Permission::create($value);
        }

        PermissionRole::create(['role_id' => 1, 'permission_id' => 1]);
        PermissionRole::create(['role_id' => 1, 'permission_id' => 2]);
        PermissionRole::create(['role_id' => 1, 'permission_id' => 3]);
        PermissionRole::create(['role_id' => 1, 'permission_id' => 4]);
    }
}
