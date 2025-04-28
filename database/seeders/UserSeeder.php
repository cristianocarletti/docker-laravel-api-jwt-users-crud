<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use App\Models\User;

class UserSeeder extends Seeder
{
    public function run(): void
    {
        User::create([
            'name' => 'João',
            'lastname' => 'Silva',
            'phone' => '+55 (11) 91234-5678',
            'email' => 'joao.silva@example.com',
            'email_verified_at' => now(),
            'password' => Hash::make('senha123'), // Senha segura
            'remember_token' => Str::random(10),
        ]);
    }
}
