<?php 
/**
 * bac3
 * Crawl - Maybe problems with using both parameter. Use only source or test!
 * 
 * @package bac3
 * @version v1.0
 * 
 * @param string $source If set, wayback machine is used as source. Else live webfeeds are used.
 * @param string $test If set, dbs1test table is used. Else default from config.php.
 * 
 * @author Marius Müller <mm@imsoftware.de>
 * @license Creative Commons BY-NC-SA
 * @copyright 2019 Marius Müller
 * Contains libraries and code from other sources. Consider individual licenses included.
 */

// HTML
echo '<html><head><meta charset="utf-8"></head><body>';
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

// INI ARGV
if (!empty($argv[1])) {
  parse_str($argv[1], $_GET);
}

// Call with test to use testtable
if (isset($_GET['test'])){
	$dbtable = 'dbs1test';
}
echo 'DBTABLE ' . $dbtable . "\n";
if (isset($_GET['source'])){
	echo "SOURCE Wayback\n";
	echo($_GET['source']) . "\n";
}
else {
	echo "SOURCE LIVE\n";
}

// INI SAVE
$feedurlstxt = 'feedurls.txt';
if (isset($_GET['source'])){
    $subtill = date("Y")+1 - $_GET['source'];
    $subfrom = $subtill++;
    $subres = $subfrom . '-' . $subtill;
	define('WBFROM', $subres); 
}
else {
	define('WBFROM', '2019-2020'); // From time (and to, e.g., to parameter needed, e.g. &to=2018)
}
define('WBLIMIT', '100000'); // Number of wbm savepoints
define('WBTIME', '7'); // Skip near savepoints, smaller means bigger steps, 1-14, 10=hours -- 9 for prod 2019 12 29 - 60 60 

// INI PIE
$app_run = time();

// WB STATS
echo "WEBFROM " . WBFROM . ' - WBLIMIT ' . WBLIMIT . ' - WBTIME ' . WBTIME . "\n";

// MySQLi
require_once('mysqli.php');

// CALLS

$feedurls = file($feedurlstxt, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);

foreach ($feedurls as $feedurl) {

	// wb or live
	if (isset($_GET['source'])) {

		echo "\n$feedurl - Wayback\n";

	    echo "saveWbCdx \n" . $feedurl . "\n";
	    $baclib->saveWbCdx($feedurl);
	    echo "\n";

	    echo "genWbFeedUrls \n";
	    $feedurls = $baclib->genWbFeedUrls($feedurl);
		echo "\n";
	}

	foreach ($feedurls as $feedurl) {
		echo("\n");
		if (isset($_GET['source']) == FALSE) {
			if ($feedurlcount >= count($feedurls)) {
				break;
			}
			echo "\n$feedurl - LIVE\n";
		}

		// Youtube
		$pattern = 'youtube.com';
		$pos = strpos($feedurl, $pattern);
		if ($pos !== false) {
			
			echo "YOUTUBE\n";
			
			$videos = $baclib->youtubeCrawl($feedurl);

			// Start inserting items
			foreach ($videos as $runcount => $item) {

				// Check Vars and decode (needed) 
				$id = mysqli_escape_string($conn, $item['link']);
				$title = mysqli_escape_string($conn, strip_tags($item['title']));
				$date = mysqli_escape_string($conn, $item['date']);
				$link = mysqli_escape_string($conn, $item['link']);
				$description = mysqli_escape_string($conn, strip_tags($item['description']));
				$content = mysqli_escape_string($conn, strip_tags($item['description']));
				$timestamp = $baclib->calcTime($date, $app_run);

				$sql = "INSERT IGNORE INTO `$dbname`.`$dbtable` (`db_pid`, `db_timestamp`, `db_source`, `db_run`, `a_id`, `a_title`, `a_date`, `a_link`, `a_desc`, `a_content`, `a_timestamp`) VALUES ('', CURRENT_TIMESTAMP, '$feedurl', '$app_run', '$id', '$title', '$date', '$link', '$description', '$content', '$timestamp');";

				if ($conn->query($sql) === TRUE) {
					echo $runcount . ' ';
				} 
				else {
					echo '<h1>Error: ' . $conn->error . "\nSQL:" . $sql;
				}
			}
		}
		else {
			echo "RSS/Atom\n";

			// Start simplepie
			$feed = new SimplePie();
			$feed->set_feed_url($feedurl);
			$feed->enable_cache(false);
			// $feed->strip_htmltags(array_merge($feed->strip_htmltags, array('h1', 'p', 'a', 'img', 'br', 'ul', 'li')));

			$feed->init();

			if ($feed->error()) {
				echo '<h1>' . $feed->error();
			}

			// Start inserting items
			foreach ($feed->get_items() as $runcount => $item) {

				// If there's no item date, use lastbuilddate, e.g. for epoch
				if (empty($item->get_date()) AND !isset($_GET['source'])) {
					$lastbuilddate = $feed->get_feed_tags('', 'channel');
					$lastbuilddate = $lastbuilddate[0]['child']['']['lastBuildDate']['0']['data'];
					echo("\n lastbuild1 $lastbuilddate");
					$feeddate = strtotime($lastbuilddate);
					$feeddate = date('Y-m-d H:i:s', $feeddate);
					echo("\n feeddate $feeddate");
				}
				elseif (empty($item->get_date()) AND isset($_GET['source'])) {
					$lastbuilddate = $feed->get_feed_tags('', 'channel');
					$lastbuilddate = $lastbuilddate[0]['child']['']['lastBuildDate']['0']['data'];
					// Replace units
					$timel2 = array('Mo','Di', 'Mi', 'Do', 'Fr','Sa', 'So');
					$timel3 = array('Mon','Tue', 'Wed', 'Thu', 'Fri','Sat', 'Sun'); 
					$timel = str_replace($timel2, $timel3, $lastbuilddate);
					$timel = strtotime($timel);
					$feeddate = date('Y-m-d H:i:s', $timel);
				}
				else {
					$feeddate = $item->get_date('Y-m-d H:i:s');
				}

				// Check Vars and decode (needed) 
				$id = mysqli_escape_string($conn, $item->get_id());
				$title = mysqli_escape_string($conn, $item->get_title());
				$date = mysqli_escape_string($conn, $item->get_gmdate());
				$link = mysqli_escape_string($conn, $item->get_link());
				$description = mysqli_escape_string($conn, $item->get_description());
				$content = mysqli_escape_string($conn, $item->get_content());
				$timestamp = mysqli_escape_string($conn, $feeddate);

				$sql = "INSERT IGNORE INTO `$dbname`.`$dbtable` (`db_pid`, `db_timestamp`, `db_source`, `db_run`, `a_id`, `a_title`, `a_date`, `a_link`, `a_desc`, `a_content`, `a_timestamp`) VALUES ('', CURRENT_TIMESTAMP, '$feedurl', '$app_run', '$id', '$title', '$date', '$link', '$description', '$content', '$timestamp');";

				if ($conn->query($sql) === TRUE) {
					echo $runcount . ' ';
				} 
				else {
					echo '<h1>Error: ' . $conn->error . "\nSQL:" . $sql;
				}
			}
		}
		$feedurlcount++;
	}
}
$conn->close();

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