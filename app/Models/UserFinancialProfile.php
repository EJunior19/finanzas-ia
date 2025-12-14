<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class UserFinancialProfile extends Model
{
    use HasFactory;

    protected $table = 'user_financial_profiles';

    protected $fillable = [
        'user_id',
        'onboarding_completed',
        'monthly_income',
        'salary_day',
        'fixed_expenses',
        'debts',
        'confidence',
    ];

    protected $casts = [
        'onboarding_completed' => 'boolean',
        'monthly_income'       => 'decimal:2',
        'salary_day'           => 'integer',
        'fixed_expenses'       => 'array',
        'debts'                => 'array',
        'confidence'           => 'integer',
    ];

    /**
     * Relación con User
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
