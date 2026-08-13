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

$diagnostic = [];

try {
    require __DIR__ . '/../vendor/autoload.php';
    $diagnostic['step_1_autoload'] = 'OK';

    $app = require_once __DIR__ . '/../bootstrap/app.php';
    $diagnostic['step_2_bootstrap'] = 'OK';

    $app->useStoragePath($tmpBase);
    $diagnostic['step_3_storage_path'] = 'OK';

    // نتأكد هل bootstrap/cache فيها ملفات قديمة متسببة في المشكلة
    $cacheFiles = glob(__DIR__ . '/../bootstrap/cache/*.php');
    $diagnostic['bootstrap_cache_files'] = $cacheFiles;

    // نتأكد هل ملف providers.php بيتقرأ صح
    $providers = require __DIR__ . '/../bootstrap/providers.php';
    $diagnostic['providers_file_content'] = $providers;

    // نبوت التطبيق يدوي عشان نمسك الخطأ بالظبط في أي مرحلة
    $kernel = $app->make(Illuminate\Contracts\Http\Kernel::class);
    $diagnostic['step_4_kernel_made'] = 'OK';

    $bootstrappers = $app->make(\Illuminate\Foundation\Http\Kernel::class);
    $diagnostic['step_5_kernel_class'] = get_class($bootstrappers);

    // نشوف هل view مسجلة فعلاً في الـ container قبل الـ handle
    $diagnostic['view_bound_before_handle'] = $app->bound('view');

    $request = Illuminate\Http\Request::capture();
    $response = $kernel->handle($request);
    $diagnostic['step_6_handled'] = 'OK';
    $diagnostic['view_bound_after_handle'] = $app->bound('view');

    $response->send();
    $kernel->terminate($request, $response);

} catch (\Throwable $e) {
    http_response_code(500);
    echo json_encode([
        'error' => true,
        'message' => $e->getMessage(),
        'file' => $e->getFile(),
        'line' => $e->getLine(),
        'diagnostic' => $diagnostic,
    ], JSON_PRETTY_PRINT);
    exit;
}