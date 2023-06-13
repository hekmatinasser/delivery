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
                'name' => 'user-create',
                'display_name' => 'اضافه کردن کاربر جدید',
                'description' => 'اضافه کردن کاربر جدید'
            ],
        ];


        foreach ($permission as $key => $value) {

            Permission::create($value);
        }

        PermissionRole::create(['role_id' =>1,'permission_id' =>1]);
    }
}
