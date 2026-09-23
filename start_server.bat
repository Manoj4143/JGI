@echo off
title Automatic Timetable Generator Server
echo ========================================================
echo   Automatic Timetable Generator - Local Server
echo ========================================================
echo.

set PHP_EXE=C:\Users\manoj\AppData\Local\Microsoft\WinGet\Packages\PHP.PHP.8.1_Microsoft.Winget.Source_8wekyb3d8bbwe\php.exe
set PHP_INI=C:\Users\manoj\AppData\Local\Microsoft\WinGet\Packages\PHP.PHP.8.1_Microsoft.Winget.Source_8wekyb3d8bbwe\php.ini

if not exist "%PHP_EXE%" (
    echo Error: PHP executable not found at %PHP_EXE%
    pause
    exit /b 1
)

echo Starting PHP built-in server on http://127.0.0.1:8000 ...
start "" "http://127.0.0.1:8000/"
"%PHP_EXE%" -S 127.0.0.1:8000 -t "%~dp0." -c "%PHP_INI%"
pause
