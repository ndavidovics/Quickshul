<?php

namespace App\Models;

use App\Services\HebrewDateService;
use App\Traits\HasTenant;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class Yahrtzeit extends Model
{
    use HasTenant;

    protected $fillable = [
        'tenant_id',
        'relationship',
        'full_name',
        'hebrew_name',
        'date_of_death',
        'hebrew_date_of_death',
        'hebrew_dod_override',
        'hebrew_month',
        'hebrew_day',
        'notes',
        'display',
        'pin_to_end',
    ];

    protected function casts(): array
    {
        return [
            'date_of_death'       => 'date',
            'hebrew_dod_override' => 'boolean',
            'display'             => 'boolean',
            'pin_to_end'          => 'boolean',
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
                $model->hebrew_date_of_death = $h['formatted'];
                $model->hebrew_month         = $h['month'];
                $model->hebrew_day           = $h['day'];
            }
        });
    }

    public function families(): BelongsToMany
    {
        return $this->belongsToMany(Family::class, 'family_yahrtzeit')
                    ->withTimestamps()
                    ->orderBy('name');
    }

    public function familyMembers(): BelongsToMany
    {
        return $this->belongsToMany(FamilyMember::class, 'family_member_yahrtzeit')
                    ->withTimestamps();
    }

    public function getRelationshipLabelAttribute(): ?string
    {
        return $this->relationship ? ucfirst($this->relationship) : null;
    }
}
