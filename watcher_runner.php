<?php
/**
 * TCPDF Job Queue System - Watcher Runner
 * Runs watcher every 1 minute continuously
 */

// Set paths
$projectPath = __DIR__ . DIRECTORY_SEPARATOR;
$logFile = $projectPath . 'logs' . DIRECTORY_SEPARATOR . 'watcher_runner.log';

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
logMessage("TCPDF Watcher Runner Started");
logMessage("========================================");

// Check if watcher file exists
$watcherFile = $projectPath . 'scripts' . DIRECTORY_SEPARATOR . 'watcher.php';

if (!file_exists($watcherFile)) {
    logMessage("ERROR: watcher.php not found at: $watcherFile");
    exit(1);
}

logMessage("Watcher file verified successfully");
logMessage("Starting infinite loop...");
logMessage("");

// Infinite loop
$cycleCount = 0;
while (true) {
    $cycleCount++;
    
    logMessage("========================================");
    logMessage("Watcher Cycle #$cycleCount Started");
    logMessage("========================================");
    
    // Run Watcher
    $watcherOutput = shell_exec("php \"$watcherFile\" 2>&1");
    if ($watcherOutput) {
        logMessage($watcherOutput);
    }
    
    logMessage("========================================");
    logMessage("Watcher Cycle #$cycleCount Completed");
    logMessage("========================================");
    logMessage("Waiting 1 minute before next cycle...");
    logMessage("");
    
    // Wait 1 minute (60 seconds)
    sleep(60);
}
