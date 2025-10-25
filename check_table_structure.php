<?php
/**
 * فحص بنية جدول 0_stock_moves
 */

require_once __DIR__ . '/db.php';

?>
<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="UTF-8">
    <title>بنية جدول 0_stock_moves</title>
    <style>
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            padding: 20px;
        }
        .container {
            max-width: 1200px;
            margin: 0 auto;
            background: white;
            padding: 30px;
            border-radius: 15px;
            box-shadow: 0 10px 30px rgba(0,0,0,0.2);
        }
        h1 {
            color: #333;
            text-align: center;
            margin-bottom: 30px;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            margin: 20px 0;
        }
        table th, table td {
            padding: 12px;
            text-align: right;
            border: 1px solid #ddd;
        }
        table th {
            background: #667eea;
            color: white;
            font-weight: bold;
        }
        table tr:nth-child(even) {
            background: #f9f9f9;
        }
        .code-box {
            background: #1e1e1e;
            color: #00ff00;
            padding: 20px;
            border-radius: 8px;
            margin: 20px 0;
            overflow-x: auto;
            direction: ltr;
            text-align: left;
        }
        .info-box {
            background: #e3f2fd;
            border-right: 4px solid #2196F3;
            padding: 15px;
            margin: 20px 0;
            border-radius: 5px;
        }
        .btn {
            display: inline-block;
            padding: 10px 20px;
            background: #4CAF50;
            color: white;
            text-decoration: none;
            border-radius: 5px;
            margin: 10px 5px;
        }
        .btn:hover {
            background: #45a049;
        }
    </style>
</head>
<body>
    <div class="container">
        <h1>🔍 بنية جدول 0_stock_moves</h1>
        
        <?php
        try {
            // الحصول على بنية الجدول
            $stmt = $pdo->query("DESCRIBE 0_stock_moves");
            $columns = $stmt->fetchAll(PDO::FETCH_ASSOC);
            
            echo '<div class="info-box">';
            echo '<strong>📊 عدد الأعمدة:</strong> ' . count($columns);
            echo '</div>';
            
            echo '<h2>📋 قائمة الأعمدة:</h2>';
            echo '<table>';
            echo '<thead>';
            echo '<tr>';
            echo '<th>#</th>';
            echo '<th>اسم العمود</th>';
            echo '<th>النوع</th>';
            echo '<th>Null</th>';
            echo '<th>Key</th>';
            echo '<th>Default</th>';
            echo '<th>Extra</th>';
            echo '</tr>';
            echo '</thead>';
            echo '<tbody>';
            
            $i = 1;
            foreach ($columns as $col) {
                echo '<tr>';
                echo '<td>' . $i++ . '</td>';
                echo '<td><strong>' . htmlspecialchars($col['Field']) . '</strong></td>';
                echo '<td>' . htmlspecialchars($col['Type']) . '</td>';
                echo '<td>' . htmlspecialchars($col['Null']) . '</td>';
                echo '<td>' . htmlspecialchars($col['Key']) . '</td>';
                echo '<td>' . htmlspecialchars($col['Default'] ?? 'NULL') . '</td>';
                echo '<td>' . htmlspecialchars($col['Extra']) . '</td>';
                echo '</tr>';
            }
            
            echo '</tbody>';
            echo '</table>';
            
            // إنشاء كود INSERT
            $columnNames = array_column($columns, 'Field');
            
            echo '<h2>💻 كود INSERT المقترح:</h2>';
            echo '<div class="code-box">';
            echo 'INSERT INTO 0_stock_moves (<br>';
            echo '&nbsp;&nbsp;' . implode(',<br>&nbsp;&nbsp;', $columnNames) . '<br>';
            echo ') VALUES (<br>';
            
            $placeholders = [];
            foreach ($columnNames as $col) {
                if ($col === 'trans_id') {
                    $placeholders[] = '&nbsp;&nbsp;? -- auto increment';
                } else {
                    $placeholders[] = '&nbsp;&nbsp;? -- ' . $col;
                }
            }
            echo implode(',<br>', $placeholders) . '<br>';
            echo ');';
            echo '</div>';
            
            // عرض عينة من البيانات
            echo '<h2>📝 عينة من البيانات (أول صف):</h2>';
            $stmt = $pdo->query("SELECT * FROM 0_stock_moves LIMIT 1");
            $sample = $stmt->fetch(PDO::FETCH_ASSOC);
            
            if ($sample) {
                echo '<table>';
                echo '<thead><tr><th>العمود</th><th>القيمة</th></tr></thead>';
                echo '<tbody>';
                foreach ($sample as $key => $value) {
                    echo '<tr>';
                    echo '<td><strong>' . htmlspecialchars($key) . '</strong></td>';
                    echo '<td>' . htmlspecialchars($value ?? 'NULL') . '</td>';
                    echo '</tr>';
                }
                echo '</tbody>';
                echo '</table>';
            }
            
            // إنشاء سكريبت PHP محدث
            echo '<h2>🔧 تحديث insert_test_data.php</h2>';
            echo '<div class="info-box">';
            echo '<p>سيتم إنشاء ملف محدث يتوافق مع بنية الجدول الفعلية</p>';
            echo '<a href="?generate=1" class="btn">✨ إنشاء الملف المحدث</a>';
            echo '</div>';
            
            if (isset($_GET['generate'])) {
                // إنشاء الملف المحدث
                $phpCode = generateInsertScript($columnNames);
                file_put_contents(__DIR__ . '/insert_test_data_v2.php', $phpCode);
                echo '<div class="info-box" style="background: #e8f5e9; border-color: #4CAF50;">';
                echo '✅ تم إنشاء الملف: <strong>insert_test_data_v2.php</strong><br>';
                echo '<a href="insert_test_data_v2.php" class="btn">🚀 فتح الملف الجديد</a>';
                echo '</div>';
            }
            
        } catch (PDOException $e) {
            echo '<div style="background: #ffebee; padding: 20px; border-radius: 5px; color: #c62828;">';
            echo '❌ خطأ: ' . htmlspecialchars($e->getMessage());
            echo '</div>';
        }
        ?>
        
        <div style="text-align: center; margin-top: 30px;">
            <a href="dashboard.php" class="btn" style="background: #2196F3;">📊 العودة إلى Dashboard</a>
        </div>
    </div>
