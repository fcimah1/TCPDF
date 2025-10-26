<?php
/**
 * TCPDF Job Queue System - Worker Runner
 * Runs worker continuously to process pending jobs
 */

// Set paths
$projectPath = __DIR__ . DIRECTORY_SEPARATOR;
$logFile = $projectPath . 'logs' . DIRECTORY_SEPARATOR . 'worker_runner.log';

// Create logs directory if not exists
if (!file_exists($projectPath . 'logs')) {
    mkdir($projectPath . 'logs', 0777, true);
}

// Log function
function logMessage($message) {
    global $logFile;
    $timestamp = date('Y-m-d H:i:s');
    file_put_contents($logFile, "[$timestamp] $message\n", FILE_APPEND);
    echo "[$timestamp] $message\n";
}

// Log start
logMessage("========================================");
logMessage("TCPDF Worker Runner Started");
logMessage("========================================");

// Check if worker file exists
$workerFile = $projectPath . 'scripts' . DIRECTORY_SEPARATOR . 'worker.php';

if (!file_exists($workerFile)) {
    logMessage("ERROR: worker.php not found at: $workerFile");
    exit(1);
}

logMessage("Worker file verified successfully");
logMessage("Starting infinite loop...");
logMessage("");

// Infinite loop
$cycleCount = 0;
while (true) {
    $cycleCount++;
    
    logMessage("========================================");
    logMessage("Worker Cycle #$cycleCount Started");
    logMessage("========================================");
    
    // Run Worker
    $workerOutput = shell_exec("php \"$workerFile\" 2>&1");
    if ($workerOutput) {
        logMessage($workerOutput);
    }
    
    logMessage("========================================");
    logMessage("Worker Cycle #$cycleCount Completed");
    logMessage("========================================");
    logMessage("Waiting 10 seconds before next cycle...");
    logMessage("");
    
    // Wait 10 seconds before next cycle
    sleep(10);
}
