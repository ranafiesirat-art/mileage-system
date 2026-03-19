@echo off
title Mileage System - Deploy

echo ===============================
echo MILEAGE SYSTEM DEPLOY
echo ===============================

cd /d C:\xampp_new\htdocs\mileage-system

echo.
set /p msg=Enter commit message:
if "%msg%"=="" set msg=auto update

git add .
git commit -m "%msg%" || echo No changes to commit
git push origin main

echo.
echo ===============================
echo DEPLOY COMPLETE (MILEAGE)
echo ===============================

pause