<?php

namespace App\Models;

use App\Traits\BelongsToClinic;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Casts\Attribute;

class Doctor extends Model
{
    use HasFactory, BelongsToClinic;

    protected $fillable = [
        'clinic_id',
        'user_id', 
        'specialty_id', 
        'name', 
        'slug', 
        'bio', 
        'years_experience', 
        'rating', 
        'image', 
        'languages', 
        'price_from'
    ];

    protected $casts = [
        'languages' => 'array',
        'name'      => 'array',
        'bio'       => 'array',
    ];

    /**
     * الـ Accessor لجلب صورة الطبيب سواء كانت رابطاً سحابياً من Cloudinary أو مساراً محلياً
     */
    protected function image(): Attribute
    {
        return Attribute::make(
            get: function ($value) {
                if (!$value) {
                    return null;
                }
                if (str_starts_with($value, 'http')) {
                    return $value;
                }
                // تنظيف المسار لضمان عدم تكرار كلمة storage
                $cleanValue = str_replace(['storage/', 'storage'], '', $value);
                return asset('storage/' . ltrim($cleanValue, '/'));
            },
        );
    }
    
    public function clinic()
    {
        return $this->belongsTo(Clinic::class, 'clinic_id');
    }

    public function user()
    {
        return $this->belongsTo(User::class, 'user_id'); 
    }

    public function specialty() 
    {
        return $this->belongsTo(Specialty::class);
    }

    public function services() 
    {
        return $this->hasMany(Service::class);
    }
     
    public function availabilities() 
    {
        return $this->hasMany(DoctorAvailability::class);
    }

    public function appointments()
    {
        return $this->hasMany(Appointment::class);
    }

    public function favorites()
    {
        return $this->hasMany(Favorite::class);
    }
}