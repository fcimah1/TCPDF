@echo off
title Remove TCPDF System from Windows Task Scheduler

echo.
echo ========================================
echo   Remove TCPDF System from Task Scheduler
echo ========================================
echo.

set TASK_NAME=TCPDF_Job_Queue_System

echo [INFO] Task Name: %TASK_NAME%
echo.

REM Check if task exists
schtasks /query /tn "%TASK_NAME%" >nul 2>&1
if %errorlevel% neq 0 (
    echo [WARNING] Task not found in Task Scheduler!
    echo [INFO] The system is not configured to auto-start.
    echo.
    pause
    exit /b 0
)

echo [INFO] Removing task from Task Scheduler...
echo.

REM Delete the task
schtasks /delete /tn "%TASK_NAME%" /f

if %errorlevel% equ 0 (
    echo.
    echo ========================================
    echo [SUCCESS] Task removed successfully!
    echo ========================================
    echo.
    echo The TCPDF Job Queue System will no longer start
    echo automatically when you log in to Windows.
    echo.
) else (
    echo.
    echo [ERROR] Failed to remove task!
    echo [ERROR] Please run this script as Administrator
    echo.
)

pause
