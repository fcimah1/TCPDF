@echo off
title Add TCPDF System to Task Scheduler (Direct PHP)

echo.
echo ========================================
echo   Add TCPDF System to Task Scheduler
echo   (Direct PHP Execution)
echo ========================================
echo.

set PROJECT_PATH=%~dp0
set VBS_FILE=%PROJECT_PATH%start_hidden.vbs
set TASK_NAME=TCPDF_Job_Queue_System

echo [INFO] Project Path: %PROJECT_PATH%
echo [INFO] VBS Launcher: %VBS_FILE%
echo [INFO] Task Name: %TASK_NAME%
echo.

REM Check if VBS file exists
if not exist "%VBS_FILE%" (
    echo [ERROR] start_hidden.vbs not found!
    pause
    exit /b 1
)

REM Check if task already exists
schtasks /query /tn "%TASK_NAME%" >nul 2>&1
if %errorlevel% equ 0 (
    echo [WARNING] Task already exists! Deleting old task...
    schtasks /delete /tn "%TASK_NAME%" /f >nul 2>&1
    echo.
)

echo [INFO] Creating scheduled task (Direct PHP execution)...
echo.

REM Create XML configuration
set XML_FILE=%PROJECT_PATH%task_config_direct.xml

(
echo ^<?xml version="1.0" encoding="UTF-16"?^>
echo ^<Task version="1.2" xmlns="http://schemas.microsoft.com/windows/2004/02/mit/task"^>
echo   ^<RegistrationInfo^>
echo     ^<Description^>TCPDF Job Queue System - Direct PHP Runner^</Description^>
echo     ^<Author^>%USERNAME%^</Author^>
echo   ^</RegistrationInfo^>
echo   ^<Triggers^>
echo     ^<LogonTrigger^>
echo       ^<Enabled^>true^</Enabled^>
echo       ^<UserId^>%USERDOMAIN%\%USERNAME%^</UserId^>
echo     ^</LogonTrigger^>
echo   ^</Triggers^>
echo   ^<Principals^>
echo     ^<Principal id="Author"^>
echo       ^<UserId^>%USERDOMAIN%\%USERNAME%^</UserId^>
echo       ^<LogonType^>InteractiveToken^</LogonType^>
echo       ^<RunLevel^>HighestAvailable^</RunLevel^>
echo     ^</Principal^>
echo   ^</Principals^>
echo   ^<Settings^>
echo     ^<MultipleInstancesPolicy^>IgnoreNew^</MultipleInstancesPolicy^>
echo     ^<DisallowStartIfOnBatteries^>false^</DisallowStartIfOnBatteries^>
echo     ^<StopIfGoingOnBatteries^>false^</StopIfGoingOnBatteries^>
echo     ^<AllowHardTerminate^>false^</AllowHardTerminate^>
echo     ^<StartWhenAvailable^>true^</StartWhenAvailable^>
echo     ^<RunOnlyIfNetworkAvailable^>false^</RunOnlyIfNetworkAvailable^>
echo     ^<IdleSettings^>
echo       ^<StopOnIdleEnd^>false^</StopOnIdleEnd^>
echo       ^<RestartOnIdle^>false^</RestartOnIdle^>
echo     ^</IdleSettings^>
echo     ^<AllowStartOnDemand^>true^</AllowStartOnDemand^>
echo     ^<Enabled^>true^</Enabled^>
echo     ^<Hidden^>false^</Hidden^>
echo     ^<RunOnlyIfIdle^>false^</RunOnlyIfIdle^>
echo     ^<WakeToRun^>false^</WakeToRun^>
echo     ^<ExecutionTimeLimit^>PT0S^</ExecutionTimeLimit^>
echo     ^<Priority^>7^</Priority^>
echo   ^</Settings^>
echo   ^<Actions Context="Author"^>
echo     ^<Exec^>
echo       ^<Command^>wscript.exe^</Command^>
echo       ^<Arguments^>"%VBS_FILE%"^</Arguments^>
echo       ^<WorkingDirectory^>%PROJECT_PATH%^</WorkingDirectory^>
echo     ^</Exec^>
echo   ^</Actions^>
echo ^</Task^>
) > "%XML_FILE%"

REM Import XML configuration
schtasks /create /tn "%TASK_NAME%" /xml "%XML_FILE%" /f

if errorlevel 1 (
    echo.
    echo [ERROR] Failed to create task!
    echo [ERROR] Please run this script as Administrator
    echo.
    if exist "%XML_FILE%" del "%XML_FILE%"
    pause
    exit /b 1
)

REM Delete temporary XML file
if exist "%XML_FILE%" del "%XML_FILE%"

echo.
echo ========================================
echo [SUCCESS] Task created successfully!
echo ========================================
echo.
echo The TCPDF Job Queue System will now start automatically
echo when you log in to Windows.
echo.
echo Task Name: %TASK_NAME%
echo Command: wscript.exe start_hidden.vbs
echo.
echo [IMPORTANT] This version:
echo - Runs completely hidden (no CMD window)
echo - Stays running continuously (won't stop when you close windows)
echo - Checks for new data every 1 minute
echo - Logs to: logs\system_runner.log
echo - Will NOT stop on battery power
echo - Execution time: Unlimited
echo.
echo To verify it's running:
echo 1. Open Task Manager (Ctrl+Shift+Esc)
echo 2. Go to Details tab
echo 3. Look for: php.exe (should be running)
echo 4. Check log: logs\system_runner.log (updates every minute)
echo.
echo To manually start the task:
echo - Open Task Scheduler (taskschd.msc)
echo - Right-click "%TASK_NAME%" and select "Run"
echo.
echo IMPORTANT: The system runs in background.
echo You won't see any CMD window, but php.exe will be in Task Manager.
echo.

pause
