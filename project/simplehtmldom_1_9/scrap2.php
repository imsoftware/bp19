<?php

require('simple_html_dom.php');

// Create DOM from URL or file
$html = file_get_html('https://www.youtube.com/feed/trending?gl=DE');

// creating an array of elements
$videos = [];

// Find top ten videos
$i = 1;
foreach ($html->find('li.expanded-shelf-content-item-wrapper') as $video) {
        if ($i > 3) {
                break;
        }

        // Find item link element
        $videoDetails = $video->find('a.yt-uix-tile-link', 0);
        echo "$videoDetails <br>";

        // get title attribute
        $videoTitle = $videoDetails->title;

        // get href attribute
        $videoUrl = 'https://youtube.com' . $videoDetails->href;
        echo "Url $videoUrl <br>";

        // Find item link element
        $videoDesc = $video->find('a.yt-uix-tile-subtext', 0);
        echo "Sub $videoDesc <br>";

        // Find item link element
        $videoDetails2 = $video->find('div.yt-lockup-description', 0);
        echo "$videoDetails2 <br>";

        $i++;
}

//var_dump($html->find('li.expanded-shelf-content-item-wrapper'));

?>
<html>
<pre>
<?php
//var_dump($videos);
//var_dump($videoDetails);
?>
</pre>
</html>