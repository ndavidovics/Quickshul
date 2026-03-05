<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PaymentToken extends Model
{
    protected $fillable = ['token', 'family_id', 'expires_at'];

    protected function casts(): array
    {
        return ['expires_at' => 'datetime'];
    }

    public function family(): BelongsTo
    {
        return $this->belongsTo(Family::class);
    }

    public function isValid(): bool
    {
        return now()->lt($this->expires_at);
    }

    public static function generateFor(Family $family): self
    {
        // Regenerate: delete old tokens for this family and issue a fresh one
        self::where('family_id', $family->id)->delete();

        return self::create([
            'token'     => bin2hex(random_bytes(32)),
            'family_id' => $family->id,
            'expires_at'=> now()->addDays(30),
        ]);
    }
}
