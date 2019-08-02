# bac3

# Manual

## Requirements

* PHP > 7.1
* MySQL 5.x or MariaDB 5.5+ (InnoDB)
* Tested with Apache 2.4

## Installation

* Clone with git or copy files to your webspace. 
* Create database and tables form dump or manually.
* Edit config/config.php
* Change or remove .htaccess

## Workflow

* Check config.php
* Check feedurls.txt
* Run "crawl.php" in console (params see file)
* Run "senti.php" in console (params see file)
* Goto: view/

## Workflow with Windows

* With Windows you can use the included batch files to run multiple crawler at the same time. 

## More Information

* Goto: http://mariusmüller.de > OpenBallot (Bachelor thesis)

# Structure Overview

## Directories

* api
* cdx
* config
* simplehtmldom_1_9
* sqladmin
* vendor
* view

## php files

* baclib.php
* crawl.php
* index.php
* senti.php

## Others

* Some other directories and files, e.g. .bat, ...