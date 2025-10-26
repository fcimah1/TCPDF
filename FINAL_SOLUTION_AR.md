# 🎯 الحل النهائي: Task يبدأ ويختفي فوراً

## 🚨 المشكلة

```
Task Scheduler → يبدأ المهمة
بعد أقل من ثانية → المهمة تختفي
php.exe لا يظهر في Task Manager
```

---

## 🔍 السبب الحقيقي

### المشكلة في الطريقة القديمة:

```
Task Scheduler
    ↓
start_system_silent.vbs (ينتهي فوراً)
    ↓
start_system.bat (يشتغل في الخلفية)
    ↓
Task Scheduler يعتقد أن المهمة انتهت! ❌
```

**التفسير:**
- الـ VBS يشغل الـ BAT بـ `False` (لا تنتظر)
- الـ VBS ينتهي فوراً
- Task Scheduler يعتبر المهمة انتهت
- يوقف كل العمليات الفرعية (php.exe)

---

## ✅ الحل النهائي: تشغيل PHP مباشرة

### الطريقة الجديدة:

```
Task Scheduler
    ↓
php.exe system_runner.php (يشتغل مباشرة)
    ↓
Task Scheduler يعرف أن المهمة لسه شغالة ✅
```

**المميزات:**
- ✅ لا يوجد VBS أو BAT وسيط
- ✅ Task Scheduler يراقب php.exe مباشرة
- ✅ لن يتوقف أبداً
- ✅ يسجل كل شيء في log

---

## 🚀 خطوات التطبيق النهائية

### 1️⃣ احذف المهمة القديمة:

```batch
# انقر بالزر الأيمن → Run as administrator
remove_from_task_scheduler.bat
```

---

### 2️⃣ أضف المهمة الجديدة (Direct PHP):

```batch
# انقر بالزر الأيمن → Run as administrator
add_to_task_scheduler_direct.bat
```

**ماذا يفعل؟**
- يبحث عن PHP في النظام
- ينشئ مهمة تشغل `php.exe system_runner.php` مباشرة
- يضبط جميع الإعدادات للتشغيل المستمر

---

### 3️⃣ تحقق من المهمة:

```
1. افتح Task Scheduler (Win + R → taskschd.msc)
2. ابحث عن: TCPDF_Job_Queue_System
3. انقر بالزر الأيمن → Run
4. راقب "Status" يجب أن يبقى "Running" ✅
```

---

### 4️⃣ تحقق من Task Manager:

```
1. افتح Task Manager (Ctrl + Shift + Esc)
2. تبويب "Details"
3. ابحث عن: php.exe
4. يجب أن تجد عملية واحدة على الأقل ✅
```

---

### 5️⃣ تحقق من الـ Log:

```
افتح: logs\system_runner.log

يجب أن تجد:
[2025-10-26 14:55:00] TCPDF System Runner Started
[2025-10-26 14:55:00] Cycle #1 Started
[2025-10-26 14:55:00] [1/2] Running Watcher...
[2025-10-26 14:55:01] [2/2] Running Worker...
[2025-10-26 14:55:01] Cycle #1 Completed
[2025-10-26 14:55:01] Waiting 5 minutes...
... (يتكرر كل 5 دقائق) ✅
```

---

## 📊 المقارنة

### الطريقة القديمة ❌:

```
Task Scheduler → VBS → BAT → PHP
                 ↑
            ينتهي فوراً
            Task Scheduler يوقف كل شيء
```

### الطريقة الجديدة ✅:

```
Task Scheduler → PHP (مباشرة)
                 ↑
            يبقى شغال
            Task Scheduler يعرف أنه شغال
```

---

## 🔍 كيف يعمل system_runner.php؟

```php
<?php
// 1. إعداد المسارات
$watcherFile = 'scripts/watcher.php';
$workerFile = 'scripts/worker.php';

// 2. حلقة لا نهائية
while (true) {
    
    // 3. تشغيل Watcher
    shell_exec("php $watcherFile");
    
    // 4. انتظار 5 ثواني
    sleep(5);
    
    // 5. تشغيل Worker
    shell_exec("php $workerFile");
    
    // 6. انتظار 5 دقائق
    sleep(300);
    
    // 7. إعادة الحلقة
}
?>
```

**المميزات:**
- ✅ PHP native (لا يحتاج BAT أو VBS)
- ✅ `sleep()` يشتغل بشكل موثوق
- ✅ يسجل كل output في log
- ✅ سهل التعديل والصيانة

---

## 🧪 الاختبار الكامل

### بعد تطبيق الحل:

#### 1. تحقق من Task Scheduler:

```
Task Scheduler → TCPDF_Job_Queue_System
├── Status: Running ✅
├── Last Run Time: (وقت تسجيل الدخول)
├── Last Run Result: (0x41301) - Task is currently running ✅
└── Next Run Time: At log on
```

