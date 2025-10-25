<?php
require_once __DIR__ . '/ReportGenerator.php';
require_once __DIR__ . '/Database.php';

class JobWorker {
    private $db;
    private $workerId;
    
    public function __construct(Database $db) {
        $this->db = $db;
        $this->workerId = 'worker_' . getmypid() . '_' . time();
    }
    
    /**
     * معالجة مهمة واحدة
     */
    public function processJob($job) {
        $jobId = $job['id'];
        $startTransId = $job['start_trans_id'];
        $endTransId = $job['end_trans_id'];
        
        try {
            // تحديث حالة المهمة إلى processing
            $this->db->updateJobStatus($jobId, 'تتم معالجة العمليات', $this->workerId);
            $this->db->log('INFO', "تتم معالجة العمليات #$jobId: $startTransId to $endTransId", $jobId);
            
            // جلب البيانات من النطاق المحدد بالضبط
            $data = $this->db->getStockMovesByRange($startTransId, $endTransId);
            
            if (empty($data)) {
                throw new Exception("لا يوجد بيانات  $startTransId to $endTransId");
            }
            
            // توليد التقرير
            $filepath = ReportGenerator::generate($data, $startTransId, $endTransId);
            
            // حفظ معلومات التقرير
            $this->db->saveReportBatch($startTransId, $endTransId, $filepath);
            
            // تحديث حالة المهمة إلى done
            $this->db->updateJobStatus($jobId, 'تم معالجة العمليات');
            $this->db->log('SUCCESS', "Job #$jobId completed. PDF: $filepath", $jobId);
            
            echo "[✅] Job #$jobId completed: $filepath\n";
            return true;
            
        } catch (Exception $e) {
            // تسجيل الخطأ
            $this->db->updateJobStatus($jobId, 'failed');
            $this->db->log('ERROR', $e->getMessage(), $jobId);
            
            echo "[❌] Job #$jobId failed: " . $e->getMessage() . "\n";
            return false;
        }
    }
    
    /**
     * معالجة جميع المهام المعلقة
     */
    public function processAllPendingJobs($limit = 1000) {
        $jobs = $this->db->getPendingJobs($limit);
        
        if (empty($jobs)) {
            echo "[💤] No pending jobs to process.\n";
            return 0;
        }
        
        $processed = 0;
        foreach ($jobs as $job) {
            if ($this->processJob($job)) {
                $processed++;
            }
        }
        
        echo "[\u2705] Processed $processed out of " . count($jobs) . " jobs.\n";
        return $processed;
    }
}
