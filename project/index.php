<?php 
/**
 * bac3
 * Index
 * 
 * @package bac3
 * @version v1.0
 * 
 * 
 * 
 * 
 * @author Marius Müller <mm@imsoftware.de>
 * @license Creative Commons BY-NC-SA
 * @copyright 2019 Marius Müller
 * Contains libraries and code from other sources. Consider individual licenses included.
 */

// HTML
echo '<html>';
echo '<pre>';
echo dirname(__FILE__) . ' - ' . basename(__FILE__) . "\n";

// Debugging
error_reporting(E_ALL & ~E_NOTICE);
trigger_error('Debugging works.');

// Monitoring
$starttime = explode(' ', microtime());
$starttime = $starttime[1] + $starttime[0];

// INI REQ
require_once __DIR__ . '/baclib.php'; // My lib <3
require_once __DIR__ . '/config/config.php'; // Includes config
require_once __DIR__ . '/vendor/autoload.php'; // Includes composer
require_once __DIR__ . '/simplehtmldom_1_9/simple_html_dom.php'; // Includes html scraper

// INI BacLib
$baclib = new BacLib;

$curdir = opendir(".");

while($entryName = readdir($curdir)) {
	$dirArray[] = $entryName;
}

closedir($curdir);

$indexcount	= count($dirArray);
echo ("All files: $indexcount<br>\n");
sort($dirArray);

echo("<table>\n");
echo("<tr><th>Name</th><th>Type</th><th>Size</th></tr>\n");
// loop through the array of files and echo them all
for($index=0; $index < $indexcount; $index++) {
    echo("<tr><td><a href=\"$dirArray[$index]\">$dirArray[$index]</a></td>");
    echo("<td>");
    echo(filetype($dirArray[$index]));
    echo("</td>");
    echo("<td>");
    echo(filesize($dirArray[$index]));
    echo("</td>");
    echo("</tr>\n");
}
echo("</table>\n");

// Monitoring
$endtime = explode(' ', microtime());
$endtime = $endtime[1] + $endtime[0];
$time = $endtime - $starttime;

echo 'PHP ' . phpversion() . " \n";
echo 'TIME ' . $time . " \n";
echo 'MEM ' . $baclib->formatBytes(memory_get_peak_usage()) . " \n";
echo "-------------------------------------------------------------------------------- \n";
echo '</pre>';
echo '</html>';