<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Plan extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'description',
        'price',
        'duration_in_days',
        'max_doctors',
        'max_patients',
        'is_active',
    ];

    protected $casts = [
        'name' => 'array',
        'description' => 'array',
        'is_active' => 'boolean',
        'price' => 'decimal:2',
    ];

    // علاقة الباقة باشتراكات العيادات المرتبطة بها
    public function subscriptions()
    {
        return $this->hasMany(ClinicSubscription::class);
    }
}