<?php

namespace App\Models;

use App\Traits\BelongsToClinic;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Casts\Attribute;

class Service extends Model
{
    use HasFactory, BelongsToClinic;

    protected $fillable = [
        'clinic_id', 
        'doctor_id', 
        'name', 
        'description',
        'price', 
        'duration_minutes', 
        'image', 
        'is_active'
    ]; 
    
    protected $casts = [
        'name' => 'array',
        'description' => 'array',
        'is_active' => 'boolean'
    ];

    protected function image(): Attribute
    {
        return Attribute::make(
            get: fn ($value) => $value ? (str_starts_with($value, 'http') ? $value : asset('storage/' . $value)) : null,
        );
    }

    public function clinic()
    {
        return $this->belongsTo(Clinic::class);
    }


    public function doctor()
    {
        return $this->belongsTo(Doctor::class);
    }

    public function appointments()
    {
        return $this->hasMany(Appointment::class);
    }
}