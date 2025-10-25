<?php
/**
 * فحص بيانات جدول 0_stock_moves
 */

require_once __DIR__ . '/db.php';

echo "<!DOCTYPE html>
<html lang='ar' dir='rtl'>
<head>
    <meta charset='UTF-8'>
    <title>فحص البيانات</title>
    <style>
        body { font-family: Arial; padding: 20px; background: #f5f5f5; }
        .info { background: white; padding: 20px; border-radius: 10px; margin: 10px 0; }
        h2 { color: #333; }
        pre { background: #1e1e1e; color: #00ff00; padding: 15px; border-radius: 5px; }
    </style>
</head>
<body>
    <h1>🔍 فحص بيانات جدول 0_stock_moves</h1>";

try {
    // الحصول على أقل trans_id
    $minId = $pdo->query("SELECT MIN(trans_id) FROM 0_stock_moves")->fetchColumn();
    echo "<div class='info'><strong>أقل trans_id:</strong> $minId</div>";
    
    // الحصول على أكبر trans_id
    $maxId = $pdo->query("SELECT MAX(trans_id) FROM 0_stock_moves")->fetchColumn();
    echo "<div class='info'><strong>أكبر trans_id:</strong> $maxId</div>";
    
    // عدد الصفوف
    $count = $pdo->query("SELECT COUNT(*) FROM 0_stock_moves")->fetchColumn();
    echo "<div class='info'><strong>إجمالي الصفوف:</strong> " . number_format($count) . "</div>";
    
    // عينة من البيانات
    echo "<div class='info'><h2>عينة من البيانات (أول 5 صفوف):</h2>";
    $stmt = $pdo->query("SELECT * FROM 0_stock_moves ORDER BY trans_id ASC LIMIT 5");
    $sample = $stmt->fetchAll(PDO::FETCH_ASSOC);
    echo "<pre>" . print_r($sample, true) . "</pre></div>";
    
    // فحص المهام في queue_jobs
    echo "<div class='info'><h2>المهام في queue_jobs:</h2>";
    $stmt = $pdo->query("SELECT id, start_trans_id, end_trans_id, status FROM queue_jobs ORDER BY id ASC LIMIT 10");
    $jobs = $stmt->fetchAll(PDO::FETCH_ASSOC);
    echo "<pre>" . print_r($jobs, true) . "</pre></div>";
    
    // اقتراح الحل
    echo "<div class='info' style='background: #fff3cd; border: 2px solid #ffc107;'>
            <h2>💡 الحل المقترح:</h2>
            <p>يبدو أن trans_id يبدأ من <strong>$minId</strong> وليس من 1</p>
            <p>يجب حذف المهام القديمة وإعادة تشغيل Watcher لإنشاء مهام صحيحة.</p>
            <a href='reset_queue.php' style='display: inline-block; padding: 10px 20px; background: #dc3545; color: white; text-decoration: none; border-radius: 5px; margin-top: 10px;'>
                🗑️ حذف جميع المهام وإعادة البدء
            </a>
          </div>";
    
} catch (Exception $e) {
    echo "<div class='info' style='background: #f8d7da; color: #721c24;'>
            <strong>خطأ:</strong> " . $e->getMessage() . "
          </div>";
}

echo "</body></html>";
?>
