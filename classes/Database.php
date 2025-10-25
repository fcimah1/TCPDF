<?php
class Database {
    private $pdo;

    public function __construct($pdo) {
        $this->pdo = $pdo;
    }

    /**
     * الحصول على آخر trans_id تمت معالجته
     */
    public function getLastProcessedId() {
        $stmt = $this->pdo->query("SELECT last_trans_id FROM processed_checkpoints ORDER BY id DESC LIMIT 1");
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if ($result) {
            return (int)$result['last_trans_id'];
        }
        
        // إذا لم يكن هناك checkpoint، ابدأ من أقل trans_id في الجدول - 1
        $minId = $this->pdo->query("SELECT MIN(trans_id) FROM 0_stock_moves")->fetchColumn();
        return $minId ? (int)$minId - 1 : 0;
    }

    /**
     * حفظ آخر trans_id تمت معالجته
     */
    public function saveLastProcessedId($id, $batchSize = 1000) {
        $stmt = $this->pdo->prepare("INSERT INTO processed_checkpoints (last_trans_id, batch_size) VALUES (?, ?)");
        $stmt->execute([$id, $batchSize]);
    }

    /**
     * جلب بيانات جديدة من جدول 0_stock_moves
     */
    public function getNewStockMoves($lastId, $limit = 1000) {
        $limit = (int)$limit;
        $sql = "SELECT * FROM 0_stock_moves WHERE trans_id > ? ORDER BY trans_id ASC LIMIT $limit";
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([$lastId]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * جلب بيانات من نطاق محدد من trans_id (من startId إلى endId)
     */
    public function getStockMovesByRange($startId, $endId) {
        $sql = "SELECT * FROM 0_stock_moves WHERE trans_id >= ? AND trans_id <= ? ORDER BY trans_id ASC";
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([$startId, $endId]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * الحصول على أقصى trans_id في الجدول
     */
    public function getMaxStockMoveId() {
        return $this->pdo->query("SELECT MAX(trans_id) FROM 0_stock_moves")->fetchColumn() ?: 0;
    }

    /**
     * إضافة مهمة جديدة إلى الـ Queue
     */
    public function addQueueJob($startTransId, $endTransId) {
        $stmt = $this->pdo->prepare(
            "INSERT INTO queue_jobs (start_trans_id, end_trans_id, status) VALUES (?, ?, 'pending')"
        );
        $stmt->execute([$startTransId, $endTransId]);
        return $this->pdo->lastInsertId();
    }

    /**
     * الحصول على المهام المعلقة
     */
    public function getPendingJobs($limit = 10) {
        $limit = (int)$limit; // تحويل لرقم صحيح للأمان
        $sql = "SELECT * FROM queue_jobs WHERE status = 'pending' ORDER BY id ASC LIMIT $limit";
        $stmt = $this->pdo->query($sql);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * تحديث حالة المهمة
     */
    public function updateJobStatus($jobId, $status, $lockedBy = null) {
        if ($status === 'processing' && $lockedBy) {
            $stmt = $this->pdo->prepare(
                "UPDATE queue_jobs SET status = ?, locked_by = ?, locked_at = NOW(), attempts = attempts + 1 WHERE id = ?"
            );
            $stmt->execute([$status, $lockedBy, $jobId]);
        } else {
            $stmt = $this->pdo->prepare(
                "UPDATE queue_jobs SET status = ?, locked_by = NULL, locked_at = NULL WHERE id = ?"
            );
            $stmt->execute([$status, $jobId]);
        }
    }

    /**
     * حفظ معلومات التقرير المولد
     */
    public function saveReportBatch($fromId, $toId, $filePath) {
        $stmt = $this->pdo->prepare(
            "INSERT INTO report_batches (from_id, to_id, file_path) VALUES (?, ?, ?)"
        );
        $stmt->execute([$fromId, $toId, $filePath]);
    }

    /**
     * تسجيل رسالة في الـ Log
     */
    public function log($level, $message, $jobId = null) {
        $stmt = $this->pdo->prepare(
            "INSERT INTO queue_logs (job_id, level, message) VALUES (?, ?, ?)"
        );
        $stmt->execute([$jobId, $level, $message]);
    }

    /**
     * الحصول على إحصائيات النظام
     */
    public function getStats() {
        $stats = [];
        
        // عدد المهام حسب الحالة
        $stmt = $this->pdo->query(
            "SELECT status, COUNT(*) as count FROM queue_jobs GROUP BY status"
        );
        $stats['jobs_by_status'] = $stmt->fetchAll(PDO::FETCH_KEY_PAIR);
        
        // عدد التقارير المولدة
        $stats['total_reports'] = $this->pdo->query(
            "SELECT COUNT(*) FROM report_batches"
        )->fetchColumn();
        
        // آخر معالجة
        $stmt = $this->pdo->query(
            "SELECT last_trans_id, created_at FROM processed_checkpoints ORDER BY id DESC LIMIT 1"
        );
        $stats['last_checkpoint'] = $stmt->fetch(PDO::FETCH_ASSOC);
        
        return $stats;
    }
}
