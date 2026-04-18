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
        Schema::create('outlet_role_module_permissions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tenant_id')->constrained('tenants')->cascadeOnDelete();
            $table->foreignId('outlet_id')->constrained('outlets')->cascadeOnDelete();
            $table->string('role', 32);
            $table->string('module_key', 64);
            $table->boolean('is_allowed')->default(true);
            $table->timestamps();

            $table->unique(['outlet_id', 'role', 'module_key'], 'outlet_role_module_permissions_unique');
            $table->index(['tenant_id', 'outlet_id'], 'outlet_role_module_permissions_tenant_outlet_index');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('outlet_role_module_permissions');
    }
};
