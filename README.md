
# TCPDF
=======
# 📊 Stock Moves PDF Generator

نظام متكامل لتوليد تقارير PDF تلقائياً من جدول حركات المخزون `0_stock_moves` باستخدام PHP Native و TCPDF و OOP مع نظام Job Queue.

## 🎯 المميزات

- ✅ **توليد تلقائي للتقارير**: كل 1000 صف جديد يتم إنشاء ملف PDF تلقائياً
- ✅ **نظام Queue متقدم**: إدارة المهام بكفاءة مع تتبع الحالة
- ✅ **تصميم OOP نظيف**: كود منظم وسهل الصيانة
- ✅ **واجهة مراقبة جميلة**: Dashboard لمتابعة النظام
- ✅ **تسجيل شامل**: Logging لجميع العمليات والأخطاء
- ✅ **تقارير احترافية**: PDF منسق بشكل جميل مع جداول وألوان

## 📁 هيكل المشروع

```
tcpdf_job_queue_stock_moves_v2/
├── api/
│   ├── run_watcher.php        # API endpoint لتشغيل Watcher
│   └── run_worker.php         # API endpoint لتشغيل Worker
├── classes/
│   ├── Database.php           # إدارة قاعدة البيانات
│   ├── ReportGenerator.php    # توليد ملفات PDF
│   └── JobWorker.php          # معالجة المهام
├── scripts/
│   ├── watcher.php            # مراقبة الجدول وإضافة مهام
│   └── worker.php             # معالجة المهام وتوليد PDF
├── reports/                   # مجلد ملفات PDF المولدة
├── tcpdf/                     # مكتبة TCPDF
├── db.php                     # إعدادات قاعدة البيانات
├── migrations.sql             # جداول قاعدة البيانات
├── index.php                  # الصفحة الرئيسية
├── dashboard.php              # لوحة التحكم
├── reset_queue.php            # إعادة تعيين النظام
└── README.md                  # هذا الملف
```

## 🚀 التثبيت والإعداد

### 1. متطلبات النظام

- PHP 7.4 أو أحدث
- MySQL 5.7 أو أحدث
- مكتبة TCPDF (موجودة في المشروع)
- جدول `0_stock_moves` في قاعدة البيانات

### 2. إعداد قاعدة البيانات

قم بتشغيل ملف `migrations.sql` لإنشاء الجداول المطلوبة:

```sql
-- جدول المهام
CREATE TABLE IF NOT EXISTS `queue_jobs` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `start_trans_id` INT NOT NULL,
  `end_trans_id` INT NOT NULL,
  `status` ENUM('pending','processing','done','failed') DEFAULT 'pending',
  `attempts` INT NOT NULL DEFAULT 0,
  `locked_by` VARCHAR(100) DEFAULT NULL,
  `locked_at` DATETIME DEFAULT NULL,
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- جدول Checkpoints
CREATE TABLE IF NOT EXISTS `processed_checkpoints` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `last_trans_id` INT NOT NULL,
  `batch_size` INT NOT NULL DEFAULT 1000,
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- جدول التقارير
CREATE TABLE IF NOT EXISTS `report_batches` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `from_id` INT NOT NULL,
  `to_id` INT NOT NULL,
  `file_path` VARCHAR(255),
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- جدول السجلات
CREATE TABLE IF NOT EXISTS `queue_logs` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `job_id` INT NULL,
  `level` VARCHAR(20),
  `message` TEXT,
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
```

### 3. تكوين الاتصال بقاعدة البيانات

عدّل ملف `db.php` بمعلومات قاعدة البيانات الخاصة بك:

```php
$dsn = "mysql:host=localhost;dbname=admin_meemkwdb;charset=utf8mb4";
$user = "root";
$pass = "";
```

### 4. التأكد من الصلاحيات

تأكد من أن مجلد `reports` قابل للكتابة:

```bash
chmod 777 reports/
```

## 📖 كيفية الاستخدام

### الطريقة الأولى: من خلال الواجهة (موصى بها)

