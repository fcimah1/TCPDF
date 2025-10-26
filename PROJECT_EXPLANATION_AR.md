# 📚 شرح تفصيلي للمشروع - TCPDF Job Queue System

## 🎯 الهدف من المشروع

تحويل بيانات حركات المخزون من قاعدة البيانات إلى تقارير PDF بشكل **تلقائي ومنظم**، مع معالجة آلاف السجلات بكفاءة عالية.

---

## 🔄 كيف يعمل النظام؟

### المراحل الأساسية:

```
1. Watcher (المراقب) 👁️
   ↓
2. Job Queue (طابور المهام) 📋
   ↓
3. Worker (المعالج) ⚙️
   ↓
4. PDF Reports (التقارير) 📄
```

---

## 📊 شرح تفصيلي لكل مرحلة

### 1️⃣ Watcher (المراقب) - `scripts/watcher.php`

**الوظيفة:** البحث عن بيانات جديدة في قاعدة البيانات

#### كيف يعمل؟

```php
// كل 5 دقائق، يفحص الجدول
SELECT MAX(trans_id) FROM stock_moves;

// مثال:
آخر معالجة: 4500299
أحدث بيانات: 4501003
بيانات جديدة: 704 سجل ✅

// إذا وصل العدد 1000 سجل:
إنشاء Job جديد في الطابور
```

#### الكود الأساسي:

```php
// 1. جلب آخر trans_id تم معالجته
$lastProcessed = $db->getLastProcessedTransId();

// 2. جلب أحدث trans_id في الجدول
$currentMax = $db->getCurrentMaxTransId();

// 3. حساب الفرق
$newRows = $currentMax - $lastProcessed;

// 4. إذا وصل 1000 سجل، إنشاء Jobs
if ($newRows >= 1000) {
    while ($lastProcessed + 1000 <= $currentMax) {
        $startId = $lastProcessed + 1;
        $endId = $lastProcessed + 1000;
        
        // إضافة المهمة إلى الـ Queue
        $jobId = $db->addQueueJob($startId, $endId);
        
        echo "[✅] Created job #$jobId: trans_id $startId to $endId\n";
        
        // تحديث آخر معالجة
        $db->saveLastProcessedId($endId, 1000);
        $lastProcessed = $endId;
    }
}
```

---

### 2️⃣ Job Queue (طابور المهام) - جدول `jobs`

**الوظيفة:** تنظيم المهام وتتبع حالتها

#### بنية الجدول:

```sql
CREATE TABLE jobs (
    id INT PRIMARY KEY AUTO_INCREMENT,
    start_trans_id INT NOT NULL,
    end_trans_id INT NOT NULL,
    status VARCHAR(50) DEFAULT 'pending',
    worker_id VARCHAR(50) DEFAULT NULL,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    updated_at DATETIME DEFAULT CURRENT_TIMESTAMP
);
```

#### حالات الـ Job:

| الحالة | الوصف | الأيقونة |
|--------|-------|----------|
| `pending` | في انتظار المعالجة | ⏳ |
| `processing` | جاري المعالجة الآن | ⚙️ |
| `done` | تم الانتهاء بنجاح | ✅ |
| `failed` | فشلت المعالجة | ❌ |

---

### 3️⃣ Worker (المعالج) - `scripts/worker.php`

**الوظيفة:** معالجة المهام وتوليد تقارير PDF

#### خطوات المعالجة:

```php
public function processJob($job) {
    // 1. تحديث الحالة إلى "processing"
    $this->db->updateJobStatus($jobId, 'تتم معالجة العمليات');
    
    // 2. جلب البيانات من قاعدة البيانات
    $data = $this->db->getStockMovesByRange($startTransId, $endTransId);
    
    // 3. توليد تقرير PDF
    $filepath = ReportGenerator::generate($data, $startTransId, $endTransId);
    
    // 4. حفظ معلومات التقرير
    $this->db->saveReportBatch($startTransId, $endTransId, $filepath);
    
    // 5. تحديث الحالة إلى "done"
    $this->db->updateJobStatus($jobId, 'تم معالجة العمليات');
}
```

