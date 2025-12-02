@echo off
echo ========================================
echo XAMPP Configuration Backup Script
echo ========================================
echo.

REM Create backup directory
set BACKUP_DIR=C:\xampp_config_backup
if not exist "%BACKUP_DIR%" mkdir "%BACKUP_DIR%"

echo Creating backup folder: %BACKUP_DIR%
echo.

REM Backup PHP configuration
echo [1/5] Backing up php.ini...
copy "C:\xampp\php\php.ini" "%BACKUP_DIR%\php.ini" >nul 2>&1
if %errorlevel% equ 0 (
    echo ✓ php.ini backed up successfully
) else (
    echo ✗ Failed to backup php.ini
)

REM Backup MySQL configuration
echo [2/5] Backing up my.ini...
copy "C:\xampp\mysql\bin\my.ini" "%BACKUP_DIR%\my.ini" >nul 2>&1
if %errorlevel% equ 0 (
    echo ✓ my.ini backed up successfully
) else (
    echo ✗ Failed to backup my.ini
)

REM Backup Apache configuration
echo [3/5] Backing up httpd.conf...
copy "C:\xampp\apache\conf\httpd.conf" "%BACKUP_DIR%\httpd.conf" >nul 2>&1
if %errorlevel% equ 0 (
    echo ✓ httpd.conf backed up successfully
) else (
    echo ✗ Failed to backup httpd.conf
)

REM Backup .htaccess if exists
echo [4/5] Backing up .htaccess (if exists)...
if exist "C:\xampp\htdocs\storeroom\.htaccess" (
    copy "C:\xampp\htdocs\storeroom\.htaccess" "%BACKUP_DIR%\.htaccess" >nul 2>&1
    echo ✓ .htaccess backed up successfully
) else (
    echo ○ No .htaccess file found (OK)
)

REM Create configuration summary
echo [5/5] Creating configuration summary...
echo XAMPP Configuration Backup > "%BACKUP_DIR%\backup_info.txt"
echo Date: %date% %time% >> "%BACKUP_DIR%\backup_info.txt"
echo. >> "%BACKUP_DIR%\backup_info.txt"
echo Files backed up: >> "%BACKUP_DIR%\backup_info.txt"
echo - php.ini >> "%BACKUP_DIR%\backup_info.txt"
echo - my.ini >> "%BACKUP_DIR%\backup_info.txt"
echo - httpd.conf >> "%BACKUP_DIR%\backup_info.txt"
if exist "C:\xampp\htdocs\storeroom\.htaccess" echo - .htaccess >> "%BACKUP_DIR%\backup_info.txt"
echo ✓ Summary created

echo.
echo ========================================
echo Backup completed!
echo Location: %BACKUP_DIR%
echo ========================================
echo.

REM Open backup folder
explorer "%BACKUP_DIR%"

pause
