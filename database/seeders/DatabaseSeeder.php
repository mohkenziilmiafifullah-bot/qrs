<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        User::create([
            'name' => 'Admin Platform',
            'email' => 'admin@qrsaas.test',
            'password' => Hash::make('password'),
            'role' => 'admin',
            'status' => 'active',
        ]);

        User::create([
            'name' => 'Sales Demo',
            'email' => 'sales@qrsaas.test',
            'password' => Hash::make('password'),
            'role' => 'sales',
            'status' => 'active',
            'wallet_balance' => 100000,
        ]);
    }
}
