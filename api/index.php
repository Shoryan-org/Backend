<?php

error_reporting(E_ALL);
ini_set('display_errors', '1');

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
