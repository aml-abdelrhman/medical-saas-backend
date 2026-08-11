<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Casts\Attribute;

class Clinic extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'slug',
        'owner_name',
        'email',
        'phone',
        'password',
        'logo',
        'status',
        'subscription_ends_at',
    ];

    protected $casts = [
        'subscription_ends_at' => 'datetime',
    ];

    // الـ Accessor الصحيح لجلب الصور من الـ Storage مباشرة
    protected function logo(): Attribute
    {
        return Attribute::make(
            get: function ($value) {
                if (!$value) return null;
                if (str_starts_with($value, 'http')) {
                    return $value;
                }
                // تنظيف المسار لضمان عدم تكرار كلمة storage
                $cleanValue = str_replace(['storage/', 'storage'], '', $value);
                return asset('storage/' . ltrim($cleanValue, '/'));
            },
        );
    }
    
    public function subscription()
    {
        return $this->hasOne(ClinicSubscription::class)->latest();
    }

    public function doctors()
    {
        return $this->hasMany(Doctor::class);
    }

    public function specialties()
    {
        return $this->hasMany(Specialty::class);
    }
    
    public function services()
    {
        return $this->hasMany(Service::class);
    }

    public function reviews()
    {
        return $this->hasMany(ClinicReview::class);
    }

    public function getAverageRatingAttribute()
    {
        return $this->reviews()->avg('rating') ?? 0;
    }

    public function getReviewsCountAttribute()
    {
        return $this->reviews()->count();
    }


    public function contactMessages()
    {
        return $this->hasMany(ClinicContactMessage::class);
    }
    // علاقة مالك العيادة
public function owner()
{
    return $this->belongsTo(User::class, 'user_id'); 


}
public function users()
{
    return $this->hasMany(User::class, 'clinic_id');
}
}