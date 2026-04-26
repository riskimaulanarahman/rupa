<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('transaction_items', function (Blueprint $table) {
            $table->string('item_type')->default('service')->change();
            $table->foreignId('staff_id')->nullable()->after('customer_package_id')->constrained('users')->nullOnDelete();
            $table->index('staff_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('transaction_items', function (Blueprint $table) {
            $table->dropIndex(['staff_id']);
            $table->dropConstrainedForeignId('staff_id');
            $table->enum('item_type', ['service', 'package', 'product', 'other'])->default('service')->change();
        });
    }
};
