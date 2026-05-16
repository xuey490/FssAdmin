@echo off
:loop
echo Starting Workerman Server...
php server3.php start
echo Server stopped, restarting in 1 second...
timeout /t 1 /nobreak >nul
goto loop