@echo off
echo ========================================================
echo         Starting UniPass Visitor Management System
echo ========================================================
echo.
echo  DB Strategy: MySQL (Local) -- TiDB Cloud (Online Fallback)
echo  ^> If MySQL/XAMPP is running  : uses LOCAL MySQL
echo  ^> If MySQL is NOT running    : auto-switches to TiDB Cloud
echo.
echo Server is running at: http://localhost:8000
echo Press Ctrl+C in this window to stop the server.
echo.

:: Open the default web browser to the login page
start http://localhost:8000/login.php

:: Check if XAMPP PHP exists, otherwise use system PHP
if exist C:\xampp\php\php.exe (
    C:\xampp\php\php.exe -S localhost:8000
) else (
    php.exe -S localhost:8000
)

pause
