<?php

namespace App;

use Cloudinary\Cloudinary;
use Illuminate\Http\UploadedFile;

trait HandlesImageUpload
{
    public function uploadImage(UploadedFile $file, $folder = 'uploads')
    {
        // نضع القيم في متغيرات أولاً للتأكد من أنها ليست فارغة
        $cloudName = env('CLOUDINARY_CLOUD_NAME');
        $apiKey    = env('CLOUDINARY_API_KEY');
        $apiSecret = env('CLOUDINARY_API_SECRET');

        // في حال كانت القيم فارغة (مثلاً لو نسيتِ تحديث الـ .env)، 
        // سنضع تحذيراً بدلاً من ترك الكود ينهار
        if (!$cloudName || !$apiKey || !$apiSecret) {
            throw new \Exception("Cloudinary configuration missing in .env file");
        }

        $cloudinary = new Cloudinary([
            'cloud' => [
                'cloud_name' => $cloudName,
                'api_key'    => $apiKey,
                'api_secret' => $apiSecret,
            ],
        ]);

        $result = $cloudinary->uploadApi()->upload($file->getRealPath(), [
            'folder' => $folder
        ]);
        
        return [
            'url' => $result['secure_url'],
            'public_id' => $result['public_id']
        ];
    }

    // أضيفي هذه الدالة داخل التريت HandlesImageUpload
public function deleteImage($publicId)
{
    if (!$publicId) return;

    $cloudName = env('CLOUDINARY_CLOUD_NAME');
    $apiKey    = env('CLOUDINARY_API_KEY');
    $apiSecret = env('CLOUDINARY_API_SECRET');

    $cloudinary = new Cloudinary([
        'cloud' => [
            'cloud_name' => $cloudName,
            'api_key'    => $apiKey,
            'api_secret' => $apiSecret,
        ],
    ]);

    // مسح الصورة باستخدام public_id
    $cloudinary->uploadApi()->destroy($publicId);
}
}