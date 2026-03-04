<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class QbConflict extends Model
{
    use HasFactory;

    protected $fillable = [
        'entity_type',
        'entity_id',
        'field',
        'portal_value',
        'portal_updated_at',
        'qb_value',
        'qb_updated_at',
        'resolved',
        'resolved_by_user_id',
        'resolved_at',
        'resolution',
    ];

    protected function casts(): array
    {
        return [
            'resolved'          => 'boolean',
            'portal_updated_at' => 'datetime',
            'qb_updated_at'     => 'datetime',
            'resolved_at'       => 'datetime',
        ];
    }

    public function resolvedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'resolved_by_user_id');
    }

    public function scopeUnresolved($query)
    {
        return $query->where('resolved', false);
    }

    public function scopeForEntity($query, string $type, int $id)
    {
        return $query->where('entity_type', $type)->where('entity_id', $id);
    }

    public function getEntityModelAttribute(): ?Model
    {
        return match($this->entity_type) {
            'family'  => Family::find($this->entity_id),
            'payment' => Payment::find($this->entity_id),
            default   => null,
        };
    }
}
