@echo off
:: Pindah ke folder project
cd /d C:\laragon\www\JababekaBizpark

:: Jalankan command artisan pakai full path PHP
:: Ganti 'php-8.x.x' dengan folder versi php yang Anda pakai di Laragon
C:\laragon\bin\php\php-8.2.29-nts-Win32-vs16-x64\php.exe artisan billing:monthly