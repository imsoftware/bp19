<?php 
/**
 * bac3
 * API-MySQLi - Interface to get article count on timeline as json
 * 
 * @package bac3
 * @version v1.0
 * 
 * @param string $topic REQ If total = all items. Multiple topics split by comma.
 * @param number $start REQ Set start date for the query. If not set, current year is used.
 * @param number $end OPT Set end date for the query. If not set, current year is used.
 * @param string $unit OPT Set time unit: Day, Month, Year, Week = D M Y W
 * 
 * @author Marius Müller <mm@imsoftware.de>
 * @license Creative Commons BY-NC-SA
 * @copyright 2019 Marius Müller
 * Contains libraries and code from other sources. Consider individual licenses included.
 */

// Debugging
error_reporting(E_ALL & ~E_NOTICE);
// trigger_error('Debugging works.');

// Monitoring
$starttime = explode(' ', microtime());
$starttime = $starttime[1] + $starttime[0];

// INI REQ
require_once '../baclib.php'; // My lib <3
require_once '../config/config.php'; // Includes config
require_once '../vendor/autoload.php'; // Includes composer
require_once '../simplehtmldom_1_9/simple_html_dom.php'; // Includes html scraper
require_once '../mysqli.php'; // MySQLi

// INI BacLib
$baclib = new BacLib;

// Errors
$errors = 'Log ready. ';

// INI ARGV
if (!empty($argv[1])) {
    parse_str($argv[1], $_GET);
}

/**
 * GET
 */

// header 1
header("Access-Control-Allow-Origin: *");

// topic
if (!empty($_GET['topic'])){
    $topic = preg_replace('/[^\p{L}\p{N},]/u', '', $_GET['topic']);

    // multiple topic(s)
    if (preg_match('/,/', $topic)) {
        $topic_comb = str_replace(',', ' ', $topic);
    }

    // start and end
    if (!empty($_GET['start']) AND !empty($_GET['end'])){

        // filterGetTime
        $time_array = $baclib->filterGetTime(array('start', 'end'), $errors);
        extract($time_array);
        extract($time_array['time_array_results']);
    
    }
    else { // start and/or end empty
        $baclib->throwError(400, 'start and/or end empty. ');
    }

}
else { // topic empty
    $baclib->throwError(400, 'Topic empty. ');
}

/**
 * SQL CALLS
 */

// Prepare sql_topic
if (empty($topic_comb)) {
    $sql_topic = '+' . $topic;
}
else {
    $sql_topic = $topic_comb;
}

// Call
$sql = 
"   SELECT *
    FROM `$dbtable`
    WHERE a_timestamp BETWEEN '$start 00:00:00' AND '$end 23:59:00'
    AND MATCH (a_comb_f) AGAINST ('$sql_topic' IN BOOLEAN MODE)
    ORDER BY a_timestamp
    LIMIT 40
";
$result = mysqli_query($conn, $sql);
$result = mysqli_fetch_all($result, MYSQLI_ASSOC);

mysqli_close($conn);

/**
 * JSON ARRAY ITEMS
 */

$meta_array['items'] = $result;

/**
 * JSON ARRAY META
 */

// topic_comb
if (empty($topic_comb)) {
    $topic_comb = $topic;
}

// Build JSON array meta
$meta_array['meta'] = array(
    'topic' =>       $topic,
    'topic_comb' =>  $topic_comb,
    'start' =>       $start,
    'end' =>         $end,
    'bac_version' => $bac_version,
    'errors' =>      $errors
);

// Header
header('Content-Type: application/json');

// Monitoring
$endtime = explode(' ', microtime());
$endtime = $endtime[1] + $endtime[0];
$time = $endtime - $starttime;
$meta_array['meta']['loadtime'] = round($time, 4);

// Print JSON finally
print_r(json_encode($meta_array, JSON_PRETTY_PRINT));

$stats = dirname(__FILE__) . ', ' . basename(__FILE__) . ', PHP ' . phpversion() . ', TIME ' . round($time, 4) . ', MEM ' . $baclib->formatBytes(memory_get_peak_usage()) . "\n";
file_put_contents('stats.txt', $stats, FILE_APPEND | LOCK_EX);