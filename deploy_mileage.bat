@echo off
title Mileage System - Deploy

echo ===============================
echo MILEAGE SYSTEM DEPLOY
echo ===============================

cd /d C:\xampp_new\htdocs\mileage-system

echo.
set /p msg=Enter commit message: 

git add .
git commit -m "%msg%"
git push origin main

echo.
echo ===============================
echo DEPLOY COMPLETE (MILEAGE)
echo ===============================

pause