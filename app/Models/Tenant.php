<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\Crypt;

class Tenant extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'slug', 'name', 'tagline', 'logo_url', 'primary_color',
        'org_address', 'org_city', 'org_state', 'org_zip', 'org_phone', 'org_email',
        'timezone', 'locale',
        'gmail_access_token', 'gmail_refresh_token', 'gmail_token_expires_at', 'gmail_email',
        'paypal_client_id', 'paypal_secret', 'paypal_mode', 'paypal_webhook_id',
        'qb_enabled', 'onboarding_step', 'status',
    ];

    protected $hidden = ['gmail_access_token', 'gmail_refresh_token', 'paypal_secret'];

    protected $casts = [
        'gmail_token_expires_at' => 'datetime',
        'qb_enabled' => 'boolean',
    ];

    // Encrypt sensitive credentials at rest
    protected function gmailAccessToken(): Attribute
    {
        return Attribute::make(
            get: fn($v) => $v ? $this->tryDecrypt($v) : null,
            set: fn($v) => $v ? Crypt::encryptString($v) : null,
        );
    }

    protected function gmailRefreshToken(): Attribute
    {
        return Attribute::make(
            get: fn($v) => $v ? $this->tryDecrypt($v) : null,
            set: fn($v) => $v ? Crypt::encryptString($v) : null,
        );
    }

    protected function paypalSecret(): Attribute
    {
        return Attribute::make(
            get: fn($v) => $v ? $this->tryDecrypt($v) : null,
            set: fn($v) => $v ? Crypt::encryptString($v) : null,
        );
    }

    // Safe decrypt — returns raw value if not yet encrypted (migration period)
    private function tryDecrypt(?string $value): ?string
    {
        if (!$value) return null;
        try {
            return Crypt::decryptString($value);
        } catch (\Throwable) {
            return $value; // already plaintext (pre-migration row)
        }
    }

    public function membershipTypes()
    {
        return $this->hasMany(MembershipType::class)->orderBy('sort_order');
    }

    public function families()
    {
        return $this->hasMany(Family::class);
    }

    public function users()
    {
        return $this->hasMany(User::class);
    }

    // The full portal URL for this tenant
    public function portalUrl(): string
    {
        return 'https://' . $this->slug . '.' . config('app.root_domain', 'quickshul.com');
    }

    public function isGmailConnected(): bool
    {
        return !empty($this->gmail_access_token);
    }

    public function isPayPalConnected(): bool
    {
        return !empty($this->paypal_client_id) && !empty($this->paypal_secret);
    }

    // Seed default membership types for a new tenant
    public function seedDefaultMembershipTypes(): void
    {
        $defaults = [
            ['slug' => 'full_family',     'label' => 'Full Family',      'is_donor' => false, 'sort_order' => 1],
            ['slug' => 'single',          'label' => 'Single Member',     'is_donor' => false, 'sort_order' => 2],
            ['slug' => 'associate',       'label' => 'Associate',         'is_donor' => false, 'sort_order' => 3],
            ['slug' => 'first_year_free', 'label' => 'First Year Free',   'is_donor' => false, 'sort_order' => 4],
            ['slug' => 'young_couple',    'label' => 'Young Couple',      'is_donor' => false, 'sort_order' => 5],
            ['slug' => 'senior',          'label' => 'Senior',            'is_donor' => false, 'sort_order' => 6],
            ['slug' => 'donor',           'label' => 'Donor',             'is_donor' => true,  'sort_order' => 7],
        ];

        foreach ($defaults as $type) {
            $this->membershipTypes()->create($type);
        }
    }
}
