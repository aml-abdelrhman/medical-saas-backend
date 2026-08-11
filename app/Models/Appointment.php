<?php
namespace App\Models;

use App\Traits\BelongsToClinic;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Appointment extends Model
{
    use HasFactory;

    protected $fillable = [
        'clinic_id',
        'patient_id',
        'doctor_id',
        'service_id',
        'appointment_date',
        'start_time',
        'end_time',
        'status',
        'notes'
    ];

    protected $casts = [
        'notes' => 'array',
        'appointment_date' => 'date',
    ];
    
    public function clinic()
    {
        return $this->belongsTo(Clinic::class, 'clinic_id');
    }
    
    // علاقة مع المريض
    public function patient()
    {
        return $this->belongsTo(User::class, 'patient_id');
    }

    // علاقة مع الطبيب
    public function doctor()
    {
        return $this->belongsTo(Doctor::class, 'doctor_id');
    }

    // علاقة مع الخدمة
    public function service()
    {
        return $this->belongsTo(Service::class, 'service_id');
    }
}
