@echo off
REM ===== Configuration =====
set "PLINK=C:\Program Files\PuTTY\plink.exe"
set SERVER_IP=192.168.1.100
set USERNAME=webserver
set PASSWORD=dapho04051983
set "REMOTE_DIR_FRONTEND=systems/Request-Form/Request-Form-Next-Js"
set "REMOTE_DIR_BACKEND=systems/Request-Form/Request-Form-Server"

REM ===== Run commands via PuTTY =====
echo.
echo.
call :ColorText 0a "==================================================================="
echo.
echo                       BUILDING AND DEPLOYING FRONTEND...
call :ColorText 0a "==================================================================="
echo.
"%PLINK%" -batch -ssh %USERNAME%@%SERVER_IP% -pw %PASSWORD% ^
  "cd %REMOTE_DIR_FRONTEND% && git pull && docker-compose down && docker-compose up --build -d"
echo.
echo.
echo.
REM ===== Execute =====
call :ColorText 0a "==================================================================="
echo.
call :ColorText 0a "============ !!! Building & Deployment completed !!! ============"
echo.
call :ColorText 0a "==================================================================="
echo.
echo.
echo.
call :ColorText 0a "0%%="
echo.
call :ColorText 0a "10%%====="
echo.
call :ColorText 0a "20%%=========="
echo.
call :ColorText 0a "30%%================"
echo.
call :ColorText 0a "40%%======================"
echo.
call :ColorText 0a "50%%============================"
echo.
call :ColorText 0a "60%%=================================="
echo.
call :ColorText 0a "70%%========================================"
echo.
call :ColorText 0a "80%%=============================================="
echo.
call :ColorText 0a "90%%==================================================="
echo.
call :ColorText 0a "100%%========================================================"
echo.
echo.

echo.
call :ColorText 0a "==================================================================="
echo.
echo                       BUILDING AND DEPLOYING BACKEND...
call :ColorText 0a "==================================================================="
echo.
echo.

"%PLINK%" -batch -ssh %USERNAME%@%SERVER_IP% -pw %PASSWORD% ^
  "cd %REMOTE_DIR_BACKEND% && git pull && docker-compose down && docker-compose up --build -d"
echo.
echo.
REM ===== Execute =====
echo.
call :ColorText 0a "==================================================================="
echo.
call :ColorText 0a "============ !!! Building & Deployment completed !!! ============"
echo.
call :ColorText 0a "==================================================================="
echo.

pause
exit /b

:ColorText
<nul set /p ".=." > "%~2"
findstr /v /a:%1 /R "^$" "%~2" nul
del "%~2" > nul 2>&1
goto :eof