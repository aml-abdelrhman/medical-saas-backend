<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Casts\Attribute;

class ClinicReview extends Model
{
    protected $table = 'clinic_reviews';

    protected $fillable = [
        'doctor_id',
        'clinic_id',
        'doctor_name',
        'clinic_name',
        'doctor_avatar',
        'rating',
        'comment',
        'is_approved',
    ];

    protected $casts = [
        'rating' => 'integer',
        'is_approved' => 'boolean',
    ];

    /**
     * Accessor لجعل رابط صورة الطبيب يأتي كاملاً وجاهزاً للاستخدام مباشرة
     */
    protected function doctorAvatar(): Attribute
    {
        return Attribute::make(
            get: fn ($value) => $value 
                ? (filter_var($value, FILTER_VALIDATE_URL) ? $value : asset('storage/' . $value)) 
                : null
        );
    }

    // ربط التقييم بالطبيب صاحب الرأي (إذا كان جدول المستخدمين Users يمثل الأطباء)
    public function doctor()
    {
        return $this->belongsTo(User::class, 'doctor_id');
    }

    // ربط اختياري بالعيادة إن وجد
    public function clinic()
    {
        return $this->belongsTo(Clinic::class);
    }
}