1. افتح المتصفح وانتقل إلى: `http://localhost/tcpdf_job_queue_stock_moves_v2/`
2. ستظهر لك لوحة التحكم Dashboard
3. اضغط على زر "🔍 بحث عن عمليات جديدة" لفحص الجدول وإضافة مهام جديدة
4. اضغط على زر "⚙️ توليد التقارير الجديده" لمعالجة المهام وتوليد ملفات PDF
5. يمكنك استخدام زر "🗑️ إعادة استخراج الملفات من البدايه" لإعادة تعيين النظام بالكامل

### الطريقة الثانية: من خلال سطر الأوامر

#### تشغيل Watcher (فحص الجدول وإضافة مهام):
```bash
php scripts/watcher.php
```

#### تشغيل Worker (معالجة المهام):
```bash
php scripts/worker.php
```

### الطريقة الثالثة: من خلال API Endpoints

يمكنك استدعاء العمليات من خلال AJAX أو أي HTTP client:

```javascript
// تشغيل Watcher
fetch('api/run_watcher.php')
  .then(response => response.json())
  .then(data => console.log(data));

// تشغيل Worker
fetch('api/run_worker.php')
  .then(response => response.json())
  .then(data => console.log(data));
```

### الطريقة الرابعة: التشغيل التلقائي عند بدء Windows ⭐ (موصى بها)

لتشغيل النظام تلقائياً عند بدء تشغيل Windows:

#### خطوة واحدة فقط:
```
انقر نقراً مزدوجاً على: add_to_startup.bat
```

**✅ تم! النظام سيعمل تلقائياً عند كل تشغيل للجهاز**

#### الملفات المتاحة:
- `start_system.bat` - تشغيل يدوي (مع نافذة CMD)
- `start_system_silent.vbs` - تشغيل يدوي (بدون نافذة)
- `add_to_startup.bat` - إضافة للتشغيل التلقائي
- `remove_from_startup.bat` - إزالة من التشغيل التلقائي

#### للمزيد من التفاصيل:
راجع الدليل الكامل: `AUTO_START_GUIDE.md` أو `QUICK_START_AR.md`

### الطريقة الخامسة: جدولة تلقائية (Cron Job - Linux/Mac)

لتشغيل النظام تلقائياً كل 5 دقائق على Linux/Mac، أضف إلى Crontab:

```bash
# تشغيل Watcher كل 5 دقائق
*/5 * * * * php /path/to/project/scripts/watcher.php

# تشغيل Worker كل دقيقة
* * * * * php /path/to/project/scripts/worker.php
```

## 🔄 آلية العمل

### 1. Watcher (المراقب)
- يفحص جدول `0_stock_moves` للبحث عن صفوف جديدة
- عندما يجد 1000 صف جديد أو أكثر، يقوم بـ:
  - إنشاء مهمة جديدة في جدول `queue_jobs`
  - حفظ آخر `trans_id` تمت معالجته في `processed_checkpoints`
  - تسجيل العملية في `queue_logs`
- يمكن تشغيله من:
  - واجهة Dashboard (زر البحث)
  - API endpoint: `api/run_watcher.php`
  - سطر الأوامر: `php scripts/watcher.php`

### 2. Worker (المعالج)
- يجلب المهام المعلقة من جدول `queue_jobs`
- لكل مهمة:
  - يجلب البيانات من `0_stock_moves`
  - يولد ملف PDF باستخدام TCPDF
  - يحفظ الملف في مجلد `reports/`
  - يسجل معلومات التقرير في `report_batches`
  - يحدث حالة المهمة إلى `done` أو `failed`
- يمكن تشغيله من:
  - واجهة Dashboard (زر التوليد)
  - API endpoint: `api/run_worker.php`
  - سطر الأوامر: `php scripts/worker.php`

### 3. Dashboard (لوحة التحكم)
- عرض إحصائيات النظام في الوقت الفعلي:
  - عدد المهام المعلقة
  - عدد المهام قيد المعالجة
  - عدد المهام المكتملة
  - عدد المهام الفاشلة
  - إجمالي التقارير المولدة
  - آخر trans_id تمت معالجته
