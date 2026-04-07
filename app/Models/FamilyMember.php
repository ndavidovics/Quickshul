<?php

namespace App\Models;

use App\Traits\HasTenant;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use App\Services\HebrewDateService;

class FamilyMember extends Model
{
    use HasFactory, SoftDeletes, HasTenant;

    protected $fillable = [
        'tenant_id',
        'family_id',
        'first_name',
        'last_name',
        'hebrew_name',
        'gender',
        'role',
        'date_of_birth',
        'hebrew_date_of_birth',
        'hebrew_dob_override',
    ];

    protected function casts(): array
    {
        return [
            'date_of_birth'       => 'date',
            'hebrew_dob_override' => 'boolean',
        ];
    }

    protected static function boot()
    {
        parent::boot();

        static::saving(function ($model) {
            $hebrewDateService = app(HebrewDateService::class);

            if ($model->date_of_birth && !$model->hebrew_dob_override && !$model->hebrew_date_of_birth) {
                $h = $hebrewDateService->gregorianToHebrew($model->date_of_birth);
                $model->hebrew_date_of_birth = "{$h['day']} {$h['month_name']} {$h['year']}";
            }
        });
    }

    public function family(): BelongsTo
    {
        return $this->belongsTo(Family::class);
    }

    public function yahrtzeits(): BelongsToMany
    {
        return $this->belongsToMany(Yahrtzeit::class, 'family_member_yahrtzeit')
                    ->withTimestamps();
    }

    public function getFullNameAttribute(): string
    {
        return "{$this->first_name} {$this->last_name}";
    }
}