---

### 4️⃣ Report Generator (مولد التقارير)

**الوظيفة:** تحويل البيانات إلى PDF منسق

```php
// 1. إنشاء PDF
$pdf = new TCPDF('L', 'mm', 'A4', true, 'UTF-8');

// 2. إضافة صفحة
$pdf->AddPage();

// 3. العنوان
$pdf->SetFont('dejavusans', 'B', 14);
$pdf->Cell(0, 10, 'تقرير حركات المخزون', 0, 1, 'C');

// 4. بناء جدول HTML
$html = '<table>...</table>';

// 5. كتابة HTML في PDF
$pdf->writeHTML($html);

// 6. حفظ الملف
$pdf->Output("reports/stock_move_{$startId}_{$endId}.pdf", 'F');
```

---

## 🗄️ قاعدة البيانات

### الجداول الأساسية:

#### 1. `stock_moves` - البيانات الأصلية
```sql
trans_id | trans_no | type | stock_id | tran_date  | qty | price
---------|----------|------|----------|------------|-----|-------
4500300  | SM001    | 13   | ITEM001  | 2025-10-26 | 100 | 50.00
```

#### 2. `jobs` - طابور المهام
```sql
id | start_trans_id | end_trans_id | status  | created_at
---|----------------|--------------|---------|------------
1  | 4500300        | 4501299      | done    | 2025-10-26
```

#### 3. `report_batches` - سجل التقارير
```sql
id | start_trans_id | end_trans_id | pdf_path
---|----------------|--------------|----------------------------------
1  | 4500300        | 4501299      | reports/stock_move_4500300_4501299.pdf
```

#### 4. `system_log` - سجل النظام
```sql
id | level   | message              | job_id | created_at
---|---------|----------------------|--------|------------
1  | INFO    | Watcher started      | NULL   | 2025-10-26
2  | SUCCESS | Job #1 completed     | 1      | 2025-10-26
```

---

## ⚙️ التشغيل التلقائي

### الطريقة: Task Scheduler

```
Windows Startup
   ↓
Task Scheduler
   ↓
start_system_silent.vbs
   ↓
start_system.bat
   ↓
حلقة لا نهائية:
  ├── watcher.php (كل 5 دقائق)
  ├── worker.php (كل 5 دقائق)
  └── إعادة ↻
```

---

## 📈 مثال عملي

```
10:00 - Watcher يفحص
├── آخر معالجة: 4500299
├── أحدث بيانات: 4500850
└── القرار: ⏸️ انتظار (551 سجل)

10:05 - Watcher يفحص
├── آخر معالجة: 4500299
├── أحدث بيانات: 4501350
└── القرار: ✅ إنشاء Job #1

10:06 - Worker يعالج
├── جلب 1000 سجل
├── توليد PDF
└── حفظ: stock_move_4500300_4501299.pdf ✅
```

---

## 📂 بنية الملفات

```
tcpdf_job_queue_stock_moves_v2/
├── scripts/
│   ├── watcher.php
│   └── worker.php
├── classes/
│   ├── Database.php
│   ├── JobWatcher.php
│   ├── JobWorker.php
│   └── ReportGenerator.php
├── reports/
│   └── stock_move_*.pdf
├── dashboard.php
└── start_system.bat
```

---

## 🎯 الخلاصة

### النظام يعمل بهذا الشكل:

1. **Watcher** يراقب قاعدة البيانات كل 5 دقائق
2. عند وصول **1000 سجل جديد**، ينشئ **Job**
3. **Worker** يأخذ الـ Job ويعالجه
4. يجلب **1000 سجل** من قاعدة البيانات
5. يولد **ملف PDF** منظم
6. يحفظ الملف في `reports/`
7. يسجل كل شيء في `system_log`
8. **يكرر** العملية تلقائياً

### المميزات:
- ✅ تلقائي بالكامل
- ✅ منظم (1000 سجل = 1 PDF)
- ✅ موثوق (تسجيل كامل)
- ✅ قابل للتوسع

---

**تم إنشاء هذا الملف في: 26 أكتوبر 2025**
