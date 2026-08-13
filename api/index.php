<?php

ini_set('display_errors', 1);
error_reporting(E_ALL);
header('Content-Type: application/json');

$tmpBase = '/tmp/laravel';
$dirs = [
    $tmpBase, $tmpBase . '/framework', $tmpBase . '/framework/cache',
    $tmpBase . '/framework/cache/data', $tmpBase . '/framework/sessions',
    $tmpBase . '/framework/views', $tmpBase . '/logs',
];
foreach ($dirs as $dir) {
    if (!is_dir($dir)) mkdir($dir, 0777, true);
}

putenv('CACHE_STORE=array');
putenv('SESSION_DRIVER=cookie');
putenv('LOG_CHANNEL=stderr');
putenv('VIEW_COMPILED_PATH=' . $tmpBase . '/framework/views');

try {
    require __DIR__ . '/../vendor/autoload.php';
    $app = require_once __DIR__ . '/../bootstrap/app.php';
    $app->useStoragePath($tmpBase);

    // تسجيل يدوي شامل لكل الـ Core Providers الأساسية
    $coreProviders = [
        \Illuminate\Filesystem\FilesystemServiceProvider::class,
        \Illuminate\View\ViewServiceProvider::class,
        \Illuminate\Session\SessionServiceProvider::class,
        \Illuminate\Cookie\CookieServiceProvider::class,
        \Illuminate\Encryption\EncryptionServiceProvider::class,
        \Illuminate\Hashing\HashServiceProvider::class,
        \Illuminate\Database\DatabaseServiceProvider::class,
        \Illuminate\Pagination\PaginationServiceProvider::class,
        \Illuminate\Validation\ValidationServiceProvider::class,
        \Illuminate\Translation\TranslationServiceProvider::class,
    ];

    foreach ($coreProviders as $provider) {
        if (class_exists($provider)) {
            $app->register($provider);
        }
    }

    $app->register(\App\Providers\AppServiceProvider::class);

    $kernel = $app->make(Illuminate\Contracts\Http\Kernel::class);

    $request = Illuminate\Http\Request::capture();
    $response = $kernel->handle($request);
    $response->send();
    $kernel->terminate($request, $response);

} catch (\Throwable $e) {
    http_response_code(500);
    echo json_encode([
        'error' => true,
        'message' => $e->getMessage(),
        'file' => $e->getFile(),
        'line' => $e->getLine(),
    ], JSON_PRETTY_PRINT);
    exit;
}