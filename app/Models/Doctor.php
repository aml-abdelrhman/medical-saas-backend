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
        'slug', // 👈 أضف هذا السطر هنا لحل المشكلة
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
     * Accessor لجعل رابط الصورة يأتي كاملاً وجاهزاً للاستخدام مباشرة
     */
    protected function image(): Attribute
    {
        return Attribute::make(
            get: fn ($value) => $value ? asset('storage/' . $value) : null
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

    public function specialty() {
        return $this->belongsTo(Specialty::class);
    }

    public function services() {
        return $this->hasMany(Service::class);
    }
     
    public function availabilities() {
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