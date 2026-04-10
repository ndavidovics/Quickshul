<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Crypt;

class PlatformSetting extends Model
{
    protected $primaryKey = 'key';
    public $incrementing  = false;
    protected $keyType    = 'string';

    protected $fillable = ['key', 'value'];

    private static array $encrypted = [
        'platform_gmail_access_token',
        'platform_gmail_refresh_token',
    ];

    public static function get(string $key, mixed $default = null): mixed
    {
        $row = static::find($key);
        if (!$row) return $default;

        $value = $row->value;
        if (in_array($key, self::$encrypted) && $value) {
            try { $value = Crypt::decryptString($value); } catch (\Throwable) {}
        }
        return $value;
    }

    public static function set(string $key, mixed $value): void
    {
        if (in_array($key, self::$encrypted) && $value) {
            $value = Crypt::encryptString($value);
        }
        static::updateOrCreate(['key' => $key], ['value' => $value]);
    }
}
