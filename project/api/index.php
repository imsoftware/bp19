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

    // total
    if ($topic === 'total') { // all items
        $thetotal = '#';
    }
    else { // single item
        $thetotal = '';
    }

    // topic_b and topic_c
    if (!empty($_GET['topic_b'])) {
        $topic_b = preg_replace('/[^\p{L}\p{N} ]/u', '', $_GET['topic_b']);
        $topic_comb .= $topic . ' ' . $topic_b;
        if (!empty($_GET['topic_c'])) {
            $topic_c = preg_replace('/[^\p{L}\p{N} ]/u', '', $_GET['topic_c']);
            $topic_comb .= ' ' . $topic_c;
        }
    }
    else {
        $topic_comb = false;
    }

    // multiple topic(s)
    if (preg_match('/,/', $topic)) {
        $topic_comb = str_replace(',', ' ', $topic);
    }

    // filterGetTime
    $time_array = $baclib->filterGetTime(array('start', 'end'), $errors);
    extract($time_array);
    extract($time_array['time_array_results']);

    // unit
    if (!empty($_GET['unit'])) {
        if ($_GET['unit'] == 'D' OR 
            $_GET['unit'] == 'M' OR 
            $_GET['unit'] == 'Y' OR 
            $_GET['unit'] == 'W') 
            {
            $timeunit = $_GET['unit'];
        }
        else { // unit wrong
            $baclib->throwError(400, 'Unit wrong. ');
        }
    }
    else { // unit empty
        $timeunit = 'Y';
        $errors .= 'Unit empty, set ' . $timeunit . '. ';
    }
}
else { // topic empty
    $baclib->throwError(400, 'Topic empty. ');
}

// Timeunit and timeformat
$timeunit_array = $baclib->formatTimeunit($timeunit, $errors);
extract($timeunit_array);

