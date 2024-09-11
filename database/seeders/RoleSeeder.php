<?php

namespace Database\Seeders;

use App\Models\Address;
use App\Models\Group;
use App\Models\Role;
use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class RoleSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
         Role::create([
            'name' => 'student',
        ]);
         Role::create([
            'name' => 'admin',
        ]);
         Role::create([
            'name' => 'teacher',
        ]);
         Role::create([
            'name' => 'master',
        ]);
      $admin=  User::create([
            'name' =>'admin',
            'username' => 'admin@gmail.com',
            'active' => 1,
            'otp' => null,
            'password' => Hash::make('12345678'),
        ]);
        Address::create([
            'city' =>'ريف دمشق',
            'town' => 'دوما',
            'section' => 'المساكن',
            'description' => null,
        ]);
        Address::create([
            'city' =>'دمشق',
            'town' => 'برزة',
            'section' => 'المساكن',
            'description' => null,
        ]);
        Address::create([
            'city' =>'ريف دمشق',
            'town' => 'مسرابا',
            'section' => 'المساكن',
            'description' => null,
        ]);
        $admin->markEmailAsVerified();
        $role = Role::where('name', 'admin')->first();
        $admin->addRole($role);

    }
}
