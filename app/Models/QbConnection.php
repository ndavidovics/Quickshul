<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class QbConnection extends Model
{
    use HasFactory;

    protected $fillable = [
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
