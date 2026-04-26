<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('operating_hours', function (Blueprint $table) {
            $table->dropUnique('operating_hours_day_of_week_unique');
            $table->unique(
                ['tenant_id', 'outlet_id', 'day_of_week'],
                'operating_hours_tenant_outlet_day_unique'
            );
        });
    }

    public function down(): void
    {
        Schema::table('operating_hours', function (Blueprint $table) {
            $table->dropUnique('operating_hours_tenant_outlet_day_unique');
            $table->unique('day_of_week');
        });
    }
};
