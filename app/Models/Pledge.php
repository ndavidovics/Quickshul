<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Pledge extends Model
{
    protected $fillable = [
        'family_id',
        'qb_invoice_id',
        'description',
        'amount',
        'balance',
        'invoice_date',
        'due_date',
        'status',
    ];

    protected function casts(): array
    {
        return [
            'invoice_date' => 'date',
            'due_date'     => 'date',
            'amount'       => 'decimal:2',
            'balance'      => 'decimal:2',
        ];
    }

    public function family(): BelongsTo
    {
        return $this->belongsTo(Family::class);
    }

    public function isPaid(): bool
    {
        return $this->status === 'paid' || (float)$this->balance === 0.0;
    }
}
