@echo on

echo CRAWL all
echo Run crawl.php
echo Check crawl.php source parameter before!

pause

start " 0" "C:\xampp\php\php.exe" crawl.php source
start " 0" "C:\xampp\php\php.exe" crawl.php source=0
start " 1" "C:\xampp\php\php.exe" crawl.php source=1
start " 2" "C:\xampp\php\php.exe" crawl.php source=2
start " 3" "C:\xampp\php\php.exe" crawl.php source=3
start " 4" "C:\xampp\php\php.exe" crawl.php source=4
start " 5" "C:\xampp\php\php.exe" crawl.php source=5
start " 6" "C:\xampp\php\php.exe" crawl.php source=6
start " 7" "C:\xampp\php\php.exe" crawl.php source=7
start " 8" "C:\xampp\php\php.exe" crawl.php source=8
start " 9" "C:\xampp\php\php.exe" crawl.php source=9
start "10" "C:\xampp\php\php.exe" crawl.php source=10
start "11" "C:\xampp\php\php.exe" crawl.php source=11
start "12" "C:\xampp\php\php.exe" crawl.php source=12
start "13" "C:\xampp\php\php.exe" crawl.php source=13
start "14" "C:\xampp\php\php.exe" crawl.php source=14
start "15" "C:\xampp\php\php.exe" crawl.php source=15

pause

echo SENTI
echo Run senti.php
echo Check senti.php $db_pid_max before!
echo Check senti.php $db_pid_max before! Runs for all (billions) when not changed.

start "senti" "C:\xampp\php\php.exe" senti.php

pause