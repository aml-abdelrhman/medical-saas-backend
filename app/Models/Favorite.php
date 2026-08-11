<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Favorite extends Model
{
    // إضافة هذا السطر ينهي أي شك في أن المودل يرى الجدول الصحيح
    protected $table = 'favorites'; 

    protected $fillable = ['patient_id', 'doctor_id'];

    public function doctor()
    {
        return $this->belongsTo(Doctor::class);
    }
}