<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Database\Factories\UserFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable
{
    /** @use HasFactory<UserFactory> */
    use HasFactory, Notifiable;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'name',
        'email',
        'password',
        'currency',
        'locale',
        'savings_target_percentage',
        'expected_monthly_income',
        'dashboard_sections',
    ];

    /**
     * Default dashboard sections and their default ordering.
     */
    public const DEFAULT_DASHBOARD_SECTIONS = [
        ['id' => 'balance_card', 'enabled' => true],
        ['id' => 'daily_limit', 'enabled' => true],
        ['id' => 'top_categories', 'enabled' => true],
        ['id' => 'recent_transactions', 'enabled' => true],
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var list<string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

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
            'savings_target_percentage' => 'float',
            'expected_monthly_income' => 'float',
            'dashboard_sections' => 'array',
        ];
    }

    /**
     * Get dashboard sections for user with fallback to defaults.
     */
    public function getDashboardSections(): array
    {
        $default = self::DEFAULT_DASHBOARD_SECTIONS;
        $saved = $this->dashboard_sections;

        if (empty($saved) || !is_array($saved)) {
            return $default;
        }

        $savedIds = [];
        $result = [];
        foreach ($saved as $item) {
            if (isset($item['id'])) {
                $savedIds[] = $item['id'];
                $result[] = [
                    'id' => $item['id'],
                    'enabled' => !empty($item['enabled']),
                ];
            }
        }

        // Add any missing default sections
        foreach ($default as $d) {
            if (!in_array($d['id'], $savedIds, true)) {
                $result[] = $d;
            }
        }

        return $result;
    }

    public function accounts(): \Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->hasMany(Account::class);
    }

    public function categories(): \Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->hasMany(Category::class);
    }

    public function budgets(): \Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->hasMany(Budget::class);
    }

    public function transactions(): \Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->hasMany(Transaction::class);
    }
}

