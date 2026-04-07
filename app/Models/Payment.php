<?php

namespace App\Models;

use App\Enums\PaymentStatus;
use App\Traits\HasTenant;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class Payment extends Model
{
    use HasFactory, SoftDeletes, HasTenant;

    protected $fillable = [
        'tenant_id',
        'family_id',
        'pledge_id',
        'amount',
        'payment_date',
        'method',
        'description',
        'qb_transaction_id',
        'qb_sales_receipt_id',
        'paypal_transaction_id',
        'paypal_order_id',
        'status',
        'notes',
    ];

    protected function casts(): array
    {
        return [
            'amount'       => 'decimal:2',
            'payment_date' => 'date',
            'status'       => PaymentStatus::class,
        ];
    }

    public function family(): BelongsTo
    {
        return $this->belongsTo(Family::class);
    }

    public function scopeCompleted($query)
    {
        return $query->where('status', PaymentStatus::Completed);
    }
}
