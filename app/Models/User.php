<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;

class User extends Authenticatable
{
    use HasApiTokens, HasFactory, Notifiable;

    protected $fillable = [
        'clinic_id',
        'name',
        'email',
        'phone',
        'password',
        'role',
        'avatar',
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected $casts = [
        'email_verified_at' => 'datetime',
        'password' => 'hashed',
    ];

    public function isSuperAdmin()
    {
        return $this->role === 'super_admin';
    }

    public function isClinicAdmin()
    {
        return $this->role === 'admin';
    }

    public function isDoctor()
    {
        return $this->role === 'doctor';
    }

    public function isPatient()
    {
        return $this->role === 'patient';
    }

    public function clinic()
    {
        return $this->belongsTo(Clinic::class, 'clinic_id');
    }

    public function doctor() 
    {
        return $this->hasOne(Doctor::class, 'user_id');
    }

    public function appointments()
    {
        return $this->hasMany(Appointment::class, 'patient_id');
    }

    public function favorites()
    {
        return $this->hasMany(Favorite::class, 'patient_id');
    }

    public function favoriteDoctors()
    {
        return $this->belongsToMany(Doctor::class, 'favorites', 'patient_id', 'doctor_id');
    }

    
}