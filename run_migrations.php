<?php
/**
 * تشغيل ملف migrations.sql لإنشاء جميع الجداول
 */

require_once __DIR__ . '/db.php';

echo "<!DOCTYPE html>
<html lang='ar' dir='rtl'>
<head>
    <meta charset='UTF-8'>
    <title>تشغيل Migrations</title>
    <style>
        body {
            font-family: Arial;
            padding: 40px;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            min-height: 100vh;
        }
        .container {
            max-width: 800px;
            margin: 0 auto;
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
        pre {
            background: #f5f5f5;
            padding: 15px;
            border-radius: 5px;
            overflow-x: auto;
        }
    </style>
</head>
<body>
    <div class='container'>
        <h1>🔧 تشغيل Migrations</h1>";

try {
    // قراءة ملف migrations.sql
    $sqlFile = __DIR__ . '/migrations.sql';
    
    if (!file_exists($sqlFile)) {
        throw new Exception("ملف migrations.sql غير موجود");
    }
    
    echo "<div class='step info'>جاري قراءة ملف migrations.sql...</div>";
    
    $sql = file_get_contents($sqlFile);
    
    // تقسيم الاستعلامات
    $queries = array_filter(
        array_map('trim', explode(';', $sql)),
        function($query) {
            return !empty($query) && !preg_match('/^--/', $query);
        }
    );
    
    echo "<div class='step info'>تم العثور على " . count($queries) . " استعلام</div>";
    
    $successCount = 0;
    $errorCount = 0;
    
    // تنفيذ كل استعلام
    foreach ($queries as $query) {
        $query = trim($query);
        if (empty($query)) continue;
        
        try {
            $pdo->exec($query);
            $successCount++;
            
            // استخراج اسم الجدول من الاستعلام
            if (preg_match('/CREATE TABLE.*?`(\w+)`/i', $query, $matches)) {
                $tableName = $matches[1];
                echo "<div class='step success'>✅ تم إنشاء/تحديث جدول: $tableName</div>";
            }
        } catch (PDOException $e) {
            $errorCount++;
            echo "<div class='step error'>❌ خطأ: " . htmlspecialchars($e->getMessage()) . "</div>";
        }
    }
    
    echo "<div class='step success'>
            <h3>🎉 اكتمل التنفيذ!</h3>
            <p>✅ نجح: $successCount استعلام</p>
            <p>❌ فشل: $errorCount استعلام</p>
          </div>";
    
    // عرض الجداول الموجودة
    echo "<div class='step info'>
            <h3>📊 الجداول الموجودة في قاعدة البيانات:</h3>";
    
    $tables = $pdo->query("SHOW TABLES")->fetchAll(PDO::FETCH_COLUMN);
    echo "<ul>";
    foreach ($tables as $table) {
        // عد الصفوف في كل جدول
        try {
            $count = $pdo->query("SELECT COUNT(*) FROM `$table`")->fetchColumn();
            echo "<li><strong>$table</strong> - $count صف</li>";
        } catch (PDOException $e) {
            echo "<li><strong>$table</strong> - خطأ في القراءة</li>";
        }
    }
    echo "</ul></div>";
    
    echo "<div style='text-align: center;'>
            <a href='dashboard.php' class='btn'>📊 لوحة التحكم الرئيسية</a>
            <a href='monthly_dashboard.php' class='btn'>📅 لوحة التقارير الشهرية</a>
          </div>";
    
} catch (Exception $e) {
    echo "<div class='step error'>
            <h3>❌ حدث خطأ</h3>
            <p>" . htmlspecialchars($e->getMessage()) . "</p>
          </div>";
    
    echo "<div style='text-align: center;'>
            <a href='dashboard.php' class='btn'>← العودة</a>
          </div>";
}

echo "    </div>
</body>
</html>";
?>
