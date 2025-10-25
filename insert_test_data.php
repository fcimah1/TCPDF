<?php
/**
 * إضافة بيانات اختبارية إلى جدول 0_stock_moves
 */

require_once __DIR__ . '/db.php';

?>
<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>إضافة بيانات اختبارية</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            min-height: 100vh;
            padding: 20px;
        }
        
        .container {
            max-width: 800px;
            margin: 0 auto;
            background: white;
            padding: 30px;
            border-radius: 15px;
            box-shadow: 0 10px 30px rgba(0,0,0,0.2);
        }
        
        h1 {
            color: #333;
            margin-bottom: 20px;
            text-align: center;
        }
        
        .info-box {
            background: #e3f2fd;
            border-right: 4px solid #2196F3;
            padding: 15px;
            margin: 20px 0;
            border-radius: 5px;
        }
        
        .success-box {
            background: #e8f5e9;
            border-right: 4px solid #4CAF50;
            padding: 15px;
            margin: 20px 0;
            border-radius: 5px;
        }
        
        .error-box {
            background: #ffebee;
            border-right: 4px solid #f44336;
            padding: 15px;
            margin: 20px 0;
            border-radius: 5px;
        }
        
        .warning-box {
            background: #fff3e0;
            border-right: 4px solid #ff9800;
            padding: 15px;
            margin: 20px 0;
            border-radius: 5px;
        }
        
        .btn {
            display: inline-block;
            padding: 12px 30px;
            background: #4CAF50;
            color: white;
            text-decoration: none;
            border-radius: 8px;
            border: none;
            cursor: pointer;
            font-size: 16px;
            margin: 10px 5px;
            transition: all 0.3s;
        }
        
        .btn:hover {
            background: #45a049;
            transform: translateY(-2px);
        }
        
        .btn-danger {
            background: #f44336;
        }
        
        .btn-danger:hover {
            background: #da190b;
        }
        
        .btn-primary {
            background: #2196F3;
        }
        
        .btn-primary:hover {
            background: #0b7dda;
        }
        
        table {
            width: 100%;
            border-collapse: collapse;
            margin: 20px 0;
        }
        
        table th, table td {
            padding: 12px;
            text-align: right;
            border-bottom: 1px solid #ddd;
        }
        
        table th {
            background: #667eea;
            color: white;
        }
        
        .actions {
            text-align: center;
            margin: 30px 0;
        }
        
        .loading {
            text-align: center;
            padding: 20px;
        }
        
        .spinner {
            border: 4px solid #f3f3f3;
            border-top: 4px solid #667eea;
            border-radius: 50%;
            width: 40px;
            height: 40px;
            animation: spin 1s linear infinite;
            margin: 0 auto;
        }
        
        @keyframes spin {
            0% { transform: rotate(0deg); }
            100% { transform: rotate(360deg); }
        }
    </style>
