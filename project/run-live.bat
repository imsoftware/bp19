@echo on

echo CRAWL live
echo Run crawl.php
echo Check crawl.php source parameter before!

pause

start " 0" "C:\xampp\php\php.exe" crawl.php

pause

echo SENTI
echo Run senti.php
echo Check senti.php $db_pid_max before! Runs for all (billions) when not changed.

start "senti" "C:\xampp\php\php.exe" senti.php

pause