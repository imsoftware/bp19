<?php 
/**
 * bac3
 * My lib <3
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

class BacLib
{
    /**
     * cdxFileName
     * Build the filename
     * 
     * @param string $url
     * @return string $cdxfilename
     **/
    function cdxFileName (string $url) {
        $cdxfilename = 'cdx/' . parse_url($url, PHP_URL_HOST) . '-' . WBFROM . '.txt';
        return $cdxfilename;
    }
    
    /**
     * saveWbCdx
     * Request wayback cdx file and save file
     * 
     * @param string $url
     * @return void
     **/
    function saveWbCdx(string $url) {
        $wbcdxurl = 
            "https://web.archive.org/cdx/search/cdx?url=" . $url . 
            "&from=" . WBFROM . "&filter=statuscode:200&collapse=timestamp:" . WBTIME . "&limit=" . WBLIMIT . "&fl=timestamp";
        $ch = curl_init($wbcdxurl);
        $fo = fopen($this->cdxFileName($url), 'w');

        curl_setopt($ch, CURLOPT_FILE, $fo);
        curl_setopt($ch, CURLOPT_HEADER, 0);

        curl_exec($ch);
        curl_close($ch);
        fclose($fo);

        echo $wbcdxurl . "\n";
    }

    /**
     * genWbFeedUrls
     * Generate WbFeedUrls from wb savepoints
     *
     * @param string $feedurl
     * @return string $wbfeedurls
     **/
    function genWbFeedUrls(string $feedurl) {
        $cdxfile = $this->cdxFileName($feedurl);
        $fo = fopen($cdxfile, "r");
        $wbtimestamps = file($cdxfile, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
        $wbfeedurls = array();
        foreach ($wbtimestamps as $wbtimestamp) {
            $wbfeedurl = "https://web.archive.org/web/" . $wbtimestamp . "id_/" . $feedurl;
            $wbfeedurls[] = $wbfeedurl; 
            echo $wbfeedurl . "\n";
        }
        fclose($fo);
        return $wbfeedurls;
    }

    /**
     * readCsv
     * CSV to array
     * 
     * @param string $csvfile
     * @return string $rows
     **/
    function readCsv(string $csvfile){
        $file_handle = fopen($csvfile, 'r');
        while (!feof($file_handle) ) {
            $rows[] = fgetcsv($file_handle, 1024);
        }
        fclose($file_handle);
        return $rows;
    }

    /**
     * calcTime
     * German time unit to sql timestamp (for youtube)
     * 
     * @param string $timestr
     * @return string $timestr
     **/
    function calcTime($timestr, $timeref)
    {
        $timeDE = array('vor ', 'Minuten', 'Stunde', 'Stunden', 'Tagen', 'Woche', 'Wochen', ' gestreamt', 'Streamed ');
        $timeEN = array('-',    'minutes', 'hour',   'hours',   'days',  'week',  'weeks',  '',           ''); 
        // Replace units, add minus, remove streams
        $timestr = str_replace($timeDE, $timeEN, $timestr);
        $timestr = date('Y-m-d H:i:s', strtotime($timestr, $timeref));
        return $timestr;
    }

    /**
     * youtubeCrawl
     * Crawl youtube with scraper
     * 
     * @param string $feedurl
     * @return array $videos
     **/
    function youtubeCrawl(string $feedurl)
    {
        $html = file_get_html($feedurl);
        $i = 1;
        foreach ($html->find('li.expanded-shelf-content-item-wrapper') as $video) {
            if ($i > 80) {
                break;
            }
            $videoDetails = $video->find('a.yt-uix-tile-link', 0);
            $videoTitle = $videoDetails->title;
            $videoUrl = 'https://youtube.com' . $videoDetails->href;
            $videoDesc = $video->find('div.yt-lockup-description', 0);
            $videoMeta = $video->find('ul.yt-lockup-meta-info', 0);
            $videos[] = [
                'title' => $videoTitle,
                'link' => $videoUrl,
                'description' => $videoDesc->innertext,
                'date' => $videoMeta->children(0)->innertext,
                'views' => $videoMeta->children(1)->innertext
            ];
            $i++;
        }
        return $videos;
    }

    /**
     * genTimeUnits
     * Generate timeunits array for x-axis of charts
     * 
     * @param string $start
     * @param string $end
     * @param string $timeunit
     * @param string $timeformat
     * @return array $timeunits
     **/
    function genTimeUnits($start, $end, $timeunit, $timeformat = 'Y-m-d') {
        $timeunits = array();

        $interval = new DateInterval('P1' . $timeunit);

        $realEnd = new DateTime($end);
        if ($timeunit == 'D') {
            $realEnd->add($interval);
        }

        $period = new DatePeriod(new DateTime($start), $interval, $realEnd);

        foreach($period as $date) {         
            $timeunits[] = $date->format($timeformat);
        }
        return $timeunits;
    }

    /**
     * formatBytes
     * Format bytes for e.g. memory.
     *
     * @param [type] $bytes
     * @param integer $precision
     * @return void
     **/
    function formatBytes($bytes, int $precision = 2) {
        $units = array('b', 'kb', 'mb', 'gb');
        $bytes = max($bytes, 0);
        $pow = floor(($bytes ? log($bytes) : 0) / log(1024));
        $pow = min($pow, count($units) - 1);
        $bytes /= (1 << (10 * $pow));
        return round($bytes, $precision) . ' ' . $units[$pow];
    }

    /**
     * throwError
     * Set http status code, throw error and die.
     * 
     * @param integer $errorcode e.g. 400 Bad Request, 500 etc.
     **/
    function throwError(int $errorcode = 500, string $desc = '')
    {
        http_response_code($errorcode);
        die('Error ' . $errorcode . ' ' . $desc);
    }

    /**
     * calcMedian
     * Calc Median
     * 
     * @param array $numbers
     * @return string $median
     **/
    function calcMedian(array $numbers)
    {
        sort($numbers);
        $count = count($numbers); // cache the count
        $index = floor($count/2); // cache the index
        if (!$count) {
            $median =  "no values";
        } elseif ($count & 1) { // count is odd
            $median = $numbers[$index];
        } else { // count is even
            $median = ($numbers[$index-1] + $numbers[$index]) / 2;
        }
        return $median;
    }

    /**
     * formatTimeunit
     * Format Y / M / D / W for SQL etc.
     *
     * @param string $timeunit
     * @param string $errors
     * @return array 
     **/
    function formatTimeunit(string $timeunit, $errors = false)
    {
        switch ($timeunit) {
            case 'D': // Day
                $thetimeunit = '%Y-%m-%d';
                $timeformat = 'Y-m-d';
                break;
            case 'M': // Month
                $thetimeunit = '%Y-%m';
                $timeformat = 'Y-m';
                break;
            case 'Y': // Year
                $thetimeunit = '%Y';
                $timeformat = 'Y';
                break;
            case 'W': // Week (ISO-8601)
                $thetimeunit = '%Y-%v';
                $timeformat = 'o-W';
                break;
            default:
                $thetimeunit = '%Y';
                $timeformat = 'Y';
                $errors .= 'Timeunit wrong. ';
                break;
        }
        return array('thetimeunit' => $thetimeunit, 'timeformat' => $timeformat, 'errors' => $errors);
    }

    /**
     * calcPercentage
     * Calculates a third array with percentage value from two arrays
     *
     * @param array $prozentwert
     * @param array $grundwert
     * @return array $percentage
     **/
    function calcPercentage(array $prozentwert, array $grundwert)
    {
        $percentage = array();
        foreach ($grundwert as $key => $value) {
            if (empty($grundwert[$key])) {
                $percentage[] = '0';
            }
            else {
                $percentage[] = ( $prozentwert[$key] / $grundwert[$key] * 100);
            }
        }
        return $percentage;
    }

    /**
     * calcRelSenti
     * Calculates relative sentiment based on arrays
     *
     * @param array $count_array
     * @param array $senti_array
     * @return array $relSenti
     **/
    function calcRelSenti(array $senti_array, array $count_array)
    {
        $relSenti = array();
        foreach ($count_array as $key => $value) {
            if (empty($count_array[$key])) {
                $relSenti[] = '0';
            }
            else {
                $relSenti[] = ( $senti_array[$key] / $count_array[$key]);
            }
        }
        return $relSenti;
    }

    /**
     * calcSentiRel
     * Calculates relative sentiment based on floats (not arrays)
     *
     * @param float $senti_array
     * @param $count_array
     * @return float $senti_mean_array
     **/
    function calcSentiRel(float $senti_array, $count_array)
    {
        if (!empty($count_array)) {
            $senti_mean_array = ($senti_array/$count_array);
        }
        else {
            $senti_mean_array = 0;
        }
        return $senti_mean_array;
    }

    /**
     * filterGetTime
     * Filter $_GET['start'] for security reasons
     *
     * @param array $gets
     * @param string $errors
     * @return array
     **/
    function filterGetTime(array $gets, $errors = false)
    {
        $time_array = array();
        foreach ($gets as $get) {
            if (!empty($_GET[$get])) {
                $time_array[$get] = $_GET[$get];
            }
            else { // set date if empty
                if ($get == 'start') { // set special date for start
                    $time_array['start'] = date('Y') . '-01-01';
                    $errors .= 'start empty, set ' . $time_array['start'] . '. ';
                }
                else { // set date = now
                    $time_array[$get] = date('Y-m-d');
                    $errors .= $get . ' empty, set ' . $time_array[$get] . '. ';
                }
            }
        }
        foreach ($time_array as $key => $value) {
            $value = preg_replace('/[^\d-]+/', '', $value); // filter $value for numbers an minus (potential typos)
            if (!preg_match('/^((((19|[2-9]\d)\d{2})\-(0[13578]|1[02])\-(0[1-9]|[12]\d|3[01]))|(((19|[2-9]\d)\d{2})\-(0[13456789]|1[012])\-(0[1-9]|[12]\d|30))|(((19|[2-9]\d)\d{2})\-02\-(0[1-9]|1\d|2[0-8]))|(((1[6-9]|[2-9]\d)(0[48]|[2468][048]|[13579][26])|((16|[2468][048]|[3579][26])00))\-02\-29))$/', $value)) { // match from 1900 to 9999
                $time_array[$key] = date('Y-m-d');
                $errors .= $key . ' wrong. ';
            }
            else {
                $time_array[$key] = $value;
            }
        }
        if ($time_array['end'] <= $time_array['start']) { // start after end
            $this->throwError(400, $errors . 'end <= start. ');
        }
        return array('time_array_results' => $time_array, 'errors' => $errors);
    }

    /**
     * mergeArraysOnTime
     * Merges two arrays on a timeline, defined by timeunits
     *
     * @param array $result_events
     * @param array $timeunits
     * @return array $xyarray
     **/
    function mergeArraysOnTime(array $result_events, array $timeunits)
    {
        // Declare all arrays new to clear
        $xyarray = array();
        $events_date = array();
        $xydif = array();
        $date_array = array();
        $count_array = array();

        // Compare arrays to get indexes
        $events_date = array_column($result_events, 'e_date');
        $xydif = array_intersect($timeunits, $events_date);

        // Create mixed array
        $timeunits_index = 0;
        foreach ($timeunits as $key => $value) {
            if ($xydif[$key]){
                $count_array = $result_events[$timeunits_index]['e_count'];
                $timeunits_index++;
            }
            else {
                $count_array = '0';
            }
            $xyarray[] = $count_array;
        }
        return $xyarray;
    }

    /**
     * calcVariance
     * Calculates variance of an array
     *
     * @param array $arrayOfNumbers
     * @return float
     **/
    function calcVariance(array $arrayOfNumbers)
    {
        $thecount = count($arrayOfNumbers);
        $vmean = array_sum($arrayOfNumbers) / $thecount;
        foreach ($arrayOfNumbers as $item) {
            $variance += pow(abs($item - $vmean), 2);
            // $variance[] = $item . ' ' . pow(abs($item - $vmean), 2);
        }
        $variance = $variance / $thecount;
        return $variance;
    }

    /**
     * calcBVariance
     * Calculates Bessel-variance of an array
     *
     * @param array $arrayOfNumbers
     * @return float
     **/
    function calcBVariance(array $arrayOfNumbers)
    {
        $thecount = count($arrayOfNumbers);
        $vmean = array_sum($arrayOfNumbers) / $thecount;
        foreach ($arrayOfNumbers as $item) {
            $variance += pow(abs($item - $vmean), 2);
            // $variance[] = $item . ' ' . pow(abs($item - $vmean), 2);
        }
        if (($thecount-1) != 0) {
            $variance = $variance / ($thecount-1);
        }
        else {
            $errors .= ' Bessel variance and deviation calc not possible (div0). ';
            $variance = 0;
        }
        return $variance;
    }

    /**
     * calcDeviation
     * Calculates standard devitation from variance
     * 
     * @param float $variance
     * @return float
     **/
    function calcDeviation($variance)
    {
        return (float)sqrt($variance);
    }

}