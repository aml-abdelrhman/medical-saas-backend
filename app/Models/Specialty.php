<?php

namespace App\Models;

use App\Traits\BelongsToClinic;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Support\Str;

class Specialty extends Model
{
    use BelongsToClinic;  
 
    protected $fillable = [
        'name', 
        'slug', 
        'image', 
        'description', 
        'clinic_id'
    ];


    protected $casts = [
        'name' => 'array',
        'description' => 'array',
    ];

    protected static function booted()
    {
        static::creating(function ($specialty) {
            $specialty->slug = Str::slug($specialty->name['ar'] ?? $specialty->name['en']);
        });

        static::updating(function ($specialty) {
            if ($specialty->isDirty('name')) {
                $specialty->slug = Str::slug($specialty->name['ar'] ?? $specialty->name['en']);
            }
        });
    }

    protected function image(): Attribute
    {
        return Attribute::make(
            get: fn ($value) => $value ? (str_starts_with($value, 'http') ? $value : asset('storage/' . $value)) : null,
        );
    }

    public function doctors()
    {
        return $this->hasMany(Doctor::class);
    }

    // التخصص يتبع عيادة واحدة حصراً
    public function clinic()
    {
        return $this->belongsTo(Clinic::class);
    }
}