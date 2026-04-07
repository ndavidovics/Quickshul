<?php

namespace App\Models;

use App\Traits\HasTenant;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CalendarMinyanException extends Model
{
    use HasFactory, HasTenant;

    protected $table = 'calendar_minyan_exceptions';

    protected $fillable = [
        'tenant_id',
        'minyan_id',
        'event_type',
        'day_type',
        'override_type',
        'override_value',
        'priority',
        'notes',
    ];

    protected $casts = [
        'override_value' => 'array',
        'priority'       => 'integer',
    ];

    public function minyan()
    {
        return $this->belongsTo(CalendarMinyan::class, 'minyan_id');
    }
}
