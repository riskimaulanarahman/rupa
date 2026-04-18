<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('outlet_invoices', function (Blueprint $table) {
            $table->enum('status', ['pending', 'awaiting_verification', 'paid', 'overdue', 'cancelled'])
                ->default('pending')
                ->change();

            $table->string('payment_proof')->nullable()->after('notes');
            $table->timestamp('payment_proof_at')->nullable()->after('payment_proof');
            $table->text('payment_note')->nullable()->after('payment_proof_at');
            $table->foreignId('approved_by')->nullable()->after('payment_note')->constrained('users')->nullOnDelete();
            $table->timestamp('approved_at')->nullable()->after('approved_by');
            $table->timestamp('rejected_at')->nullable()->after('approved_at');
            $table->text('rejection_reason')->nullable()->after('rejected_at');
            $table->enum('type', ['new_plan', 'renewal', 'upgrade'])->default('renewal')->after('rejection_reason');
        });
    }

    public function down(): void
    {
        Schema::table('outlet_invoices', function (Blueprint $table) {
            $table->enum('status', ['pending', 'paid', 'overdue', 'cancelled'])
                ->default('pending')
                ->change();

            $table->dropForeign(['approved_by']);
            $table->dropColumn([
                'payment_proof',
                'payment_proof_at',
                'payment_note',
                'approved_by',
                'approved_at',
                'rejected_at',
                'rejection_reason',
                'type',
            ]);
        });
    }
};
