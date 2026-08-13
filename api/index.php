<?php

ini_set('display_errors', 1);
error_reporting(E_ALL);

// إجبار Laravel يستخدم /tmp للكتابة (المسار الوحيد القابل للكتابة في Vercel)
$tmpBase = '/tmp/laravel';
$dirs = [
    $tmpBase,
    $tmpBase . '/framework',
    $tmpBase . '/framework/cache',
    $tmpBase . '/framework/cache/data',
    $tmpBase . '/framework/sessions',
    $tmpBase . '/framework/views',
    $tmpBase . '/logs',
    $tmpBase . '/bootstrap_cache',
];
foreach ($dirs as $dir) {
    if (!is_dir($dir)) {
        mkdir($dir, 0777, true);
    }
}

putenv('APP_STORAGE=' . $tmpBase);
putenv('VIEW_COMPILED_PATH=' . $tmpBase . '/framework/views');
putenv('CACHE_STORE=array');
putenv('SESSION_DRIVER=cookie');
putenv('LOG_CHANNEL=stderr');

try {
    require __DIR__ . '/../vendor/autoload.php';

    $app = require_once __DIR__ . '/../bootstrap/app.php';

    // إعادة توجيه مسارات storage و bootstrap/cache لـ /tmp
    $app->useStoragePath($tmpBase);
    $app->useBootstrapPath(__DIR__ . '/../bootstrap');

    $kernel = $app->make(Illuminate\Contracts\Http\Kernel::class);

    $response = $kernel->handle(
        $request = Illuminate\Http\Request::capture()
    )->send();

    $kernel->terminate($request, $response);

} catch (\Throwable $e) {
    http_response_code(500);
    header('Content-Type: application/json');
    echo json_encode([
        'error' => true,
        'message' => $e->getMessage(),
        'file' => $e->getFile(),
        'line' => $e->getLine(),
    ]);
}