<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('appointments', function (Blueprint $table) {
            $table->decimal('completed_incentive', 12, 2)->nullable()->after('status');
        });

        DB::table('appointments')
            ->where('status', 'completed')
            ->whereNull('completed_incentive')
            ->select('id', 'service_id')
            ->orderBy('id')
            ->chunkById(100, function ($appointments): void {
                $serviceIncentives = DB::table('services')
                    ->whereIn('id', $appointments->pluck('service_id')->filter()->unique()->values())
                    ->pluck('incentive', 'id');

                foreach ($appointments as $appointment) {
                    DB::table('appointments')
                        ->where('id', $appointment->id)
                        ->update([
                            'completed_incentive' => $serviceIncentives[$appointment->service_id] ?? 0,
                        ]);
                }
            });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('appointments', function (Blueprint $table) {
            $table->dropColumn('completed_incentive');
        });
    }
};