// Prepare sql_topic
if (empty($topic_comb)) {
    $sql_topic = '+' . $topic;
}
else {
    $sql_topic = $topic_comb;
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

// Call topic
$sql = 
"   SELECT
        DATE_FORMAT(a_timestamp, '$thetimeunit') AS date_array,
        COUNT(a_timestamp) AS count_array,
        SUM(a_comb_senti) AS senti,
        a_comb_f
    FROM `$dbtable`
    WHERE a_timestamp BETWEEN '$start 00:00:00' AND '$end 23:59:00'
    $thetotal AND MATCH (a_comb_f) AGAINST ('$sql_topic' IN BOOLEAN MODE)
    GROUP BY date_array
    ORDER BY date_array
";
$result = mysqli_query($conn, $sql);
$result = mysqli_fetch_all($result, MYSQLI_ASSOC);

// Call total
$sql_total = 
"   SELECT
        DATE_FORMAT(a_timestamp, '$thetimeunit') AS date_array,
        COUNT(a_timestamp) AS count_array,
        SUM(a_comb_senti) AS senti
    FROM `$dbtable`
    WHERE a_timestamp BETWEEN '$start 00:00:00' AND '$end 23:59:00'
    GROUP BY date_array
    ORDER BY date_array
";
$result_total = mysqli_query($conn, $sql_total);
$result_total = mysqli_fetch_all($result_total, MYSQLI_ASSOC);

// Call events
$sql_events = 
"   SELECT 
        DATE_FORMAT(e_timestamp, '$thetimeunit') AS e_date,
        COUNT(e_timestamp) AS e_count,
        e_title,
        e_desc,
        e_type,
        e_pid
    FROM `$dbtable_events`
    WHERE e_timestamp BETWEEN '$start 00:00:00' AND '$end 23:59:00'
    AND MATCH (e_desc) AGAINST ('$sql_topic' IN BOOLEAN MODE)
    GROUP BY e_date
    ORDER BY e_date
";
$result_events = mysqli_query($conn, $sql_events);
$result_events = mysqli_fetch_all($result_events, MYSQLI_ASSOC);

mysqli_close($conn);

/**
 * JSON ARRAY RESULT AND RESULT_TOTAL
 */

// Timeunits
$timeunits = $baclib->genTimeUnits($start, $end, $timeunit, $timeformat);
$timeunits_count = count($timeunits);

$sql_results = array('result' => $result, 'total' => $result_total);

foreach ($sql_results as $sql_key => $sql_result) {
    
    // Declare all arrays new to clear
    $xyarray = array();
    $results_date = array();
    $xydif = array();
    $date_array = array();
    $count_array = array();
    $median = array();
    
    // Compare arrays to get indexes
    $results_date = array_column($sql_result, 'date_array');
    $xydif = array_intersect($timeunits, $results_date);

    // Create mixed array
    $timeunits_index = 0;
    foreach ($timeunits as $key => $value) {
        if ($xydif[$key]){
            $count_array = $sql_result[$timeunits_index]['count_array'];
            $senti_array = $sql_result[$timeunits_index]['senti'];
            $timeunits_index++;
        }
        else {
            $count_array = '0';
            $senti_array = '0';
        }
        $xyarray[] = array(
            'date_array' => $value, 
            'count_array' => $count_array, 
            'senti_array' => $senti_array,
            'senti_mean_array' => $baclib->calcSentiRel($senti_array, $count_array)
        );
    }

    // Calc
    $date_array = array_column($xyarray, 'date_array');
    $count_array = array_column($xyarray, 'count_array');
    $senti_array = array_column($xyarray, 'senti_array');
    $senti_mean_array = array_column($xyarray, 'senti_mean_array');
    $count = count($results_date);
    $sum = array_sum($count_array);
    $mean = round(($sum/$timeunits_count), 2);
    $median = $baclib->calcMedian($count_array);
    $min = min($count_array);
    $max = max($count_array);
    $senti = array_sum($senti_array);
    $senti_array_mean = (array_sum($senti_array)/$timeunits_count);
    $senti_mean_array_mean = (array_sum($senti_mean_array)/$timeunits_count);

    // variance
    $variance = $baclib->calcVariance($count_array);
    $deviation = $baclib->calcDeviation($variance);
    $varianceB = $baclib->calcBVariance($count_array);
    $deviationB = $baclib->calcDeviation($varianceB);

    // Build JSON array results
    $meta_array[$sql_key] = array(
        'date_array' => $date_array,
        'count_array' => $count_array,
        'count' => $count,
        'sum' => $sum,
        'mean' => $mean,
        'median' => $median,
        'min' => $min,
        'max' => $max,
        'senti' => $senti,
        'senti_array' => $senti_array,
        'senti_array_mean' => $senti_array_mean,
        'senti_mean_array' => $senti_mean_array,
        'senti_mean_array_mean' => $senti_mean_array_mean,
        'variance' => $variance,
        'deviation' => $deviation,
        'varianceB' => $varianceB,
        'deviationB' => $deviationB
    );
}

// percent_array
$percent_array = $baclib->calcPercentage($meta_array['result']['count_array'], $meta_array['total']['count_array']);
$meta_array['result']['percent_array'] = $percent_array;
$meta_array['result']['percent_mean'] = (array_sum($percent_array)/$timeunits_count);

/**
 * JSON ARRAY EVENTS
 */

$events_array = $baclib->mergeArraysOnTime($result_events, $timeunits);
$meta_array['events'] = array(
    'events_array' => $events_array,
    'events_full_array' => $result_events
);

/**
 * JSON ARRAY META
 */

// topic_comb
if (empty($topic_comb)) {
    $topic_comb = $topic;
}
// topic_comb_array
$topic_comb_array = preg_split('/[ ]/', $topic_comb, null, PREG_SPLIT_NO_EMPTY);

// Build JSON array meta
$meta_array['meta'] = array(
    'topic' =>            $topic,
    'topic_comb' =>       $topic_comb,
    'topic_comb_array' => $topic_comb_array,
    'start' =>            $start,
    'end' =>              $end,
    'timeunits_count' =>  $timeunits_count,
    'timeunit' =>         $timeunit,
    'bac_version' =>      $bac_version,
    'errors' =>           $errors
);

// save
if ($_GET['save'] == '1') {
    $stats = array(
        'topic' => $meta_array['meta']['topic_comb_array'][0],
        'start' => $meta_array['meta']['start'],
        'end' => $meta_array['meta']['end'],
        'sum' => $meta_array['result']['sum'],
        'count' => $meta_array['total']['count'],
        'mean' => $meta_array['result']['mean'],
        'median' => $meta_array['result']['median'],
        'min' => $meta_array['result']['min'],
        'max' => $meta_array['result']['max'],
        'senti' => $meta_array['result']['senti'],
        'senti_array_mean' => $meta_array['result']['senti_array_mean'],
        'senti_mean_array_mean' => $meta_array['result']['senti_mean_array_mean'],
        'percent_mean' => $meta_array['result']['percent_mean'],
        'variance' => $meta_array['result']['variance'],
        'deviation' => $meta_array['result']['deviation'],
        'varianceB' => $meta_array['result']['varianceB'],
        'deviationB' => $meta_array['result']['deviationB']
    );
    $fp = fopen('export.csv', 'a');
    fputcsv($fp, $stats);
    fclose($fp);
    $fpt = fopen('export/export-' . $meta_array['meta']['topic_comb_array'][0] . '.csv', 'w');
    fputcsv($fpt, $stats);
    fclose($fpt);
}

// Header 2
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