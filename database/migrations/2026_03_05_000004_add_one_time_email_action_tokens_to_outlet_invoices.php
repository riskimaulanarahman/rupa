<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('outlet_invoices', function (Blueprint $table) {
            $table->string('approve_email_token')->nullable()->after('type');
            $table->string('reject_email_token')->nullable()->after('approve_email_token');
            $table->timestamp('approve_email_used_at')->nullable()->after('reject_email_token');
            $table->timestamp('reject_email_used_at')->nullable()->after('approve_email_used_at');
        });
    }

    public function down(): void
    {
        Schema::table('outlet_invoices', function (Blueprint $table) {
            $table->dropColumn([
                'approve_email_token',
                'reject_email_token',
                'approve_email_used_at',
                'reject_email_used_at',
            ]);
        });
    }
};
