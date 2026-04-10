<?php

namespace App\Models;

use App\Traits\HasTenant;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Facades\Crypt;

class QbConnection extends Model
{
    use HasFactory, HasTenant;

    protected $fillable = [
        'tenant_id',
        'realm_id',
        'access_token',
        'refresh_token',
        'access_token_expires_at',
        'refresh_token_expires_at',
        'connected_by_user_id',
    ];

    protected $hidden = [
        'access_token',
        'refresh_token',
    ];

    protected function casts(): array
    {
        return [
            'access_token_expires_at'  => 'datetime',
            'refresh_token_expires_at' => 'datetime',
        ];
    }

    protected function accessToken(): Attribute
    {
        return Attribute::make(
            get: fn($v) => $v ? $this->tryDecrypt($v) : null,
            set: fn($v) => $v ? Crypt::encryptString($v) : null,
        );
    }

    protected function refreshToken(): Attribute
    {
        return Attribute::make(
            get: fn($v) => $v ? $this->tryDecrypt($v) : null,
            set: fn($v) => $v ? Crypt::encryptString($v) : null,
        );
    }

    private function tryDecrypt(?string $value): ?string
    {
        if (!$value) return null;
        try {
            return Crypt::decryptString($value);
        } catch (\Throwable) {
            return $value;
        }
    }

    public function connectedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'connected_by_user_id');
    }

    public function isAccessTokenExpired(): bool
    {
        return now()->gte($this->access_token_expires_at->subMinutes(5));
    }

    public function isRefreshTokenExpired(): bool
    {
        return now()->gte($this->refresh_token_expires_at);
    }
}