- عرض آخر 10 مهام
- عرض آخر 10 تقارير مع روابط التحميل
- عرض آخر 20 سجل من الأحداث
- أزرار تحكم سريعة:
  - بحث عن عمليات جديدة
  - توليد التقارير الجديدة
  - إعادة تعيين النظام
  - تحديث الصفحة

## 📊 حالات المهام

- **pending**: مهمة جديدة في انتظار المعالجة
- **processing**: المهمة قيد المعالجة حالياً
- **done**: المهمة اكتملت بنجاح
- **failed**: المهمة فشلت (يتم تسجيل الخطأ)

## 🎨 تخصيص التقارير

يمكنك تعديل تصميم التقارير من خلال ملف `classes/ReportGenerator.php`:

```php
// تعديل الألوان
$html .= '<style>
    th {
        background-color: #4CAF50;  // لون رأس الجدول
        color: white;
    }
    tr:nth-child(even) {
        background-color: #f2f2f2;  // لون الصفوف الزوجية
    }
</style>';
```

## 🔧 استكشاف الأخطاء

### المشكلة: لا يتم إنشاء ملفات PDF

**الحلول:**
1. تأكد من صلاحيات مجلد `reports/`
2. تحقق من وجود مكتبة TCPDF في `tcpdf/`
3. راجع سجل الأخطاء في جدول `queue_logs`

### المشكلة: لا يتم إضافة مهام جديدة

**الحلول:**
1. تأكد من وجود بيانات في جدول `0_stock_moves`
2. تحقق من أن عدد الصفوف الجديدة >= 1000
3. راجع إعدادات قاعدة البيانات في `db.php`

### المشكلة: المهام تفشل باستمرار

**الحلول:**
1. راجع جدول `queue_logs` للحصول على تفاصيل الخطأ
2. تأكد من صحة بنية جدول `0_stock_moves`
3. تحقق من ذاكرة PHP المتاحة (قد تحتاج لزيادة `memory_limit`)

## 📈 الأداء والتحسينات

- **حجم الدفعة**: افتراضياً 1000 صف، يمكن تعديله من `Database.php`
- **عدد المهام المتزامنة**: Worker يعالج 10 مهام كحد أقصى في المرة الواحدة
- **الذاكرة**: تأكد من أن `memory_limit` في PHP كافٍ (يُنصح بـ 256M أو أكثر)
- **API Endpoints**: تعمل في الخلفية ولا تحجب المستخدم
- **تحديث تلقائي**: Dashboard يعيد تحميل الصفحة تلقائياً بعد العمليات

## 🔐 الأمان

- ✅ استخدام Prepared Statements لمنع SQL Injection
- ✅ تنظيف المدخلات باستخدام `htmlspecialchars()`
- ✅ التحقق من أنواع البيانات
- ✅ معالجة الأخطاء بشكل آمن

## 📝 الترخيص

هذا المشروع مفتوح المصدر ويمكن استخدامه بحرية.

## 👨‍💻 التقنيات المستخدمة

تم تطوير هذا النظام باستخدام:
- **PHP 8.2** - لغة البرمجة الأساسية
- **MySQL** - قاعدة البيانات
- **TCPDF Library** - توليد ملفات PDF
- **Object-Oriented Programming (OOP)** - نمط البرمجة
- **Job Queue Pattern** - نمط إدارة المهام
- **AJAX & Fetch API** - التفاعل مع الخادم
- **Bootstrap CSS** - تنسيق الواجهة
- **Native JavaScript** - البرمجة الأمامية

## 📞 الدعم

للحصول على المساعدة أو الإبلاغ عن مشاكل، يرجى مراجعة:
- جدول `queue_logs` للأخطاء
- لوحة التحكم Dashboard للإحصائيات
- ملفات السجلات في مجلد `logs/`

---

**ملاحظة**: تأكد من تشغيل `migrations.sql` قبل استخدام النظام لأول مرة!
