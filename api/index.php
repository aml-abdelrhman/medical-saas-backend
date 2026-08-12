<?php

use Illuminate\Http\Request;

define('LARAVEL_START', microtime(true));

// تحديد المسار الصحيح للمجلدات لأن Vercel بيغير مسار التشغيل
require __DIR__ . '/../vendor/autoload.php';

$app = require_once __DIR__ . '/../bootstrap/app.php';

$kernel = $app->make(Illuminate\Contracts\Http\Kernel::class);

$response = $kernel->handle(
    $request = Request::capture()
)->send();

$kernel->terminate($request, $response);