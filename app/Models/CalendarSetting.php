<?php

namespace App\Models;

use App\Traits\HasTenant;
use Illuminate\Database\Eloquent\Model;

class CalendarSetting extends Model
{
    use HasTenant;

    protected $table = 'calendar_settings';

    protected $fillable = ['tenant_id', 'key', 'value'];

    public static function get(string $key, $default = null)
    {
        $record = static::where('key', $key)->first();
        return $record ? $record->value : $default;
    }

    public static function set(string $key, $value): void
    {
        static::updateOrCreate(['key' => $key], ['value' => $value]);
    }
}