#### 2. تحقق من Task Manager:

```
Task Manager → Details → php.exe
├── Name: php.exe
├── PID: (رقم العملية)
├── Status: Running ✅
├── User name: (اسم المستخدم)
└── Command line: php.exe "...\system_runner.php" ✅
```

#### 3. تحقق من الـ Log:

```powershell
# افتح PowerShell في مجلد المشروع
Get-Content logs\system_runner.log -Tail 20 -Wait

# يجب أن ترى تحديثات كل 5 دقائق ✅
```

---

## 🛠️ استكشاف الأخطاء

### إذا المهمة لسه بتختفي:

#### 1. تحقق من PHP في PATH:

```batch
# افتح CMD
where php

# يجب أن يظهر مسار PHP:
C:\php\php.exe
```

إذا لم يظهر:
```batch
# أضف PHP للـ PATH:
# Control Panel → System → Advanced → Environment Variables
# أضف مسار PHP لـ PATH
```

#### 2. جرب تشغيل يدوي:

```batch
# افتح CMD في مجلد المشروع
cd e:\programs\composer\htdocs\TCPDF\tcpdf_job_queue_stock_moves_v2

# شغل الملف يدوياً
php system_runner.php

# يجب أن يشتغل ويبقى شغال ✅
```

#### 3. افحص Task Scheduler History:

```
Task Scheduler → TCPDF_Job_Queue_System
→ تبويب "History" (أسفل)
→ ابحث عن أخطاء:
   - Event ID 103: Task started
   - Event ID 102: Task completed (لا يجب أن يظهر!)
   - Event ID 111: Task terminated (خطأ!)
```

#### 4. افحص إعدادات المهمة:

```
Task Scheduler → TCPDF_Job_Queue_System
→ Properties → Settings

تأكد من:
☐ Stop the task if it runs longer than: [UNCHECKED] ✅
☐ Stop if the computer switches to battery power [UNCHECKED] ✅
```

---

## 💡 نصائح إضافية

### 1. لمراقبة النظام في الوقت الفعلي:

```batch
# افتح PowerShell
Get-Content logs\system_runner.log -Tail 50 -Wait
```

### 2. لإعادة تشغيل المهمة يدوياً:

```
Task Scheduler → TCPDF_Job_Queue_System
→ انقر بالزر الأيمن → End (إيقاف)
→ انقر بالزر الأيمن → Run (تشغيل)
```

### 3. لإيقاف النظام مؤقتاً:

```
Task Manager → Details → php.exe
→ انقر بالزر الأيمن → End task
```

### 4. للتشغيل اليدوي (للاختبار):

```batch
# افتح CMD في مجلد المشروع
php system_runner.php
```

---

## 📋 ملخص سريع

### المشكلة:
```
Task Scheduler يبدأ المهمة لكن تختفي فوراً
السبب: VBS ينتهي فوراً، Task Scheduler يوقف العمليات الفرعية
```

### الحل:
```batch
1. remove_from_task_scheduler.bat (كـ Admin)
2. add_to_task_scheduler_direct.bat (كـ Admin)
3. تحقق من Task Scheduler → Status: Running ✅
4. تحقق من Task Manager → php.exe موجود ✅
5. تحقق من logs\system_runner.log ✅
```

### التحقق النهائي:
```
✅ Task Scheduler → Status: Running
✅ Task Manager → php.exe موجود
✅ logs\system_runner.log يتحدث كل 5 دقائق
```

---

## 🎯 النتيجة المتوقعة

بعد تطبيق هذا الحل:

```
Task Scheduler:
└── TCPDF_Job_Queue_System
    ├── Status: Running (لن يتوقف أبداً) ✅
    ├── Command: php.exe system_runner.php
    └── Working Directory: (مجلد المشروع)

Task Manager:
└── Details
    └── php.exe
        ├── Status: Running ✅
        ├── CPU: ~0% (في وضع الانتظار)
        ├── Memory: ~20 MB
        └── Command: php.exe "...\system_runner.php"

Logs:
└── logs\system_runner.log
    └── يتحدث كل 5 دقائق ✅
```

---

## 🆘 إذا لم ينجح أي حل

### الخيار الأخير: Windows Service

إذا Task Scheduler مازال لا يشتغل، يمكن تحويل النظام إلى **Windows Service** باستخدام:

- **NSSM** (Non-Sucking Service Manager)
- أو **WinSW** (Windows Service Wrapper)

**هل تريد شرح كيفية تحويله إلى Windows Service؟**

---

**هذا هو الحل النهائي الأكثر موثوقية!** 🎉

**تم إنشاء هذا الملف في: 26 أكتوبر 2025**
