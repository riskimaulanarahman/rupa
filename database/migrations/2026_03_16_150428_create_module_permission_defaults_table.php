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
        Schema::create('module_permission_defaults', function (Blueprint $table) {
            $table->id();
            $table->string('role', 32);
            $table->string('module_key', 64);
            $table->boolean('is_allowed')->default(true);
            $table->timestamps();

            $table->unique(['role', 'module_key'], 'module_permission_defaults_role_module_unique');
        });

        $now = now();
        $roles = ['owner', 'admin', 'beautician'];
        $modules = [
            'dashboard',
            'appointments',
            'customers',
            'treatment_records',
            'service_categories',
            'services',
            'products',
            'packages',
            'customer_packages',
            'transactions',
            'loyalty',
            'reports',
            'outlets',
            'import_data',
            'staff',
            'settings',
            'billing',
        ];

        $records = [];
        foreach ($roles as $role) {
            foreach ($modules as $module) {
                $isAllowed = match ($module) {
                    'dashboard', 'reports' => in_array($role, ['owner', 'admin'], true),
                    'settings', 'import_data' => in_array($role, ['owner', 'admin'], true),
                    'staff', 'outlets', 'billing' => $role === 'owner',
                    default => in_array($role, ['owner', 'admin', 'beautician'], true),
                };

                $records[] = [
                    'role' => $role,
                    'module_key' => $module,
                    'is_allowed' => $isAllowed,
                    'created_at' => $now,
                    'updated_at' => $now,
                ];
            }
        }

        DB::table('module_permission_defaults')->insert($records);
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('module_permission_defaults');
    }
};
