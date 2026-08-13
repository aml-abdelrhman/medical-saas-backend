<?php

// ------------------------------------------------------------------
// CORS Configuration for Vercel Serverless
// ------------------------------------------------------------------
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    header('Access-Control-Allow-Origin: *');
    header('Access-Control-Allow-Methods: GET, POST, PUT, PATCH, DELETE, OPTIONS');
    header('Access-Control-Allow-Headers: Content-Type, Authorization, X-Requested-With, X-CSRF-Token, Accept, Origin, Accept-Version, Cache-Control, Pragma, Expires');
    header('Access-Control-Max-Age: 86400');
    http_response_code(200);
    exit(0);
}

ini_set('display_errors', 1);
error_reporting(E_ALL);

$tmpBase = '/tmp/laravel';
$dirs = [
    $tmpBase,
    $tmpBase . '/framework',
    $tmpBase . '/framework/cache',
    $tmpBase . '/framework/cache/data',
    $tmpBase . '/framework/sessions',
    $tmpBase . '/framework/views',
    $tmpBase . '/logs',
];
foreach ($dirs as $dir) {
    if (!is_dir($dir))
        mkdir($dir, 0777, true);
}

putenv('CACHE_STORE=array');
putenv('SESSION_DRIVER=cookie');
putenv('LOG_CHANNEL=stderr');
putenv('VIEW_COMPILED_PATH=' . $tmpBase . '/framework/views');
putenv('APP_DEBUG=true');

try {
    require __DIR__ . '/../vendor/autoload.php';

    $app = require_once __DIR__ . '/../bootstrap/app.php';
    $app->useStoragePath($tmpBase);

    $kernel = $app->make(Illuminate\Contracts\Http\Kernel::class);

    // Fix for Vercel routing: Ensure Laravel doesn't strip /api
    $_SERVER['SCRIPT_NAME'] = '/index.php';

    $request = Illuminate\Http\Request::capture();
    $response = $kernel->handle($request);

    $response->send();
    $kernel->terminate($request, $response);

} catch (\Throwable $e) {
    // تم إضافة هيدر الـ CORS هنا عشان المتصفح يمنع ظهور Network Error الوهمية
    // ويسمح بقراءة تفاصيل الخطأ الحقيقي وإظهارها في الـ Console مباشرة
    header('Access-Control-Allow-Origin: *');
    header('Access-Control-Allow-Methods: GET, POST, PUT, PATCH, DELETE, OPTIONS');
    header('Access-Control-Allow-Headers: Content-Type, Authorization, X-Requested-With, X-CSRF-Token, Accept, Origin');
    
    http_response_code(500);
    header('Content-Type: application/json');
    echo json_encode([
        'error' => true,
        'message' => $e->getMessage(),
        'file' => $e->getFile(),
        'line' => $e->getLine(),
        'trace' => explode("\n", $e->getTraceAsString()),
    ], JSON_PRETTY_PRINT);
}