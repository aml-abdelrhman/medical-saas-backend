<?php

namespace App\Models;

use App\Traits\BelongsToClinic;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class DoctorAvailability extends Model
{
    use HasFactory, BelongsToClinic;

    protected $table = 'doctor_availabilities';

    protected $fillable = [
        'clinic_id', // أضفناه هنا في حال رغبت بتخزينه مباشرة
        'doctor_id',
        'day_of_week',
        'start_time',
        'end_time',
        'is_active',
        'day_name',
    ]; 
    
    protected $casts = [
        'day_name' => 'array',
    ];

    // علاقة مع العيادة مباشرة
    public function clinic()
    {
        return $this->belongsTo(Clinic::class);
    }
    
    // علاقة عكسية: هذا الوقت يخص طبيب واحد
    public function doctor()
    {
        return $this->belongsTo(Doctor::class);
    }
}