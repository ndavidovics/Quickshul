<?php

namespace App\Models;

use App\Enums\EmailSendStatus;
use App\Traits\HasTenant;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class EmailSend extends Model
{
    use HasFactory, HasTenant;

    protected $fillable = [
        'tenant_id',
        'template_id',
        'family_id',
        'recipient_email',
        'subject',
        'body',
        'status',
        'sent_at',
        'error',
    ];

    protected function casts(): array
    {
        return [
            'status'  => EmailSendStatus::class,
            'sent_at' => 'datetime',
        ];
    }

    public function template(): BelongsTo
    {
        return $this->belongsTo(EmailTemplate::class, 'template_id');
    }

    public function family(): BelongsTo
    {
        return $this->belongsTo(Family::class);
    }
}
