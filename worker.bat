@echo off
:: Pindah ke folder project
cd /d C:\laragon\www\JababekaBizpark

:: Jalankan worker
C:\laragon\bin\php\php-8.2.29-nts-Win32-vs16-x64\php.exe artisan queue:work --tries=3 --timeout=90