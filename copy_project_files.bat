@echo off
echo ========================================
echo Copy Storeroom Project Files to Laragon
echo ========================================
echo.

echo Checking for source files...
echo.

if exist "C:\xampp\htdocs\storeroom\" (
    echo Found files in: C:\xampp\htdocs\storeroom\
    set SOURCE=C:\xampp\htdocs\storeroom
    goto COPY
)

echo Checking backup folders...
for /d %%i in (C:\storeroom_backup_*) do (
    if exist "%%i\" (
        echo Found files in: %%i
        set SOURCE=%%i
        goto COPY
    )
)

echo ✗ No source files found!
echo Please manually copy your storeroom files.
pause
exit

:COPY
echo.
echo Copying files from:
echo %SOURCE%
echo.
echo To:
echo C:\laragon\www\storeroom\
echo.
pause

xcopy "%SOURCE%\*" "C:\laragon\www\storeroom\" /E /I /H /Y /EXCLUDE:copy_project_files.bat

echo.
echo ========================================
echo Files copied successfully!
echo ========================================
echo.
pause

explorer "C:\laragon\www\storeroom\"
