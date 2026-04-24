@echo off
echo.
echo ========================================
echo   HandyCRM AI Test Agent
echo ========================================
echo.
echo Starting automated tests...
echo.

cd /d "%~dp0"
node test-agent.js

echo.
echo Test complete! Opening report...
start test-report.html

pause
