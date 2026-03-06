<?php

namespace App\Models;

use App\Services\HebrewDateService;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Yahrtzeit extends Model
{
    protected $fillable = [
        'family_id',
        'family_member_id',
        'relationship',
        'full_name',
        'hebrew_name',
        'date_of_death',
        'hebrew_date_of_death',
        'hebrew_dod_override',
        'hebrew_month',
        'hebrew_day',
        'notes',
    ];

    protected function casts(): array
    {
        return [
            'date_of_death'       => 'date',
            'hebrew_dod_override' => 'boolean',
        ];
    }

    protected static function boot()
    {
        parent::boot();

        static::saving(function ($model) {
            // Auto-calculate hebrew fields from Gregorian date if not overridden
            if ($model->date_of_death && !$model->hebrew_dod_override) {
                $svc = app(HebrewDateService::class);
                $h   = $svc->gregorianToHebrew($model->date_of_death);
                $model->hebrew_date_of_death = "{$h['day']} {$h['month_name']} {$h['year']}";
                $model->hebrew_month         = $h['month'];
                $model->hebrew_day           = $h['day'];
            }
        });
    }

    public function family(): BelongsTo
    {
        return $this->belongsTo(Family::class);
    }

    public function familyMember(): BelongsTo
    {
        return $this->belongsTo(FamilyMember::class);
    }

    public function getRelationshipLabelAttribute(): ?string
    {
        return $this->relationship ? ucfirst($this->relationship) : null;
    }
}
