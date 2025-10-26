' ====================================
' TCPDF System Runner - Hidden Launcher
' Runs PHP in completely hidden mode
' ====================================

Set WshShell = CreateObject("WScript.Shell")
Set FSO = CreateObject("Scripting.FileSystemObject")

' Get current folder path
strPath = FSO.GetParentFolderName(WScript.ScriptFullName)

' Find PHP executable
strPHP = "php.exe"

' Path to system_runner.php
strPHPFile = strPath & "\system_runner.php"

' Check if PHP file exists
If Not FSO.FileExists(strPHPFile) Then
    MsgBox "Error: system_runner.php not found!" & vbCrLf & "Path: " & strPHPFile, vbCritical, "File Not Found"
    WScript.Quit 1
End If

' Run PHP in completely hidden mode
' 0 = Hide window completely
' False = Do not wait for execution to finish
WshShell.Run strPHP & " """ & strPHPFile & """", 0, False

' Clean up
Set FSO = Nothing
Set WshShell = Nothing
