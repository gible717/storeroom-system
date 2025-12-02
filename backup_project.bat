@echo off
echo ========================================
echo Storeroom Project Backup
echo ========================================
echo.

REM Create backup folder with timestamp
for /f "tokens=2 delims==" %%I in ('wmic os get localdatetime /value') do set datetime=%%I
set BACKUP_DATE=%datetime:~0,8%
set BACKUP_DIR=C:\storeroom_backup_%BACKUP_DATE%

echo Creating project backup: %BACKUP_DIR%
echo.

REM Copy entire storeroom folder
xcopy "C:\xampp\htdocs\storeroom" "%BACKUP_DIR%\" /E /I /H /Y

echo.
echo ========================================
echo Backup completed!
echo Location: %BACKUP_DIR%
echo ========================================
echo.

explorer "%BACKUP_DIR%"
pause
