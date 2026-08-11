<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ClinicContactMessage extends Model
{
    protected $fillable = [
        'clinic_id',
        'name',
        'phone',
        'email',
        'message',
        'is_read',
    ];

    protected $casts = [
        'is_read' => 'boolean',
    ];

    public function clinic()
    {
        return $this->belongsTo(Clinic::class);
    }
}