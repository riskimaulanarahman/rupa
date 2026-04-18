<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('outlet_invoices')) {
            return;
        }

        $duplicates = DB::table('outlet_invoices')
            ->select('tenant_id', 'billing_period', DB::raw('COUNT(*) as duplicate_count'))
            ->groupBy('tenant_id', 'billing_period')
            ->having('duplicate_count', '>', 1)
            ->get();

        foreach ($duplicates as $duplicate) {
            $ids = DB::table('outlet_invoices')
                ->where('tenant_id', $duplicate->tenant_id)
                ->where('billing_period', $duplicate->billing_period)
                ->orderByDesc('created_at')
                ->orderByDesc('id')
                ->pluck('id');

            $idToKeep = $ids->shift();
            if ($idToKeep === null) {
                continue;
            }

            DB::table('outlet_invoices')
                ->whereIn('id', $ids->all())
                ->delete();
        }

        Schema::table('outlet_invoices', function (Blueprint $table) {
            $table->unique(['tenant_id', 'billing_period'], 'outlet_invoices_tenant_period_unique');
        });
    }

    public function down(): void
    {
        if (! Schema::hasTable('outlet_invoices')) {
            return;
        }

        Schema::table('outlet_invoices', function (Blueprint $table) {
            $table->dropUnique('outlet_invoices_tenant_period_unique');
        });
    }
};
