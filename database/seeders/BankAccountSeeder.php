<?php

namespace Database\Seeders;

use App\Models\BankAccount;
use Illuminate\Database\Seeder;

class BankAccountSeeder extends Seeder
{
    public function run(): void
    {
        $accounts = [
            [
                'bank_name' => 'BCA',
                'account_number' => '1234567890',
                'account_name' => 'PT Rupa Platform Indonesia',
                'is_active' => true,
            ],
            [
                'bank_name' => 'Mandiri',
                'account_number' => '9876543210',
                'account_name' => 'PT Rupa Platform Indonesia',
                'is_active' => true,
            ],
        ];

        foreach ($accounts as $account) {
            BankAccount::query()->updateOrCreate(
                [
                    'bank_name' => $account['bank_name'],
                    'account_number' => $account['account_number'],
                ],
                $account
            );
        }
    }
}
