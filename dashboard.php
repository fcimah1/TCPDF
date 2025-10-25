<?php
/**
 * Dashboard - واجهة مراقبة نظام توليد PDF
 */

require_once __DIR__ . '/db.php';
require_once __DIR__ . '/classes/Database.php';

$db = new Database($pdo);

// الحصول على الإحصائيات
$stats = $db->getStats();

    // الحصول على آخر المهام
    $recentJobs = $pdo->query("SELECT * FROM queue_jobs ORDER BY id DESC LIMIT 10")->fetchAll(PDO::FETCH_ASSOC);

// الحصول على آخر التقارير
$recentReports = $pdo->query("SELECT * FROM report_batches ORDER BY id DESC LIMIT 10")->fetchAll(PDO::FETCH_ASSOC);

// الحصول على آخر اللوجات
$recentLogs = $pdo->query("SELECT * FROM queue_logs ORDER BY id DESC LIMIT 20")->fetchAll(PDO::FETCH_ASSOC);
?>
<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Stock Moves PDF Generator - Dashboard</title>
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
            max-width: 1400px;
            margin: 0 auto;
        }
        
        .header {
            background: white;
            padding: 30px;
            border-radius: 15px;
            box-shadow: 0 10px 30px rgba(0,0,0,0.2);
            margin-bottom: 30px;
            text-align: center;
        }
        
        .header h1 {
            color: #333;
            font-size: 2.5em;
            margin-bottom: 10px;
        }
        
        .header p {
            color: #666;
            font-size: 1.1em;
        }
        
        .stats-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
            gap: 20px;
            margin-bottom: 30px;
        }
        
        .stat-card {
            background: white;
            padding: 25px;
            border-radius: 15px;
            box-shadow: 0 5px 15px rgba(0,0,0,0.1);
            text-align: center;
            transition: transform 0.3s;
        }
        
        .stat-card:hover {
            transform: translateY(-5px);
        }
        
        .stat-card .icon {
            font-size: 3em;
            margin-bottom: 10px;
        }
        
        .stat-card .label {
            color: #666;
            font-size: 0.9em;
            margin-bottom: 5px;
        }
        
        .stat-card .value {
            color: #333;
            font-size: 2em;
            font-weight: bold;
        }
        
        .section {
            background: white;
            padding: 25px;
            border-radius: 15px;
            box-shadow: 0 5px 15px rgba(0,0,0,0.1);
            margin-bottom: 30px;
        }
        
        .section h2 {
            color: #333;
            margin-bottom: 20px;
            padding-bottom: 10px;
            border-bottom: 3px solid #667eea;
        }
        
        table {
            width: 100%;
            border-collapse: collapse;
        }
        
        table th {
            background: #667eea;
            color: white;
            padding: 12px;
            text-align: right;
            font-weight: bold;
        }
        
        table td {
            padding: 12px;
            border-bottom: 1px solid #eee;
        }
        
        table tr:hover {
            background: #f5f5f5;
        }
        
        .badge {
            display: inline-block;
            padding: 5px 15px;
            border-radius: 20px;
            font-size: 0.85em;
            font-weight: bold;
        }
        
        .badge-pending {
            background: #ffc107;
            color: #000;
        }
        
        .badge-processing {
            background: #2196F3;
            color: white;
        }
        
        .badge-done {
            background: #4CAF50;
            color: white;
        }
        
        .badge-failed {
            background: #f44336;
            color: white;
        }
        
        .badge-info {
            background: #17a2b8;
            color: white;
        }
        
        .badge-success {
            background: #28a745;
            color: white;
        }
        
        .badge-error {
            background: #dc3545;
            color: white;
        }
        
        .actions {
            display: flex;
            gap: 10px;
            align-items: center;
            text-align: center;
            justify-content: center;
            margin-bottom: 20px;
        }
        
        .btn {
            padding: 12px 30px;
            border: none;
            border-radius: 8px;
            font-size: 1em;
            cursor: pointer;
            transition: all 0.3s;
            text-decoration: none;
            display: inline-block;
            color: white;
            font-family: inherit;
        }
        
        button.btn {
            border: none;
            outline: none;
        }
        
        .btn-primary {
            background: #667eea;
        }
        
        .btn-primary:hover {
            background: #5568d3;
        }
        
        .btn-success {
            background: #4CAF50;
        }
        
        .btn-success:hover {
            background: #45a049;
        }
        
        .btn-danger {
            background: #f44336;
        }
        
        .btn-danger:hover {
            background: #da190b;
        }
        
        .refresh-info {
            text-align: center;
            color: white;
            margin-top: 20px;
            font-size: 0.9em;
        }
        
        .notification {
            position: fixed;
            top: 20px;
            right: 20px;
            padding: 15px 25px;
            border-radius: 8px;
            color: white;
            font-weight: bold;
            z-index: 9999;
            animation: slideIn 0.3s ease-out;
            box-shadow: 0 5px 15px rgba(0,0,0,0.3);
        }
        
        .notification.success {
            background: #28a745;
        }
        
        .notification.error {
            background: #dc3545;
        }
        
        .notification.info {
            background: #17a2b8;
        }
        
        @keyframes slideIn {
            from {
                transform: translateX(400px);
                opacity: 0;
            }
            to {
                transform: translateX(0);
                opacity: 1;
            }
        }
        
        .loading {
            opacity: 0.6;
            pointer-events: none;
        }
    </style>
    <script>
        // متغيرات لمنع التشغيل المتكرر
        let watcherRunning = false;
        let workerRunning = false;
        
        function showNotification(message, type = 'success') {
            const notification = document.createElement('div');
            notification.className = `notification ${type}`;
            notification.textContent = message;
            document.body.appendChild(notification);
            
            setTimeout(() => {
                notification.style.animation = 'slideIn 0.3s ease-out reverse';
                setTimeout(() => notification.remove(), 300);
            }, 3000);
        }
        
        function runWatcher() {
            // منع التشغيل إذا كان يعمل بالفعل
            if (watcherRunning) {
                showNotification('⚠️ Watcher يعمل بالفعل، يرجى الانتظار...', 'info');
                return false;
            }
            
            const btn = event.target;
            watcherRunning = true;
            btn.disabled = true;
            btn.classList.add('loading');
            btn.textContent = '⏳ جاري التشغيل...';
            
            fetch('api/run_watcher.php')
                .then(response => response.json())
                .then(data => {
                    showNotification('✅ تم تشغيل Watcher في الخلفية', 'success');
                    btn.classList.remove('loading');
                    btn.textContent = '🔍 تشغيل Watcher';
                    
                    // تحديث الصفحة بعد 2 ثانية
                    setTimeout(() => location.reload(), 2000);
                })
                .catch(error => {
                    showNotification('❌ حدث خطأ: ' + error.message, 'error');
                    btn.classList.remove('loading');
                    btn.textContent = '🔍 تشغيل Watcher';
                    btn.disabled = false;
                    watcherRunning = false;
                });
            
            return false;
        }
        
        function runWorker() {
            // منع التشغيل إذا كان يعمل بالفعل
            if (workerRunning) {
                showNotification('⚠️ Worker يعمل بالفعل، يرجى الانتظار...', 'info');
                return false;
            }
            
            const btn = event.target;
            workerRunning = true;
            btn.disabled = true;
            btn.classList.add('loading');
            btn.textContent = '⏳ جاري التشغيل...';
            
            fetch('api/run_worker.php')
                .then(response => response.json())
                .then(data => {
                    showNotification('✅ تم تشغيل Worker في الخلفية', 'success');
                    btn.classList.remove('loading');
                    btn.textContent = '⚙️ تشغيل Worker';
                    
                    // تحديث الصفحة بعد 2 ثانية
                    setTimeout(() => location.reload(), 2000);
                })
                .catch(error => {
                    showNotification('❌ حدث خطأ: ' + error.message, 'error');
                    btn.classList.remove('loading');
                    btn.textContent = '⚙️ تشغيل Worker';
                    btn.disabled = false;
                    workerRunning = false;
                });
            
            return false;
        }
    </script>
