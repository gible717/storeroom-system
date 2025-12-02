@echo off
echo ========================================
echo Import Database to Laragon
echo ========================================
echo.

echo Step 1: Creating database...
cd C:\laragon\bin\mysql\mysql-8.0.30\bin

mysql -u root -e "CREATE DATABASE IF NOT EXISTS storeroom_db CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci;"

if %errorlevel% equ 0 (
    echo ✓ Database 'storeroom_db' created successfully
) else (
    echo ✗ Failed to create database
    pause
    exit
)

echo.
echo Step 2: Importing SQL backup...
echo Please enter the full path to your SQL backup file.
echo Example: C:\xampp_config_backup\storeroom_db_export.sql
echo.
set /p SQL_FILE="SQL file path: "

if not exist "%SQL_FILE%" (
    echo ✗ File not found: %SQL_FILE%
    pause
    exit
)

mysql -u root storeroom_db < "%SQL_FILE%"

if %errorlevel% equ 0 (
    echo.
    echo ✓ Database imported successfully!
    echo ✓ Database: storeroom_db
    echo ✓ Location: Laragon MySQL
) else (
    echo ✗ Import failed
)

echo.
echo ========================================
pause
