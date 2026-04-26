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
        Schema::table('services', function (Blueprint $table) {
            $table->string('pricing_mode')->default('fixed')->after('duration_minutes');
            $table->decimal('price_min', 12, 2)->nullable()->after('price');
            $table->decimal('price_max', 12, 2)->nullable()->after('price_min');
        });

        DB::table('services')->update([
            'pricing_mode' => 'fixed',
        ]);

        DB::statement('UPDATE services SET price_min = price, price_max = price');
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('services', function (Blueprint $table) {
            $table->dropColumn(['pricing_mode', 'price_min', 'price_max']);
        });
    }
};
