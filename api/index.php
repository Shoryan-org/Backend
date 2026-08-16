<?php

error_reporting(E_ALL);
ini_set('display_errors', '1');

// Vercel's filesystem is read-only except /tmp — pre-create the storage
// folders Laravel needs before it boots, since /tmp is empty on every
// cold start.
$storagePath = '/tmp/storage';
foreach (
    [
        '/logs',
        '/framework/cache/data',
        '/framework/sessions',
        '/framework/views',
    ] as $dir
) {
    if (! is_dir($storagePath . $dir)) {
        mkdir($storagePath . $dir, 0775, true);
    }
}

try {
    require __DIR__ . '/../public/index.php';
} catch (\Throwable $e) {
    error_log('=== LARAVEL ERROR ===');
    error_log($e->getMessage());
    error_log($e->getFile());
    error_log((string) $e->getLine());
    error_log($e->getTraceAsString());

    http_response_code(500);
    echo 'Laravel boot error: ' . $e->getMessage();
}
