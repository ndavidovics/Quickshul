<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class FamilyEmail extends Model
{
    use HasFactory;

    protected $fillable = [
        'family_id',
        'email',
        'is_primary',
    ];

    protected function casts(): array
    {
        return [
            'is_primary' => 'boolean',
        ];
    }

    public function family(): BelongsTo
    {
        return $this->belongsTo(Family::class);
    }
}
