<?php

namespace Database\Seeders;

use App\Models\Customer;
use Illuminate\Database\Seeder;

class CustomerSeeder extends Seeder
{
    public function run(): void
    {
        $customers = [
            [
                'name' => 'Rina Wijaya',
                'phone' => '081234567890',
                'email' => 'rina@jagoflutter.com',
                'birthdate' => '1990-03-15',
                'gender' => 'female',
                'address' => 'Jl. Sudirman No. 123, Jakarta Pusat',
                'skin_type' => 'combination',
                'skin_concerns' => ['acne', 'large_pores'],
                'allergies' => 'AHA, Parfum',
            ],
            [
                'name' => 'Siti Aminah',
                'phone' => '081398765432',
                'email' => 'siti@jagoflutter.com',
                'birthdate' => '1988-07-22',
                'gender' => 'female',
                'address' => 'Jl. Gatot Subroto No. 45, Jakarta Selatan',
                'skin_type' => 'oily',
                'skin_concerns' => ['acne', 'blackheads'],
                'allergies' => null,
            ],
            [
                'name' => 'Dewi Kartika',
                'phone' => '081512345678',
                'email' => 'dewi@jagoflutter.com',
                'birthdate' => '1995-11-30',
                'gender' => 'female',
                'address' => 'Jl. Rasuna Said No. 78, Jakarta Selatan',
                'skin_type' => 'dry',
                'skin_concerns' => ['dehydration', 'dull_skin'],
                'allergies' => null,
            ],
            [
                'name' => 'Anisa Putri',
                'phone' => '081287654321',
                'email' => 'anisa@jagoflutter.com',
                'birthdate' => '1992-05-18',
                'gender' => 'female',
                'address' => 'Jl. Thamrin No. 56, Jakarta Pusat',
                'skin_type' => 'sensitive',
                'skin_concerns' => ['redness', 'sensitive'],
                'allergies' => 'Retinol',
            ],
            [
                'name' => 'Maya Sari',
                'phone' => '081365432109',
                'email' => 'maya.s@jagoflutter.com',
                'birthdate' => '1985-09-08',
                'gender' => 'female',
                'address' => 'Jl. Kuningan No. 12, Jakarta Selatan',
                'skin_type' => 'normal',
                'skin_concerns' => ['aging', 'pigmentation'],
                'allergies' => null,
            ],
            [
                'name' => 'Fitri Handayani',
                'phone' => '082145678901',
                'email' => 'fitri.h@jagoflutter.com',
                'birthdate' => '1993-01-25',
                'gender' => 'female',
                'address' => 'Jl. Kemang Raya No. 88, Jakarta Selatan',
                'skin_type' => 'oily',
                'skin_concerns' => ['acne', 'oily'],
                'allergies' => null,
            ],
            [
                'name' => 'Ratna Dewi',
                'phone' => '082234567890',
                'email' => 'ratna.d@jagoflutter.com',
                'birthdate' => '1987-06-12',
                'gender' => 'female',
                'address' => 'Jl. Senopati No. 33, Jakarta Selatan',
                'skin_type' => 'combination',
                'skin_concerns' => ['pigmentation', 'aging'],
                'allergies' => 'Salicylic Acid',
            ],
            [
                'name' => 'Linda Kusuma',
                'phone' => '082398765432',
                'email' => 'linda.k@jagoflutter.com',
                'birthdate' => '1991-12-03',
                'gender' => 'female',
                'address' => 'Jl. Blok M No. 15, Jakarta Selatan',
                'skin_type' => 'dry',
                'skin_concerns' => ['dehydration', 'wrinkles'],
                'allergies' => null,
            ],
            [
                'name' => 'Yuni Astuti',
                'phone' => '081356789012',
                'email' => 'yuni.a@jagoflutter.com',
                'birthdate' => '1994-08-19',
                'gender' => 'female',
                'address' => 'Jl. Pondok Indah No. 22, Jakarta Selatan',
                'skin_type' => 'normal',
                'skin_concerns' => ['dull_skin'],
                'allergies' => null,
            ],
            [
                'name' => 'Dian Purnama',
                'phone' => '081445678901',
                'email' => 'dian.p@jagoflutter.com',
                'birthdate' => '1989-04-07',
                'gender' => 'female',
                'address' => 'Jl. Kelapa Gading No. 56, Jakarta Utara',
                'skin_type' => 'sensitive',
                'skin_concerns' => ['redness', 'sensitive'],
                'allergies' => 'Fragrance, Alcohol',
            ],
            [
                'name' => 'Budi Santoso',
                'phone' => '081567890123',
                'email' => 'budi.s@jagoflutter.com',
                'birthdate' => '1986-10-14',
                'gender' => 'male',
                'address' => 'Jl. PIK No. 77, Jakarta Utara',
                'skin_type' => 'oily',
                'skin_concerns' => ['acne', 'large_pores'],
                'allergies' => null,
            ],
            [
                'name' => 'Reza Pratama',
                'phone' => '081678901234',
                'email' => 'reza.p@jagoflutter.com',
                'birthdate' => '1990-02-28',
                'gender' => 'male',
                'address' => 'Jl. Menteng No. 44, Jakarta Pusat',
                'skin_type' => 'combination',
                'skin_concerns' => ['blackheads', 'oily'],
                'allergies' => null,
            ],
        ];

        foreach ($customers as $customer) {
            Customer::create($customer);
        }
    }
}
