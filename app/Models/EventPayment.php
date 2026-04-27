<?php

namespace App\Models;

use App\Traits\HasTenant;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class EventPayment extends Model
{
    use HasTenant;

    protected $fillable = [
        'tenant_id',
        'event_id',
        'family_id',
        'payer_name',
        'payer_email',
        'ticket_quantities',
        'subtotal',
        'total_amount',
        'paypal_order_id',
        'paypal_transaction_id',
        'qb_sales_receipt_id',
        'status',
    ];

    protected $casts = [
        'ticket_quantities' => 'array',
        'subtotal'          => 'decimal:2',
        'total_amount'      => 'decimal:2',
    ];

    public function event(): BelongsTo
    {
        return $this->belongsTo(Event::class);
    }

    public function family(): BelongsTo
    {
        return $this->belongsTo(Family::class);
    }
}
