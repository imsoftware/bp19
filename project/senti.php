<?php 
/**
 * bac3
 * Senti - Run sentiment analyse on db. 
 * 
 * @package bac3
 * @version v1.0
 * 
 * @param int $db_pid_max Set it to test or run at all.
 *
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

// MySQLi
require_once('mysqli.php');

$sql = mysqli_query($conn, "SELECT * FROM `$dbname`.`$dbtable` WHERE `db_pid`=$db_pid");

// CALLS

// Max db_pid (index)
$sqlcount = mysqli_query($conn, "SELECT max(db_pid) FROM `$dbname`.`$dbtable`");
$sqlcount = mysqli_fetch_array($sqlcount);
echo "\n$sqlcount\n";
$db_pid_max = $sqlcount[0];
// $db_pid_max = 10;

// for($db_pid = 1; $db_pid < $db_pid_max; $db_pid++) {
for($db_pid = $db_pid_max; $db_pid > 0; $db_pid--) {

    echo("$db_pid - ");

    $sql = mysqli_query($conn, "SELECT * FROM `$dbname`.`$dbtable` WHERE `db_pid`=$db_pid");

    if($data = mysqli_fetch_array($sql))
    {

        // Senti
        $senti = new aheissenberger\sentimentanalyser\Sentiment;
        $a_title_senti = $senti->analyse($data['a_title']);
        $a_desc_senti = $senti->analyse($data['a_desc']);
        $a_comb_senti = $data['a_title'] . ' ' . $data['a_desc'];
        $a_comb_senti = $senti->analyse($a_comb_senti);
        echo "Title $a_title_senti x Desc $a_desc_senti x Comb $a_comb_senti ";

        // Filter
        $delexp = array(
            ':','[mehr]', '+++', '?', '!',';', ',', '.', '"', '- ', ' -', '   ', '  ', "'", '<br>', '<br/>', '<em>In eigener Sache</em><br><a href="https://steadyhq.com/rivva">Hilfe benötigt: <strong>Bitte unterstützt Rivva über Steady</strong></a>', '<i>', '</i>', 'In eigener Sache', 'Bitte unterstützt Rivva über Steady', '<br>', '</br>'
        );
        $a_title_f = str_replace($delexp, ' ', $data['a_title']);
        $a_desc_f = str_replace($delexp, ' ', $data['a_desc']);

        // a_comb_f
        $a_comb_f = "$a_title_f $a_desc_f";

        $a_comb_f  = str_replace($delexp, ' ', $a_comb_f);

        // echo("$a_comb_f\n");

        $sqlup="
            UPDATE `$dbname`.`$dbtable` 
            SET 
                `a_title_f`=    '$a_title_f',     `a_desc_f`=    '$a_desc_f',     `a_comb_f`=    '$a_comb_f',
                `a_title_senti`='$a_title_senti', `a_desc_senti`='$a_desc_senti', `a_comb_senti`='$a_comb_senti',
                `edit`='1'
            WHERE `db_pid`=$db_pid";
        if(mysqli_query($conn, $sqlup))
        {
            $i++;
            echo "\n $i / $db_pid  ";
        }
    }
    // Linebreak if after two results.
    echo ' ';
    if ($db_pid % 2 == 0)
    {
        echo " \n";
    }
}
echo "\n";


echo 'DBTABLE ' . $dbtable . "\n";

$conn->close();	

// Monitoring
$endtime = explode(' ', microtime());
$endtime = $endtime[1] + $endtime[0];
$time = $endtime - $starttime;

echo 'PHP' . phpversion() . "\n";
echo 'TIME ' . $time . "\n";
echo 'MEM ' . $baclib->formatBytes(memory_get_peak_usage()) . "\n";
echo "--------------------------------------------------------------------------------\n";
echo '</pre>';
echo '</html>';