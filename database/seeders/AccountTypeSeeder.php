<?php

namespace Database\Seeders;

use App\Models\AccountType;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class AccountTypeSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $data = [
            ['name' => 'Aktiva', 'description' => 'Aktiva Lancar'],
            ['name' => 'Kewajiban', 'description' => 'Kewajiban'],
            ['name' => 'Beban', 'description' => 'Beban']
        ];

        foreach ($data as $value) {
            AccountType::create($value);
        }
    }
}
