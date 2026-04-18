<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // Legacy placeholder migration: intentionally no-op.
        // Keep this migration to maintain ordering/history in existing databases.
        if (! Schema::hasTable('users_role_enum')) {
            return;
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (! Schema::hasTable('users_role_enum')) {
            return;
        }
    }
};
