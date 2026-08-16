<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreSpecialtyRequest extends FormRequest
{
    /**
     * السماح بتنفيذ الطلب
     */
    public function authorize(): bool
    {
        return true; 
    }

    /**
     * قواعد التحقق لدعم الحقول متعددة اللغات (JSON) والصورة
     */
    public function rules(): array
    {
        return [
            // التحقق من أن name مصفوفة تحتوي على ar و en
            'name'           => 'required|array',
            'name.ar'        => 'required|string|max:255',
            'name.en'        => 'required|string|max:255',
            
            // التحقق من الوصف كـ مصفوفة متعددة اللغات
            'description'    => 'nullable|array',
            'description.ar' => 'nullable|string',
            'description.en' => 'nullable|string',
            
            // التحقق من الصورة
            'image'          => 'nullable|image|mimes:jpeg,png,jpg,webp|max:2048',
        ];
    }
}