<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class OutletInvoice extends Model
{
    /** @use HasFactory<\Database\Factories\OutletInvoiceFactory> */
    use HasFactory;

    protected $fillable = [
        'tenant_id',
        'plan_id',
        'billing_period',
        'outlet_count',
        'plan_price',
        'total_amount',
        'status',
        'type',
        'due_date',
        'paid_at',
        'notes',
        'payment_proof',
        'payment_proof_at',
        'payment_note',
        'approved_by',
        'approved_at',
        'rejected_at',
        'rejection_reason',
        'approve_email_token',
        'reject_email_token',
        'approve_email_used_at',
        'reject_email_used_at',
    ];

    protected function casts(): array
    {
        return [
            'outlet_count' => 'integer',
            'plan_price' => 'integer',
            'total_amount' => 'integer',
            'due_date' => 'datetime',
            'paid_at' => 'datetime',
            'payment_proof_at' => 'datetime',
            'approved_at' => 'datetime',
            'rejected_at' => 'datetime',
            'approve_email_used_at' => 'datetime',
            'reject_email_used_at' => 'datetime',
        ];
    }

    public function tenant(): BelongsTo
    {
        return $this->belongsTo(Tenant::class);
    }

    public function plan(): BelongsTo
    {
        return $this->belongsTo(Plan::class);
    }

    public function approvedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'approved_by');
    }

    public function isPaid(): bool
    {
        return $this->status === 'paid';
    }

    public function isOverdue(): bool
    {
        return $this->status === 'overdue'
            || ($this->status === 'pending' && $this->due_date?->isPast());
    }

    public function getFormattedTotalAttribute(): string
    {
        return 'Rp '.number_format($this->total_amount, 0, ',', '.');
    }

    public function getBillingPeriodLabelAttribute(): string
    {
        if (! $this->billing_period) {
            return '-';
        }

        return Carbon::createFromFormat('Y-m', $this->billing_period)->translatedFormat('F Y');
    }

    public function getStatusLabelAttribute(): string
    {
        return match ($this->status) {
            'pending' => 'Menunggu Pembayaran',
            'awaiting_verification' => 'Menunggu Verifikasi',
            'paid' => 'Lunas',
            'overdue' => 'Jatuh Tempo',
            'cancelled' => 'Dibatalkan',
            default => ucfirst($this->status),
        };
    }

    public function getStatusColorAttribute(): string
    {
        return match ($this->status) {
            'paid' => 'bg-green-100 text-green-700',
            'awaiting_verification' => 'bg-blue-100 text-blue-700',
            'pending' => 'bg-yellow-100 text-yellow-700',
            'overdue' => 'bg-red-100 text-red-700',
            'cancelled' => 'bg-gray-100 text-gray-700',
            default => 'bg-gray-100 text-gray-700',
        };
    }

    public function getPaymentProofUrlAttribute(): ?string
    {
        if (! $this->payment_proof) {
            return null;
        }

        return asset('storage/'.$this->payment_proof);
    }
}
