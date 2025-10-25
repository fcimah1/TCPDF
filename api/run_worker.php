<?php
/**
 * API endpoint لتشغيل Worker
 */

// السماح بتشغيل السكريبت في الخلفية
ignore_user_abort(true);
set_time_limit(0);

// إرسال response فوراً للمستخدم
header('Content-Type: application/json');
ob_start();

echo json_encode([
    'status' => 'started',
    'message' => 'Worker started in background',
    'timestamp' => date('Y-m-d H:i:s')
]);

$size = ob_get_length();
header("Content-Length: $size");
header("Connection: close");
ob_end_flush();
flush();

// الآن المستخدم حصل على response، والسكريبت يكمل في الخلفية
if (function_exists('fastcgi_finish_request')) {
    fastcgi_finish_request();
}

// تشغيل Worker
require_once __DIR__ . '/../scripts/worker.php';
?>
