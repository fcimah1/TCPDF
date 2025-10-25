<?php
/**
 * Installation Script
 * تشغيل هذا الملف لإنشاء الجداول المطلوبة
 */

require_once __DIR__ . '/db.php';

echo "<!DOCTYPE html>
<html lang='ar' dir='rtl'>
<head>
    <meta charset='UTF-8'>
    <meta name='viewport' content='width=device-width, initial-scale=1.0'>
    <title>تثبيت النظام</title>
    <style>
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            min-height: 100vh;
            padding: 20px;
            margin: 0;
        }
        .container {
            max-width: 800px;
            margin: 50px auto;
            background: white;
            padding: 40px;
            border-radius: 15px;
            box-shadow: 0 10px 30px rgba(0,0,0,0.2);
        }
        h1 {
            color: #333;
            text-align: center;
            margin-bottom: 30px;
        }
        .step {
            padding: 15px;
            margin: 10px 0;
            border-radius: 8px;
            border-left: 4px solid #667eea;
        }
        .success {
            background: #d4edda;
            border-color: #28a745;
            color: #155724;
        }
        .error {
            background: #f8d7da;
            border-color: #dc3545;
            color: #721c24;
        }
        .info {
            background: #d1ecf1;
            border-color: #17a2b8;
            color: #0c5460;
        }
        .btn {
            display: inline-block;
            padding: 12px 30px;
            background: #667eea;
            color: white;
            text-decoration: none;
            border-radius: 8px;
            margin-top: 20px;
            text-align: center;
        }
        .btn:hover {
            background: #5568d3;
        }
        pre {
            background: #f5f5f5;
            padding: 10px;
            border-radius: 5px;
            overflow-x: auto;
        }
    </style>
</head>
<body>
    <div class='container'>
        <h1>🚀 تثبيت نظام Stock Moves PDF Generator</h1>";

try {
    // قراءة ملف migrations.sql
    $sql = file_get_contents(__DIR__ . '/migrations.sql');
    
    if (!$sql) {
        throw new Exception("فشل في قراءة ملف migrations.sql");
    }
    
    echo "<div class='step info'>📄 تم قراءة ملف migrations.sql بنجاح</div>";
    
    // تقسيم الاستعلامات
    $queries = array_filter(
        array_map('trim', explode(';', $sql)),
        function($query) {
            return !empty($query) && !preg_match('/^--/', $query);
        }
    );
    
    echo "<div class='step info'>🔍 تم العثور على " . count($queries) . " استعلام</div>";
    
    // تنفيذ كل استعلام
    $successCount = 0;
    foreach ($queries as $query) {
        if (empty(trim($query))) continue;
        
        try {
            $pdo->exec($query);
            $successCount++;
            
            // استخراج اسم الجدول من الاستعلام
            if (preg_match('/CREATE TABLE.*?`(\w+)`/i', $query, $matches)) {
                $tableName = $matches[1];
                echo "<div class='step success'>✅ تم إنشاء جدول: <strong>$tableName</strong></div>";
            }
        } catch (PDOException $e) {
            // إذا كان الجدول موجود بالفعل، نتجاهل الخطأ
            if (strpos($e->getMessage(), 'already exists') !== false) {
                if (preg_match('/CREATE TABLE.*?`(\w+)`/i', $query, $matches)) {
                    $tableName = $matches[1];
                    echo "<div class='step info'>ℹ️ الجدول موجود بالفعل: <strong>$tableName</strong></div>";
                }
            } else {
                throw $e;
            }
        }
    }
    
    echo "<div class='step success'>
            <h3>✅ تم التثبيت بنجاح!</h3>
            <p>تم إنشاء جميع الجداول المطلوبة.</p>
          </div>";
    
    // التحقق من وجود جدول 0_stock_moves
    echo "<div class='step info'>
            <h3>🔍 التحقق من جدول 0_stock_moves</h3>";
    
    try {
        $stmt = $pdo->query("SELECT COUNT(*) as count FROM 0_stock_moves");
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        $count = $result['count'];
        
        echo "<p>✅ جدول 0_stock_moves موجود</p>";
        echo "<p>📊 عدد الصفوف الحالية: <strong>$count</strong></p>";
        
        if ($count >= 1000) {
            echo "<p style='color: green;'>✅ يوجد بيانات كافية لبدء التشغيل!</p>";
        } else {
            echo "<p style='color: orange;'>⚠️ تحتاج إلى " . (1000 - $count) . " صف إضافي لبدء توليد التقارير</p>";
        }
        
    } catch (PDOException $e) {
        echo "<p style='color: red;'>❌ جدول 0_stock_moves غير موجود!</p>";
        echo "<p>يرجى التأكد من وجود هذا الجدول في قاعدة البيانات.</p>";
    }
    
    echo "</div>";
    
    // التحقق من مجلد reports
    echo "<div class='step info'>
            <h3>📁 التحقق من المجلدات</h3>";
    
    $reportsDir = __DIR__ . '/reports';
    if (!is_dir($reportsDir)) {
        mkdir($reportsDir, 0777, true);
        echo "<p>✅ تم إنشاء مجلد reports</p>";
    } else {
        echo "<p>✅ مجلد reports موجود</p>";
    }
    
    $logsDir = __DIR__ . '/logs';
    if (!is_dir($logsDir)) {
        mkdir($logsDir, 0777, true);
        echo "<p>✅ تم إنشاء مجلد logs</p>";
    } else {
        echo "<p>✅ مجلد logs موجود</p>";
    }
    
    echo "</div>";
    
    echo "<div style='text-align: center;'>
            <a href='dashboard.php' class='btn'>🎉 انتقل إلى لوحة التحكم</a>
          </div>";
    
} catch (Exception $e) {
    echo "<div class='step error'>
            <h3>❌ حدث خطأ أثناء التثبيت</h3>
            <p>" . htmlspecialchars($e->getMessage()) . "</p>
            <pre>" . htmlspecialchars($e->getTraceAsString()) . "</pre>
          </div>";
}

echo "    </div>
</body>
</html>";
?>
