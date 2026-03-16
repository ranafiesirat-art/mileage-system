@echo off
title Mileage System - One Click Deploy

echo ===============================
echo MILEAGE SYSTEM DEPLOY
echo ===============================

cd /d C:\xampp_new\htdocs\mileage-system

echo.
set /p msg=Enter commit message: 

git add .
git commit -m "%msg%"
git push

echo.
echo ===============================
echo DEPLOY COMPLETE
echo ===============================
pause