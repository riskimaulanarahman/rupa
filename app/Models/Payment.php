<?php

namespace App\Models;

use App\Traits\BelongsToOutlet;
use App\Traits\BelongsToTenant;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Payment extends Model
{
    use BelongsToOutlet, BelongsToTenant;

    protected $fillable = [
        'tenant_id',
        'outlet_id',
        'transaction_id',
        'received_by',
        'payment_method',
        'amount',
        'reference_number',
        'notes',
        'paid_at',
    ];

    protected function casts(): array
    {
        return [
            'amount' => 'decimal:2',
            'paid_at' => 'datetime',
        ];
    }

    public function transaction(): BelongsTo
    {
        return $this->belongsTo(Transaction::class);
    }

    public function receiver(): BelongsTo
    {
        return $this->belongsTo(User::class, 'received_by');
    }

    public function getPaymentMethodLabelAttribute(): string
    {
        return Transaction::PAYMENT_METHODS[$this->payment_method] ?? $this->payment_method;
    }

    public function getFormattedAmountAttribute(): string
    {
        return 'Rp '.number_format($this->amount, 0, ',', '.');
    }
}
