<?php
namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreSpecialtyRequest extends FormRequest
{
    /**
     * تأكدي من تغييرها إلى true حتى يتم تنفيذ الطلب
     */
    public function authorize(): bool
    {
        return true; 
    }

    /**
     * قواعد التحقق الجديدة لدعم الـ JSON
     */
    public function rules(): array
    {
        return [
            // التحقق من أن name مصفوفة تحتوي على ar و en
            'name'        => 'required|array',
            'name.ar'     => 'required|string|max:255',
            'name.en'     => 'required|string|max:255',
            
            // التحقق من الوصف كـ JSON
            'description' => 'nullable|array',
            'description.ar' => 'nullable|string',
            'description.en' => 'nullable|string',
            
            // الصورة تظل كما هي
            'image'       => 'nullable|image|mimes:jpeg,png,jpg,webp|max:2048',
        ];
    }
}