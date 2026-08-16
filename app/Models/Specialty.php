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
            $name = is_array($specialty->name) ? $specialty->name : json_decode($specialty->name, true);
            $specialty->slug = Str::slug($name['ar'] ?? ($name['en'] ?? 'specialty'));
        });

        static::updating(function ($specialty) {
            if ($specialty->isDirty('name')) {
                $name = is_array($specialty->name) ? $specialty->name : json_decode($specialty->name, true);
                $specialty->slug = Str::slug($name['ar'] ?? ($name['en'] ?? 'specialty'));
            }
        });
    }

    /**
     * Accessor لجلب صورة التخصص سواء كانت رابطاً سحابياً من Cloudinary أو مساراً محلياً
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