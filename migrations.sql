-- migrations.sql

-- جدول الطابور (مهام توليد تقارير)
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
);

-- جدول checkpoints لتخزين آخر trans_id تمت معالجته
CREATE TABLE IF NOT EXISTS `processed_checkpoints` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `last_trans_id` INT NOT NULL,
  `batch_size` INT NOT NULL DEFAULT 1000,
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- جدول لتسجيل ملفات التقرير (history)
CREATE TABLE IF NOT EXISTS `report_batches` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `from_id` INT NOT NULL,
  `to_id` INT NOT NULL,
  `file_path` VARCHAR(255),
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- جدول لوج للأخطاء والتنبيهات
CREATE TABLE IF NOT EXISTS `queue_logs` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `job_id` INT NULL,
  `level` VARCHAR(20),
  `message` TEXT,
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

