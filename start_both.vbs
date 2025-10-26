Set WshShell = CreateObject("WScript.Shell")

' Start Watcher Runner
WshShell.Run "php.exe ""E:\programs\composer\htdocs\TCPDF\tcpdf_job_queue_stock_moves_v2\watcher_runner.php""", 0, False

' Wait 2 seconds
WScript.Sleep 2000

' Start Worker Runner
WshShell.Run "php.exe ""E:\programs\composer\htdocs\TCPDF\tcpdf_job_queue_stock_moves_v2\worker_runner.php""", 0, False

Set WshShell = Nothing

MsgBox "TCPDF System Started!" & vbCrLf & vbCrLf & "Watcher and Worker are now running in background.", vbInformation, "TCPDF System"