</body>
</html>

<?php
function generateInsertScript($columns) {
    $columnsStr = implode(', ', $columns);
    
    return '<?php
/**
 * إضافة بيانات اختبارية - محدث تلقائياً
 */

require_once __DIR__ . "/db.php";

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $count = isset($_POST["count"]) ? (int)$_POST["count"] : 2000;
    
    try {
        // الحصول على آخر trans_id
        $stmt = $pdo->query("SELECT IFNULL(MAX(trans_id), 0) as last_id FROM 0_stock_moves");
        $lastId = $stmt->fetch(PDO::FETCH_ASSOC)["last_id"];
        
        $pdo->beginTransaction();
        
        for ($i = 1; $i <= $count; $i++) {
            $data = [
                "trans_id" => $lastId + $i,
                "trans_no" => rand(1000, 9999),
                "stock_id" => "ITEM" . str_pad(rand(1, 100), 3, "0", STR_PAD_LEFT),
                "type" => rand(10, 30),
                "loc_code" => "LOC" . rand(1, 5),
                "tran_date" => date("Y-m-d", strtotime("-" . rand(0, 365) . " days")),
                "price" => round(rand(10, 1000) + (rand(0, 99) / 100), 2),
                "reference" => "REF-" . str_pad(rand(1, 99999), 5, "0", STR_PAD_LEFT),
                "qty" => rand(1, 100),
                "standard_cost" => round(rand(10, 500) + (rand(0, 99) / 100), 2),
            ];
            
            // إضافة الأعمدة الإضافية بقيم افتراضية
            $allColumns = $columns;
            foreach ($allColumns as $col) {
                if (!isset($data[$col])) {
                    $data[$col] = null;
                }
            }
            
            $placeholders = array_map(function($c) { return ':' . $c; }, $allColumns);
            $sql = "INSERT INTO 0_stock_moves (" . implode(", ", $allColumns) . ") VALUES (" . implode(", ", $placeholders) . ")";
            $stmt = $pdo->prepare($sql);
            $stmt->execute($data);
        }
        
        $pdo->commit();
        echo "✅ تم إضافة $count صف بنجاح!";
        
    } catch (Exception $e) {
        $pdo->rollBack();
        echo "❌ خطأ: " . $e->getMessage();
    }
}
?>';
}
?>
