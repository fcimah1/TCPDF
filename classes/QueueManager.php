<?php
class QueueManager {
    private $queueFile;

    public function __construct($queueFile = null) {
        // حدد المسار الافتراضي للـ queue file
        if ($queueFile === null) {
            $queueFile = __DIR__ . '/../logs/queue.json';
        }

        $this->queueFile = $queueFile;

        // تأكد أن المجلد موجود
        $dir = dirname($this->queueFile);
        if (!is_dir($dir)) {
            mkdir($dir, 0777, true);
        }

        // لو الملف مش موجود، أنشئه فاضي
        if (!file_exists($this->queueFile)) {
            file_put_contents($this->queueFile, json_encode([]));
        }
    }

    public function addJob($job) {
        $jobs = $this->getJobs();
        $jobs[] = $job;
        file_put_contents($this->queueFile, json_encode($jobs, JSON_PRETTY_PRINT));
    }

    public function getJobs() {
        $content = file_get_contents($this->queueFile);
        return json_decode($content, true) ?: [];
    }

    public function clearJobs() {
        file_put_contents($this->queueFile, json_encode([]));
    }
}
