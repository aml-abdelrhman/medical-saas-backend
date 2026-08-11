<?php

namespace App\Models;

use App\Traits\BelongsToClinic;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Review extends Model
{
    use HasFactory, BelongsToClinic;

    protected $fillable = [
        'clinic_id',
        'patient_id',
        'doctor_id',
        'appointment_id',
        'rating',
        'comment'
    ];

    protected $casts = [
        'comment' => 'array',
        'rating' => 'integer',
    ];

    public function clinic()
    {
        return $this->belongsTo(Clinic::class);
    }

    public function doctor()
    {
        return $this->belongsTo(Doctor::class);
    }

    public function patient()
    {
        return $this->belongsTo(User::class, 'patient_id');
    }
}