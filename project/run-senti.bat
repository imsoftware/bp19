@echo on

echo SENTI
echo Run senti.php
echo Check senti.php $db_pid_max before! Runs for all (billions) when not changed.

pause

start "senti" "C:\xampp\php\php.exe" senti.php

pause