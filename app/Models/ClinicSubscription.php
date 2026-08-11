<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ClinicSubscription extends Model
{
    use HasFactory;

    protected $fillable = [
        'clinic_id',
        'plan_id',
        'starts_at',
        'ends_at',
        'status',
    ];

    protected $casts = [
        'starts_at' => 'date',
        'ends_at' => 'date',
    ];

    // علاقة الاشتراك بالعيادة
    public function clinic()
    {
        return $this->belongsTo(Clinic::class);
    }

    // علاقة الاشتراك بالباقة
    public function plan()
    {
        return $this->belongsTo(Plan::class);
    }

    // التحقق مما إذا كان الاشتراك نشطاً وصالحاً حتى اللحظة
    public function isActive()
    {
        return $this->status === 'active' && now()->lte($this->ends_at);
    }
}