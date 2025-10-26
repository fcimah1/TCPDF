<?php
/**
 * TCPDF Job Queue System - Main Runner
 * Runs continuously in background
 */

// Set paths
$projectPath = __DIR__ . DIRECTORY_SEPARATOR;
$logFile = $projectPath . 'logs' . DIRECTORY_SEPARATOR . 'system_runner.log';

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
logMessage("TCPDF System Runner Started");
logMessage("========================================");

// Check if watcher and worker files exist
$watcherFile = $projectPath . 'scripts' . DIRECTORY_SEPARATOR . 'watcher.php';
$workerFile = $projectPath . 'scripts' . DIRECTORY_SEPARATOR . 'worker.php';

if (!file_exists($watcherFile)) {
    logMessage("ERROR: watcher.php not found at: $watcherFile");
    exit(1);
}

if (!file_exists($workerFile)) {
    logMessage("ERROR: worker.php not found at: $workerFile");
    exit(1);
}

logMessage("Files verified successfully");
logMessage("Starting infinite loop...");
logMessage("");

// Infinite loop
$cycleCount = 0;
while (true) {
    $cycleCount++;
    
    logMessage("========================================");
    logMessage("Cycle #$cycleCount Started");
    logMessage("========================================");
    
    // Run Watcher
    logMessage("[1/2] Running Watcher...");
    $watcherOutput = shell_exec("php \"$watcherFile\" 2>&1");
    if ($watcherOutput) {
        logMessage($watcherOutput);
    }
    
    // Wait 5 seconds
    logMessage("Waiting 5 seconds...");
    sleep(5);
    
    // Run Worker
    logMessage("[2/2] Running Worker...");
    $workerOutput = shell_exec("php \"$workerFile\" 2>&1");
    if ($workerOutput) {
        logMessage($workerOutput);
    }
    
    logMessage("========================================");
    logMessage("Cycle #$cycleCount Completed");
    logMessage("========================================");
    logMessage("Waiting 1 minute before next cycle...");
    logMessage("");
    
    // Wait 1 minute (60 seconds)
    sleep(60);
}
