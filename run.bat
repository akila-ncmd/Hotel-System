@echo off
setlocal EnableDelayedExpansion

REM ============================================================
REM  Diamond Shine - local development launcher
REM
REM  Usage:
REM     run.bat                 start the app on port 8000
REM     run.bat --port 8080     start on a different port
REM     run.bat --vite          also start the Vite dev server
REM                             (only needed when editing resources/js)
REM     run.bat --help
REM ============================================================

pushd "%~dp0"

set "PORT=8000"
set "WITH_VITE=0"

REM ---------- parse arguments ----------
:parse_args
if "%~1"=="" goto args_done
if /i "%~1"=="--help"  goto show_help
if /i "%~1"=="-h"      goto show_help
if /i "%~1"=="--vite"  ( set "WITH_VITE=1" & shift & goto parse_args )
if /i "%~1"=="--port"  ( set "PORT=%~2"    & shift & shift & goto parse_args )
echo [warn] Unknown option: %~1
echo     Run "run.bat --help" to see the available options.
goto fail
:args_done

echo.
echo ============================================
echo   Diamond Shine - starting local dev server
echo ============================================
echo.

REM ---------- PHP ----------
where php >nul 2>nul
if errorlevel 1 (
    echo [X] PHP was not found on your PATH.
    echo     Install PHP 8.1+ ^(or start XAMPP/Laragon^) and try again.
    goto fail
)
for /f "tokens=2 delims= " %%v in ('php -r "echo 'PHP '.PHP_VERSION;" 2^>nul') do set "PHPVER=%%v"
echo [ok] PHP !PHPVER!

REM ---------- Composer dependencies ----------
if not exist "vendor\autoload.php" (
    echo [..] Installing PHP dependencies ^(first run^)...
    where composer >nul 2>nul
    if errorlevel 1 (
        echo [X] vendor\ is missing and Composer is not on your PATH.
        echo     Install Composer, then run: composer install
        goto fail
    )
    call composer install --no-interaction
    if errorlevel 1 goto fail
)
echo [ok] PHP dependencies

REM ---------- .env ----------
if not exist ".env" (
    echo [..] No .env found - creating one from .env.example...
    copy /y ".env.example" ".env" >nul
    if errorlevel 1 goto fail
    php artisan key:generate
    if errorlevel 1 goto fail
    echo.
    echo [warn] A fresh .env was created. Check the DB_ settings before continuing:
    echo     the project expects MySQL on port 3307, database "hotel_reservation".
    echo.
)
echo [ok] .env present

REM ---------- Frontend build ----------
REM Only the home page loads the Vite bundle (the WebGL hero). If it has never
REM been built, the hero silently falls back to the CSS parallax layers.
if not exist "public\build\manifest.json" (
    where npm >nul 2>nul
    if errorlevel 1 (
        echo [warn] public\build is missing and npm is not on your PATH.
        echo     The site will still run; the WebGL hero falls back to CSS.
    ) else (
        if not exist "node_modules" (
            echo [..] Installing npm packages ^(first run^)...
            call npm install
            if errorlevel 1 goto fail
        )
        echo [..] Building frontend assets...
        call npm run build
        if errorlevel 1 goto fail
    )
)
echo [ok] Frontend assets

REM ---------- Clear stale caches ----------
REM Cheap, and prevents "why is the old brand name still showing" confusion
REM after config or view changes.
call php artisan optimize:clear >nul 2>nul
echo [ok] Caches cleared

REM ---------- Database ----------
call php artisan migrate:status >nul 2>nul
if errorlevel 1 (
    echo.
    echo [X] Could not reach the database.
    echo     Start MySQL and confirm it is listening on the port in .env
    echo     ^(this project uses port 3307, database "hotel_reservation"^).
    echo.
    echo     In XAMPP: open the Control Panel and Start MySQL.
    echo.
    goto fail
)
echo [ok] Database reachable

REM Warn about pending migrations without applying them automatically -
REM changing the developer's schema on startup would be a surprise.
call php artisan migrate:status 2>nul | findstr /i /c:"Pending" >nul
if not errorlevel 1 (
    echo.
    echo [warn] You have pending migrations. To apply them:
    echo        php artisan migrate
    echo.
)

REM ---------- Vite (optional) ----------
if "!WITH_VITE!"=="1" (
    echo [..] Starting Vite dev server in a separate window...
    start "Diamond Shine - Vite" cmd /k npm run dev
)

REM ---------- Serve ----------
echo.
echo --------------------------------------------
echo   App:   http://localhost:!PORT!
echo.
echo   Sign in with password: password
echo     clerk@hotel.com     ^(clerk,    Colombo^)
echo     manager@hotel.com   ^(manager,  Colombo^)
echo     customer@hotel.com  ^(customer^)
echo.
echo   Login also requires the matching role, and
echo   the correct branch for staff accounts.
echo.
echo   Press Ctrl+C to stop the server.
echo --------------------------------------------
echo.

REM Open the browser shortly after the server comes up.
start "" /min cmd /c "timeout /t 3 >nul & start "" http://localhost:!PORT!"

php artisan serve --port=!PORT!

popd
endlocal
exit /b 0

REM ============================================================
:show_help
echo.
echo Diamond Shine - local development launcher
echo.
echo   run.bat                 Start the app on http://localhost:8000
echo   run.bat --port 8080     Start on a different port
echo   run.bat --vite          Also start the Vite dev server in a new window
echo                           ^(only needed when editing resources/js^)
echo   run.bat --help          Show this message
echo.
echo Prerequisites: PHP 8.1+, Composer, MySQL running on port 3307.
echo Node/npm are only needed to build the WebGL hero.
echo.
popd
endlocal
exit /b 0

REM ============================================================
:fail
echo.
echo Startup aborted.
echo.
popd
endlocal
pause
exit /b 1
