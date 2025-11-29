<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Spatie\Permission\Models\Role;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
  
        $role = Role::firstOrCreate(['name' => 'admin']);

    
        $user = User::firstOrCreate(
            ['email' => 'admin@admin.com'],
            [
                'name' => 'Administrador',
                'password' => Hash::make('-GpZND<I(n37a26~R-'), 
                'role' => 'admin',
            ]
        );

    
        $user->assignRole($role);

      
        $this->call([\Database\Seeders\OrderStatusesSeeder::class]);
    }
}