<?php
/**
 * Worker Script
 * يعالج المهام المعلقة في الـ Queue ويولد ملفات PDF
 */

require_once __DIR__ . '/../db.php';
require_once __DIR__ . '/../classes/Database.php';
require_once __DIR__ . '/../classes/JobWorker.php';

$db = new Database($pdo);
$worker = new JobWorker($db);

// إنشاء ملف قفل لمنع التشغيل المتزامن
$lockFile = __DIR__ . '/../worker.lock';

// التحقق من وجود ملف القفل
if (file_exists($lockFile)) {
    $lockTime = filemtime($lockFile);
    $currentTime = time();
    
    // إذا كان الملف موجود منذ أقل من 10 دقائق، لا تشغل
    if (($currentTime - $lockTime) < 600) {
        echo "[⚠️] Worker is already running. Please wait...\n";
        exit(0);
    } else {
        // إذا كان الملف قديم (أكثر من 10 دقائق)، احذفه وكمل
        unlink($lockFile);
    }
}

// إنشاء ملف القفل
file_put_contents($lockFile, date('Y-m-d H:i:s'));

echo "========================================\n";
echo "[Worker] 🚀 Started at " . date('Y-m-d H:i:s') . "\n";
echo "========================================\n";

try {
    // معالجة جميع المهام المعلقة (بحد أقصى 1000 مهمة في المرة الواحدة)
    $processed = $worker->processAllPendingJobs(1000);
    
    if ($processed > 0) {
        echo "========================================\n";
        echo "[Success] Processed $processed job(s) successfully\n";
    }
    
} catch (Exception $e) {
    echo "[❌] Worker error: " . $e->getMessage() . "\n";
    $db->log('ERROR', "Worker error: " . $e->getMessage());
} finally {
    // حذف ملف القفل عند الانتهاء
    if (file_exists($lockFile)) {
        unlink($lockFile);
    }
}

echo "========================================\n";
echo "[Worker] Finished at " . date('Y-m-d H:i:s') . "\n";
echo "========================================\n";
