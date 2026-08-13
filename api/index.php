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

$diagnostic = [];
$app = null;

try {
    require __DIR__ . '/../vendor/autoload.php';
    $app = require_once __DIR__ . '/../bootstrap/app.php';
    $app->useStoragePath($tmpBase);

    // تشخيص: هل فيه كاش قديم لقائمة الـ packages بيتقرأ بدل providers.php؟
    $diagnostic['manifest_check'] = file_exists(__DIR__ . '/../bootstrap/cache/packages.php')
        ? file_get_contents(__DIR__ . '/../bootstrap/cache/packages.php')
        : 'FILE NOT FOUND - will use providers.php directly';

    $diagnostic['services_manifest_check'] = file_exists(__DIR__ . '/../bootstrap/cache/services.php')
        ? file_get_contents(__DIR__ . '/../bootstrap/cache/services.php')
        : 'FILE NOT FOUND';

    $kernel = $app->make(Illuminate\Contracts\Http\Kernel::class);

    $request = Illuminate\Http\Request::capture();
    $response = $kernel->handle($request);

    $diagnostic['loaded_providers'] = array_keys($app->getLoadedProviders());
    $diagnostic['view_bound_after'] = $app->bound('view');

    $response->send();
    $kernel->terminate($request, $response);

} catch (\Throwable $e) {
    http_response_code(500);

    $previous = $e->getPrevious();

    echo json_encode([
        'error' => true,
        'message' => $e->getMessage(),
        'file' => $e->getFile(),
        'line' => $e->getLine(),
        'previous_exception' => $previous ? [
            'message' => $previous->getMessage(),
            'file' => $previous->getFile(),
            'line' => $previous->getLine(),
        ] : null,
        'loaded_providers' => $app ? array_keys($app->getLoadedProviders()) : 'app not created',
        'diagnostic' => $diagnostic,
    ], JSON_PRETTY_PRINT);
    exit;
}