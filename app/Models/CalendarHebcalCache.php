<?php

namespace App\Models;

use App\Traits\HasTenant;
use Illuminate\Database\Eloquent\Model;

class CalendarHebcalCache extends Model
{
    use HasTenant;

    protected $table = 'calendar_hebcal_cache';

    protected $fillable = ['tenant_id', 'year', 'data', 'fetched_at'];

    protected $casts = [
        'fetched_at' => 'datetime',
    ];
}
