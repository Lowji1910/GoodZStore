@echo off
echo ========================================
echo   GoodZStore AI Server Launcher
echo ========================================
echo.

REM Check if Python is installed
python --version >nul 2>&1
if errorlevel 1 (
    echo [ERROR] Python is not installed or not in PATH
    echo Please install Python 3.9+ from https://www.python.org/
    pause
    exit /b 1
)

echo [OK] Python found
echo.

REM Check if virtual environment exists
if not exist "venv\" (
    echo [INFO] Creating virtual environment...
    python -m venv venv
    if errorlevel 1 (
        echo [ERROR] Failed to create virtual environment
        pause
        exit /b 1
    )
    echo [OK] Virtual environment created
)

REM Activate virtual environment
echo [INFO] Activating virtual environment...
call venv\Scripts\activate.bat

REM Check if dependencies are installed
echo [INFO] Checking dependencies...
pip show flask >nul 2>&1
if errorlevel 1 (
    echo [INFO] Installing dependencies...
    pip install -r requirements.txt
    if errorlevel 1 (
        echo [ERROR] Failed to install dependencies
        pause
        exit /b 1
    )
    echo [OK] Dependencies installed
) else (
    echo [OK] Dependencies already installed
)

REM Check if shared root .env file exists
if not exist "..\.env" (
    echo [WARNING] ..\.env file not found!
    echo Please create the shared .env file at the repo root before launching
    echo See README.md for details
    pause
    exit /b 1
)

echo [OK] Configuration file found
echo.

REM Start the server
echo ========================================
echo   Starting Flask AI Server...
echo ========================================
echo.
echo Server will run at: http://127.0.0.1:5000
echo Press Ctrl+C to stop the server
echo.

python app.py

REM If server stops
echo.
echo ========================================
echo   Server stopped
echo ========================================
pause
