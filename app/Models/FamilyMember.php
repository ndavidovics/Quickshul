<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;
use App\Services\HebrewDateService;

class FamilyMember extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'family_id',
        'first_name',
        'last_name',
        'hebrew_name',
        'gender',
        'role',
        'date_of_birth',
        'hebrew_date_of_birth',
        'hebrew_dob_override',
        'date_of_death',
        'hebrew_date_of_death',
        'hebrew_dod_override',
    ];

    protected function casts(): array
    {
        return [
            'date_of_birth'       => 'date',
            'date_of_death'       => 'date',
            'hebrew_dob_override' => 'boolean',
            'hebrew_dod_override' => 'boolean',
        ];
    }

    protected static function boot()
    {
        parent::boot();

        // Auto-generate Hebrew dates from Gregorian dates if not already set
        static::saving(function($model) {
            $hebrewDateService = app(HebrewDateService::class);

            // Auto-generate hebrew_date_of_birth if gregorian date is set but hebrew is not
            if ($model->date_of_birth && !$model->hebrew_dob_override && !$model->hebrew_date_of_birth) {
                $h = $hebrewDateService->gregorianToHebrew($model->date_of_birth);
                $model->hebrew_date_of_birth = "{$h['day']} {$h['month_name']} {$h['year']}";
            }

            // Auto-generate hebrew_date_of_death if gregorian date is set but hebrew is not
            if ($model->date_of_death && !$model->hebrew_dod_override && !$model->hebrew_date_of_death) {
                $h = $hebrewDateService->gregorianToHebrew($model->date_of_death);
                $model->hebrew_date_of_death = "{$h['day']} {$h['month_name']} {$h['year']}";
            }
        });
    }

    public function family(): BelongsTo
    {
        return $this->belongsTo(Family::class);
    }

    public function scopeLiving($query)
    {
        return $query->whereNull('date_of_death');
    }

    public function getFullNameAttribute(): string
    {
        return "{$this->first_name} {$this->last_name}";
    }

    public function isDeceased(): bool
    {
        return $this->date_of_death !== null;
    }
}
