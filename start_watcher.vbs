Set WshShell = CreateObject("WScript.Shell")
WshShell.Run "php.exe ""E:\programs\composer\htdocs\TCPDF\tcpdf_job_queue_stock_moves_v2\watcher_runner.php""", 0, False
Set WshShell = Nothing
