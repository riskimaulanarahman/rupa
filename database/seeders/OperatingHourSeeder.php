<?php

namespace Database\Seeders;

use App\Models\OperatingHour;
use Illuminate\Database\Seeder;

class OperatingHourSeeder extends Seeder
{
    public function run(): void
    {
        $hours = [
            ['day_of_week' => 0, 'is_closed' => true, 'open_time' => null, 'close_time' => null], // Sunday
            ['day_of_week' => 1, 'is_closed' => false, 'open_time' => '09:00', 'close_time' => '18:00'], // Monday
            ['day_of_week' => 2, 'is_closed' => false, 'open_time' => '09:00', 'close_time' => '18:00'], // Tuesday
            ['day_of_week' => 3, 'is_closed' => false, 'open_time' => '09:00', 'close_time' => '18:00'], // Wednesday
            ['day_of_week' => 4, 'is_closed' => false, 'open_time' => '09:00', 'close_time' => '18:00'], // Thursday
            ['day_of_week' => 5, 'is_closed' => false, 'open_time' => '09:00', 'close_time' => '18:00'], // Friday
            ['day_of_week' => 6, 'is_closed' => false, 'open_time' => '09:00', 'close_time' => '15:00'], // Saturday
        ];

        foreach ($hours as $hour) {
            OperatingHour::create($hour);
        }
    }
}
