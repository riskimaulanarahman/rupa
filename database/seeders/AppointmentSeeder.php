<?php

namespace Database\Seeders;

use App\Models\Appointment;
use App\Models\Customer;
use App\Models\Service;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Database\Seeder;

class AppointmentSeeder extends Seeder
{
    public function run(): void
    {
        $customers = Customer::all();
        $services = Service::all();
        $beauticians = User::where('role', 'beautician')->get();

        if ($customers->isEmpty() || $services->isEmpty() || $beauticians->isEmpty()) {
            return;
        }

        // Create appointments for the past 10 days (more recent data for dashboard)
        for ($i = 10; $i >= 0; $i--) {
            $date = Carbon::today()->subDays($i);

            // Create 4-8 appointments per day (more data for charts)
            $appointmentsPerDay = rand(4, 8);

            for ($j = 0; $j < $appointmentsPerDay; $j++) {
                $customer = $customers->random();
                $service = $services->random();
                $beautician = $beauticians->random();

                // Random time between 9:00 and 17:00
                $hour = rand(9, 17);
                $minute = [0, 30][rand(0, 1)];
                $startTime = sprintf('%02d:%02d:00', $hour, $minute);
                $endTime = Carbon::parse($startTime)->addMinutes($service->duration_minutes)->format('H:i:s');

                // Past appointments are completed, today has mix of completed and upcoming
                if ($i > 0) {
                    // Past days: 90% completed
                    $status = rand(1, 10) <= 9 ? 'completed' : 'confirmed';
                } else {
                    // Today: earlier appointments completed, later ones pending/confirmed
                    if ($hour < 14) {
                        // Morning appointments already completed
                        $status = rand(1, 10) <= 8 ? 'completed' : 'confirmed';
                    } else {
                        // Afternoon appointments still pending/confirmed
                        $status = ['pending', 'confirmed'][rand(0, 1)];
                    }
                }

                Appointment::create([
                    'customer_id' => $customer->id,
                    'service_id' => $service->id,
                    'staff_id' => $beautician->id,
                    'appointment_date' => $date,
                    'start_time' => $startTime,
                    'end_time' => $endTime,
                    'status' => $status,
                    'notes' => rand(0, 4) === 0 ? 'Customer reguler' : null,
                    'created_at' => $date->copy()->setTime(rand(8, 12), rand(0, 59)),
                    'updated_at' => $date->copy()->setTime(rand(8, 12), rand(0, 59)),
                ]);
            }
        }

        // Create future appointments
        for ($i = 1; $i <= 5; $i++) {
            $date = Carbon::today()->addDays($i);
            $appointmentsPerDay = rand(2, 4);

            for ($j = 0; $j < $appointmentsPerDay; $j++) {
                $customer = $customers->random();
                $service = $services->random();
                $beautician = $beauticians->random();

                $hour = rand(9, 17);
                $minute = [0, 30][rand(0, 1)];
                $startTime = sprintf('%02d:%02d:00', $hour, $minute);
                $endTime = Carbon::parse($startTime)->addMinutes($service->duration_minutes)->format('H:i:s');

                Appointment::create([
                    'customer_id' => $customer->id,
                    'service_id' => $service->id,
                    'staff_id' => $beautician->id,
                    'appointment_date' => $date,
                    'start_time' => $startTime,
                    'end_time' => $endTime,
                    'status' => ['pending', 'confirmed'][rand(0, 1)],
                    'notes' => null,
                ]);
            }
        }
    }
}
