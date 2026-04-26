<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * @var array<int, string>
     */
    private array $allowedModules = [
        'dashboard',
        'appointments',
        'customers',
        'transactions',
    ];

    /**
     * Run the migrations.
     */
    public function up(): void
    {
        if (Schema::hasTable('module_permission_defaults')) {
            DB::table('module_permission_defaults')
                ->where('role', 'admin')
                ->whereNotIn('module_key', $this->allowedModules)
                ->update([
                    'is_allowed' => false,
                    'updated_at' => now(),
                ]);
        }

        if (Schema::hasTable('outlet_role_module_permissions')) {
            DB::table('outlet_role_module_permissions')
                ->where('role', 'admin')
                ->whereNotIn('module_key', $this->allowedModules)
                ->update([
                    'is_allowed' => false,
                    'updated_at' => now(),
                ]);
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Irreversible data backfill.
    }
};