</head>
<body>
    <div class="container">
        <h1>📊 إضافة بيانات اختبارية</h1>
        
        <?php
        if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action'])) {
            $action = $_POST['action'];
            $count = isset($_POST['count']) ? (int)$_POST['count'] : 2000;
            
            if ($count < 1 || $count > 10000) {
                echo '<div class="error-box">❌ العدد يجب أن يكون بين 1 و 10000</div>';
            } else {
                try {
                    // الحصول على بنية الجدول
                    $stmt = $pdo->query("DESCRIBE 0_stock_moves");
                    $tableColumns = $stmt->fetchAll(PDO::FETCH_ASSOC);
                    $columnNames = array_column($tableColumns, 'Field');
                    
                    // الحصول على آخر trans_id
                    $stmt = $pdo->query("SELECT IFNULL(MAX(trans_id), 0) as last_id FROM 0_stock_moves");
                    $lastId = $stmt->fetch(PDO::FETCH_ASSOC)['last_id'];
                    
                    echo '<div class="info-box">📌 آخر trans_id موجود: ' . number_format($lastId) . '</div>';
                    echo '<div class="info-box">📋 عدد الأعمدة في الجدول: ' . count($columnNames) . '</div>';
                    echo '<div class="loading"><div class="spinner"></div><p>جاري إضافة ' . number_format($count) . ' صف...</p></div>';
                    
                    // إنشاء البيانات
                    $pdo->beginTransaction();
                    
                    for ($i = 1; $i <= $count; $i++) {
                        $data = [];
                        
                        foreach ($columnNames as $col) {
                            switch ($col) {
                                case 'trans_id':
                                    $data[$col] = $lastId + $i;
                                    break;
                                case 'trans_no':
                                    $data[$col] = rand(1000, 9999);
                                    break;
                                case 'stock_id':
                                    $data[$col] = 'ITEM' . str_pad(rand(1, 100), 3, '0', STR_PAD_LEFT);
                                    break;
                                case 'type':
                                    $data[$col] = rand(10, 30);
                                    break;
                                case 'loc_code':
                                    $data[$col] = 'LOC' . rand(1, 5);
                                    break;
                                case 'tran_date':
                                    $data[$col] = date('Y-m-d', strtotime('-' . rand(0, 365) . ' days'));
                                    break;
                                case 'person_id':
                                    $data[$col] = rand(1, 50);
                                    break;
                                case 'price':
                                    $data[$col] = round(rand(10, 1000) + (rand(0, 99) / 100), 2);
                                    break;
                                case 'reference':
                                    $data[$col] = 'REF-' . str_pad(rand(1, 99999), 5, '0', STR_PAD_LEFT);
                                    break;
                                case 'qty':
                                    $data[$col] = rand(1, 100);
                                    break;
                                case 'discount_percent':
                                    $data[$col] = round(rand(0, 20) + (rand(0, 99) / 100), 2);
                                    break;
                                case 'standard_cost':
                                    $data[$col] = round(rand(10, 500) + (rand(0, 99) / 100), 2);
                                    break;
                                case 'visible':
                                    $data[$col] = 1;
                                    break;
                                case 'increase':
                                    $data[$col] = rand(0, 1);
                                    break;
                                case 'adj':
                                    $data[$col] = rand(0, 1);
                                    break;
                                case 'dim_id':
                                    $data[$col] = rand(0, 10);
                                    break;
                                case 'raw_version':
                                    $data[$col] = 1;
                                    break;
                                case 'prev_raw_version':
                                    $data[$col] = 0;
                                    break;
                                case 'is_close':
                                    $data[$col] = rand(0, 1);
                                    break;
                                case 'created_by':
                                    $data[$col] = 'admin';
                                    break;
                                case 'expiry_date':
                                    $data[$col] = null;
                                    break;
                                case 'serial_number':
                                    $data[$col] = null;
                                    break;
                                case 'branch_id':
                                    $data[$col] = rand(1, 5);
                                    break;
                                case 'update_at':
                                    $data[$col] = date('Y-m-d H:i:s');
                                    break;
                                default:
                                    $data[$col] = null;
                            }
                        }
                        
                        $placeholders = array_map(function($col) { return ':' . $col; }, $columnNames);
                        $sql = "INSERT INTO 0_stock_moves (" . implode(', ', $columnNames) . ") VALUES (" . implode(', ', $placeholders) . ")";
                        $stmt = $pdo->prepare($sql);
                        $stmt->execute($data);
                    }
                    
                    $pdo->commit();
                    
                    // الحصول على الإحصائيات
                    $stmt = $pdo->query("SELECT 
                        MIN(trans_id) as first_id,
                        MAX(trans_id) as last_id,
                        COUNT(*) as total
                    FROM 0_stock_moves 
                    WHERE trans_id > $lastId");
                    
                    $stats = $stmt->fetch(PDO::FETCH_ASSOC);
                    
                    echo '<script>
                        document.querySelector(".loading").style.display = "none";
                    </script>';
                    
                    echo '<div class="success-box">
                        <h3>✅ تم إضافة البيانات بنجاح!</h3>
                        <table>
                            <tr>
                                <th>أول trans_id جديد</th>
                                <td>' . number_format($stats['first_id']) . '</td>
                            </tr>
                            <tr>
                                <th>آخر trans_id جديد</th>
                                <td>' . number_format($stats['last_id']) . '</td>
                            </tr>
                            <tr>
                                <th>إجمالي الصفوف المضافة</th>
                                <td>' . number_format($stats['total']) . '</td>
                            </tr>
                        </table>
                    </div>';
                    
                    echo '<div class="warning-box">
                        ⚠️ <strong>الخطوة التالية:</strong><br>
                        اذهب إلى Dashboard واضغط على زر "🔍 بحث عن عمليات جديدة" لإنشاء المهام الجديدة
                    </div>';
                    
                } catch (PDOException $e) {
                    echo '<div class="error-box">❌ خطأ: ' . htmlspecialchars($e->getMessage()) . '</div>';
                }
            }
        }
        ?>
        
        <div class="info-box">
            <h3>📝 معلومات:</h3>
            <ul>
                <li>سيتم إضافة صفوف جديدة إلى جدول <code>0_stock_moves</code></li>
                <li>كل صف سيحتوي على بيانات عشوائية واقعية</li>
                <li>سيتم إضافة الصفوف بعد آخر <code>trans_id</code> موجود</li>
                <li>بعد الإضافة، اذهب إلى Dashboard وشغل Watcher</li>
            </ul>
        </div>
        
        <?php
        // عرض الإحصائيات الحالية
        try {
            $stmt = $pdo->query("SELECT 
                COUNT(*) as total_rows,
                MIN(trans_id) as min_id,
                MAX(trans_id) as max_id
            FROM 0_stock_moves");
            $current = $stmt->fetch(PDO::FETCH_ASSOC);
            
            echo '<div class="info-box">
                <h3>📊 الإحصائيات الحالية:</h3>
                <table>
                    <tr>
                        <th>إجمالي الصفوف</th>
                        <td>' . number_format($current['total_rows']) . '</td>
                    </tr>
                    <tr>
                        <th>أول trans_id</th>
                        <td>' . number_format($current['min_id']) . '</td>
                    </tr>
                    <tr>
                        <th>آخر trans_id</th>
                        <td>' . number_format($current['max_id']) . '</td>
                    </tr>
                </table>
            </div>';
        } catch (PDOException $e) {
            echo '<div class="error-box">❌ خطأ في قراءة البيانات: ' . htmlspecialchars($e->getMessage()) . '</div>';
        }
        ?>
        
        <form method="POST" onsubmit="return confirm('هل أنت متأكد من إضافة البيانات؟');">
            <div class="info-box">
                <label for="count"><strong>عدد الصفوف المراد إضافتها:</strong></label><br>
                <input type="number" name="count" id="count" value="2000" min="1" max="10000" 
                       style="padding: 8px; margin: 10px 0; width: 200px; border: 1px solid #ddd; border-radius: 5px;">
            </div>
            
            <div class="actions">
                <button type="submit" name="action" value="insert" class="btn">
                    ➕ إضافة البيانات
                </button>
                <a href="dashboard.php" class="btn btn-primary">
                    📊 العودة إلى Dashboard
                </a>
            </div>
        </form>
    </div>
</body>
</html>