</head>
<body>
    <div class="container">
        <div class="header">
            <h1>📊 ادارة المهام</h1>
            <p>نظام توليد تقارير PDF تلقائياً من جدول حركات المخزون</p>
        </div>
        
        <div class="stats-grid">
            <div class="stat-card">
                <div class="icon">⏳</div>
                <div class="label">عمليات لم تسترجع بعد</div>
                <div class="value"><?= $stats['jobs_by_status']['pending'] ?? 0 ?></div>
            </div>
            
            <div class="stat-card">
                <div class="icon">⚙️</div>
                <div class="label">قيد المعالجة</div>
                <div class="value"><?= $stats['jobs_by_status']['processing'] ?? 0 ?></div>
            </div>
            
            <div class="stat-card">
                <div class="icon">✅</div>
                <div class="label">مكتملة</div>
                <div class="value"><?= $stats['jobs_by_status']['done'] ?? 0 ?></div>
            </div>
            
            <div class="stat-card">
                <div class="icon">❌</div>
                <div class="label">فاشلة</div>
                <div class="value"><?= $stats['jobs_by_status']['failed'] ?? 0 ?></div>
            </div>
            
            <div class="stat-card">
                <div class="icon">📄</div>
                <div class="label">إجمالي التقارير</div>
                <div class="value"><?= $stats['total_reports'] ?? 0 ?></div>
            </div>
            
            <div class="stat-card">
                <div class="icon">🔢</div>
                <div class="label">آخر عملية</div>
                <div class="value"><?= $stats['last_checkpoint']['last_trans_id'] ?? 0 ?></div>
            </div>
        </div>
        <div class="actions">
            <button onclick="runWatcher()" class="btn btn-primary">🔍  بحث عن عمليات جديدة</button>
            <button onclick="runWorker()" class="btn btn-success">⚙️  توليد التقارير الجديده</button>
            <!-- <a href="check_data.php" class="btn btn-primary " style="background: #17a2b8;">🔍 فحص البيانات</a> -->
            <!-- <a href="run_migrations.php" class="btn btn-primary " style="background: #6c757d;">⚙️ تشغيل Migrations</a> -->
            <a href="reset_queue.php" class="btn btn-danger ">🗑️ إعادة استخراج الملفات من البدايه</a>
            <a href="dashboard.php" class="btn btn-danger  ">🔄 تحديث الصفحة</a>
        </div>
        
        <div class="section">
            <h2>📋 آخر المهام</h2>
            <table>
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>من Trans ID</th>
                        <th>إلى Trans ID</th>
                        <th>الحالة</th>
                        <th>المحاولات</th>
                        <th>تاريخ الإنشاء</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($recentJobs as $job): ?>
                    <tr>
                        <td><?= $job['id'] ?></td>
                        <td><?= $job['start_trans_id'] ?></td>
                        <td><?= $job['end_trans_id'] ?></td>
                        <td>
                            <span class="badge badge-<?= $job['status'] ?>">
                                <?= $job['status'] ?>
                            </span>
                        </td>
                        <td><?= $job['attempts'] ?></td>
                        <td><?= $job['created_at'] ?></td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
        
        <div class="section">
            <h2>📄 آخر التقارير المولدة</h2>
            <table>
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>من ID</th>
                        <th>إلى ID</th>
                        <th>مسار الملف</th>
                        <th>تاريخ الإنشاء</th>
                        <th>إجراء</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($recentReports as $report): ?>
                    <tr>
                        <td><?= $report['id'] ?></td>
                        <td><?= $report['from_id'] ?></td>
                        <td><?= $report['to_id'] ?></td>
                        <td><?= basename($report['file_path']) ?></td>
                        <td><?= $report['created_at'] ?></td>
                        <td>
                            <a href="<?= $report['file_path'] ?>" target="_blank" class="badge badge-info">
                                📥 تحميل
                            </a>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
        
        <div class="section">
            <h2>📝 سجل الأحداث</h2>
            <table>
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Job ID</th>
                        <th>المستوى</th>
                        <th>الرسالة</th>
                        <th>التاريخ</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($recentLogs as $log): ?>
                    <tr>
                        <td><?= $log['id'] ?></td>
                        <td><?= $log['job_id'] ?? '-' ?></td>
                        <td>
                            <span class="badge badge-<?= strtolower($log['level']) ?>">
                                <?= $log['level'] ?>
                            </span>
                        </td>
                        <td><?= htmlspecialchars($log['message']) ?></td>
                        <td><?= $log['created_at'] ?></td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
        
        <div class="refresh-info">
            ⏰ آخر تحديث: <?= date('Y-m-d H:i:s') ?>
        </div>
    </div>
</body>
</html>
