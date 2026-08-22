<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Database\Factories\UserFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Hidden;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;
use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Relations\HasMany;

#[Fillable([
    'name',
    'email',
    'password',
    'phone',
    'blood_type',
    'latitude',
    'longitude',
    'address_text',
    'email_verified_at',
    'last_donation_at',
    'no_of_donations',
    'is_available',
    'fcm_token',
    'eligibility_notified_at',
    'weight',
    'date_of_birth',
    'gender',
    'hemoglobin',
])]
#[Hidden(['password', 'remember_token'])]
class User extends Authenticatable implements MustVerifyEmail
{
    /** @use HasFactory<UserFactory> */
    use HasFactory, Notifiable, HasApiTokens;

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
            'last_donation_at' => 'datetime',
            'eligibility_notified_at' => 'datetime',
            'date_of_birth' => 'date',
            'weight' => 'decimal:2',
            'hemoglobin' => 'decimal:1',
        ];
    }

    public function compatibleBloodTypes(): array
    {
        return match ($this->blood_type) {
            'O-' => ['O-', 'O+', 'A-', 'A+', 'B-', 'B+', 'AB-', 'AB+'],
            'O+' => ['O+', 'A+', 'B+', 'AB+'],
            'A-' => ['A-', 'A+', 'AB-', 'AB+'],
            'A+' => ['A+', 'AB+'],
            'B-' => ['B-', 'B+', 'AB-', 'AB+'],
            'B+' => ['B+', 'AB+'],
            'AB-' => ['AB-', 'AB+'],
            'AB+' => ['AB+'],
            default => [],
        };
    }


    public function notifications(): HasMany
    {
        return $this->hasMany(Notification::class);
    }

    public function responses(): HasMany
    {
        return $this->hasMany(Response::class);
    }

    public function donations(): HasMany
    {
        return $this->hasMany(Donation::class, 'donor_id');
    }

    public function canDonate(): bool
    {
        return $this->last_donation_at === null
            || $this->last_donation_at->lte(now()->subMonths(3));
    }
}
