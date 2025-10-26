<?php
/**
 * Watcher Script
 * يراقب جدول 0_stock_moves ويضيف مهام جديدة للـ Queue
 * كل 1000 صف جديد يتم إنشاء مهمة لتوليد PDF
 */

require_once __DIR__ . '/../db.php';
require_once __DIR__ . '/../classes/Database.php';

$db = new Database($pdo);

// إنشاء ملف قفل لمنع التشغيل المتزامن
$lockFile = __DIR__ . '/../watcher.lock';

// التحقق من وجود ملف القفل
if (file_exists($lockFile)) {
    $lockTime = filemtime($lockFile);
    $currentTime = time();
    
    // إذا كان الملف موجود منذ أقل من 5 دقائق، لا تشغل
    if (($currentTime - $lockTime) < 120) {
        echo "[⚠️] Watcher is already running. Please wait...\n";
        exit(0);
    } else {
        // إذا كان الملف قديم (أكثر من 5 دقائق)، احذفه وكمل
        unlink($lockFile);
    }
}

// إنشاء ملف القفل
file_put_contents($lockFile, date('Y-m-d H:i:s'));

echo "========================================\n";
echo "[Watcher] 🔍 Started at " . date('Y-m-d H:i:s') . "\n";
echo "========================================\n";

try {
    // الحصول على آخر trans_id تمت معالجته
    $lastProcessed = $db->getLastProcessedId();
    echo "[Info] Last processed trans_id: $lastProcessed\n";
    
    // الحصول على أقصى trans_id في الجدول
    $currentMax = $db->getMaxStockMoveId();
    echo "[Info] Current max trans_id: $currentMax\n";
    
    // حساب عدد الصفوف الجديدة
    $newRows = $currentMax - $lastProcessed;
    echo "[Info] New rows available: $newRows\n";
    
    // إذا كان هناك 1000 صف جديد أو أكثر
    if ($newRows >= 1000) {
        $batchesCreated = 0;
        
        // إنشاء مهام لكل 1000 صف (بدون حد أقصى)
        while ($lastProcessed + 1000 <= $currentMax) {
            $startId = $lastProcessed + 1;
            $endId = $lastProcessed + 1000;
            
            // إضافة المهمة إلى الـ Queue
            $jobId = $db->addQueueJob($startId, $endId);
            
            echo "[✅] Created job #$jobId: trans_id $startId to $endId\n";
            
            // تحديث آخر معالجة
            $db->saveLastProcessedId($endId, 1000);
            $lastProcessed = $endId;
            $batchesCreated++;
        }
        
        echo "========================================\n";
        echo "[Success] Created $batchesCreated new job(s)\n";
        $db->log('INFO', "Watcher created $batchesCreated new job(s)");
        
    } else {
        echo "[⏸] Waiting... Need " . (1000 - $newRows) . " more rows to create a new job\n";
    }
    
} catch (Exception $e) {
    echo "[❌] Error: " . $e->getMessage() . "\n";
    $db->log('ERROR', "Watcher error: " . $e->getMessage());
} finally {
    // حذف ملف القفل عند الانتهاء
    if (file_exists($lockFile)) {
        unlink($lockFile);
    }
}

echo "========================================\n";
echo "[Watcher] Finished at " . date('Y-m-d H:i:s') . "\n";
echo "========================================\n";

