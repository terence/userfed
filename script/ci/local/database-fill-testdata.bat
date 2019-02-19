@echo off
cd ..\..\..
SET APPLICATION_ENV=development
REM php code\public_html\index.php mock data --orgcount=20 --appcount=30
REM php code\public_html\index.php mock data --xss --orgcount=20 --appcount=30
REM php code\public_html\index.php mock user --usercount=20
REM php code\public_html\index.php mock user --xss --usercount=20
echo Generate data
php code\public_html\index.php mock user --usercount=20
pause