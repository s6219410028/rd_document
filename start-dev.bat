@echo off
title RD Document - Dev Server

echo Starting Vite frontend on http://localhost:5173 ...
start "Vite Frontend" cmd /k "cd /d %~dp0frontend && npm run dev"

echo.
echo Vite is starting. Open: http://localhost:5173
echo API will be served by XAMPP Apache at: http://localhost:8000/api
echo.
pause
