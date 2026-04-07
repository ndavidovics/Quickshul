<?php

namespace App\Models;

use App\Traits\HasTenant;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CalendarMinyan extends Model
{
    use HasFactory, HasTenant;

    protected $table = 'calendar_minyanim';

    protected $fillable = [
        'tenant_id',
        'name', 'type', 'sort_order',
        'sun', 'mon', 'tue', 'wed', 'thu', 'fri', 'sat',
        'active', 'notes',
        'time_rules',
    ];

    protected $casts = [
        'active'     => 'boolean',
        'time_rules' => 'array',
    ];

    /**
     * Get the configured time for a given day-of-week.
     * 0 = Sunday, 1 = Monday, ..., 6 = Saturday
     */
    public function getTimeForDay(int $dow): ?string
    {
        $map = [0 => 'sun', 1 => 'mon', 2 => 'tue', 3 => 'wed', 4 => 'thu', 5 => 'fri', 6 => 'sat'];
        $col = $map[$dow] ?? null;
        if (!$col) return null;
        return $this->{$col} ?: null;
    }

    /**
     * Get the time rule spec for a given day-of-week.
     * Returns time_rules[$dow_key] if set, otherwise falls back to static column value.
     */
    public function getTimeRuleForDay(int $dow): array
    {
        $map = [0 => 'sun', 1 => 'mon', 2 => 'tue', 3 => 'wed', 4 => 'thu', 5 => 'fri', 6 => 'sat'];
        $key = $map[$dow] ?? null;

        if (!$key) {
            return ['type' => 'static', 'time' => null];
        }

        // Check time_rules first
        $rules = $this->time_rules;
        if (is_array($rules) && isset($rules[$key]) && is_array($rules[$key])) {
            return $rules[$key];
        }

        // Fall back to static column
        $time = $this->{$key} ?: null;
        return ['type' => 'static', 'time' => $time];
    }

    /**
     * Exceptions relationship.
     */
    public function exceptions()
    {
        return $this->hasMany(CalendarMinyanException::class, 'minyan_id');
    }
}
