@echo off
echo ========================================================
echo         Starting UniPass Visitor Management System
echo ========================================================
echo.
echo Server is running at: http://localhost:8000
echo Press Ctrl+C in this window to stop the server.
echo.

:: Open the default web browser to the login page
start http://localhost:8000/login.php

:: Start the PHP built-in web server using XAMPP's PHP executable
C:\xampp\php\php.exe -S localhost:8000

pause
