<?php

return [

    'notification_url' => env('CLOUDINARY_NOTIFICATION_URL'),

    'cloud_url' => 'cloudinary://572968122319822:zdkcDD05lfv_3dTwL4KPK29zz50@dfgdtlfhg',

    'cloud' => [
        'cloud_name' => 'dfgdtlfhg',
        'api_key'    => '572968122319822',
        'api_secret' => 'zdkcDD05lfv_3dTwL4KPK29zz50',
    ],

    'upload_preset' => env('CLOUDINARY_UPLOAD_PRESET'),
    'upload_route' => env('CLOUDINARY_UPLOAD_ROUTE'),
    'upload_action' => env('CLOUDINARY_UPLOAD_ACTION'),
];