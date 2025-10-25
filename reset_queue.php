<?php
/**
 * إعادة تعيين النظام - حذف جميع المهام والبدء من جديد
 */

require_once __DIR__ . '/db.php';

echo "<!DOCTYPE html>
<html lang='ar' dir='rtl'>
<head>
    <meta charset='UTF-8'>
    <title>إعادة تعيين النظام</title>
    <style>
        body { 
            font-family: Arial; 
            padding: 20px; 
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            min-height: 100vh;
        }
        .container {
            max-width: 800px;
            margin: 50px auto;
            background: white;
            padding: 40px;
            border-radius: 15px;
            box-shadow: 0 10px 30px rgba(0,0,0,0.2);
        }
        h1 { color: #333; text-align: center; }
        .step { 
            padding: 15px; 
            margin: 10px 0; 
            border-radius: 8px; 
            border-left: 4px solid #667eea; 
        }
        .success { background: #d4edda; border-color: #28a745; color: #155724; }
        .error { background: #f8d7da; border-color: #dc3545; color: #721c24; }
        .info { background: #d1ecf1; border-color: #17a2b8; color: #0c5460; }
        .warning { background: #fff3cd; border-color: #ffc107; color: #856404; }
        .btn {
            display: inline-block;
            padding: 12px 30px;
            background: #667eea;
            color: white;
            text-decoration: none;
            border-radius: 8px;
            margin: 10px 5px;
            text-align: center;
        }
        .btn:hover { background: #5568d3; }
        .btn-danger { background: #dc3545; }
        .btn-danger:hover { background: #c82333; }
    </style>
</head>
<body>
    <div class='container'>
        <h1>🔄 إعادة تعيين النظام</h1>";

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['confirm'])) {
    try {
        // حذف جميع المهام
        $deleted = $pdo->exec("DELETE FROM queue_jobs");
        echo "<div class='step success'>✅ تم حذف $deleted مهمة من queue_jobs</div>";
        
        // حذف جميع checkpoints
        $deleted = $pdo->exec("DELETE FROM processed_checkpoints");
        echo "<div class='step success'>✅ تم حذف $deleted checkpoint</div>";
        
        // حذف جميع التقارير من قاعدة البيانات (الملفات ستبقى)
        $deleted = $pdo->exec("DELETE FROM report_batches");
        echo "<div class='step success'>✅ تم حذف $deleted سجل تقرير</div>";
        
        // حذف اللوجات القديمة
        $deleted = $pdo->exec("DELETE FROM queue_logs");
        echo "<div class='step success'>✅ تم حذف $deleted سجل log</div>";
        
        echo "<div class='step success'>
                <h3>🎉 تم إعادة تعيين النظام بنجاح!</h3>
                <p>الآن يمكنك:</p>
                <ol>
                    <li>تشغيل Watcher لإنشاء مهام جديدة صحيحة</li>
                    <li>تشغيل Worker لمعالجة المهام</li>
                </ol>
              </div>";
        
        echo "<div style='text-align: center;'>
                <a href='dashboard.php' class='btn'>📊 العودة للوحة التحكم</a>
                <a href='run_watcher.php' class='btn'>🔍 تشغيل Watcher</a>
              </div>";
        
    } catch (Exception $e) {
        echo "<div class='step error'>
                <h3>❌ حدث خطأ</h3>
                <p>" . htmlspecialchars($e->getMessage()) . "</p>
              </div>";
    }
} else {
    // عرض صفحة التأكيد
    try {
        // عرض الإحصائيات الحالية
        $jobsCount = $pdo->query("SELECT COUNT(*) FROM queue_jobs")->fetchColumn();
        $checkpointsCount = $pdo->query("SELECT COUNT(*) FROM processed_checkpoints")->fetchColumn();
        $reportsCount = $pdo->query("SELECT COUNT(*) FROM report_batches")->fetchColumn();
        $logsCount = $pdo->query("SELECT COUNT(*) FROM queue_logs")->fetchColumn();
        
        echo "<div class='step warning'>
                <h3>⚠️ تحذير</h3>
                <p>هذه العملية ستحذف:</p>
                <ul>
                    <li><strong>$jobsCount</strong> مهمة من queue_jobs</li>
                    <li><strong>$checkpointsCount</strong> checkpoint</li>
                    <li><strong>$reportsCount</strong> سجل تقرير (الملفات ستبقى)</li>
                    <li><strong>$logsCount</strong> سجل log</li>
                </ul>
                <p><strong>ملاحظة:</strong> ملفات PDF الموجودة في مجلد reports/ لن يتم حذفها.</p>
              </div>";
        
        echo "<form method='POST' style='text-align: center;'>
                <input type='hidden' name='confirm' value='1'>
                <button type='submit' class='btn btn-danger'>🗑️ نعم، احذف كل شيء وابدأ من جديد</button>
                <a href='dashboard.php' class='btn'>❌ إلغاء</a>
              </form>";
        
    } catch (Exception $e) {
        echo "<div class='step error'>
                <h3>❌ خطأ في الاتصال بقاعدة البيانات</h3>
                <p>" . htmlspecialchars($e->getMessage()) . "</p>
              </div>";
    }
}

echo "    </div>
</body>
</html>";
?>
