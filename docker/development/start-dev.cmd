@echo off
REM Windows batch script wrapper for start-dev.ps1
setlocal enabledelayedexpansion

REM Get the directory where this script is located
set "SCRIPT_DIR=%~dp0"

REM Run PowerShell with the start-dev.ps1 script
powershell -NoProfile -ExecutionPolicy Bypass -File "!SCRIPT_DIR!start-dev.ps1" %*

exit /b %ERRORLEVEL%
