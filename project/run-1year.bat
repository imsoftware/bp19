@echo on

echo CRAWL 1 Year
echo Run crawl.php
echo Check crawl.php source parameter before!

pause

start " 0" "C:\xampp\php\php.exe" crawl.php source
start " 0" "C:\xampp\php\php.exe" crawl.php source=0
start " 0" "C:\xampp\php\php.exe" crawl.php source=1

pause

echo SENTI
echo Run senti.php
echo Check senti.php $db_pid_max before! Runs for all (billions) when not changed.

start "senti" "C:\xampp\php\php.exe" senti.php

pause