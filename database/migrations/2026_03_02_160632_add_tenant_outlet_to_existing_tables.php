<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Tables that belong to both a tenant and an outlet.
     */
    private array $outletTables = [
        'users',
        'customers',
        'appointments',
        'treatment_records',
        'packages',
        'customer_packages',
        'package_usages',
        'transactions',
        'transaction_items',
        'payments',
        'services',
        'service_categories',
        'products',
        'product_categories',
        'operating_hours',
        'settings',
        'loyalty_points',
        'loyalty_rewards',
        'loyalty_redemptions',
        'referral_logs',
        'import_logs',
    ];

    public function up(): void
    {
        foreach ($this->outletTables as $tableName) {
            Schema::table($tableName, function (Blueprint $table) {
                // Add tenant_id (nullable initially for existing data)
                $table->foreignId('tenant_id')
                    ->nullable()
                    ->after('id')
                    ->constrained('tenants')
                    ->nullOnDelete();

                // Add outlet_id (nullable initially for existing data)
                $table->foreignId('outlet_id')
                    ->nullable()
                    ->after('tenant_id')
                    ->constrained('outlets')
                    ->nullOnDelete();

                // Add index for performance
                $table->index(['tenant_id', 'outlet_id']);
            });
        }
    }

    public function down(): void
    {
        foreach ($this->outletTables as $tableName) {
            Schema::table($tableName, function (Blueprint $table) use ($tableName) {
                $table->dropIndex([$tableName.'_tenant_id_outlet_id_index']);
                $table->dropForeign(['tenant_id']);
                $table->dropForeign(['outlet_id']);
                $table->dropColumn(['tenant_id', 'outlet_id']);
            });
        }
    }
};
